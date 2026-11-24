# Bloque 13 · Preparación de la ampliación AJAX

## Qué cambia
- Se añade una segunda forma de gestionar el catálogo, sin recargar la página, que convive con el CRUD clásico de los bloques 9 y 10.
- Archivos nuevos en la segunda etapa (`docker/`, puerto 8091):
  - `src/canciones-ajax.php`: pantalla privada de gestión.
  - `src/api/catalogo.php`: API JSON con las cuatro operaciones del CRUD.
  - `src/scripts/catalogo-ajax.js`: peticiones `fetch` y dibujo de la tabla.
  - `src/lib/api.php`: respuesta JSON común, lectura del cuerpo y token de la cabecera.
- Los datos siguen en `docker/data/canciones.json` y `docker/data/usuarios.json`, montados en `/var/www/data`, fuera del DocumentRoot. No hay una copia del catálogo en `src/`.
- MariaDB y PostgreSQL siguen en Compose, pero la ampliación no los usa para guardar canciones.

## Por qué
Conservar el CRUD clásico permite comparar una petición que recarga la página con otra que solo actualiza la tabla. Las dos usan el mismo archivo y las mismas reglas.

## Punto de recuperación
- Commit `7c23499` («Arreglar las capturas del informe…») en `main`, subido a GitHub antes de empezar. Es el estado completo de RA1.
- Antes de crearlo comprobé que se podía entrar, consultar el catálogo y reproducir un audio.

## Qué probar
```powershell
cd docker
docker compose up -d   # web, postgres y mariadb
docker compose ps      # web en 127.0.0.1:8091->80/tcp, bases de datos healthy
```
- Abrir siempre `http://localhost:8091` (no mezclar con `127.0.0.1`: no comparten la cookie de sesión). No usar Live Server para PHP.
- La home (`home.php`) y el CRUD clásico (`canciones.php`) deben funcionar antes de añadir AJAX.
- La primera etapa (8090) no se modifica.

## Resultado esperado
Entorno arrancado, servicios en marcha y la etapa anterior intacta. En el informe no aparecen contraseñas ni el `.env`.
