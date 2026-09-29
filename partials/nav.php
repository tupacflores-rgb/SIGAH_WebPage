<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$navItems = [
    'home.php'        => 'Dashboard',
    'asist.php'       => 'Asistencia',
    'registry-al.php' => 'Alumnos',
    'report.php'      => 'Reportes',
    'notif.php'       => 'Notificaciones',
    'faq.php'         => 'FAQ',
    'config.php'      => 'Configuración',
];
$currentPage = current_page();
?>
<nav id="main-nav" aria-label="Navegación principal">
  <?php foreach ($navItems as $file => $label): ?>
    <a href="<?= e(url($file)) ?>"<?= $file === $currentPage ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>