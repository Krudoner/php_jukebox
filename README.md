# Jukebox de la asociación · PHP

Aplicación de servidor en PHP para el jukebox de la asociación, que integra MUSICFY, el reproductor de 0612 (Howler.js, Tailwind, ecualizador y espectro): registro, inicio de sesión, home privada con el reproductor y CRUD del catálogo musical guardado en archivos JSON.

La ampliación de RA2 añade una gestión del catálogo sin recargas (`canciones-ajax.php`) que usa `fetch` contra una API JSON (`api/catalogo.php`). El CRUD clásico y el reproductor siguen funcionando con el mismo catálogo.

## Requisitos

- Docker Desktop con Docker Compose.
- Puertos libres en el equipo: 8090 (etapa inicial) y 8091 (etapa ampliada).
- No es necesario instalar PHP en Windows.

## Carpetas

```
jukebox_php/
  compose.yaml          Etapa inicial (8090), congelada
  src/                  Pasos 01 a 10 y lib/
  docs/                 Resúmenes de cada bloque (01 a 20)
  docs/pruebas-api/     Cuerpos JSON para probar api/catalogo.php
  data-ejemplo/         Datos ficticios para preparar una copia nueva
  docker/
    compose.yaml        Etapa ampliada (8091): web, postgres y mariadb
    Dockerfile          PHP 8.5 + Apache + pdo_pgsql + pdo_mysql
    .env.example        Variables de ejemplo (copiar a .env)
    apache/             Virtual host jukebox.test y ServerName global
    src/                Código público (DocumentRoot /var/www/html)
    data/               JSON privados (/var/www/data, fuera del DocumentRoot)
```

## Preparar una copia nueva

1. Copia `docker/.env.example` a `docker/.env` y cambia las contraseñas de laboratorio.
2. Copia `data-ejemplo/canciones.json` y `data-ejemplo/usuarios.json` a `docker/data/`.
   Hazlo solo al preparar una copia nueva: nunca sobre datos que quieras conservar.
3. Los 12 MP3 ya vienen incluidos en `docker/src/audio/`.
   Sus nombres deben coincidir con `AUDIOS_PERMITIDOS` en `docker/src/lib/canciones.php`.

## Arranque

```powershell
cd jukebox_php\docker
docker compose config --quiet
docker compose up -d --build
docker compose ps
```

La etapa inicial se arranca desde la raíz del proyecto con `docker compose up -d`.

## URL

| Etapa | URL |
|---|---|
| Inicial | http://localhost:8090/01-bienvenida.php |
| Ampliada · registro | http://localhost:8091/register.php |
| Ampliada · login | http://localhost:8091/login.php |
| Ampliada · home | http://localhost:8091/home.php |
| Ampliada · catálogo | http://localhost:8091/canciones.php |
| Ampliada · gestión AJAX | http://localhost:8091/canciones-ajax.php |
| Ampliada · API del reproductor | http://localhost:8091/api/canciones.php |
| Ampliada · API de gestión | http://localhost:8091/api/catalogo.php?action=list |

## Crear la primera cuenta ficticia

1. Abre http://localhost:8091/register.php.
2. Usa un alias, un correo ficticio (por ejemplo `prueba@jukebox.test`) y una contraseña de laboratorio de 12 a 64 caracteres que no utilices en ningún otro sitio.
3. Tras el registro se abre el login. Entra con el mismo correo y contraseña.

## API de gestión (api/catalogo.php)

| Acción | Método | Cuerpo JSON | Respuesta correcta |
|---|---|---|---|
| `?action=list` | GET | — | 200 `{"ok": true, "data": [...]}` |
| `?action=create` | POST | title, artist, category, duration, src | 201 y la lista |
| `?action=update` | POST | id y los cinco campos | 200 y la lista |
| `?action=delete` | POST | id | 200 y la lista |

Los POST necesitan la sesión, la cabecera `X-CSRF-Token` (el token está en la meta `csrf-token` de `canciones-ajax.php`) y `Content-Type: application/json`. Errores: 400, 401, 403, 404, 405, 415, 422 y 500 con `{"ok": false, "error": "..."}`. El detalle está en `docs/14-peticiones.md`.

## Requisito de red

La home carga Tailwind y Howler desde sus CDN, así que el navegador necesita conexión a Internet.

## Datos y seguridad

- `docker/data/` y `docker/.env` están excluidos de Git y del contexto de construcción.
- Las contraseñas se guardan con `password_hash`; el JSON nunca contiene la clave original.
- Eliminar una ficha no borra su archivo de audio.
- Laboratorio local por HTTP publicado en 127.0.0.1: no debe publicarse en Internet.
