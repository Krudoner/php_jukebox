<?php
declare(strict_types=1);

// Comprobación de acceso para las páginas privadas. Se incluye antes del HTML.

require_once __DIR__ . '/sesion.php';

if (!usuarioAutenticado()) {
    header('Location: login.php', true, 303);
    exit;
}
