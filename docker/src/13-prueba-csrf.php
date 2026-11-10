<?php
declare(strict_types=1);

// Bloque 9: formulario de prueba del token CSRF. No guarda ningún dato.

require_once __DIR__ . '/lib/sesion.php';
require_once __DIR__ . '/lib/plantilla.php';

exigirMetodo(['GET', 'POST']);

$aceptado = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirCsrf(); // Si falta el token o no coincide, termina aquí con 403.
    $aceptado = true;
}

abrirPagina('Prueba del token CSRF');
?>
<?php if ($aceptado): ?>
    <p class="ok">El token es correcto y el envío se ha aceptado. No se ha guardado ningún dato.</p>
<?php endif; ?>
<form method="post">
    <?= campoCsrf() ?>
    <label>Mensaje de prueba <input type="text" name="mensaje"></label>
    <button type="submit">Enviar</button>
</form>
<?php
cerrarPagina();
