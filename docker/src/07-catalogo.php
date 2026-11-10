<?php
$songs = [
    [
        'id' => 1,
        'title' => 'Sesión de prueba',
        'artist' => 'Grupo del aula',
        'category' => 'electronica',
        'duration' => 185,
        'src' => 'audio/demo-01.mp3',
    ],
    [
        'id' => 2,
        'title' => 'Acordes de Rock',
        'artist' => 'Banda de Rock',
        'category' => 'rock',
        'duration' => 210,
        'src' => 'audio/demo-02.mp3',
    ],
    [
        'id' => 3,
        'title' => 'Sinfonía Digital',
        'artist' => 'Orquesta Virtual',
        'category' => 'bandas-sonoras',
        'duration' => 245,
        'src' => 'audio/demo-03.mp3',
    ],
    [
        'id' => 4,
        'title' => 'Ritmo Constante',
        'artist' => 'The Loopers',
        'category' => 'electronica',
        'duration' => 160,
        'src' => 'audio/demo-04.mp3',
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Catálogo Musical</title>
</head>
<body>
    <h1>Catálogo de Canciones del Jukebox</h1>

    <?php if (empty($songs)): ?>
        <p>Todavía no hay canciones.</p>
    <?php else: ?>
        <div class="catalogo">
            <?php foreach ($songs as $song): ?>
                <div class="ficha-cancion" style="border: 1px solid #ccc; margin: 10px; padding: 10px;">
                    <h3>ID: <?php echo $song['id']; ?> - <?php echo htmlspecialchars($song['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h3>
                    <p><strong>Artista:</strong> <?php echo htmlspecialchars($song['artist'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                    <p><strong>Categoría:</strong> <?php echo htmlspecialchars($song['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                    <p><strong>Duración:</strong> <?php echo $song['duration']; ?> segundos</p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>