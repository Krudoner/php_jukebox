<?php
declare(strict_types=1);

// Bloque 11: lista JSON de canciones para el reproductor. Requiere sesión, no redirige.

require_once __DIR__ . '/../lib/sesion.php';
require_once __DIR__ . '/../lib/almacen-json.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!usuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no iniciada'], JSON_UNESCAPED_UNICODE);
    exit;
}

session_write_close(); // La API no modifica la sesión: libera su bloqueo.

try {
    // Solo los campos que necesita el reproductor, en una lista sin envolver.
    $songs = array_map(fn (array $song): array => [
        'id' => $song['id'],
        'title' => $song['title'],
        'artist' => $song['artist'],
        'category' => $song['category'],
        'duration' => $song['duration'],
        'src' => $song['src'],
    ], leerCanciones());

    echo json_encode($songs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
} catch (JsonException | RuntimeException $ex) {
    error_log('api/canciones: ' . $ex->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se ha podido leer el catálogo'], JSON_UNESCAPED_UNICODE);
}
