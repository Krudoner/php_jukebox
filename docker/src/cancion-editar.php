<?php
declare(strict_types=1);

// Bloque 10: Update. GET carga la ficha por id; POST valida y la sustituye conservando su id.

require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';
require_once __DIR__ . '/lib/plantilla.php';
require_once __DIR__ . '/lib/formulario-cancion.php';

exigirMetodo(['GET', 'POST']);

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = idValido($_GET['id'] ?? null);
    if ($id === null) {
        paginaError(400, 'El identificador de la canción no es válido.');
    }
    try {
        $songs = leerCanciones();
    } catch (JsonException | RuntimeException $ex) {
        error_log('cancion-editar: ' . $ex->getMessage());
        paginaError(500, 'No se ha podido cargar el catálogo.');
    }
    $position = buscarPosicion($songs, $id);
    if ($position === null) {
        paginaError(404, 'No existe ninguna canción con ese identificador.');
    }
    $song = $songs[$position];
    $valores = [
        'title' => $song['title'],
        'artist' => $song['artist'],
        'category' => $song['category'],
        'duration' => (string) $song['duration'],
        'src' => $song['src'],
    ];
} else {
    exigirCsrf();
    $id = idValido($_POST['id'] ?? null);
    if ($id === null) {
        paginaError(400, 'El identificador de la canción no es válido.');
    }

    [$datos, $errores, $valores] = validarCancion($_POST);

    if ($errores === []) {
        try {
            // Se vuelve a leer y localizar la ficha bajo bloqueo.
            actualizarColeccion(RUTA_CANCIONES, function (array &$songs) use ($id, $datos): void {
                foreach ($songs as $position => $song) {
                    if (is_array($song) && ($song['id'] ?? null) === $id) {
                        // $position localiza el elemento; $id identifica la canción.
                        $songs[$position] = ['id' => $id] + $datos;
                        return;
                    }
                }
                throw new OperacionRechazada('No existe ninguna canción con ese identificador.', 404);
            });
            header('Location: canciones.php', true, 303);
            exit;
        } catch (OperacionRechazada $ex) {
            paginaError($ex->getCode(), $ex->getMessage());
        } catch (JsonException | RuntimeException $ex) {
            error_log('cancion-editar: ' . $ex->getMessage());
            http_response_code(500);
            $errores['general'] = 'No se han podido guardar los cambios. El catálogo no se ha modificado.';
        }
    } else {
        http_response_code(400);
    }
}

abrirPagina('Editar canción ' . $id);
pintarFormularioCancion($valores, $errores, 'Guardar cambios', $id);
cerrarPagina();
