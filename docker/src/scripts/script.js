// Bloque 11: reproductor MUSICFY de 0612 conectado a api/canciones.php.
// Se conserva el reproductor original; los cambios son la fuente de datos,
// la adaptación de las claves, los textos con textContent y el ecualizador.

// VARIABLES GLOBALES DEL REPRODUCTOR
let datosMusica = null;      
let sound = null;            
let indiceActual = 0;        
let listaActual = [];        
let isPlaying = false;      
let idActual = null;         // id de la canción cargada, para no perderla al filtrar.


// VARIABLES DE AUDIO Y ECUALIZADOR (WEB AUDIO API)
let contextoAudio = null;         
let analizadorFrecuencias = null; 
let filtroGraves = null;          
let filtroMedios = null;          
let filtroAgudos = null;          
let arrayDatosFrecuencia = null;  
let cadenaCreada = false;         
// Cada elemento <audio> solo puede conectarse una vez a Web Audio: guardamos los ya conectados.
const elementosConectados = new WeakSet();


// DATOS QUE EL SERVIDOR NO GUARDA
// El catálogo PHP no tiene imagen ni nombre visible del género: se eligen aquí.
const CARATULAS = {
    'Death Shock': 'img/death_shock.jpg',
    'P.A.W.N Gang': 'img/pawn_gang.jpg',
    'The Straikerz': 'img/the_straikerz.jpg'
};
const CARATULA_VACIA = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
const NOMBRES_GENERO = {
    'hardcore-uptempo': 'Hardcore Uptempo',
    'musica-urbana': 'Música Urbana',
    'rawstyle': 'Rawstyle',
    'electronica': 'Electrónica',
    'rock': 'Rock',
    'bandas-sonoras': 'Bandas sonoras'
};


// ADAPTACIÓN: el servidor usa title, artist, category, duration y src.
// Las traducimos a los nombres que ya usaba este reproductor.
function adaptarCancion(cancion) {
    return {
        id: cancion.id,
        titulo: cancion.title,
        artista: cancion.artist,
        generoClave: cancion.category,
        genero: NOMBRES_GENERO[cancion.category] || cancion.category,
        imagen: CARATULAS[cancion.artist] || CARATULA_VACIA,
        archivo: cancion.src
    };
}

// Comprueba que una canción recibida tiene los campos que necesita el reproductor.
function cancionValida(cancion) {
    return cancion !== null && typeof cancion === 'object'
        && Number.isInteger(cancion.id)
        && typeof cancion.title === 'string'
        && typeof cancion.artist === 'string'
        && typeof cancion.category === 'string'
        && typeof cancion.src === 'string';
}

// Muestra un mensaje en la lista (error o lista vacía). textContent: nunca se interpreta como HTML.
function mostrarMensajeLista(texto, color) {
    const contenedor = document.getElementById("contenedor-canciones");
    contenedor.replaceChildren();
    const p = document.createElement("p");
    p.className = `${color} text-center py-4`;
    p.textContent = texto;
    contenedor.appendChild(p);
}

// Activa o desactiva los botones de reproducción.
function activarControles(activos) {
    ["btn-prev", "btn-play-pause", "btn-next"].forEach(id => {
        document.getElementById(id).disabled = !activos;
    });
}


// INICIALIZACIÓN AL CARGAR LA PÁGINA
document.addEventListener("DOMContentLoaded", () => {
    inicializarVisualizadorBarras();
    cargarCatalogo();
});

// Pedimos las canciones al servidor. Antes se leía canciones.json en el cliente.
async function cargarCatalogo() {
    try {
        const response = await fetch('./api/canciones.php', { headers: { Accept: 'application/json' } });
        // Si la API responde 401 o 500, no seguimos.
        if (!response.ok) throw new Error(`La API ha respondido con el estado ${response.status}`);
        const data = await response.json();
        // Si no es una lista válida, mostramos un error controlado.
        if (!Array.isArray(data) || !data.every(cancionValida)) {
            throw new Error("La respuesta no es una lista de canciones válida");
        }
        datosMusica = { canciones: data.map(adaptarCancion) };
    } catch (error) {
        console.error("Error al cargar el catálogo:", error);
        document.getElementById("footer-titulo").textContent = "Jukebox no disponible";
        mostrarMensajeLista("No se ha podido cargar el catálogo del servidor.", "text-red-400");
        activarControles(false);
        return;
    }

    listaActual = datosMusica.canciones;
    renderizarFiltros();

    // Si está vacía, desactivamos la reproducción y explicamos el motivo.
    if (listaActual.length === 0) {
        renderizarPlaylist(listaActual);
        document.getElementById("footer-titulo").textContent = "Sin canciones";
        mostrarMensajeLista("Todavía no hay canciones. Añade una desde el catálogo musical.", "text-gray-500");
        activarControles(false);
        return;
    }

    renderizarPlaylist(listaActual);
    activarControles(true);
    cargarCancion(0, false); // El sonido empieza con una acción del usuario.
}



