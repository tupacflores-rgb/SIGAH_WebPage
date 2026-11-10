<?php
declare(strict_types=1);

/**
 * TP7 — Conexión PDO con manejo de excepciones (try-catch PDOException).
 *
 * Uso (después de cargar app/bootstrap.php):
 *     require_once __DIR__ . '/../config/conexion.php';
 *     $stmt = $pdo->prepare('SELECT * FROM alumnos WHERE id = :id');
 *
 * Toma host, base y credenciales del .env / .env.local (DB_HOST, DB_NAME,
 * DB_USER, DB_PASS), igual que el resto del proyecto.
 */

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$host    = (string) env('DB_HOST', 'localhost');
$port    = (int) env('DB_PORT', 3306);
$db      = (string) env('DB_NAME', 'sigah');
$user    = (string) env('DB_USER', 'root');
$pass    = (string) env('DB_PASS', '');
$charset = (string) env('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('SIGAH · Error de conexión: ' . $e->getMessage());
    http_response_code(500);
    exit(
        'Error crítico de conexión con la base de datos. '
        . 'Verificá que MySQL esté iniciado y que sql/sigah.sql esté importado.'
        . ((bool) env('APP_DEBUG', false) ? ' Detalle: ' . e($e->getMessage()) : '')
    );
}
