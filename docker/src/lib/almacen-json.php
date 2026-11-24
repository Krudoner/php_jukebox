<?php
declare(strict_types=1);

// Lectura y escritura centralizada de los JSON privados.
// Las rutas son fijas: nunca se construyen con datos recibidos por GET o POST.

require_once __DIR__ . '/canciones.php';

const RUTA_CANCIONES = '/var/www/data/canciones.json';
const RUTA_USUARIOS = '/var/www/data/usuarios.json';

// Se lanza cuando una operación no debe escribir (id inexistente, correo duplicado...).
// El código de la excepción es el estado HTTP que debe devolver la página.
final class OperacionRechazada extends RuntimeException
{
}

// Convierte el texto JSON en una lista PHP o lanza una excepción.
function decodificarLista(string $texto): array
{
    $datos = json_decode($texto, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($datos) || !array_is_list($datos)) {
        throw new UnexpectedValueException('La raíz del JSON debe ser una lista.');
    }
    return $datos;
}

// Lee una colección completa con bloqueo compartido (LOCK_SH).
function leerColeccion(string $ruta): array
{
    $handle = @fopen($ruta, 'r');
    if ($handle === false) {
        throw new RuntimeException('No se pudo abrir ' . basename($ruta));
    }
    try {
        if (!flock($handle, LOCK_SH)) {
            throw new RuntimeException('No se pudo bloquear ' . basename($ruta) . ' para leer');
        }
        $texto = stream_get_contents($handle);
        flock($handle, LOCK_UN);
        if ($texto === false) {
            throw new RuntimeException('No se pudo leer ' . basename($ruta));
        }
        return decodificarLista($texto);
    } finally {
        fclose($handle);
    }
}

// Lee el catálogo y comprueba cada ficha antes de devolverlo.
function leerCanciones(): array
{
    $songs = leerColeccion(RUTA_CANCIONES);
    foreach ($songs as $song) {
        if (!cancionValida($song)) {
            throw new UnexpectedValueException('El catálogo contiene una ficha con un formato incorrecto.');
        }
    }
    return $songs;
}

// Calcula el siguiente id: máximo existente + 1, o 1 si la colección está vacía.
function siguienteId(array $records): int
{
    $max = 0;
    foreach ($records as $record) {
        if (is_array($record) && is_int($record['id'] ?? null) && $record['id'] > $max) {
            $max = $record['id'];
        }
    }
    return $max + 1;
}

// Devuelve la posición del registro con ese id, o null si no existe.
function buscarPosicion(array $records, int $id): ?int
{
    foreach ($records as $position => $record) {
        if (is_array($record) && ($record['id'] ?? null) === $id) {
            return $position;
        }
    }
    return null;
}

// Procedimiento común de actualización: leer, modificar y guardar con el mismo LOCK_EX.
// $modificar recibe la colección por referencia; si lanza una excepción no se escribe nada.
function actualizarColeccion(string $ruta, callable $modificar): mixed
{
    // 1. Abrir la ruta fija sin vaciar el archivo.
    $handle = @fopen($ruta, 'r+');
    if ($handle === false) {
        throw new RuntimeException('No se pudo abrir ' . basename($ruta) . ' para escribir');
    }

    $bloqueado = false;
    try {
        // 2. Bloqueo exclusivo, lectura y validación de los datos actuales.
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('No se pudo bloquear ' . basename($ruta));
        }
        $bloqueado = true;

        $texto = stream_get_contents($handle);
        if ($texto === false) {
            throw new RuntimeException('No se pudo leer ' . basename($ruta));
        }
        $records = decodificarLista($texto);

        // 3. Modificar el array y convertirlo a JSON antes de tocar el archivo.
        $resultado = $modificar($records);
        $records = array_values($records);
        $json = json_encode(
            $records,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
        ) . "\n";

        if (!rewind($handle)) {
            throw new RuntimeException('No se pudo volver al inicio de ' . basename($ruta));
        }
        $bytes = fwrite($handle, $json);
        if ($bytes !== strlen($json)) {
            throw new RuntimeException('Escritura incompleta en ' . basename($ruta));
        }
        if (!ftruncate($handle, strlen($json)) || !fflush($handle)) {
            throw new RuntimeException('No se pudo confirmar la escritura de ' . basename($ruta));
        }
        return $resultado;
    } finally {
        // 4. Liberar el bloqueo y cerrar, también si hubo un error.
        if ($bloqueado) {
            flock($handle, LOCK_UN);
        }
        fclose($handle);
    }
}

// Bloques 16 a 18: operaciones del catálogo compartidas por el CRUD clásico y la API AJAX.
// Las tres trabajan dentro de actualizarColeccion (un único LOCK_EX para leer, cambiar y guardar)
// y devuelven la lista ya guardada. Si el id no existe lanzan OperacionRechazada con 404.

// Alta: el id lo calcula PHP (máximo + 1); nunca se acepta uno enviado por el cliente.
function crearCancion(array $datos): array
{
    return actualizarColeccion(RUTA_CANCIONES, function (array &$songs) use ($datos): array {
        $songs[] = ['id' => siguienteId($songs)] + $datos;
        return array_values($songs);
    });
}

// Edición: se busca la ficha por id sobre los datos recién leídos y se sustituye conservando su id.
function editarCancion(int $id, array $datos): array
{
    return actualizarColeccion(RUTA_CANCIONES, function (array &$songs) use ($id, $datos): array {
        $position = buscarPosicion($songs, $id);
        if ($position === null) {
            // Otra pestaña pudo borrarla: no se vuelve a crear.
            throw new OperacionRechazada('No existe ninguna canción con ese identificador.', 404);
        }
        // $position localiza el elemento; $id identifica la canción.
        $songs[$position] = ['id' => $id] + $datos;
        return array_values($songs);
    });
}

// Eliminación: quita solo la ficha (el MP3 no se borra) y reindexa sin renumerar los id.
function eliminarCancion(int $id): array
{
    return actualizarColeccion(RUTA_CANCIONES, function (array &$songs) use ($id): array {
        $position = buscarPosicion($songs, $id);
        if ($position === null) {
            throw new OperacionRechazada('No existe ninguna canción con ese identificador.', 404);
        }
        unset($songs[$position]);
        $songs = array_values($songs); // Índices consecutivos: el JSON sigue siendo una lista.
        return $songs;
    });
}
