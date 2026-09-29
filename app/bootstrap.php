<?php
declare(strict_types=1);

/**
 * SIGAH — arranque de la aplicación.
 *
 * Todas las páginas .php empiezan con:
 *     require __DIR__ . '/app/bootstrap.php';
 */

if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    exit('SIGAH requiere PHP 8.1 o superior. Versión detectada: ' . PHP_VERSION);
}

define('SIGAH', true);
define('APP_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));
define('DATA_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'data');

require APP_PATH . '/Env.php';

// 1) .env       → variables globales del proyecto
// 2) .env.local → sobreescrituras de esta máquina (no se versiona)
Env::load(
    ROOT_PATH . DIRECTORY_SEPARATOR . '.env',
    ROOT_PATH . DIRECTORY_SEPARATOR . '.env.local'
);

require APP_PATH . '/helpers.php';
require APP_PATH . '/auth.php';
require APP_PATH . '/database.php';

/* ------------------------------------------------------------------
 |  Configuración general derivada del .env
 | ------------------------------------------------------------------ */

mb_internal_encoding('UTF-8');
date_default_timezone_set((string) env('APP_TIMEZONE', 'America/Argentina/Salta'));

if ((bool) env('APP_DEBUG', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', '0');
}

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
}

/* ------------------------------------------------------------------
 |  Verificación de que el .env existe
 | ------------------------------------------------------------------ */

if (!Env::has('APP_NAME')) {
    http_response_code(500);
    exit(
        'No se encontró el archivo .env. Copiá <code>.env.example</code> a <code>.env</code> '
        . 'en la raíz del proyecto y volvé a cargar la página.'
    );
}

start_session();
