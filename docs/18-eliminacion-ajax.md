# Bloque 18 · Eliminación sin confundir fichas

## Qué cambia
- Eliminar pide confirmación con `window.confirm` mostrando el título y el artista. Si se cancela, no se envía nada.
- `enviarOperacion('delete', { id })`: POST con el token y el id. Mientras espera, las acciones están desactivadas.
- En PHP, `eliminarCancion()` busca por id dentro de `actualizarColeccion()`, hace `unset` y `array_values` antes de guardar. No renumera los id y no usa `unlink`: el MP3 se conserva. Si el id no existe responde 404. `cancion-eliminar.php` usa la misma función.
- La tabla se redibuja con `data` solo tras el 200. Si se estaba editando esa ficha, se sale del modo edición. Si no quedan canciones aparece la fila de catálogo vacío.

## Por qué
`array_values` mantiene el JSON como lista (sin huecos se serializaría como objeto). El id identifica la canción; la posición solo indica dónde está en el array. Con las fichas 2, 5 y 9, borrar la 5 deja la 2 y la 9: la segunda fila pasa a ser la 9, sin cambiar su id.

La tabla cambia después de la confirmación porque solo el servidor sabe si el archivo se ha guardado: si la pantalla quitara la fila antes, podría mostrar un borrado que no ha ocurrido.

## Qué probar
- Eliminar una canción intermedia (id 5) y editar después la que quedó debajo (id 6): cambia la correcta.
- Cancelar el diálogo: ninguna petición.
- Eliminar un id inexistente: 404 sin cambios.
- En una copia de pruebas, eliminar la última ficha: aparece «Todavía no hay canciones».

## Resultado esperado
CRUD completo: consulta, alta, edición y eliminación, con id estables.
