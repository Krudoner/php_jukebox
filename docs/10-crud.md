# Bloque 10 · CRUD del catálogo

- Read: `canciones.php` lista las fichas con enlaces de edición y eliminación.
- Create: `cancion-nueva.php` valida con `validarCancion()` y calcula el id como máximo existente + 1.
- Update: `cancion-editar.php` localiza la ficha por id (400 si el id no es válido, 404 si no existe) y conserva el id.
- Delete: `cancion-eliminar.php` confirma por GET y elimina por POST con `unset` y `array_values`; no borra el MP3.
- Todas las páginas exigen sesión; todos los cambios exigen POST y token válido; tras guardar se redirige con 303.
