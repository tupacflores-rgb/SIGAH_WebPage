<?php
declare(strict_types=1);

/**
 * TP8 — Guardián de rutas reutilizable.
 *
 * Incluir al inicio de cualquier página protegida:
 *     require __DIR__ . '/app/bootstrap.php';
 *     require __DIR__ . '/auth_guard.php';          // solo exige sesión
 *
 * Para restringir por rol, definir antes $rolesPermitidos:
 *     $rolesPermitidos = ['admin'];
 *     require __DIR__ . '/auth_guard.php';
 */

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

if (!isset($_SESSION['usuario_id'])) {
    redirect('index.php?error=acceso_denegado');
}

if (!empty($rolesPermitidos) && !in_array((string) ($_SESSION['rol'] ?? ''), $rolesPermitidos, true)) {
    http_response_code(403);
    flash_set('error', 'No tenés permisos para acceder a esa sección.');
    redirect('home.php');
}
