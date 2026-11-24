# Bloque 20 · Entrega y presentación técnica

## Qué se entrega
- Las dos etapas: la inicial (8090) sin cambios y la ampliada (8091) con las páginas clásicas, la home con el reproductor y la nueva gestión AJAX.
- `README.md` con las instrucciones para preparar una copia, `docker/.env.example`, `data-ejemplo/` con datos ficticios y `docs/pruebas-api/` con los cuerpos de prueba.
- Notas `docs/13-preparacion-ajax.md` a `docs/20-entrega.md` y la memoria `PHP_para_un_jukebox_AJAX.odt`.
- No se entregan `.env`, `docker/data/`, cookies, tokens ni contraseñas reales (excluidos en `.gitignore`).

## Guion de la demostración
1. Entrar con la cuenta ficticia, mostrar la home y el catálogo. RA1: registro, login, home, reproductor y CRUD clásico. RA2: gestión sin recargas.
2. Abrir Red/Network (Fetch/XHR). Añadir una canción: petición POST `create`, carga útil JSON, respuesta 201 y fila nueva sin navegación.
3. Editar la ficha (mismo id) y eliminar otra con confirmación. Enviar un dato no válido: 422 y el archivo no cambia.
4. Abrir el CRUD clásico y la home para comprobar la integración. Cerrar sesión: la API responde 401.

## Preguntas preparadas
- **¿Dónde se ejecuta PHP?** En el contenedor `web` (Apache con el módulo PHP). JavaScript se ejecuta en el navegador.
- **¿Qué hace await?** Espera a que una promesa termine sin bloquear la interfaz; `fetch` devuelve la respuesta y `respuesta.json()` el cuerpo interpretado.
- **¿Por qué `response.ok` no es `contenido.ok`?** El primero es el estado HTTP (200-299); el segundo es un campo de nuestro contrato. Se comprueban los dos.
- **¿Por qué se valida otra vez en el servidor?** La validación del navegador se puede saltar; PHP decide qué se guarda.
- **¿Cookie de sesión o token CSRF?** La cookie identifica la sesión y la envía el navegador automáticamente; el token lo añade nuestro JavaScript y demuestra que la petición sale de nuestra página.
- **¿Por qué el id no es el número de fila?** Al borrar una ficha las filas se desplazan; el id no cambia nunca.

## Comprobación desde cero
Preparar una copia según el README, `docker compose up -d --build` desde `docker/` y abrir `http://localhost:8091/api/catalogo.php?action=list` en una ventana privada: 401 con `{"ok":false,"error":"Debes iniciar sesión."}`.
