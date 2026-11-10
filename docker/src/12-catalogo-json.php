<?php
declare(strict_types=1);

// Bloque 8: catálogo leído desde el JSON privado /var/www/data/canciones.json.
// Bloque 10: protegido con la comprobación de acceso, igual que el resto de la segunda etapa.

require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';
require_once __DIR__ . '/lib/plantilla.php';

$songs = [];
$error = null;

try {
    $songs = leerCanciones();
} catch (JsonException | RuntimeException $ex) {
    // El detalle va al log del contenedor; la página muestra un mensaje controlado.
    error_log('12-catalogo-json: ' . $ex->getMessage());
    http_response_code(500);
    $error = 'No se ha podido cargar el catálogo. El archivo de datos no se ha modificado.';
}

abrirPagina('Catálogo desde JSON');
?>
<?php if ($error !== null): ?>
    <p class="error"><?= e($error) ?></p>
<?php elseif ($songs === []): ?>
    <p>Todavía no hay canciones.</p>
<?php else: ?>
    <ul class="fichas">
        <?php foreach ($songs as $song): ?>
            <li>
                <strong><?= e($song['title']) ?></strong> — <?= e($song['artist']) ?>
                <span class="meta"><?= e($song['category']) ?> · <?= e(duracionLegible($song['duration'])) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php
cerrarPagina();
