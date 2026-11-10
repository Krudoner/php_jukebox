<?php
// Bloque 6 - Paso 1: Filtrar el catálogo mediante GET
require_once __DIR__ . '/lib/canciones.php';
$songs = require_once __DIR__ . '/lib/catalogo-inicial.php';

$category = $_GET['category'] ?? 'todas';
$allowed = ['todas', 'electronica', 'rock', 'bandas-sonoras'];

// Validar que sea texto y esté permitido
if (!is_string($category) || !in_array($category, $allowed, true)) {
    http_response_code(400);
    $errorMensaje = 'Categoría no válida';
} else {
    $errorMensaje = null;
    if ($category !== 'todas') {
        $songs = array_filter($songs, function($song) use ($category) {
            return $song['category'] === $category;
        });
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Filtrar Catálogo</title>
</head>
<body>
    <h1>Catálogo Filtrado por Categoría</h1>

    <?php if ($errorMensaje !== null): ?>
        <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($errorMensaje, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <?php else: ?>
        <p>Mostrando categoría: <strong><?php echo htmlspecialchars($category, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong></p>
        
        <?php if (empty($songs)): ?>
            <p>Todavía no hay canciones en esta categoría.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($songs as $song): ?>
                    <li><?php echo htmlspecialchars($song['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> (<?php echo htmlspecialchars($song['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>