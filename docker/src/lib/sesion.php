<?php
declare(strict_types=1);

// Sesión común: se carga con require_once antes de generar cualquier HTML.

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_name('JUKEBOXSESSID'); // Nombre propio para este proyecto.
    session_set_cookie_params([
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => false, // HTTP local; será true cuando exista HTTPS.
    ]);
    session_start();
}

// Token CSRF asociado a la sesión.
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Campo oculto que acompaña a todos los formularios que envían datos.
function campoCsrf(): string
{
    $token = htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return '<input type="hidden" name="csrf" value="' . $token . '">';
}

// Compara el token recibido con el guardado en la sesión.
function csrfValido(): bool
{
    $recibido = $_POST['csrf'] ?? null;
    return is_string($recibido)
        && is_string($_SESSION['csrf'] ?? null)
        && hash_equals($_SESSION['csrf'], $recibido);
}

// Si el token falta o no coincide, responde 403 y detiene la petición sin escribir.
function exigirCsrf(): void
{
    if (csrfValido()) {
        return;
    }
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Envío rechazado</title></head>'
        . '<body><h1>Envío rechazado</h1><p>El formulario no es válido o ha caducado. '
        . 'Vuelve a cargar la página e inténtalo de nuevo.</p></body></html>';
    exit;
}

// Responde 405 si la petición usa un método no admitido por la página.
function exigirMetodo(array $metodos): void
{
    if (in_array($_SERVER['REQUEST_METHOD'], $metodos, true)) {
        return;
    }
    http_response_code(405);
    header('Allow: ' . implode(', ', $metodos));
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Método no permitido';
    exit;
}

// Una sesión sin usuario_id significa que nadie ha iniciado sesión.
function usuarioAutenticado(): bool
{
    return is_int($_SESSION['usuario_id'] ?? null);
}
