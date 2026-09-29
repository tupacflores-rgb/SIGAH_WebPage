<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require_login();

/* ------------------------------------------------------------------
 |  Estadísticas calculadas en el servidor a partir de data/
 | ------------------------------------------------------------------ */
$estudiantes = read_json('students.json')['estudiantes'] ?? [];
$institucion = read_json('institution.json');
$cursos      = $institucion['cursos'] ?? [];

$umbralRiesgo = (int) env('ATTENDANCE_RISK_THRESHOLD', 85);

$totalAlumnos = count($estudiantes);
$asistencias  = array_map(static fn (array $a): float => (float) ($a['asistencia'] ?? 0), $estudiantes);
$promedioAsis = $asistencias ? (int) round(array_sum($asistencias) / count($asistencias)) : 0;

$enRiesgo = count(array_filter(
    $estudiantes,
    static fn (array $a): bool => (float) ($a['asistencia'] ?? 100) < $umbralRiesgo
));

$totalCursos = count($cursos);

$usuario = auth_user();

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Dashboard';
$pageIcon    = 'fa-home';
$pageHeading = 'Panel Principal';
$pageDesc    = 'Resumen del sistema y accesos rápidos a todas las secciones.';
$breadcrumb  = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard'],
];

$pageStyles = <<<'CSS'
    .dash-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }
    @media(max-width:600px){ .dash-grid { grid-template-columns: 1fr 1fr; } }
    .dash-btn { background: var(--surface); border-radius: var(--radius); padding: 28px 16px; text-align: center; cursor: pointer; box-shadow: var(--shadow); text-decoration: none; color: var(--dark); display: flex; flex-direction: column; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; transition: box-shadow 0.2s, transform 0.15s; border-top: 4px solid var(--border); }
    .dash-btn:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.14); transform: translateY(-2px); text-decoration: none; }
    .dash-btn .icon { font-size: 36px; }
    .dash-btn.c1 { border-color: var(--primary); }
    .dash-btn.c2 { border-color: var(--success); }
    .dash-btn.c3 { border-color: var(--warning); }
    .dash-btn.c4 { border-color: #9C27B0; }
    .dash-btn.c5 { border-color: #F44336; }
    .dash-btn.c6 { border-color: #607D8B; }
    .welcome-box {
      background: linear-gradient(135deg, var(--dark), #0d3d54);
      border-radius: var(--radius);
      padding: 0;
      color: white;
      margin-bottom: 20px;
      overflow: hidden;
      display: flex;
      align-items: stretch;
      min-height: 110px;
    }
    .welcome-img { width: 180px; flex-shrink: 0; object-fit: cover; opacity: 0.55; }
    .welcome-text { padding: 22px 24px; }
    .welcome-box h2 { font-size: 18px; color: white; border: none; padding: 0; margin: 0 0 4px; }
    .welcome-box p { color: #a0c4d5; font-size: 13px; margin-top: 6px; }
    .stats-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; margin-bottom: 20px; }
    @media(max-width:600px){ .stats-row { grid-template-columns: 1fr 1fr; } }
    .stat { background: var(--surface); border-radius: var(--radius); padding: 14px 12px; text-align: center; box-shadow: var(--shadow); }
    .stat .n { font-size: 22px; font-weight: 700; }
    .stat .l { font-size: 11px; color: var(--light-gray); margin-top: 3px; }
    .aviso-img { width: 100%; height: 130px; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 14px; opacity: 0.85; }
    @media(max-width:600px){ .welcome-img { display: none; } }
CSS;

include __DIR__ . '/partials/header.php';

$flashSuccess = flash_get('success');
$flashError   = flash_get('error');
?>

  <main>
<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert-error" role="alert"><?= e($flashError) ?></div>
<?php endif; ?>

    <div class="welcome-box">
      <img class="welcome-img"
           src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&q=80"
           alt="Aula escolar – <?= e(env('SCHOOL_SHORT', '')) ?>">
      <div class="welcome-text">
        <h2><i class="fas fa-hand-sparkles"></i> Bienvenido/a, <?= e($usuario['nombre'] ?? '') ?></h2>
        <p>
          Hoy es <strong><?= e(fecha_larga()) ?></strong>.
          Tenés <strong><?= $enRiesgo ?> alumno<?= $enRiesgo === 1 ? '' : 's' ?></strong>
          por debajo del <?= $umbralRiesgo ?>% de asistencia.
        </p>
<?php if (!empty($usuario['demo'])): ?>
        <p><i class="fas fa-circle-info"></i> Estás navegando con el <strong>acceso de prueba</strong>.</p>
<?php endif; ?>
      </div>
    </div>

    <h2 style="font-size:15px;color:var(--gray);margin-bottom:12px;border:none;padding:0">
      <i class="fas fa-chart-simple"></i> Estadísticas del día
    </h2>
    <div class="stats-row">
      <div class="stat">
        <div class="n" style="color:var(--primary)"><?= $totalAlumnos ?></div>
        <div class="l"><i class="fas fa-users"></i> Alumnos</div>
      </div>
      <div class="stat">
        <div class="n" style="color:var(--success)"><?= $promedioAsis ?>%</div>
        <div class="l"><i class="fas fa-check-circle"></i> Asistencia</div>
      </div>
      <div class="stat">
        <div class="n" style="color:var(--warning)"><?= $enRiesgo ?></div>
        <div class="l"><i class="fas fa-exclamation-triangle"></i> En riesgo</div>
      </div>
      <div class="stat">
        <div class="n" style="color:#9C27B0"><?= $totalCursos ?></div>
        <div class="l"><i class="fas fa-graduation-cap"></i> Cursos</div>
      </div>
    </div>

    <div class="card">
      <h2><i class="fas fa-code"></i> Demo de JavaScript</h2>
      <p id="saludoDemo">Este panel demuestra mensajes, validación, menú interactivo, reloj y galería.</p>
      <div id="reloj">Cargando hora...</div>

      <div class="demo-menu">
        <button class="demo-menu-btn active" type="button" data-target="demo-panel-1">Sección 1</button>
        <button class="demo-menu-btn" type="button" data-target="demo-panel-2">Sección 2</button>
        <button class="demo-menu-btn" type="button" data-target="demo-panel-3">Sección 3</button>
      </div>

      <div class="demo-panel active" id="demo-panel-1">
        <p>Este bloque se muestra u oculta con el menú interactivo y ayuda a probar la navegación dinámica.</p>
      </div>
      <div class="demo-panel" id="demo-panel-2">
        <p>También podés cambiar el tema, validar formularios y revisar el resumen antes de enviar la información.</p>
      </div>
      <div class="demo-panel" id="demo-panel-3">
        <p>La galería permite avanzar imágenes y ampliar una vista previa al hacer clic sobre cada miniatura.</p>
      </div>

      <div class="gallery-main">
        <img id="galleryImage" src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&q=80" alt="Imagen principal de galería">
      </div>
      <div class="gallery-thumbs">
        <img class="gallery-thumb active" src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=500&q=80" alt="Miniatura 1" data-index="0">
        <img class="gallery-thumb" src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&q=80" alt="Miniatura 2" data-index="1">
        <img class="gallery-thumb" src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=500&q=80" alt="Miniatura 3" data-index="2">
        <img class="gallery-thumb" src="https://images.unsplash.com/photo-1513258496099-48168024aec0?w=500&q=80" alt="Miniatura 4" data-index="3">
      </div>
      <div class="gallery-controls">
        <button class="btn btn-outline" type="button" id="prevGallery">Anterior</button>
        <button class="btn btn-primary" type="button" id="nextGallery">Siguiente</button>
      </div>
    </div>

    <div class="card">
      <h2><i class="fas fa-file-signature"></i> Formulario con resumen</h2>
      <form id="resumenForm" novalidate>
        <div class="form-group">
          <label for="nombreResumen">Nombre completo</label>
          <input type="text" id="nombreResumen" placeholder="Ej: Ana García">
          <div class="field-error" id="nombreResumenError"></div>
        </div>
        <div class="form-group">
          <label for="emailResumen">Correo electrónico</label>
          <input type="email" id="emailResumen" placeholder="ana@sigah.edu.ar">
          <div class="field-error" id="emailResumenError"></div>
        </div>
        <div class="form-group">
          <label for="cursoResumen">Curso</label>
          <select id="cursoResumen">
            <option value="">Seleccioná un curso</option>
<?php foreach ($cursos as $curso): ?>
            <option value="<?= e($curso['nombre'] ?? '') ?>"><?= e($curso['nombre'] ?? '') ?></option>
<?php endforeach; ?>
          </select>
          <div class="field-error" id="cursoResumenError"></div>
        </div>
        <button class="btn btn-success" type="submit">Ver resumen</button>
        <div id="summaryBox" class="summary-box"></div>
      </form>
    </div>

    <h2 style="font-size:15px;color:var(--gray);margin-bottom:12px;border:none;padding:0">
      <i class="fas fa-bolt"></i> Accesos rápidos
    </h2>
    <div class="dash-grid">
      <a class="dash-btn c1" href="<?= e(url('asist.php')) ?>"><div class="icon"><i class="fas fa-clipboard-list"></i></div>Asistencia</a>
      <a class="dash-btn c2" href="<?= e(url('registry-al.php')) ?>"><div class="icon"><i class="fas fa-user-plus"></i></div>Registro Alumnos</a>
      <a class="dash-btn c3" href="<?= e(url('notif.php')) ?>"><div class="icon"><i class="fas fa-bell"></i></div>Notificaciones</a>
      <a class="dash-btn c4" href="<?= e(url('report.php')) ?>"><div class="icon"><i class="fas fa-chart-bar"></i></div>Reportes</a>
      <a class="dash-btn c5" href="<?= e(url('clock.php')) ?>"><div class="icon"><i class="fas fa-clock"></i></div>Horario</a>
      <a class="dash-btn c6" href="<?= e(url('config.php')) ?>"><div class="icon"><i class="fas fa-cogs"></i></div>Configuración</a>
    </div>

    <div class="card mt-24">
      <h2><i class="fas fa-bullhorn"></i> Aviso del sistema</h2>
      <img class="aviso-img"
           src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=900&q=75"
           alt="Desarrollo de sistemas – <?= e(env('APP_NAME', 'SIGAH')) ?>">
      <p>El sistema <?= e(env('APP_NAME', 'SIGAH')) ?> fue desarrollado como proyecto final de la materia
         <strong>Diseño UI/UX</strong> en la <strong><?= e(env('SCHOOL_NAME', '')) ?></strong>.</p>
      <br>
      <p>Esta versión web corre sobre <strong>PHP <?= e(PHP_VERSION) ?></strong> con <strong>Apache</strong> (XAMPP),
         y puede conectarse a <strong>MariaDB</strong> para la gestión de datos.</p>
      <br>
      <p>Para soporte técnico contactar a <strong><?= e(env('SUPPORT_BRAND', 'Pixel Fix')) ?></strong> —
         más de 100 reparaciones y miles de sistemas optimizados.</p>
    </div>
  </main>

<?php
$pageScripts = <<<'JS'
(function () {
  /* ---- Reloj ---- */
  function updateClock() {
    var reloj = document.getElementById('reloj');
    if (!reloj) return;
    reloj.textContent = new Date().toLocaleTimeString('es-AR', {
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    });
  }
  updateClock();
  setInterval(updateClock, 1000);

  /* ---- Menú de secciones ---- */
  document.querySelectorAll('.demo-menu-btn').forEach(function (button) {
    button.addEventListener('click', function () {
      document.querySelectorAll('.demo-menu-btn').forEach(function (b) { b.classList.remove('active'); });
      document.querySelectorAll('.demo-panel').forEach(function (p) { p.classList.remove('active'); });
      button.classList.add('active');
      var target = document.getElementById(button.dataset.target);
      if (target) target.classList.add('active');
    });
  });

  /* ---- Galería ---- */
  var galleryImages = [
    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&q=80',
    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200&q=80',
    'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1200&q=80',
    'https://images.unsplash.com/photo-1513258496099-48168024aec0?w=1200&q=80'
  ];
  var galleryIndex = 0;
  var galleryImage = document.getElementById('galleryImage');
  var thumbs = document.querySelectorAll('.gallery-thumb');

  function renderGallery() {
    if (!galleryImage) return;
    galleryImage.src = galleryImages[galleryIndex];
    galleryImage.alt = 'Imagen ' + (galleryIndex + 1) + ' de la galería';
    thumbs.forEach(function (thumb, index) {
      thumb.classList.toggle('active', index === galleryIndex);
    });
  }

  var next = document.getElementById('nextGallery');
  var prev = document.getElementById('prevGallery');

  if (next) next.addEventListener('click', function () {
    galleryIndex = (galleryIndex + 1) % galleryImages.length;
    renderGallery();
  });
  if (prev) prev.addEventListener('click', function () {
    galleryIndex = (galleryIndex - 1 + galleryImages.length) % galleryImages.length;
    renderGallery();
  });

  thumbs.forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      galleryIndex = Number(thumb.dataset.index);
      renderGallery();
    });
  });

  /* ---- Formulario con resumen ---- */
  var form = document.getElementById('resumenForm');
  if (!form) return;

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    var nombre  = document.getElementById('nombreResumen').value.trim();
    var email   = document.getElementById('emailResumen').value.trim();
    var curso   = document.getElementById('cursoResumen').value;
    var summary = document.getElementById('summaryBox');
    var valid   = true;

    form.querySelectorAll('.field-error').forEach(function (err) {
      err.textContent = '';
      err.classList.remove('show');
    });

    function fail(id, message) {
      var el = document.getElementById(id);
      el.textContent = message;
      el.classList.add('show');
      valid = false;
    }

    if (!nombre) fail('nombreResumenError', 'El nombre es obligatorio.');
    if (!email) fail('emailResumenError', 'El correo es obligatorio.');
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) fail('emailResumenError', 'Ingresá un correo válido.');
    if (!curso) fail('cursoResumenError', 'Debés seleccionar un curso.');

    if (!valid) {
      summary.classList.remove('show');
      return;
    }

    summary.innerHTML = '<strong>Resumen:</strong><br>Nombre: ' + nombre +
                        '<br>Correo: ' + email + '<br>Curso: ' + curso;
    summary.classList.add('show');
  });
})();
JS;

if ((bool) env('SHOW_WELCOME_ALERT', false)) {
    $pageScripts .= "\nwindow.addEventListener('load', function () { alert('¡Bienvenido al panel de " . e((string) env('APP_NAME', 'SIGAH')) . "!'); });";
}

include __DIR__ . '/partials/footer.php';
