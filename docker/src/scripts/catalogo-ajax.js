'use strict';

// Bloques 15 a 19: gestión del catálogo sin recargar la página.
// El navegador solo pide operaciones a api/catalogo.php; PHP comprueba la sesión, el token
// y los datos, guarda en el JSON privado y responde {ok, data} o {ok: false, error}.
// La tabla cambia solo cuando el servidor confirma la operación.

const API = 'api/catalogo.php';
const COLUMNAS = 6;

const formulario = document.getElementById('form-cancion');
const tituloFormulario = document.getElementById('titulo-form');
const botonGuardar = document.getElementById('btn-guardar');
const botonCancelar = document.getElementById('btn-cancelar');
const botonRecargar = document.getElementById('btn-recargar');
const tabla = document.getElementById('tabla-canciones');
const tbody = document.getElementById('cuerpo-canciones');
const mensaje = document.getElementById('mensaje');
// Token CSRF publicado por PHP en la meta; no es una contraseña, pero no se muestra.
const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

let canciones = [];          // Última lista confirmada por el servidor.
let idEditando = null;       // null: modo alta. Un número: id de la ficha que se edita.
let operacionEnCurso = false; // Evita dos peticiones a la vez (doble clic).


// ---------- Mensajes y estado de espera ----------

// Los mensajes permanecen hasta la siguiente acción: no desaparecen solos.
function mostrarMensaje(texto, tipo = 'info') {
    mensaje.textContent = texto;
    mensaje.className = `mensaje ${tipo}`;
}

// Mientras hay una petición pendiente se desactivan todas las acciones que modifican datos.
function bloquear(activo) {
    operacionEnCurso = activo;
    tabla.setAttribute('aria-busy', String(activo));
    botonGuardar.disabled = activo;
    botonCancelar.disabled = activo;
    botonRecargar.disabled = activo;
    tbody.querySelectorAll('button').forEach((boton) => { boton.disabled = activo; });
}

// fetch solo falla por sí mismo si no hay conexión (TypeError). Un 404 o un 500 llegan como respuesta.
function textoError(error, sinConexion) {
    return error instanceof TypeError ? sinConexion : error.message;
}


// ---------- Comunicación con la API ----------

function cancionValida(cancion) {
    return cancion !== null && typeof cancion === 'object'
        && Number.isInteger(cancion.id) && cancion.id > 0
        && typeof cancion.title === 'string'
        && typeof cancion.artist === 'string'
        && typeof cancion.category === 'string'
        && Number.isInteger(cancion.duration)
        && typeof cancion.src === 'string';
}

// Interpreta el cuerpo una sola vez y comprueba las dos condiciones del contrato:
// respuesta.ok (estado HTTP 200-299) y contenido.ok (campo de nuestra API).
async function leerRespuesta(respuesta) {
    let contenido;
    try {
        contenido = await respuesta.json();
    } catch {
        throw new Error(`El servidor ha respondido ${respuesta.status} sin un JSON válido.`);
    }
    if (!respuesta.ok || contenido?.ok !== true) {
        const error = new Error(contenido?.error || `El servidor ha respondido ${respuesta.status}.`);
        error.estado = respuesta.status;
        throw error;
    }
    if (!Array.isArray(contenido.data) || !contenido.data.every(cancionValida)) {
        throw new Error('La respuesta no contiene una lista de canciones válida.');
    }
    return contenido.data;
}

