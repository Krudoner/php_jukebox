<?php
declare(strict_types=1);

// Bloque 10: Create. Alta de una ficha con el id calculado por el servidor.

require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';
require_once __DIR__ . '/lib/plantilla.php';
require_once __DIR__ . '/lib/formulario-cancion.php';

exigirMetodo(['GET', 'POST']);

$valores = ['title' => '', 'artist' => '', 'category' => '', 'duration' => '', 'src' => AUDIOS_PERMITIDOS[0]];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirCsrf();
    [$datos, $errores, $valores] = validarCancion($_POST);

    if ($errores === []) {
        try {
            actualizarColeccion(RUTA_CANCIONES, function (array &$songs) use ($datos): int {
                $newId = siguienteId($songs); // Lo calcula PHP, no el navegador.
                $newSong = [
                    'id' => $newId,
                    'title' => $datos['title'],
                    'artist' => $datos['artist'],
                    'category' => $datos['category'],
                    'duration' => $datos['duration'],
                    'src' => $datos['src'],
                ];
                $songs[] = $newSong;
                return $newId;
            });
            header('Location: canciones.php', true, 303);
            exit;
        } catch (JsonException | RuntimeException $ex) {
            error_log('cancion-nueva: ' . $ex->getMessage());
            http_response_code(500);
            $errores['general'] = 'No se ha podido guardar la canción. El catálogo no se ha modificado.';
        }
    } else {
        http_response_code(400);
    }
}

abrirPagina('Nueva canción');
pintarFormularioCancion($valores, $errores, 'Guardar canción');
cerrarPagina();
