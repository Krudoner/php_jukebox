# Bloque 7 · Segunda etapa de Docker

- `docker/` en el puerto 8091 con web, postgres:17-bookworm y mariadb:11.4.
- Dockerfile con `pdo_pgsql` y `pdo_mysql`; virtual host `jukebox.conf` activado con `a2ensite`.
- `./src` → `/var/www/html` y `./data` → `/var/www/data` (fuera del DocumentRoot).
- `11-conexiones.php` devuelve el estado de cada motor en JSON, 200 si ambos están ok y 503 si falla alguno.
