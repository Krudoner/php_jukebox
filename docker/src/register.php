<?php
declare(strict_types=1);

// Bloque 9: alta de una cuenta de prueba en /var/www/data/usuarios.json.

require_once __DIR__ . '/lib/sesion.php';
require_once __DIR__ . '/lib/almacen-json.php';
require_once __DIR__ . '/lib/plantilla.php';

exigirMetodo(['GET', 'POST']);

$alias = '';
$email = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirCsrf();

    $aliasRecibido = $_POST['alias'] ?? null;
    $emailRecibido = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $password2 = $_POST['password2'] ?? null;

    // Primero el tipo: un campo enviado como array se rechaza sin avisos PHP.
    if (!is_string($aliasRecibido) || !is_string($emailRecibido)
        || !is_string($password) || !is_string($password2)) {
        $errores['general'] = 'Los datos enviados no tienen un formato válido.';
    } else {
        $alias = trim($aliasRecibido);
        $email = strtolower(trim($emailRecibido)); // Correo normalizado.

        $longitudAlias = mb_strlen($alias);
        if ($longitudAlias < 2 || $longitudAlias > 30) {
            $errores['alias'] = 'El alias debe tener entre 2 y 30 caracteres.';
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errores['email'] = 'El correo no tiene un formato válido.';
        }
        $longitudClave = mb_strlen($password);
        if ($longitudClave < 12 || $longitudClave > 64) {
            $errores['password'] = 'La contraseña debe tener entre 12 y 64 caracteres.';
        } elseif (strlen($password) > 72) {
            $errores['password'] = 'La contraseña ocupa demasiados bytes; usa menos caracteres especiales.';
        }
        if ($password !== $password2) {
            $errores['password2'] = 'Las contraseñas no coinciden.';
        }
    }

    if ($errores === []) {
        $hash = password_hash($password, PASSWORD_DEFAULT); // Nunca se guarda $password.
        try {
            // Duplicados y alta dentro de la misma operación bloqueada.
            actualizarColeccion(RUTA_USUARIOS, function (array &$users) use ($alias, $email, $hash): int {
                foreach ($users as $user) {
                    if (is_array($user) && ($user['email'] ?? null) === $email) {
                        throw new OperacionRechazada('Ya existe una cuenta con ese correo.', 409);
                    }
                }
                $newId = siguienteId($users);
                $users[] = [
                    'id' => $newId,
                    'alias' => $alias,
                    'email' => $email,
                    'password_hash' => $hash,
                ];
                return $newId;
            });
            header('Location: login.php?registrado=1', true, 303);
            exit;
        } catch (OperacionRechazada $ex) {
            http_response_code($ex->getCode());
            $errores['email'] = $ex->getMessage();
        } catch (JsonException | RuntimeException $ex) {
            error_log('register: ' . $ex->getMessage());
            http_response_code(500);
            $errores['general'] = 'No se ha podido completar el registro. Inténtalo más tarde.';
        }
    } else {
        http_response_code(400);
    }
}

abrirPagina('Crear una cuenta de prueba');
?>
<?php if (isset($errores['general'])): ?>
    <p class="error"><?= e($errores['general']) ?></p>
<?php endif; ?>
<form method="post" class="ficha-form">
    <?= campoCsrf() ?>
    <label>Alias
        <input type="text" name="alias" minlength="2" maxlength="30" required value="<?= e($alias) ?>">
        <?php if (isset($errores['alias'])): ?><span class="error"><?= e($errores['alias']) ?></span><?php endif; ?>
    </label>
    <label>Correo ficticio
        <input type="email" name="email" required value="<?= e($email) ?>">
        <?php if (isset($errores['email'])): ?><span class="error"><?= e($errores['email']) ?></span><?php endif; ?>
    </label>
    <label>Contraseña (12 a 64 caracteres)
        <input type="password" name="password" minlength="12" maxlength="64" required autocomplete="new-password">
        <?php if (isset($errores['password'])): ?><span class="error"><?= e($errores['password']) ?></span><?php endif; ?>
    </label>
    <label>Repite la contraseña
        <input type="password" name="password2" minlength="12" maxlength="64" required autocomplete="new-password">
        <?php if (isset($errores['password2'])): ?><span class="error"><?= e($errores['password2']) ?></span><?php endif; ?>
    </label>
    <p><button type="submit">Crear cuenta</button> <a href="login.php">Ya tengo cuenta</a></p>
</form>
<?php
cerrarPagina();
