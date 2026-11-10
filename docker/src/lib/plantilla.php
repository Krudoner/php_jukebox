<?php
declare(strict_types=1);

// Cabecera y pie comunes de las páginas HTML.

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/canciones.php';

function abrirPagina(string $titulo): void
{
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<header class="barra">
    <strong><?= e(APP_NAME) ?></strong>
    <?php if (usuarioAutenticado()): ?>
        <nav>
            <a href="home.php">Inicio</a>
            <a href="canciones.php">Catálogo</a>
            <a href="cancion-nueva.php">Nueva canción</a>
            <form method="post" action="logout.php" class="en-linea">
                <?= campoCsrf() ?>
                <button type="submit">Cerrar sesión (<?= e((string) ($_SESSION['alias'] ?? '')) ?>)</button>
            </form>
        </nav>
    <?php else: ?>
        <nav>
            <a href="login.php">Iniciar sesión</a>
            <a href="register.php">Crear cuenta</a>
        </nav>
    <?php endif; ?>
</header>
<main>
    <h1><?= e($titulo) ?></h1>
    <?php
}

function cerrarPagina(): void
{
    echo "\n</main>\n</body>\n</html>\n";
}

// Página de error controlada con su estado HTTP.
function paginaError(int $estado, string $mensaje): never
{
    http_response_code($estado);
    abrirPagina('No se puede completar la petición');
    echo '<p class="error">' . e($mensaje) . '</p>';
    echo '<p><a href="canciones.php">Volver al catálogo</a></p>';
    cerrarPagina();
    exit;
}
