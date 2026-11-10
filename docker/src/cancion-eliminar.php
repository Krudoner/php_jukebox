<?php
declare(strict_types=1);

// Bloque 10: Delete. GET muestra la confirmación; solo POST elimina la ficha.
// El archivo de audio no se borra.

require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';
require_once __DIR__ . '/lib/plantilla.php';

exigirMetodo(['GET', 'POST']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirCsrf();
    $id = idValido($_POST['id'] ?? null);
    if ($id === null) {
        paginaError(400, 'El identificador de la canción no es válido.');
    }
    try {
        actualizarColeccion(RUTA_CANCIONES, function (array &$songs) use ($id): void {
            $position = buscarPosicion($songs, $id);
            if ($position === null) {
                throw new OperacionRechazada('No existe ninguna canción con ese identificador.', 404);
            }
            unset($songs[$position]);
            $songs = array_values($songs); // Índices consecutivos: el JSON sigue siendo una lista.
        });
        header('Location: canciones.php', true, 303);
        exit;
    } catch (OperacionRechazada $ex) {
        paginaError($ex->getCode(), $ex->getMessage());
    } catch (JsonException | RuntimeException $ex) {
        error_log('cancion-eliminar: ' . $ex->getMessage());
        paginaError(500, 'No se ha podido eliminar la canción. El catálogo no se ha modificado.');
    }
}

// GET: confirmación, sin escribir nada.
$id = idValido($_GET['id'] ?? null);
if ($id === null) {
    paginaError(400, 'El identificador de la canción no es válido.');
}
try {
    $songs = leerCanciones();
} catch (JsonException | RuntimeException $ex) {
    error_log('cancion-eliminar: ' . $ex->getMessage());
    paginaError(500, 'No se ha podido cargar el catálogo.');
}
$position = buscarPosicion($songs, $id);
if ($position === null) {
    paginaError(404, 'No existe ninguna canción con ese identificador.');
}
$song = $songs[$position];

abrirPagina('Eliminar canción');
?>
<p>¿Seguro que quieres eliminar la ficha <strong><?= e($song['title']) ?></strong> de <?= e($song['artist']) ?>?</p>
<p>Se eliminará solo la ficha del catálogo; el archivo de audio se conserva.</p>
<form method="post">
    <?= campoCsrf() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <button type="submit">Eliminar</button>
    <a href="canciones.php">Cancelar</a>
</form>
<?php
cerrarPagina();
