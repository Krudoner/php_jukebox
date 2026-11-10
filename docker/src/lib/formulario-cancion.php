<?php
declare(strict_types=1);

// Formulario compartido por el alta y la edición de canciones.

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/canciones.php';

function errorCampo(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? ' <span class="error">' . e($errores[$campo]) . '</span>' : '';
}

function pintarFormularioCancion(array $valores, array $errores, string $boton, ?int $id = null): void
{
    $nombres = ['electronica' => 'Electrónica', 'rock' => 'Rock', 'bandas-sonoras' => 'Bandas sonoras',
        'hardcore-uptempo' => 'Hardcore Uptempo', 'musica-urbana' => 'Música urbana', 'rawstyle' => 'Rawstyle'];
    ?>
    <?php if (isset($errores['general'])): ?>
        <p class="error"><?= e($errores['general']) ?></p>
    <?php endif; ?>
    <form method="post" class="ficha-form">
        <?= campoCsrf() ?>
        <?php if ($id !== null): ?>
            <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif; ?>

        <label>Título
            <input type="text" name="title" maxlength="<?= MAX_TEXTO_CANCION ?>" required
                   value="<?= e($valores['title']) ?>"><?= errorCampo($errores, 'title') ?>
        </label>

        <label>Artista
            <input type="text" name="artist" maxlength="<?= MAX_TEXTO_CANCION ?>" required
                   value="<?= e($valores['artist']) ?>"><?= errorCampo($errores, 'artist') ?>
        </label>

        <label>Categoría
            <select name="category" required>
                <option value="">Elige una categoría</option>
                <?php foreach (CATEGORIAS as $categoria): ?>
                    <option value="<?= e($categoria) ?>" <?= $valores['category'] === $categoria ? 'selected' : '' ?>>
                        <?= e($nombres[$categoria]) ?>
                    </option>
                <?php endforeach; ?>
            </select><?= errorCampo($errores, 'category') ?>
        </label>

        <label>Duración (segundos)
            <input type="number" name="duration" min="1" max="<?= DURACION_MAXIMA ?>" required
                   value="<?= e($valores['duration']) ?>"><?= errorCampo($errores, 'duration') ?>
        </label>

        <label>Archivo de audio
            <select name="src" required>
                <?php foreach (AUDIOS_PERMITIDOS as $audio): ?>
                    <option value="<?= e($audio) ?>" <?= $valores['src'] === $audio ? 'selected' : '' ?>>
                        <?= e(basename($audio)) ?>
                    </option>
                <?php endforeach; ?>
            </select><?= errorCampo($errores, 'src') ?>
        </label>

        <p>
            <button type="submit"><?= e($boton) ?></button>
            <a href="canciones.php">Cancelar</a>
        </p>
    </form>
    <?php
}
