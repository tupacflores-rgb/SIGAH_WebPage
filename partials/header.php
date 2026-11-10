<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/** Plantilla HTML compartida: metadatos, estilos y apertura del body. */
$titulo_pagina = $titulo_pagina ?? '';
$pageStyles  = $pageStyles  ?? '';
$bodyClass   = $bodyClass   ?? '';
$pageDesc    = $pageDesc    ?? '';
$breadcrumb  = $breadcrumb  ?? [];
$showHero    = $showHero    ?? false;

$appName  = (string) env('APP_NAME', 'SIGAH');
$fullName = $titulo_pagina !== '' ? $appName . ' — ' . $titulo_pagina : $appName . ' — ' . env('APP_TAGLINE', '');
$bodyClasses = trim(($bodyClass !== '' ? $bodyClass . ' ' : '') . (current_theme() === 'dark' ? 'dark-mode' : ''));
?>
<!DOCTYPE html>
<html lang="<?= e(env('APP_LOCALE', 'es')) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($pageDesc !== '' ? $pageDesc : env('APP_TAGLINE', '')) ?>">
  <title><?= e($fullName) ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(env('CDN_FONTAWESOME', '')) ?>">
<?php if ($pageStyles !== ''): ?>
  <style>
<?= $pageStyles ?>
  </style>
<?php endif; ?>
</head>
<body<?= $bodyClasses !== '' ? ' class="' . e($bodyClasses) . '"' : '' ?>>
<?php if ($showHero): ?>
  <div class="login-hero"></div>
<?php endif; ?>

<?php if ((bool) env('DB_ENABLED', false) && !db_ready()): ?>
  <div class="alert alert-error" role="alert" style="margin:12px">
    <strong>Sin conexión a MySQL.</strong> Los datos NO se están guardando en la base.
    Iniciá MySQL en XAMPP e importá <code>sql/sigah.sql</code> en phpMyAdmin.
<?php if ((bool) env('APP_DEBUG', false) && db_last_error()): ?>
    <br><small>Detalle: <?= e(db_last_error()) ?></small>
<?php endif; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/site-header.php'; ?>

<?php if (!empty($pageHeadingHtml) || !empty($pageHeading)): ?>
  <?php include __DIR__ . '/page-header.php'; ?>
<?php endif; ?>
