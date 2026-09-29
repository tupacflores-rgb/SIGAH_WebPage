<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/**
 * Pie de página + scripts globales. Cierra el documento.
 *
 * Variable LOCAL opcional:
 *   $pageScripts  string  JavaScript propio de la página (se imprime tal cual).
 */

$pageScripts = $pageScripts ?? '';
?>
<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <h4><?= e(env('APP_NAME', 'SIGAH')) ?></h4>
      <p><?= e(env('APP_TAGLINE', '')) ?> de la <?= e(env('SCHOOL_NAME', '')) ?>.</p>
    </div>

    <div class="footer-links">
      <h5>Secciones</h5>
      <a href="<?= e(url('home.php')) ?>">Dashboard</a>
      <a href="<?= e(url('asist.php')) ?>">Asistencia</a>
      <a href="<?= e(url('report.php')) ?>">Reportes</a>
      <a href="<?= e(url('faq.php')) ?>">Preguntas frecuentes</a>
    </div>

    <div class="footer-contact">
      <h5>Contacto</h5>
      <p><i class="fas fa-envelope"></i> <a href="mailto:<?= e(env('SCHOOL_EMAIL', '')) ?>"><?= e(env('SCHOOL_EMAIL', '')) ?></a></p>
      <p><i class="fas fa-phone"></i> <?= e(env('SCHOOL_PHONE', '')) ?></p>
      <p><i class="fas fa-location-dot"></i> <?= e(env('SCHOOL_CITY', '')) ?></p>
    </div>
  </div>

  <div class="footer-copy">
    © <?= date('Y') ?> <?= e(env('APP_NAME', 'SIGAH')) ?> v<?= e(env('APP_VERSION', '1.0.0')) ?> — Diseño UI/UX
  </div>
</footer>

<script>
  // Variables GLOBALES del .env que el navegador puede usar.
  window.SIGAH = <?= json_encode(public_env(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= e(asset('js/components.js')) ?>"></script>
<?php if ($pageScripts !== ''): ?>
<script>
<?= $pageScripts ?>
</script>
<?php endif; ?>
</body>
</html>
