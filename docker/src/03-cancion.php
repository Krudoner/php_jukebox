<?php

$title = '<b>Sesión de prueba</b>'; 
$artist = 'Grupo del aula';         
$duration = 185;                    
$volume = 0.5;                      
$available = true;                  

const APP_NAME = 'Jukebox de la asociación'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo APP_NAME; ?></title>
</head>
<body>
   
    <div style="background: #f4f4f4; padding: 10px; margin-bottom: 20px; font-family: monospace;">
        <h3>Depuración de tipos (var_dump):</h3>
        <?php
        echo '<pre>';
        var_dump($title);
        var_dump($artist);
        var_dump($duration);
        var_dump($volume);
        var_dump($available);
        echo '</pre>';
        ?>
    </div>

    <h1><?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
    <p>Artista: <?php echo htmlspecialchars($artist, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <p>Duración: <?php echo $duration; ?> segundos</p>
</body>
</html>