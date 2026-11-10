<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

// Cada ítem declara qué roles lo ven (coincide con los require_role de cada página).
$todos   = ['admin', 'docente', 'alumno'];
$staff   = ['admin', 'docente'];
$alumnos = db_ready() ? 'admin/index.php' : 'registry-al.php';

$navItems = [
    'home.php'        => ['Dashboard',      $todos],
    'asist.php'       => ['Asistencia',     $staff],
    $alumnos          => ['Alumnos',        $staff],
    'report.php'      => ['Reportes',       $staff],
    'notif.php'       => ['Notificaciones', $todos],
    'faq.php'         => ['FAQ',            $todos],
    'admin_panel.php' => ['Usuarios',       ['admin']],
    'config.php'      => ['Configuración',  ['admin']],
];
$rolActual  = auth_role();
$currentUrl = ltrim(str_replace(base_path_url(), '', (string) ($_SERVER['SCRIPT_NAME'] ?? '')), '/');
?>
<nav id="main-nav" aria-label="Navegación principal">
  <?php foreach ($navItems as $file => [$label, $roles]): ?>
    <?php if (!in_array($rolActual, $roles, true)) { continue; } ?>
    <?php $activo = $file === $currentUrl || (str_starts_with($file, 'admin/') && str_starts_with($currentUrl, 'admin/')); ?>
    <a href="<?= e(url($file)) ?>"<?= $activo ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>