// CONFIGURACIÓN DEL ECUALIZADOR VISUAL
function inicializarVisualizadorBarras() {
    const contenedorVisual = document.getElementById("visualizer");
    contenedorVisual.replaceChildren();
    // Creamos barra Sonido
    for (let i = 0; i < 32; i++) {
        const barra = document.createElement("div");
        barra.className = "flex-1 bg-gradient-to-t from-red-950 via-red-600 to-red-400 rounded h-[10%] transition-all duration-75";
        contenedorVisual.appendChild(barra);
    }
}



// CONEXIÓN DE FILTROS REALES (GRAVES, MEDIOS, AGUDOS)

// Crea una sola vez la cadena filtros → analizador → altavoces.
function crearCadenaAudio() {
    if (cadenaCreada) return;

    const AudioContext = window.AudioContext || window.webkitAudioContext;
    contextoAudio = new AudioContext();

    // Configurar filtro de Graves
    filtroGraves = contextoAudio.createBiquadFilter();
    filtroGraves.type = "lowshelf";
    filtroGraves.frequency.value = 250;
    filtroGraves.gain.value = parseFloat(document.getElementById("val-graves-input")?.value || 0);

    // Configurar filtro de Medios
    filtroMedios = contextoAudio.createBiquadFilter();
    filtroMedios.type = "peaking";
    filtroMedios.frequency.value = 1500;
    filtroMedios.Q.value = 1;
    filtroMedios.gain.value = parseFloat(document.getElementById("val-medios-input")?.value || 0);

    // Configurar filtro de Agudos
    filtroAgudos = contextoAudio.createBiquadFilter();
    filtroAgudos.type = "highshelf";
    filtroAgudos.frequency.value = 4000;
    filtroAgudos.gain.value = parseFloat(document.getElementById("val-agudos-input")?.value || 0);

    // Configurar Analizador para el movimiento de las barras
    analizadorFrecuencias = contextoAudio.createAnalyser();
    analizadorFrecuencias.fftSize = 128;
    arrayDatosFrecuencia = new Uint8Array(analizadorFrecuencias.frequencyBinCount);

    // Conectar la cadena hasta los altavoces
    filtroGraves.connect(filtroMedios);
    filtroMedios.connect(filtroAgudos);
    filtroAgudos.connect(analizadorFrecuencias);
    analizadorFrecuencias.connect(contextoAudio.destination);

    cadenaCreada = true;
}

// Conecta el elemento <audio> de la canción actual a la cadena ya creada.
// Con html5: true, Howler puede usar un elemento distinto en cada canción: si no se
// conectara, el ecualizador dejaría de funcionar al cambiar de pista.
function configurarFiltrosYAnalizador() {
    try {
        if (sound && sound._sounds && sound._sounds[0] && sound._sounds[0]._node) {
            const audioElement = sound._sounds[0]._node;

            crearCadenaAudio();
            if (contextoAudio.state === 'suspended') {
                contextoAudio.resume();
            }

            if (!elementosConectados.has(audioElement)) {
                // Creamos el nodo fuente a partir del elemento HTML5
                const sourceNode = contextoAudio.createMediaElementSource(audioElement);
                sourceNode.connect(filtroGraves);
                elementosConectados.add(audioElement);
            }
        }
    } catch (e) {
        console.error("Error al configurar Web Audio API:", e);
    }
}



// CONTROLES DE REPRODUCCIÓN (CARGAR, PLAY, PAUSE)

