<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/**
 * Franja oscura con migas de pan, título y bajada.
 * Se incluye desde head.php cuando la página define $pageHeading.
 */

$breadcrumb = $breadcrumb ?? [];
?>
<div class="page-header">
<?php if ($breadcrumb): ?>
  <nav class="breadcrumb" aria-label="Ruta de navegación">
    <?php $last = array_key_last($breadcrumb); ?>
    <?php foreach ($breadcrumb as $i => $crumb): ?>
      <?php if (!empty($crumb['url'])): ?>
        <a href="<?= e(url($crumb['url'])) ?>"><?= e($crumb['label']) ?></a>
      <?php else: ?>
        <?= e($crumb['label']) ?>
      <?php endif; ?>
      <?php if ($i !== $last): ?><span>›</span><?php endif; ?>
    <?php endforeach; ?>
  </nav>
<?php endif; ?>

  <h1>
<?php if (!empty($pageHeadingHtml)): ?>
    <?= $pageHeadingHtml ?>
<?php else: ?>
    <?php if (!empty($pageIcon)): ?><i class="fas <?= e($pageIcon) ?> icon"></i><?php endif; ?>
    <?= e($pageHeading) ?>
<?php endif; ?>
  </h1>

<?php if (!empty($pageDesc)): ?>
  <p><?= e($pageDesc) ?></p>
<?php endif; ?>
</div>
