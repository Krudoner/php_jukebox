<?php
declare(strict_types=1);

// Bloque 10: Read. Consulta del catálogo privado con enlaces de edición y eliminación.

require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';
require_once __DIR__ . '/lib/plantilla.php';

exigirMetodo(['GET']);

$songs = [];
$error = null;
try {
    $songs = leerCanciones();
} catch (JsonException | RuntimeException $ex) {
    error_log('canciones: ' . $ex->getMessage());
    http_response_code(500);
    $error = 'No se ha podido cargar el catálogo.';
}

abrirPagina('Catálogo musical');
?>
<p><a href="home.php">Volver a la home</a> · <a href="cancion-nueva.php">Añadir una canción</a></p>

<?php if ($error !== null): ?>
    <p class="error"><?= e($error) ?></p>
<?php elseif ($songs === []): ?>
    <p>Todavía no hay canciones.</p>
<?php else: ?>
    <table class="catalogo">
        <thead>
            <tr><th>Id</th><th>Título</th><th>Artista</th><th>Categoría</th><th>Duración</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($songs as $song): ?>
            <tr>
                <td><?= $song['id'] ?></td>
                <td><?= e($song['title']) ?></td>
                <td><?= e($song['artist']) ?></td>
                <td><?= e($song['category']) ?></td>
                <td><?= e(duracionLegible($song['duration'])) ?></td>
                <td>
                    <a href="cancion-editar.php?id=<?= $song['id'] ?>">Editar</a>
                    <!-- GET solo abre la confirmación; el borrado se hace por POST. -->
                    <form method="get" action="cancion-eliminar.php" class="en-linea">
                        <input type="hidden" name="id" value="<?= $song['id'] ?>">
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php
cerrarPagina();
