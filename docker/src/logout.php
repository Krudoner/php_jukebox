<?php
declare(strict_types=1);

// Bloque 9: cierre de sesión. Solo POST con token válido; un enlace GET no cierra la sesión.

require_once __DIR__ . '/lib/sesion.php';

exigirMetodo(['POST']);
exigirCsrf();

$_SESSION = [];

// Elimina la cookie con los mismos parámetros con los que se creó.
$params = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 42000,
    'path' => $params['path'],
    'domain' => $params['domain'],
    'secure' => $params['secure'],
    'httponly' => $params['httponly'],
    'samesite' => $params['samesite'],
]);

session_destroy();

header('Location: login.php', true, 303);
exit;
