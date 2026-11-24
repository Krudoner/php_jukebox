# Bloque 16 · Alta segura con JSON

## Qué cambia
- Formulario en `canciones-ajax.php`: `title` y `artist` (`required`, `maxlength="80"`), `category` y `src` con `select`, y `duration` con `type="number" min="1" max="1800" step="1"`. Todos con `label`. Sin `novalidate`; el envío llama a `reportValidity()`.
- El token CSRF de la sesión se publica en `<meta name="csrf-token">`, escapado con `e()`. JavaScript lo envía en la cabecera `X-CSRF-Token`.
- En la API, los POST comprueban (en este orden): sesión (401), token con `hash_equals` sobre dos cadenas (403), `Content-Type: application/json` con o sin `charset` (415) y cuerpo leído de `php://input` con `JSON_THROW_ON_ERROR` (400 si está mal escrito o no es un objeto).
- `validarCancionJson()` (`lib/canciones.php`) comprueba primero los tipos (textos como cadenas, `duration` como entero JSON) y después reutiliza `validarCancion()`, la misma del formulario clásico. Si algo falla responde 422 con el mensaje y el detalle por campo.
- `crearCancion()` (`lib/almacen-json.php`) asigna el id (máximo + 1) dentro de `actualizarColeccion()`, con un único `LOCK_EX` para leer, cambiar y guardar. La usan la API y `cancion-nueva.php`. Un id enviado por el cliente se ignora.
- En JavaScript: `preventDefault`, objeto `cancion` con `duration: Number(...)`, `fetch` POST con `JSON.stringify`, y `enviarOperacion()` común para create, update y delete.
- La tabla y el formulario solo cambian tras la confirmación (201). Si falla, los datos escritos se conservan.
- `operacionEnCurso` y `bloquear()` desactivan los botones mientras hay una petición. Se reactivan en `finally`. Un doble clic envía una sola alta.

## Por qué
- La sesión dice quién eres. El token demuestra que el envío sale de una página de la aplicación. Son controles distintos: la cookie la envía el navegador solo; el token lo añade nuestro JavaScript.
- PHP valida otra vez porque cualquiera puede saltarse el formulario y enviar la petición a mano (curl, Postman, consola).
- Si se pierde la respuesta de un POST no se repite el alta automáticamente: el mensaje pide volver a consultar antes de reenviar.

## Qué probar
Cuerpos en `docs/pruebas-api/`:

| Prueba | Resultado esperado |
|---|---|
| POST con sesión y sin token | 403 y el hash de `canciones.json` no cambia |
| `alta-duracion-negativa.json` | 422 · «La duración debe ser un entero entre 1 y 1800 segundos.» |
| `alta-categoria-no-admitida.json` | 422 · «Selecciona una categoría válida.» |
| `alta-json-mal-formado.json` | 400 · «El cuerpo no es un JSON válido.» |
| Alta desde la pantalla (doble clic) | Una sola petición `create` 201, fila nueva sin navegación de tipo document |
| Recargar después | La canción sigue guardada |

## Resultado esperado
El alta funciona sin recargar, el formulario se vacía solo después del 201 y los errores se muestran sin perder lo escrito.
