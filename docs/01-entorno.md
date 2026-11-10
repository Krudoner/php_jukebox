# Bloque 1 · Levantar el primer servicio

- `compose.yaml` publica `127.0.0.1:8090:80` y monta `./src` en `/var/www/html`.
- `src/01-bienvenida.php` muestra el saludo con `echo`.
- Pruebas: 200 con el saludo; 404 en `/no-existe.php` (el servidor responde); error de conexión con el servicio detenido; `php -l` detecta el punto y coma que falta.
