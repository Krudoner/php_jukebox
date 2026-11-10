<?php
declare(strict_types=1);

// Bloque 7: diagnóstico de los dos motores. Devuelve el estado y el nombre de la base, nunca claves.

header('Content-Type: application/json; charset=utf-8');

// Prueba una conexión PDO y devuelve su estado sin incluir credenciales.
function probarConexion(string $motor, string $dsn, string|false $usuario, string|false $clave, string $consulta): array
{
    try {
        $db = new PDO($dsn, (string) $usuario, (string) $clave, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $nombre = $db->query($consulta)->fetchColumn();
        return ['estado' => 'ok', 'base' => $nombre];
    } catch (PDOException $error) {
        // El diagnóstico va al log del contenedor, sin usuario ni contraseña.
        error_log("11-conexiones: fallo en $motor (código " . $error->getCode() . ')');
        return ['estado' => 'error'];
    }
}

$resultado = [
    'postgresql' => probarConexion(
        'postgresql',
        'pgsql:host=postgres;port=5432;dbname=jukebox_pg',
        getenv('PGUSER'),
        getenv('PGPASSWORD'),
        'SELECT current_database()'
    ),
    'mariadb' => probarConexion(
        'mariadb',
        'mysql:host=mariadb;port=3306;dbname=jukebox_maria;charset=utf8mb4',
        getenv('MARIADB_USER'),
        getenv('MARIADB_PASSWORD'),
        'SELECT DATABASE()'
    ),
];

$todoOk = $resultado['postgresql']['estado'] === 'ok' && $resultado['mariadb']['estado'] === 'ok';
http_response_code($todoOk ? 200 : 503);
echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
