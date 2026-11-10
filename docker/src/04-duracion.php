<?php
$duration = 185; 

$minutes = intdiv($duration, 60); 
$seconds = $duration % 60;        
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Duración legible</title>
</head>
<body>
    <h1>Cálculo de duración</h1>
    <p>La duración total es de <?php echo $duration; ?> segundos.</p>
    <p>Equivale a: <strong><?php echo $minutes; ?> minutos y <?php echo $seconds; ?> segundos</strong>.</p>
</body>
</html>