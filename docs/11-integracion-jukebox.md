# Bloque 11 · Integración del jukebox

- Reproductor MUSICFY de 0612: `scripts/script.js`, `img/` y `audio/`; su HTML (Tailwind) está en `home.php` tras la comprobación de sesión, con la sesión del usuario en la cabecera.
- `api/canciones.php` devuelve una lista JSON; sin sesión responde 401 con un error JSON.
- `script.js` sustituye `fetch('canciones.json')` por `cargarCatalogo()`, que pide `./api/canciones.php`, comprueba `response.ok` y la lista, y traduce cada ficha con `adaptarCancion()` (title → titulo, artist → artista, category → genero, src → archivo).
- La carátula y el nombre del género se eligen en el cliente; los botones de filtro se crean con los géneros del catálogo.
- Las tarjetas se construyen con nodos y `textContent` en lugar de `innerHTML`.
- Ecualizador: la cadena de filtros y el analizador se crean una vez y cada elemento `<audio>` nuevo se conecta a ella (WeakSet), así sigue funcionando al cambiar de pista.
- Corrección de datos: la ruta de «VUi SE PULiSiA» no coincidía en mayúsculas con el archivo real.
