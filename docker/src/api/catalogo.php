<?php
declare(strict_types=1);

// Bloques 15 a 18: API de gestión del catálogo para la pantalla canciones-ajax.php.
// Contrato: la acción viaja en la URL (?action=list|create|update|delete) y los datos en un cuerpo JSON.
// Respuesta correcta: {"ok": true, "data": [lista]}. Error: {"ok": false, "error": "mensaje"}.
// api/canciones.php se mantiene igual para el reproductor: devuelve la lista sin envolver.

require_once __DIR__ . '/../lib/sesion.php';
require_once __DIR__ . '/../lib/almacen-json.php';
require_once __DIR__ . '/../lib/api.php';

// Método que admite cada acción. Ninguna petición GET puede cambiar datos.
const ACCIONES_CATALOGO = ['list' => 'GET', 'create' => 'POST', 'update' => 'POST', 'delete' => 'POST'];

// 1. Sesión. No se incluye acceso.php porque redirige al login con HTML:
//    un fetch que espera JSON necesita un 401 con JSON.
if (!usuarioAutenticado()) {
    responderError(401, 'Debes iniciar sesión.');
}

// 2. Acción: una cadena del contrato. ?action[]=list llega como array y se rechaza.
$accion = $_GET['action'] ?? 'list';
if (!is_string($accion) || !array_key_exists($accion, ACCIONES_CATALOGO)) {
    responderError(400, 'Acción desconocida. Usa list, create, update o delete.');
}

// 3. Método permitido para esa acción, con la cabecera Allow.
$metodo = ACCIONES_CATALOGO[$accion];
if ($_SERVER['REQUEST_METHOD'] !== $metodo) {
    responderError(405, "La acción $accion solo admite el método $metodo.", [], ['Allow' => $metodo]);
}

// 4. Consulta: solo lectura con bloqueo compartido. Un JSON dañado es un error, no una lista vacía.
if ($accion === 'list') {
    session_write_close(); // La consulta no modifica la sesión: libera su bloqueo.
    try {
        responderJson(200, ['ok' => true, 'data' => array_values(leerCanciones())]);
    } catch (JsonException | RuntimeException $ex) {
        error_log('api/catalogo list: ' . $ex->getMessage());
        responderError(500, 'No se ha podido leer el catálogo.');
    }
}

// 5. Cambios: token CSRF de la cabecera, tipo del cuerpo y objeto JSON, en este orden.
if (!tokenCabeceraValido()) {
    responderError(403, 'Falta el token de seguridad o no es válido. Recarga la página e inténtalo de nuevo.');
}
session_write_close();

if (!cuerpoEsJson()) {
    responderError(415, 'El cuerpo debe enviarse como application/json.');
}
$entrada = leerCuerpoJson();

// Devuelve el id si es un entero JSON positivo; "3", 3.5 o -1 no son identificadores.
function idDeEntrada(array $entrada): int
{
    $id = $entrada['id'] ?? null;
    if (!is_int($id) || $id < 1) {
        responderError(422, 'El id debe ser un entero positivo.', ['fields' => ['id' => 'El id debe ser un entero positivo.']]);
    }
    return $id;
}

// Valida los cinco campos de la ficha o responde 422 con el primer mensaje y el detalle por campo.
function cancionDeEntrada(array $entrada): array
{
    [$datos, $errores] = validarCancionJson($entrada);
    if ($errores !== []) {
        responderError(422, (string) reset($errores), ['fields' => $errores]);
    }
    return $datos;
}

try {
    switch ($accion) {
        case 'create':
            // El cliente no elige el id: si lo envía, se ignora.
            $lista = crearCancion(cancionDeEntrada($entrada));
            responderJson(201, ['ok' => true, 'data' => $lista]);
        case 'update':
            $id = idDeEntrada($entrada);
            $lista = editarCancion($id, cancionDeEntrada($entrada));
            responderJson(200, ['ok' => true, 'data' => $lista]);
        case 'delete':
            $lista = eliminarCancion(idDeEntrada($entrada));
            responderJson(200, ['ok' => true, 'data' => $lista]);
    }
} catch (OperacionRechazada $ex) {
    responderError($ex->getCode(), $ex->getMessage());
} catch (JsonException | RuntimeException $ex) {
    // El detalle (ruta, motivo) queda en el log del contenedor; el navegador recibe un mensaje controlado.
    error_log('api/catalogo ' . $accion . ': ' . $ex->getMessage());
    responderError(500, 'No se ha podido guardar el catálogo. No se ha modificado.');
}
