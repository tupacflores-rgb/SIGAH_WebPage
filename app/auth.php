<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/* ------------------------------------------------------------------
 |  Sesión
 | ------------------------------------------------------------------ */

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $lifetime = (int) env('SESSION_LIFETIME', 120) * 60;

    session_name((string) env('SESSION_NAME', 'SIGAH_SESSION'));
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => base_path_url() . '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (($_SERVER['HTTPS'] ?? 'off') !== 'off'),
    ]);

    session_start();
}

/* ------------------------------------------------------------------
 |  Usuarios (data/credentials.json)
 | ------------------------------------------------------------------ */

/**
 * @return array<int,array<string,mixed>>
 */
function users_all(): array
{
    $data = read_json('credentials.json');

    return is_array($data['usuarios'] ?? null) ? $data['usuarios'] : [];
}

/**
 * Reglas de contraseña definidas en credentials.json, con valores por defecto.
 *
 * @return array<string,mixed>
 */
function password_rules(): array
{
    $data = read_json('credentials.json');

    return ($data['validacion']['contrasena'] ?? null) ?: [
        'minCaracteres'    => 8,
        'requiereNumero'   => true,
        'requireMayuscula' => true,
        'requireMinuscula' => true,
        'requireEspecial'  => true,
    ];
}

/**
 * Valida una contraseña contra las reglas. Devuelve la lista de errores.
 *
 * @return string[]
 */
function password_errors(string $password): array
{
    $rules  = password_rules();
    $min    = (int) ($rules['minCaracteres'] ?? 8);
    $errors = [];

    if (mb_strlen($password) < $min) {
        $errors[] = "Mínimo {$min} caracteres";
    }
    if (!empty($rules['requiereNumero']) && !preg_match('/\d/', $password)) {
        $errors[] = 'Debe contener al menos un número';
    }
    if (!empty($rules['requireMayuscula']) && !preg_match('/[A-ZÁÉÍÓÚÑ]/u', $password)) {
        $errors[] = 'Debe contener al menos una mayúscula';
    }
    if (!empty($rules['requireMinuscula']) && !preg_match('/[a-záéíóúñ]/u', $password)) {
        $errors[] = 'Debe contener al menos una minúscula';
    }
    if (!empty($rules['requireEspecial']) && !preg_match('/[@$!%*?&]/', $password)) {
        $errors[] = 'Debe contener un carácter especial (@$!%*?&)';
    }

    return $errors;
}

/**
 * Busca un usuario por correo o nombre de usuario.
 *
 * @return array<string,mixed>|null
 */
function users_find(string $identifier): ?array
{
    $needle = mb_strtolower(trim($identifier));

    foreach (users_all() as $user) {
        $email   = mb_strtolower((string) ($user['email'] ?? ''));
        $usuario = mb_strtolower((string) ($user['usuario'] ?? ''));

        if ($needle !== '' && ($needle === $email || $needle === $usuario)) {
            return $user;
        }
    }

    return null;
}

/**
 * Verifica la contraseña. Acepta hashes de password_hash() y, por compatibilidad
 * con los datos de ejemplo originales, contraseñas en texto plano.
 */
function password_matches(string $password, string $stored): bool
{
    if ($stored === '') {
        return false;
    }

    if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon') || str_starts_with($stored, '$2a$')) {
        return password_verify($password, $stored);
    }

    return hash_equals($stored, $password);
}

/**
 * Agrega un usuario nuevo a credentials.json (con la contraseña hasheada).
 */
function users_create(array $user): bool
{
    $data = read_json('credentials.json');
    $data['usuarios'] ??= [];

    $data['usuarios'][] = $user;

    return write_json('credentials.json', $data);
}

function users_next_id(): int
{
    $ids = array_map(static fn (array $u): int => (int) ($u['id'] ?? 0), users_all());

    return ($ids ? max($ids) : 0) + 1;
}

/* ------------------------------------------------------------------
 |  Autenticación
 | ------------------------------------------------------------------ */

function auth_login(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'id'      => (int) ($user['id'] ?? 0),
        'nombre'  => (string) ($user['nombre'] ?? ''),
        'email'   => (string) ($user['email'] ?? ''),
        'usuario' => (string) ($user['usuario'] ?? ''),
        'rol'     => (string) ($user['rol'] ?? 'docente'),
        'demo'    => (bool) ($user['demo'] ?? false),
    ];
    $_SESSION['login_at'] = time();
}

/**
 * Cierra la sesión: borra los datos y renueva el identificador,
 * pero mantiene la sesión viva para poder mostrar el mensaje de despedida.
 */
function auth_logout(): void
{
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * @return array<string,mixed>|null
 */
function auth_user(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

function auth_role(): string
{
    return (string) (auth_user()['rol'] ?? 'invitado');
}

function auth_name(): string
{
    return (string) (auth_user()['nombre'] ?? 'Invitado');
}

/**
 * Exige sesión iniciada: si no la hay, vuelve al login.
 */
function require_login(): void
{
    if (auth_check()) {
        return;
    }

    flash_set('error', 'Iniciá sesión para acceder a esa sección.');
    $_SESSION['_intended'] = (string) ($_SERVER['REQUEST_URI'] ?? '');

    redirect('index.php');
}

/**
 * Exige uno de los roles indicados.
 */
function require_role(string ...$roles): void
{
    require_login();

    if (!in_array(auth_role(), $roles, true)) {
        http_response_code(403);
        flash_set('error', 'No tenés permisos para acceder a esa sección.');
        redirect('home.php');
    }
}

function auth_login_at(): ?int
{
    return isset($_SESSION['login_at']) ? (int) $_SESSION['login_at'] : null;
}
