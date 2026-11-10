<?php
declare(strict_types=1);

// Bloque 9: verificación de credenciales y apertura de la sesión.

require_once __DIR__ . '/lib/sesion.php';
require_once __DIR__ . '/lib/almacen-json.php';
require_once __DIR__ . '/lib/plantilla.php';

exigirMetodo(['GET', 'POST']);

if (usuarioAutenticado()) {
    header('Location: home.php', true, 303);
    exit;
}

$email = '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirCsrf();

    $emailRecibido = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!is_string($emailRecibido) || !is_string($password)) {
        http_response_code(400);
        $error = 'Credenciales no válidas.';
    } else {
        $email = strtolower(trim($emailRecibido)); // Misma normalización que en el registro.
        try {
            $user = null;
            foreach (leerColeccion(RUTA_USUARIOS) as $candidate) {
                if (is_array($candidate) && ($candidate['email'] ?? null) === $email) {
                    $user = $candidate;
                    break;
                }
            }

            if ($user !== null && is_string($user['password_hash'] ?? null)
                && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true); // Renueva el identificador.
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['alias'] = $user['alias'];
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                header('Location: home.php', true, 303);
                exit;
            }

            // Correo desconocido o contraseña incorrecta: el mismo mensaje.
            http_response_code(401);
            $error = 'Credenciales no válidas.';
        } catch (JsonException | RuntimeException $ex) {
            error_log('login: ' . $ex->getMessage()); // Nunca se registra la contraseña.
            http_response_code(500);
            $error = 'No se ha podido comprobar el acceso. Inténtalo más tarde.';
        }
    }
}

abrirPagina('Iniciar sesión');
?>
<?php if (($_GET['registrado'] ?? null) === '1' && $error === null): ?>
    <p class="ok">Cuenta creada. Ya puedes iniciar sesión.</p>
<?php endif; ?>
<?php if ($error !== null): ?>
    <p class="error"><?= e($error) ?></p>
<?php endif; ?>
<form method="post" class="ficha-form">
    <?= campoCsrf() ?>
    <label>Correo
        <input type="email" name="email" required value="<?= e($email) ?>">
    </label>
    <label>Contraseña
        <input type="password" name="password" required autocomplete="current-password">
    </label>
    <p><button type="submit">Entrar</button> <a href="register.php">Crear una cuenta</a></p>
</form>
<?php
cerrarPagina();
