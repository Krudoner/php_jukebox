# Bloque 15 · Consulta desde PHP y tabla con fetch

## Qué cambia
- `api/catalogo.php` carga `lib/sesion.php`, `lib/almacen-json.php` y `lib/api.php` con rutas `__DIR__ . '/../lib/'`.
- Comprueba la sesión con `usuarioAutenticado()`. No incluye `acceso.php`, que redirige con HTML: responde 401 en JSON.
- Lee `action` de `$_GET` (por defecto `list`), exige que sea una cadena del contrato y el método correcto.
- `list` solo con GET: `leerCanciones()` (la misma del CRUD clásico, con `LOCK_SH`) y `responderJson(200, ['ok' => true, 'data' => array_values(...)])`.
- Un archivo ilegible o con JSON dañado responde 500; nunca se sustituye por `[]`.
- `canciones-ajax.php` es una página privada con título, zona de mensajes, formulario y una tabla con `tbody id="cuerpo-canciones"`. Carga `scripts/catalogo-ajax.js` con `defer` y está enlazada desde la home, la barra común y la gestión clásica.
- `cargarCatalogo()` usa `try / catch / finally`: en `try` pide la lista y la comprueba, en `catch` muestra el error y en `finally` quita el estado de espera.
- `leerRespuesta()` lee el cuerpo una sola vez y comprueba `respuesta.ok` (HTTP 200-299), `contenido.ok` (contrato) y `Array.isArray(contenido.data)`.
- `renderizarCanciones()` vacía el `tbody` con `replaceChildren` y crea filas y celdas con `textContent`. Con la lista vacía crea una fila «Todavía no hay canciones» que ocupa las seis columnas. Los botones Editar y Eliminar guardan el id en `dataset.id`.
- Mensajes en `<p id="mensaje" role="status" aria-live="polite">`, fuera del `tbody`. Se quedan hasta la siguiente acción.

## Por qué
- `await fetch(...)` espera la respuesta HTTP (estado y cabeceras). `await respuesta.json()` espera e interpreta el cuerpo. Son dos pasos: un 404 o un 500 no hacen fallar `fetch`, hay que comprobarlos.
- `textContent` escribe texto: un título con `<b>` se ve literalmente y no se ejecuta.
- El id identifica la ficha; el número de fila cambia al borrar otra canción.

## Qué probar
- Con sesión: `/api/catalogo.php?action=list` en otra pestaña muestra JSON con `ok` y `data`.
- Ventana privada: la misma URL da 401 con JSON, no la página de login.
- Red/Network al recargar `canciones-ajax.php`: una petición `list` de tipo fetch con estado 200.
- Copia de pruebas con `[]`: aparece «Todavía no hay canciones». Con un título `<b>Demo</b>`: se ve literalmente.

## Resultado esperado
La tabla se llena con datos reales del servidor y la consulta se entiende sin abrir la consola.