function cargarCancion(index, reproducirInmediato = true) {
    // No intentamos crear un Howl si la lista está vacía.
    if (listaActual.length === 0) return;

    if (sound) {
        sound.unload(); // <-- Limpia la canción anterior
    }
    indiceActual = index;
    const cancion = listaActual[index];
    idActual = cancion.id;

    // Actualizamos los textos e imágenes en la interfaz (textContent: texto literal)
    document.getElementById("side-titulo").textContent = cancion.titulo;
    document.getElementById("side-artista").textContent = cancion.artista;
    document.getElementById("side-genero").textContent = cancion.genero;
    document.getElementById("side-img").src = cancion.imagen;

    document.getElementById("footer-titulo").textContent = cancion.titulo;
    document.getElementById("footer-artista").textContent = cancion.artista;
    document.getElementById("footer-img").src = cancion.imagen;
    document.getElementById("seek-bar").value = 0;
    document.getElementById("current-time").textContent = "0:00";

    // Creamos la instancia con Howler.js
    sound = new Howl({
        src: [cancion.archivo],
        html5: true, 
        volume: parseFloat(document.getElementById("volumen-bar").value),
        onplay: () => {
            isPlaying = true;
            document.getElementById("btn-play-pause").textContent = "⏸";

            configurarFiltrosYAnalizador();

            requestAnimationFrame(actualizarReproductorYVisualizador);
        },
        onpause: () => {
            isPlaying = false;
            document.getElementById("btn-play-pause").textContent = "▶";
            resetearVisualizador();
        },
        onend: () => {
            cancionSiguiente(); 
        },
        onload: () => {
            const duracion = sound.duration();
            document.getElementById("total-time").textContent = formatearTiempo(duracion);
        },
        onloaderror: () => {
            document.getElementById("footer-artista").textContent = "No se ha podido cargar el audio";
        }
    });

    if (reproducirInmediato) {
        sound.play();
    } else {
        isPlaying = false;
        document.getElementById("btn-play-pause").textContent = "▶";
    }
    
    marcarCancionActiva(cancion.id);
}

function reproducirCancionIndex(index) {
    cargarCancion(index, true);
}

function alternarPlayPause() {
    if (!sound) return;
    if (isPlaying) {
        sound.pause();
    } else {
        if (contextoAudio && contextoAudio.state === 'suspended') {
            contextoAudio.resume();
        }
        sound.play();
    }
}

function cancionSiguiente() {
    if (listaActual.length === 0) return;
    let nuevoIndice = (indiceActual + 1) % listaActual.length;
    reproducirCancionIndex(nuevoIndice);
}

function cancionAnterior() {
    if (listaActual.length === 0) return;
    let nuevoIndice = (indiceActual - 1 + listaActual.length) % listaActual.length;
    reproducirCancionIndex(nuevoIndice);
}

function cambiarVolumen(valor) {
    if (sound) {
        sound.volume(parseFloat(valor));
    }
}

function cambiarPosicion(valor) {
    if (sound && sound.duration()) {
        let duracion = sound.duration();
        let nuevaPosicion = (valor / 100) * duracion;
        sound.seek(nuevaPosicion);
    }
}



// ANIMACIÓN DEL ESPECTRO Y BARRA DE TIEMPO
function actualizarReproductorYVisualizador() {
    if (isPlaying && sound) {
        // Actualizar barra de progreso general con validación de duración
        let seek = sound.seek();
        let duration = sound.duration();
        if (duration && !isNaN(duration) && duration > 0) {
            let porcentaje = (seek / duration) * 100;
            document.getElementById("seek-bar").value = porcentaje;
            document.getElementById("current-time").textContent = formatearTiempo(seek);
        }

        // Mover las barras del ecualizador al ritmo real de la música
        if (analizadorFrecuencias && arrayDatosFrecuencia) {
            analizadorFrecuencias.getByteFrequencyData(arrayDatosFrecuencia);
            const barrasVisualizador = document.querySelectorAll("#visualizer > div");
            barrasVisualizador.forEach((barra, idx) => {
                let valorFrecuencia = arrayDatosFrecuencia[idx] || 0;
                let alturaPorcentaje = Math.max(8, (valorFrecuencia / 255) * 100);
                barra.style.height = `${alturaPorcentaje}%`;
            });
        }

        requestAnimationFrame(actualizarReproductorYVisualizador);
    }
}

function resetearVisualizador() {
    const barrasVisualizador = document.querySelectorAll("#visualizer > div");
    barrasVisualizador.forEach(barra => {
        barra.style.height = "10%";
    });
}

function formatearTiempo(segundos) {
    let mins = Math.floor(segundos / 60);
    let secs = Math.floor(segundos % 60);
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
}


// LISTA DE REPRODUCCIÓN Y FILTROS

// Crea un botón de filtro por cada género presente en el catálogo del servidor.
function renderizarFiltros() {
    const contenedor = document.getElementById("filtros-genero");
    contenedor.replaceChildren();
    const generos = [...new Set(datosMusica.canciones.map(c => c.generoClave))];

    [["Todos", "Todos"], ...generos.map(g => [g, NOMBRES_GENERO[g] || g])].forEach(([clave, nombre]) => {
        const boton = document.createElement("button");
        boton.type = "button";
        boton.dataset.genero = clave;
        boton.textContent = nombre;
        boton.addEventListener("click", () => filtrarPorGenero(clave));
        contenedor.appendChild(boton);
    });
    marcarFiltroActivo("Todos");
}

