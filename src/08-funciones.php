<?php
require_once __DIR__ . '/lib/canciones.php';

$prueba1 = 60;
$prueba2 = 185;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Prueba de Funciones Modularizadas</title>
</head>
<body>
    <h1>Resultados de la función duracionLegible</h1>
    <p>Para <?php echo $prueba1; ?> segundos: <strong><?php echo duracionLegible($prueba1); ?></strong></p>
    <p>Para <?php echo $prueba2; ?> segundos: <strong><?php echo duracionLegible($prueba2); ?></strong></p>
</body>
</html>