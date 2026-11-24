# Bloque 14 · Peticiones y contrato de la API

## Qué cambia
La gestión deja de enviar formularios que cambian de página. JavaScript pide la operación con `fetch` y recibe JSON:

acción del usuario → `catalogo-ajax.js` (navegador) → `api/catalogo.php` (PHP en el contenedor) → `/var/www/data/canciones.json` → respuesta JSON → `catalogo-ajax.js` actualiza la tabla.

El navegador nunca abre el archivo privado: pide a PHP una operación permitida.

## Dos direcciones, dos responsabilidades
| Dirección | Quién la usa | Respuesta |
|---|---|---|
| `api/canciones.php` | Reproductor de la home (`script.js`) | Lista sin envolver: `[{"id":1,...}]`. **No cambia su formato.** |
| `api/catalogo.php` | Gestión AJAX (`catalogo-ajax.js`) | `{"ok": true, "data": [...]}` o `{"ok": false, "error": "..."}` |

Las dos leen el mismo archivo con la misma función `leerCanciones()`. No hay dos catálogos.

## Contrato de api/catalogo.php
| Acción | Método | Cuerpo JSON | Respuesta correcta |
|---|---|---|---|
| `?action=list` | GET | sin cuerpo | 200 y la lista |
| `?action=create` | POST | title, artist, category, duration, src | 201 y la lista actualizada |
| `?action=update` | POST | id y los cinco campos | 200 y la lista actualizada |
| `?action=delete` | POST | id | 200 y la lista actualizada |

Ninguna petición GET crea, edita ni elimina. Los POST llevan el token en la cabecera `X-CSRF-Token`.

## Errores
| Código | Cuándo | Mensaje |
|---|---|---|
| 400 | Acción desconocida, `action[]`, JSON mal escrito o cuerpo que no es un objeto | «Acción desconocida…», «El cuerpo no es un JSON válido.» |
| 401 | No hay sesión | «Debes iniciar sesión.» |
| 403 | Token ausente o incorrecto | «Falta el token de seguridad o no es válido…» |
| 404 | El id no existe | «No existe ninguna canción con ese identificador.» |
| 405 | Método no permitido (con cabecera `Allow`) | «La acción create solo admite el método POST.» |
| 415 | El cuerpo no es `application/json` | «El cuerpo debe enviarse como application/json.» |
| 422 | Campos con tipos o valores no válidos | p. ej. «La duración debe ser un entero entre 1 y 1800 segundos.» |
| 500 | No se puede leer o guardar el catálogo | «No se ha podido leer el catálogo.» |

Todas las respuestas salen de `responderJson()` (`lib/api.php`): fija el estado, `Content-Type: application/json; charset=utf-8`, codifica y termina. Antes no se imprime nada. Los detalles técnicos van a `error_log`, nunca al navegador.

## Qué probar
| Situación | Resultado esperado |
|---|---|
| Consultar sin sesión | 401 · `{"ok":false,"error":"Debes iniciar sesión."}` |
| Editar un id inexistente | 404 · «No existe ninguna canción con ese identificador.» |
| Enviar una duración negativa | 422 · «La duración debe ser un entero entre 1 y 1800 segundos.» |

## Por qué la API no redirige al login
La página privada puede redirigir con 303, porque la abre el navegador. La API la llama `fetch` esperando JSON: si recibiera la página de login, `response.json()` fallaría al interpretar HTML. Por eso responde 401 con un JSON.
