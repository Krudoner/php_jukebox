<?php

$duration = 240; 
$estado = '';

if ($duration === 0) {
    $estado = 'Sin duración';
} elseif ($duration >= 1 && $duration <= 240) {
    $estado = 'Hasta cuatro minutos';
} else {
    $estado = 'Más de cuatro minutos';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado de la canción</title>
</head>
<body>
    <h1>Evaluación de regla de negocio</h1>
    <p>Duración analizada: <?php echo $duration; ?> segundos.</p>
    <p>Estado asignado por el servidor: <strong><?php echo htmlspecialchars($estado, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong></p>
</body>
</html>