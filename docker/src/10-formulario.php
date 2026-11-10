<?php

require_once __DIR__ . '/lib/canciones.php';

$errores = [];
$titulo = '';
$artista = '';
$categoria = 'electronica';
$duracion = '';
$srcSeleccionado = 'audio/demo-01.mp3';
$vistaPrevia = false;

$allowedCategories = ['electronica', 'rock', 'bandas-sonoras'];
$allowedAudios = ['audio/demo-01.mp3', 'audio/demo-02.mp3'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['title'] ?? '';
    $artista = $_POST['artist'] ?? '';
    $categoria = $_POST['category'] ?? '';
    $duracionInput = $_POST['duration'] ?? '';
    $srcSeleccionado = $_POST['src'] ?? '';

    // Validar título
    if (!is_string($titulo) || trim($titulo) === '') {
        $errores['title'] = 'El título es obligatorio.';
    } else {
        $titulo = trim($titulo);
    }

    // Validar artista
    if (!is_string($artista) || trim($artista) === '') {
        $errores['artist'] = 'El artista es obligatorio.';
    } else {
        $artista = trim($artista);
    }

    // Validar categoría
    if (!is_string($categoria) || !in_array($categoria, $allowedCategories, true)) {
        $errores['category'] = 'La categoría seleccionada no es válida.';
    }

    // Validar duración como entero
    $duracionFiltro = filter_var($duracionInput, FILTER_VALIDATE_INT);
    if ($duracionFiltro === false || $duracionFiltro < 1 || $duracionFiltro > 1800) {
        $errores['duration'] = 'La duración debe ser un número entero entre 1 y 1800 segundos.';
    } else {
        $duracion = $duracionFiltro;
    }

    // Validar src
    if (!is_string($srcSeleccionado) || !in_array($srcSeleccionado, $allowedAudios, true)) {
        $errores['src'] = 'El archivo de audio seleccionado no es válido.';
    }

    if (empty($errores)) {
        $vistaPrevia = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Formulario y Vista Previa de Canción</title>
</head>
<body>
    <h1>Gestión de Ficha de Canción (Vista Previa)</h1>

    <?php if ($vistaPrevia): ?>
        <div style="border: 2px dashed green; padding: 15px; background: #e9ffe9;">
            <p style="color: green; font-weight: bold;">Vista previa, canción no guardada.</p>
            <p><strong>Título:</strong> <?php echo htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <p><strong>Artista:</strong> <?php echo htmlspecialchars($artista, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <p><strong>Categoría:</strong> <?php echo htmlspecialchars($categoria, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <p><strong>Duración:</strong> <?php echo duracionLegible($duracion); ?></p>
            <p><strong>Audio:</strong> <?php echo htmlspecialchars($srcSeleccionado, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
        </div>
        <p><a href="10-formulario.php">Crear otra ficha</a></p>
    <?php else: ?>
        <form action="10-formulario.php" method="POST">
            <div>
                <label>Título:</label><br>
                <input type="text" name="title" value="<?php echo htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                <?php if (isset($errores['title'])): ?>
                    <span style="color: red;"><?php echo $errores['title']; ?></span>
                <?php endif; ?>
            </div>
            <br>
            <div>
                <label>Artista:</label><br>
                <input type="text" name="artist" value="<?php echo htmlspecialchars($artista, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                <?php if (isset($errores['artist'])): ?>
                    <span style="color: red;"><?php echo $errores['artist']; ?></span>
                <?php endif; ?>
            </div>
            <br>
            <div>
                <label>Categoría:</label><br>
                <select name="category">
                    <option value="electronica" <?php if($categoria==='electronica') echo 'selected'; ?>>Electrónica</option>
                    <option value="rock" <?php if($categoria==='rock') echo 'selected'; ?>>Rock</option>
                    <option value="bandas-sonoras" <?php if($categoria==='bandas-sonoras') echo 'selected'; ?>>Bandas Sonoras</option>
                </select>
            </div>
            <br>
            <div>
                <label>Duración (segundos):</label><br>
                <input type="text" name="duration" value="<?php echo htmlspecialchars($duracion, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                <?php if (isset($errores['duration'])): ?>
                    <span style="color: red;"><?php echo $errores['duration']; ?></span>
                <?php endif; ?>
            </div>
            <br>
            <div>
                <label>Archivo de Audio:</label><br>
                <select name="src">
                    <option value="audio/demo-01.mp3" <?php if($srcSeleccionado==='audio/demo-01.mp3') echo 'selected'; ?>>demo-01.mp3</option>
                    <option value="audio/demo-02.mp3" <?php if($srcSeleccionado==='audio/demo-02.mp3') echo 'selected'; ?>>demo-02.mp3</option>
                </select>
            </div>
            <br>
            <button type="submit">Generar Vista Previa</button>
        </form>
    <?php endif; ?>
</body>
</html>