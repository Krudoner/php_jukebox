# Bloque 17 · Edición de la canción elegida

## Qué cambia
- El botón Editar lleva el id en `dataset.id`. JavaScript lo convierte con `Number`, comprueba que sea un entero positivo y busca la ficha en la lista local por id (`canciones.find`), no por posición.
- `iniciarEdicion()` rellena el mismo formulario, guarda el id en `idEditando`, cambia el título a «Editando: …», el botón a «Guardar cambios», muestra «Cancelar edición» (`type="button"`) y lleva el foco al primer campo.
- En modo edición el envío usa `action=update` y añade `id` al cuerpo. Mismo token, mismas validaciones y mismo bloqueo de botones que el alta.
- En PHP, `idDeEntrada()` exige un entero positivo (422) y `editarCancion()` busca la ficha por id dentro de `actualizarColeccion()`. Si existe la sustituye conservando el id; si no, lanza `OperacionRechazada` con 404 y no la vuelve a crear. `cancion-editar.php` usa la misma función.
- Tras el éxito se dibuja `data` y se vuelve al modo alta. Cancelar solo limpia el formulario: no envía nada.

## Por qué
Aunque JavaScript tenga la ficha en memoria, otra pestaña puede haberla borrado. El servidor comprueba su existencia en el momento del cambio, sobre los datos recién leídos bajo bloqueo.

## Cómo se distinguen las tres acciones
| Acción | Estado | Petición |
|---|---|---|
| Crear | `idEditando === null` | `create` sin id |
| Editar | `idEditando` con el id elegido | `update` con id |
| Cancelar | vuelve a `null` | ninguna |

## Qué probar
- Cambiar título y duración de una canción: mismo id y mismo número de fichas.
- Cancelar: mensaje «Edición cancelada», sin peticiones en Red.
- Abrir la edición, eliminar esa ficha desde otra pestaña y guardar: 404 y mensaje que pide volver a consultar.

## Resultado esperado
Se edita la ficha concreta sin duplicarla ni tocar otras filas.
