<?php
declare(strict_types=1);

// Reglas comunes de las canciones: se reutilizan en el catálogo, en el alta y en la edición.

const APP_NAME = 'Jukebox de la asociación';

// A las tres categorías del enunciado se añaden los géneros del reproductor de 0612.
const CATEGORIAS = ['electronica', 'rock', 'bandas-sonoras', 'hardcore-uptempo', 'musica-urbana', 'rawstyle'];

// Audios autorizados del reproductor de cliente (carpeta audio/).
const AUDIOS_PERMITIDOS = [
    "audio/Death Shock - I'm Addicted.mp3",
    'audio/Death Shock - Knocked.mp3',
    'audio/Death Shock - Pain Out.mp3',
    'audio/P.A.W.N GANG - ELLAS SABAN LU KE iA _ PROD. ETM.mp3',
    'audio/P.A.W.N GANG - LA CLiKA.mp3',
    'audio/P.A.W.N GANG - NAi TRUCAM AL MOBiL _ NEM A FE NAGOSiS.mp3',
    'audio/P.A.W.N GANG - TU I TU.mp3',
    'audio/P.A.W.N GANG - VUi SE PULiSiA __ MONRRA STRAPS.mp3',
    'audio/The Straikerz - Firebombs.mp3',
    'audio/The Straikerz - Jackpot.mp3',
    'audio/The Straikerz - Kickroll Is Not A Crime _ POWER HOUR.mp3',
    'audio/The Straikerz - Mi Madre.mp3',
];

const MAX_TEXTO_CANCION = 80;
const DURACION_MAXIMA = 1800;

function duracionLegible(int $total): string
{
    $minutes = intdiv($total, 60);
    $seconds = $total % 60;
    return "$minutes minuto(s) y $seconds segundo(s)";
}

// Prepara un texto para insertarlo en HTML. El dato original no cambia.
function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Devuelve un id entero positivo o null si el valor recibido no lo es.
function idValido(mixed $valor): ?int
{
    if (!is_string($valor)) {
        return null;
    }
    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

// Comprueba que una ficha leída del JSON tiene las claves y tipos esperados.
function cancionValida(mixed $song): bool
{
    return is_array($song)
        && is_int($song['id'] ?? null) && $song['id'] > 0
        && is_string($song['title'] ?? null)
        && is_string($song['artist'] ?? null)
        && is_string($song['category'] ?? null)
        && is_int($song['duration'] ?? null) && $song['duration'] >= 0
        && is_string($song['src'] ?? null);
}

// Valida los campos enviados por un formulario de canción.
// Devuelve [datos validados o null, errores, valores para volver a pintar el formulario].
function validarCancion(array $entrada): array
{
    $errores = [];
    $valores = ['title' => '', 'artist' => '', 'category' => '', 'duration' => '', 'src' => ''];

    foreach (['title' => 'El título', 'artist' => 'El artista'] as $campo => $nombre) {
        $valor = $entrada[$campo] ?? '';
        if (!is_string($valor)) {
            $errores[$campo] = "$nombre debe enviarse como texto.";
            continue;
        }
        $valor = trim($valor);
        $valores[$campo] = $valor;
        if ($valor === '') {
            $errores[$campo] = "$nombre es obligatorio.";
        } elseif (mb_strlen($valor) > MAX_TEXTO_CANCION) {
            $errores[$campo] = "$nombre admite como máximo " . MAX_TEXTO_CANCION . ' caracteres.';
        }
    }

    $category = $entrada['category'] ?? '';
    if (!is_string($category) || !in_array($category, CATEGORIAS, true)) {
        $errores['category'] = 'Selecciona una categoría válida.';
    } else {
        $valores['category'] = $category;
    }

    $duration = $entrada['duration'] ?? '';
    $durationInt = false;
    if (!is_string($duration)) {
        $errores['duration'] = 'La duración debe enviarse como texto.';
    } else {
        $valores['duration'] = trim($duration);
        $durationInt = filter_var($valores['duration'], FILTER_VALIDATE_INT);
        if ($durationInt === false || $durationInt < 1 || $durationInt > DURACION_MAXIMA) {
            $errores['duration'] = 'La duración debe ser un número entero entre 1 y ' . DURACION_MAXIMA . ' segundos.';
        }
    }

    $src = $entrada['src'] ?? '';
    if (!is_string($src) || !in_array($src, AUDIOS_PERMITIDOS, true)) {
        $errores['src'] = 'El archivo de audio seleccionado no es válido.';
    } else {
        $valores['src'] = $src;
    }

    if ($errores !== []) {
        return [null, $errores, $valores];
    }

    $datos = [
        'title' => $valores['title'],
        'artist' => $valores['artist'],
        'category' => $valores['category'],
        'duration' => $durationInt,
        'src' => $valores['src'],
    ];
    return [$datos, [], $valores];
}
