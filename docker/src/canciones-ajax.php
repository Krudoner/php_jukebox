<?php
declare(strict_types=1);

// Bloques 15 a 19: gestión del catálogo sin recargar la página.
// PHP genera la estructura y el token; scripts/catalogo-ajax.js pide los datos a api/catalogo.php
// y dibuja la tabla. Es una página privada: sin sesión, acceso.php redirige al login.

require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/plantilla.php';

exigirMetodo(['GET']);

abrirPagina('Gestión del catálogo (AJAX)', [
    // Token CSRF de la sesión, escapado para HTML. JavaScript lo envía en la cabecera X-CSRF-Token.
    '<meta name="csrf-token" content="' . e((string) $_SESSION['csrf']) . '">',
    // defer: el script se ejecuta cuando el HTML ya está analizado y encuentra la tabla y el formulario.
    '<script src="scripts/catalogo-ajax.js" defer></script>',
]);
?>
<p><a href="home.php">Volver a la home</a> · <a href="canciones.php">Gestión clásica</a></p>

<!-- Mensajes de estado: fuera del tbody y anunciados por los lectores de pantalla. -->
<p id="mensaje" class="mensaje" role="status" aria-live="polite">Cargando catálogo…</p>

<form id="form-cancion" class="ficha-form panel">
    <h2 id="titulo-form">Nueva canción</h2>
    <label>Título
        <input type="text" name="title" maxlength="<?= MAX_TEXTO_CANCION ?>" required>
    </label>
    <label>Artista
        <input type="text" name="artist" maxlength="<?= MAX_TEXTO_CANCION ?>" required>
    </label>
    <label>Categoría
        <select name="category" required>
            <option value="">Elige una categoría</option>
            <?php foreach (CATEGORIAS as $categoria): ?>
                <option value="<?= e($categoria) ?>"><?= e(NOMBRES_CATEGORIA[$categoria]) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Duración (segundos)
        <input type="number" name="duration" min="1" max="<?= DURACION_MAXIMA ?>" step="1" required>
    </label>
    <label>Archivo de audio
        <select name="src" required>
            <?php foreach (AUDIOS_PERMITIDOS as $audio): ?>
                <option value="<?= e($audio) ?>"><?= e(basename($audio)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <p>
        <button type="submit" id="btn-guardar">Añadir canción</button>
        <button type="button" id="btn-cancelar" hidden>Cancelar edición</button>
    </p>
</form>

<h2>Canciones del catálogo</h2>
<p><button type="button" id="btn-recargar">Volver a consultar</button></p>
<table class="catalogo" id="tabla-canciones">
    <thead>
        <tr>
            <th scope="col">Id</th>
            <th scope="col">Título</th>
            <th scope="col">Artista</th>
            <th scope="col">Categoría</th>
            <th scope="col">Duración</th>
            <th scope="col">Acciones</th>
        </tr>
    </thead>
    <tbody id="cuerpo-canciones"></tbody>
</table>
<noscript><p class="error">Esta pantalla necesita JavaScript. Puedes usar la <a href="canciones.php">gestión clásica</a>.</p></noscript>
<?php
cerrarPagina();
