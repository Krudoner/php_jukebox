<?php
$titulos = ['Sesión de prueba', 'Rock Sinfónico', 'Electrónica de Aula'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Listas y bucles</title>
</head>
<body>
    <h1>Prueba de bucles en PHP</h1>
    
    <h2>Bucle for (Posiciones 1 a 3)</h2>
    <ul>
        <?php
        for ($i = 1; $i <= 3; $i++) {
            echo "<li>Posición número: $i</li>";
        }
        ?>
    </ul>

    <h2>Bucle foreach (Títulos de canciones)</h2>
    <ul>
        <?php
        foreach ($titulos as $index => $titulo) {            
            $posicionUsuario = $index + 1;
            echo "<li>Elemento " . $posicionUsuario . ": " . htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</li>";
        }
        ?>
    </ul>
</body>
</html>