// Envío común de create, update y delete: POST, cuerpo JSON y token en la cabecera.
// La cookie de sesión la añade el navegador porque la petición es al mismo origen.
async function enviarOperacion(accion, datos) {
    const respuesta = await fetch(`${API}?action=${accion}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': token,
        },
        body: JSON.stringify(datos),
    });
    return leerRespuesta(respuesta);
}


// ---------- Consulta y tabla ----------

async function cargarCatalogo() {
    if (operacionEnCurso) return;
    bloquear(true);
    mostrarMensaje('Cargando catálogo…');
    try {
        const respuesta = await fetch(`${API}?action=list`, { headers: { Accept: 'application/json' } });
        canciones = await leerRespuesta(respuesta);
        renderizarCanciones(canciones);
        mostrarMensaje(`Catálogo cargado: ${canciones.length} canciones.`, 'ok');
    } catch (error) {
        mostrarMensaje(textoError(error, 'No hay conexión con el servidor. Comprueba la red y pulsa «Volver a consultar».'), 'error');
    } finally {
        // Se retira el estado de espera tanto si ha funcionado como si no.
        bloquear(false);
    }
}

function duracionLegible(total) {
    return `${Math.floor(total / 60)} minuto(s) y ${total % 60} segundo(s)`;
}

function crearBoton(texto, accion, cancion) {
    const boton = document.createElement('button');
    boton.type = 'button';
    boton.textContent = texto;
    boton.dataset.accion = accion;
    boton.dataset.id = String(cancion.id); // El id identifica la ficha; la posición en la tabla puede cambiar.
    boton.setAttribute('aria-label', `${texto} «${cancion.title}»`);
    boton.disabled = operacionEnCurso;
    return boton;
}

// Reconstruye el tbody con nodos y textContent: un título con < o > se muestra tal cual.
function renderizarCanciones(lista) {
    tbody.replaceChildren();

    if (lista.length === 0) {
        // La fila vacía se crea cada vez; no se guarda una referencia a una fila eliminada.
        const fila = document.createElement('tr');
        const celda = document.createElement('td');
        celda.colSpan = COLUMNAS;
        celda.className = 'vacio';
        celda.textContent = 'Todavía no hay canciones.';
        fila.append(celda);
        tbody.append(fila);
        return;
    }

    for (const cancion of lista) {
        const fila = document.createElement('tr');
        const textos = [String(cancion.id), cancion.title, cancion.artist, cancion.category, duracionLegible(cancion.duration)];
        for (const texto of textos) {
            const celda = document.createElement('td');
            celda.textContent = texto;
            fila.append(celda);
        }
        const acciones = document.createElement('td');
        acciones.append(crearBoton('Editar', 'editar', cancion), ' ', crearBoton('Eliminar', 'eliminar', cancion));
        fila.append(acciones);
        tbody.append(fila);
    }
}


// ---------- Alta y edición ----------

function salirDeEdicion() {
    idEditando = null;
    formulario.reset();
    tituloFormulario.textContent = 'Nueva canción';
    botonGuardar.textContent = 'Añadir canción';
    botonCancelar.hidden = true;
}

function iniciarEdicion(cancion) {
    const campos = formulario.elements;
    idEditando = cancion.id;
    campos.title.value = cancion.title;
    campos.artist.value = cancion.artist;
    campos.category.value = cancion.category;
    campos.duration.value = String(cancion.duration);
    campos.src.value = cancion.src;
    tituloFormulario.textContent = `Editando: ${cancion.title}`;
    botonGuardar.textContent = 'Guardar cambios';
    botonCancelar.hidden = false;
    mostrarMensaje(`Editando la canción ${cancion.id}. Pulsa «Guardar cambios» o «Cancelar edición».`);
    campos.title.focus(); // El foco va al primer campo para seguir con el teclado.
}

// El formulario no cambia de página: preventDefault y fetch.
formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (operacionEnCurso || !formulario.reportValidity()) return;

    const campos = formulario.elements;
    const cancion = {
        title: campos.title.value,
        artist: campos.artist.value,
        category: campos.category.value,
        duration: Number(campos.duration.value), // En JSON la duración viaja como número.
        src: campos.src.value,
    };
    const editando = idEditando !== null;
    if (editando) cancion.id = idEditando;

    bloquear(true);
    mostrarMensaje('Guardando…');
    try {
        canciones = await enviarOperacion(editando ? 'update' : 'create', cancion);
        renderizarCanciones(canciones);
        salirDeEdicion(); // El formulario se vacía solo cuando el servidor ha confirmado el cambio.
        mostrarMensaje(editando ? 'Cambios guardados.' : 'Canción añadida.', 'ok');
    } catch (error) {
        // Se conservan los datos escritos para poder corregirlos.
        let texto = textoError(error, 'No se ha podido confirmar el guardado porque se ha perdido la conexión. '
            + 'Pulsa «Volver a consultar» antes de repetirlo.');
        if (editando && error.estado === 404) {
            texto += ' Puede que se haya eliminado desde otra pestaña: vuelve a consultar la lista.';
        }
        mostrarMensaje(texto, 'error');
    } finally {
        bloquear(false);
    }
});

// Cancelar solo limpia el formulario: no envía ninguna petición.
botonCancelar.addEventListener('click', () => {
    salirDeEdicion();
    mostrarMensaje('Edición cancelada. No se ha enviado ningún cambio.');
    formulario.elements.title.focus();
});


// ---------- Eliminación ----------

async function eliminarCancion(cancion) {
    if (!window.confirm(`¿Eliminar «${cancion.title}» de ${cancion.artist}? El archivo de audio no se borra.`)) {
        mostrarMensaje('Eliminación cancelada. No se ha enviado ningún cambio.');
        return;
    }
    bloquear(true);
    mostrarMensaje('Eliminando…');
    try {
        canciones = await enviarOperacion('delete', { id: cancion.id });
        if (idEditando === cancion.id) salirDeEdicion();
        renderizarCanciones(canciones);
        mostrarMensaje(`Canción «${cancion.title}» eliminada.`, 'ok');
    } catch (error) {
        mostrarMensaje(textoError(error, 'No se ha podido confirmar la eliminación porque se ha perdido la conexión. '
            + 'Pulsa «Volver a consultar» antes de repetirla.'), 'error');
    } finally {
        bloquear(false);
    }
}

// Un solo manejador para los botones de la tabla: localiza la ficha por id, no por fila.
tbody.addEventListener('click', (evento) => {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton || operacionEnCurso) return;

    const id = Number(boton.dataset.id);
    const cancion = Number.isInteger(id) && id > 0 ? canciones.find((c) => c.id === id) : undefined;
    if (!cancion) {
        mostrarMensaje('Esa canción ya no está en la lista. Pulsa «Volver a consultar».', 'error');
        return;
    }
    if (boton.dataset.accion === 'editar') {
        iniciarEdicion(cancion);
    } else {
        eliminarCancion(cancion);
    }
});

botonRecargar.addEventListener('click', cargarCatalogo);

cargarCatalogo();