function marcarFiltroActivo(genero) {
    document.querySelectorAll("#filtros-genero button").forEach(boton => {
        const activo = boton.dataset.genero === genero;
        boton.className = activo
            ? "px-3 py-1 text-xs rounded-lg bg-red-600 text-white font-medium transition hover:bg-red-500 shadow-md shadow-red-950/40"
            : "px-3 py-1 text-xs rounded-lg bg-[#181818] text-gray-300 font-medium transition hover:bg-[#222222] border border-red-950/30";
    });
}

function renderizarPlaylist(canciones) {
    const contenedor = document.getElementById("contenedor-canciones");
    contenedor.replaceChildren();
    document.getElementById("total-canciones").textContent = `${canciones.length} canciones`;

    if (canciones.length === 0) {
        mostrarMensajeLista("No hay canciones para este género.", "text-gray-500");
        return;
    }

    // Las tarjetas se construyen con nodos y textContent: un título con etiquetas
    // HTML se muestra literalmente en lugar de interpretarse.
    canciones.forEach((cancion) => {
        const tarjeta = document.createElement("div");
        tarjeta.id = `track-${cancion.id}`;
        tarjeta.className = "flex items-center justify-between p-2.5 rounded-xl hover:bg-zinc-800/50 transition cursor-pointer group border border-transparent";
        
        tarjeta.onclick = () => {
            let realIndex = listaActual.findIndex(c => c.id === cancion.id);
            reproducirCancionIndex(realIndex);
        };

        const izquierda = document.createElement("div");
        izquierda.className = "flex items-center gap-3 truncate";

        const img = document.createElement("img");
        img.src = cancion.imagen;
        img.alt = cancion.artista;
        img.className = "w-11 h-11 rounded-lg object-cover bg-[#181818]";

        const textos = document.createElement("div");
        textos.className = "truncate";
        const titulo = document.createElement("h4");
        titulo.className = "text-sm font-medium group-hover:text-red-400 transition truncate";
        titulo.textContent = cancion.titulo;
        const detalle = document.createElement("p");
        detalle.className = "text-xs text-gray-400 truncate";
        detalle.textContent = `${cancion.artista} - `;
        const genero = document.createElement("span");
        genero.className = "text-gray-500";
        genero.textContent = cancion.genero;
        detalle.appendChild(genero);
        textos.append(titulo, detalle);
        izquierda.append(img, textos);

        const estado = document.createElement("span");
        estado.className = "status-indicator text-xs text-red-400 font-medium px-2";

        tarjeta.append(izquierda, estado);
        contenedor.appendChild(tarjeta);
    });

    if (idActual !== null) marcarCancionActiva(idActual);
}

function marcarCancionActiva(id) {
    document.querySelectorAll("#contenedor-canciones > div").forEach(el => {
        el.classList.remove("bg-zinc-800/80", "border-red-600/30");
        const span = el.querySelector(".status-indicator");
        if (span) span.textContent = "";
    });
    const actual = document.getElementById(`track-${id}`);
    if (actual) {
        actual.classList.add("bg-zinc-800/80", "border-red-600/30");
        const span = actual.querySelector(".status-indicator");
        if (span) span.textContent = "▶ sonando";
    }
}

function filtrarPorGenero(genero) {
    if (!datosMusica) return;
    if (genero === "Todos") {
        listaActual = datosMusica.canciones;
    } else {
        listaActual = datosMusica.canciones.filter(c => c.generoClave === genero);
    }
    // Siguiente y Anterior continúan desde la canción actual si sigue en la lista filtrada.
    indiceActual = Math.max(0, listaActual.findIndex(c => c.id === idActual));
    marcarFiltroActivo(genero);
    renderizarPlaylist(listaActual);
}

// CONTROLES DEL ECUALIZADOR (GRAVES, MEDIOS, AGUDOS)
function ajustarGraves(valor) {
    document.getElementById("val-graves").textContent = `${valor} dB`;
    if (filtroGraves) {
        filtroGraves.gain.value = parseFloat(valor);
    }
}

function ajustarMedios(valor) {
    document.getElementById("val-medios").textContent = `${valor} dB`;
    if (filtroMedios) {
        filtroMedios.gain.value = parseFloat(valor);
    }
}

function ajustarAgudos(valor) {
    document.getElementById("val-agudos").textContent = `${valor} dB`;
    if (filtroAgudos) {
        filtroAgudos.gain.value = parseFloat(valor);
    }
}
