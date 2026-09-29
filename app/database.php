<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/**
 * Conexión opcional a MariaDB / MySQL (el motor que ya trae XAMPP).
 *
 * Por defecto SIGAH funciona con los archivos JSON de data/, así que la web
 * anda apenas la copiás a htdocs. Si querés usar la base de datos:
 *
 *   1. Importá sql/sigah.sql desde phpMyAdmin.
 *   2. Poné DB_ENABLED=true en el .env (o mejor, en tu .env.local).
 *
 * db() devuelve null cuando la base está deshabilitada, de modo que las
 * páginas pueden hacer:  $pdo = db(); if ($pdo) { ... } else { ...JSON... }
 */
function db(): ?PDO
{
    static $pdo = null;
    static $tried = false;

    if (!(bool) env('DB_ENABLED', false)) {
        return null;
    }

    if ($tried) {
        return $pdo;
    }

    $tried = true;

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        (string) env('DB_HOST', '127.0.0.1'),
        (int) env('DB_PORT', 3306),
        (string) env('DB_NAME', 'sigah'),
        (string) env('DB_CHARSET', 'utf8mb4')
    );

    try {
        $pdo = new PDO(
            $dsn,
            (string) env('DB_USER', 'root'),
            (string) env('DB_PASS', ''),
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        $pdo = null;

        if ((bool) env('APP_DEBUG', false)) {
            error_log('SIGAH · Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Indica si hay conexión real a la base de datos.
 */
function db_ready(): bool
{
    return db() instanceof PDO;
}
