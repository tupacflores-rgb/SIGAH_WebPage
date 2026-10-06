<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/** Barra superior del sitio; la navegación se renderiza en nav.php. */
$isDark = current_theme() === 'dark';
?>
<header class="site-header">
  <a class="brand" href="<?= e(url(auth_check() ? 'home.php' : 'index.php')) ?>" aria-label="Ir al inicio">
    <span class="logo-text"><?= e(env('APP_NAME', 'SIGAH')) ?></span>
    <span class="logo-sub"><?= e(env('SCHOOL_SHORT', 'EET N°3100')) ?></span>
  </a>

<?php if (auth_check()): ?>
  <button class="hamburger" aria-label="Abrir menú" aria-expanded="false" aria-controls="main-nav" type="button">☰</button>
  <?php include __DIR__ . '/nav.php'; ?>
<?php endif; ?>

  <div class="header-actions">
    <button class="theme-toggle" id="themeToggle" type="button"
            aria-label="Cambiar entre tema claro y oscuro"
            aria-pressed="<?= $isDark ? 'true' : 'false' ?>">
      <i class="fas <?= $isDark ? 'fa-sun' : 'fa-moon' ?>"></i>
    </button>

<?php if (auth_check()): ?>
    <form method="post" action="<?= e(url('logout.php')) ?>" style="margin:0">
      <?= csrf_field() ?>
      <button class="btn btn-outline btn-small" type="submit">Salir</button>
    </form>
<?php else: ?>
    <a class="btn btn-outline btn-small" href="<?= e(url('index.php')) ?>">Ingresar</a>
<?php endif; ?>
  </div>
</header>