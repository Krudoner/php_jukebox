# Bloque 19 · Integración y pruebas de fallos

## Qué cambia
- Enlaces entre pantallas: la barra común (`plantilla.php`) y la home tienen «Gestión AJAX»; la gestión clásica enlaza a la AJAX y la AJAX a la clásica y a la home. Las tres requieren sesión.
- Accesibilidad: todos los campos tienen `label`, los botones de la tabla tienen `aria-label` con el título («Editar «Mi Madre»»), el foco va al primer campo al editar y al cancelar.
- Mensajes diferenciados: «Cargando catálogo…», «Guardando…», «Eliminando…», «Canción añadida», «Cambios guardados» y los errores concretos de la API. El éxito nunca se muestra desde `finally`.
- Código común extraído: `leerRespuesta()` (comprobación del contrato) y `enviarOperacion()` (POST con JSON y token). En PHP, `crearCancion()`, `editarCancion()` y `eliminarCancion()` las usan la API y el CRUD clásico.
- El reproductor no cambia: la home sigue pidiendo `api/canciones.php`, que devuelve la lista sin envolver. Un cambio confirmado desde AJAX aparece al volver a cargar la home.

## Matriz de pruebas
| Caso | Acción | Esperado |
|---|---|---|
| Acceso | Consulta sin sesión | 401 JSON |
| Acceso | POST con sesión y sin token | 403, archivo igual |
| Acceso | Logout en otra pestaña y guardar | 401 «Debes iniciar sesión.» |
| Contrato | GET `?action=create` | 405 con `Allow: POST` |
| Contrato | Cuerpo como formulario | 415 |
| Contrato | JSON mal escrito | 400 |
| Contrato | Duración negativa / categoría no admitida | 422 |
| Contrato | Editar id inexistente | 404 |
| Interfaz | Doble clic en Añadir | Una sola petición create |
| Interfaz | Error de validación | Datos conservados en el formulario |
| Interfaz | Catálogo vacío | «Todavía no hay canciones» |
| Interfaz | Cancelar edición o borrado | Ninguna petición |
| Persistencia | Recargar tras un cambio | Datos conservados |
| Persistencia | JSON dañado (copia de pruebas) | 500 y mensaje; el archivo no se sustituye por `[]` |
| Red | Offline en las DevTools y consultar | «No hay conexión con el servidor…»; al volver a Online, la lista carga |

## Cómo observar
- Red/Network filtrado por Fetch/XHR: URL, método, estado, carga útil y respuesta. Las operaciones son `fetch`; no aparece ninguna navegación `document` nueva.
- `docker compose ps` y `docker compose logs --tail=50 web` si aparece un 500 o HTML inesperado.
- Ocultar cookies y tokens en las evidencias: en los comandos van en variables (`$sid`, `$token`).

## Fallo encontrado
En la primera prueba con curl, la consulta «sin sesión» devolvió la lista. Revisando el comando vi que la función de prueba ya añadía `-b` con el archivo de cookies y el segundo `-b` no lo sustituía: curl enviaba la cookie. Repetí la petición sin cookies y en una ventana privada: 401 con JSON. No era un fallo de la API, sino de la prueba; desde entonces las pruebas sin sesión se hacen sin ningún archivo de cookies.

## Resultado esperado
Una aplicación con tres interfaces (home, gestión clásica, gestión AJAX) y un único catálogo, con los límites demostrados.
