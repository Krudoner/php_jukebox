<?php
declare(strict_types=1);

// Bloques 14 a 16: utilidades comunes de las API JSON.
// Una sola forma de responder y de leer el cuerpo, para que todas las rutas cumplan el mismo contrato.

// Fija el estado HTTP y las cabeceras, codifica el contenido y termina la ejecución.
// Antes de esta llamada no se imprime nada: ni espacios, ni avisos, ni HTML.
function responderJson(int $estado, array $contenido, array $cabeceras = []): never
{
    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    foreach ($cabeceras as $nombre => $valor) {
        header($nombre . ': ' . $valor);
    }
    echo json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

// Respuesta de error del contrato: {"ok": false, "error": "..."}; nunca incluye una lista nueva.
function responderError(int $estado, string $mensaje, array $extra = [], array $cabeceras = []): never
{
    responderJson($estado, ['ok' => false, 'error' => $mensaje] + $extra, $cabeceras);
}

// Token CSRF enviado por JavaScript en la cabecera X-CSRF-Token.
// PHP la publica en $_SERVER como HTTP_X_CSRF_TOKEN. Ambos valores deben ser cadenas.
function tokenCabeceraValido(): bool
{
    $recibido = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    return is_string($recibido)
        && is_string($_SESSION['csrf'] ?? null)
        && hash_equals($_SESSION['csrf'], $recibido);
}

// Acepta application/json, también con parámetros como charset=utf-8.
function cuerpoEsJson(): bool
{
    $tipo = $_SERVER['CONTENT_TYPE'] ?? '';
    return is_string($tipo) && preg_match('~^application/json\s*(;|$)~i', trim($tipo)) === 1;
}

// Lee el cuerpo de la petición (php://input, no $_POST) y exige un objeto JSON.
// Un JSON mal escrito nunca se convierte en un formulario vacío: responde 400.
function leerCuerpoJson(): array
{
    $cuerpo = file_get_contents('php://input');
    if ($cuerpo === false) {
        responderError(400, 'No se ha podido leer el cuerpo de la petición.');
    }
    try {
        $objeto = json_decode($cuerpo, false, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        responderError(400, 'El cuerpo no es un JSON válido.');
    }
    // Se rechazan listas, null, números y textos sueltos: esperamos un objeto.
    if (!is_object($objeto)) {
        responderError(400, 'El cuerpo debe ser un objeto JSON con los campos de la canción.');
    }
    return get_object_vars($objeto);
}
