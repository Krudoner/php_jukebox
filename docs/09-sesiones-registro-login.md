# Bloque 9 · Sesiones, registro y login

- `lib/sesion.php` configura la cookie `JUKEBOXSESSID` (httponly, SameSite=Lax, modo estricto) y genera el token CSRF.
- Todos los formularios llevan el campo oculto `csrf`; un envío sin él o con otro valor recibe 403 y no escribe.
- `actualizarColeccion()` centraliza leer, modificar y guardar con `fopen r+` y `flock LOCK_EX`; las lecturas usan `LOCK_SH`.
- `register.php` valida alias, correo y contraseña, rechaza duplicados y guarda solo `password_hash`.
- `login.php` usa `password_verify` y `session_regenerate_id`; ante cualquier fallo muestra «Credenciales no válidas».
- `lib/acceso.php` protege las páginas privadas; `logout.php` solo acepta POST con token.
