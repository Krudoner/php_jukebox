# Bloque 8 · Leer el catálogo desde JSON

- El catálogo contiene las 12 canciones del reproductor de cliente y está en `docker/data/canciones.json`, montado en `/var/www/data`, fuera del DocumentRoot.
- `lib/almacen-json.php` lee la ruta fija, decodifica con `JSON_THROW_ON_ERROR` y comprueba que la raíz es una lista y que cada ficha tiene sus campos.
- `12-catalogo-json.php` reutiliza `duracionLegible` y `htmlspecialchars` para generar las fichas.
- Pruebas: añadir una ficha al JSON la muestra en la página; `[]` muestra «Todavía no hay canciones»; una coma incorrecta produce un error controlado sin modificar el archivo; `/data/canciones.json` no se descarga por HTTP.
