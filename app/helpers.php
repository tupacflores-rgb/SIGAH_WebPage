<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/* ------------------------------------------------------------------
 |  Variables de entorno
 | ------------------------------------------------------------------ */

/**
 * Lee una variable del .env / .env.local.
 */
function env(string $key, mixed $default = null): mixed
{
    return Env::get($key, $default);
}

/**
 * Variables GLOBALES que también se exponen al JavaScript del navegador.
 * Nunca incluir aquí credenciales ni datos sensibles.
 *
 * @return array<string,mixed>
 */
function public_env(): array
{
    return [
        'appName'    => env('APP_NAME', 'SIGAH'),
        'appVersion' => env('APP_VERSION', '1.0.0'),
        'appEnv'     => env('APP_ENV', 'local'),
        'basePath'   => base_path_url(),
        'school'     => env('SCHOOL_SHORT', 'EET N°3100'),
        'locale'     => env('APP_LOCALE', 'es'),
    ];
}

/* ------------------------------------------------------------------
 |  URLs y rutas
 | ------------------------------------------------------------------ */

/**
 * Prefijo de URL del proyecto dentro de htdocs.
 *
 * Se detecta solo: si el proyecto está en C:\xampp\htdocs\sigah devuelve "/sigah",
 * y si está directamente en htdocs devuelve "".
 * BASE_PATH en el .env lo fuerza cuando hace falta (proxy, alias de Apache, etc.).
 */
function base_path_url(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $configured = (string) env('BASE_PATH', '');
    if (trim($configured) !== '') {
        return $base = '/' . trim($configured, '/');
    }

    $docRoot = str_replace('\\', '/', rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/'));
    $root    = str_replace('\\', '/', ROOT_PATH);

    if ($docRoot !== '' && str_starts_with($root . '/', $docRoot . '/')) {
        return $base = rtrim(substr($root, strlen($docRoot)), '/');
    }

    return $base = '';
}

/**
 * Construye una URL interna: url('home.php') => /sigah/home.php
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');

    return base_path_url() . '/' . $path;
}

/**
 * URL de un archivo dentro de assets/: asset('css/style.css')
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Redirige a una URL interna y corta la ejecución.
 */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Nombre del archivo actual (ej. "home.php"), para marcar el menú activo.
 */
function current_page(): string
{
    return basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
}

/* ------------------------------------------------------------------
 |  Salida segura
 | ------------------------------------------------------------------ */

/**
 * Escapa texto antes de imprimirlo en HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ------------------------------------------------------------------
 |  Lectura / escritura de los archivos JSON de data/
 | ------------------------------------------------------------------ */

function data_file(string $name): string
{
    return DATA_PATH . DIRECTORY_SEPARATOR . basename($name);
}

/**
 * Lee un JSON de data/ y lo devuelve como array.
 *
 * @return array<mixed>
 */
function read_json(string $name, array $default = []): array
{
    $file = data_file($name);

    if (!is_file($file)) {
        return $default;
    }

    $raw = file_get_contents($file);
    if ($raw === false || trim($raw) === '') {
        return $default;
    }

    $data = json_decode($raw, true);

    return is_array($data) ? $data : $default;
}

/**
 * Guarda un array como JSON en data/ (escritura atómica con bloqueo).
 */
function write_json(string $name, array $data): bool
{
    $file = data_file($name);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($json === false) {
        return false;
    }

    return file_put_contents($file, $json . "\n", LOCK_EX) !== false;
}

/* ------------------------------------------------------------------
 |  Mensajes flash (sobreviven a un redirect)
 | ------------------------------------------------------------------ */

function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'][$type] = $message;
}

function flash_get(string $type): ?string
{
    if (!isset($_SESSION['_flash'][$type])) {
        return null;
    }

    $message = (string) $_SESSION['_flash'][$type];
    unset($_SESSION['_flash'][$type]);

    return $message;
}

/* ------------------------------------------------------------------
 |  Tema claro / oscuro (se resuelve en el servidor para evitar parpadeo)
 | ------------------------------------------------------------------ */

function current_theme(): string
{
    $theme = (string) ($_COOKIE['sigah_theme'] ?? env('DEFAULT_THEME', 'light'));

    return $theme === 'dark' ? 'dark' : 'light';
}

/* ------------------------------------------------------------------
 |  Fechas en español (sin depender de la extensión intl)
 | ------------------------------------------------------------------ */

function fecha_larga(?int $timestamp = null): string
{
    $timestamp ??= time();

    $dias   = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses  = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
               'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    $dia = $dias[(int) date('w', $timestamp)];
    $mes = $meses[(int) date('n', $timestamp) - 1];

    return sprintf('%s, %s de %s de %s', $dia, date('j', $timestamp), $mes, date('Y', $timestamp));
}

/* ------------------------------------------------------------------
 |  CSRF
 | ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = (string) ($_POST['_csrf'] ?? '');

    return $sent !== '' && hash_equals(csrf_token(), $sent);
}

/**
 * Corta la petición si el token CSRF no es válido.
 */
function csrf_check(): void
{
    if (!csrf_valid()) {
        http_response_code(419);
        exit('Token de seguridad inválido o vencido. Volvé atrás y recargá la página.');
    }
}
