<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
// Página pública.

$app     = (string) env('APP_NAME', 'SIGAH');
$marca   = (string) env('SUPPORT_BRAND', 'Pixel Fix');
$soporte = (string) env('SUPPORT_EMAIL', '');
$escuela = (string) env('SCHOOL_NAME', '');
$ciudad  = (string) env('SCHOOL_CITY', 'Salta');

$nacimiento = new DateTimeImmutable('2009-01-05');
$edad       = $nacimiento->diff(new DateTimeImmutable('today'))->y;

$lenguajes = [
    'HTML5'      => 90,
    'CSS3'       => 85,
    'PHP'        => 80,
    'Python'     => 85,
    'JavaScript' => 70,
    'C++'        => 55,
];

$basesDatos = [
    'MariaDB' => 80,
    'MySQL'   => 75,
    'SQLite'  => 80,
];

$proyectos = [
    [
        'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?w=800&q=75',
        $app . ' — Sistema Integral de Gestión de Asistencias y Horarios',
        '<i class="fab fa-php"></i> PHP · <i class="fab fa-html5"></i> HTML/CSS/JS · <i class="fas fa-database"></i> MariaDB',
        'Sistema web completo para gestión escolar. Incluye registro de alumnos, toma de asistencia, '
        . 'visualización de horarios, reportes y notificaciones automáticas.',
    ],
    [
        'https://images.unsplash.com/photo-1484807352052-23338990c6c6?w=800&q=75',
        'SANE — Sistema Automático de Notificaciones Escolares',
        '<i class="fab fa-python"></i> Python',
        'Módulo de automatización para el envío de alertas a padres y docentes ante altos índices de ausentismo.',
    ],
    [
        'https://images.unsplash.com/photo-1597852074816-d933c7d2b988?w=800&q=75',
        $marca,
        '<i class="fas fa-tools"></i> Hardware · <i class="fas fa-code"></i> Software · <i class="fas fa-user-friends"></i> Atención al cliente',
        'Marca personal de reparación de computadoras. +100 reparaciones y miles de consultas resueltas en '
        . $ciudad . ' desde 2022.',
    ],
];

$titulo_pagina = 'Currículum Vitae';
$pageIcon    = 'fa-file-alt';
$pageHeading = 'Currículum Vitae';
$pageDesc    = 'Perfil profesional y académico de Rolando Maximo Tupac Flores Arce.';
$breadcrumb  = [
    ['label' => $app, 'url' => 'index.php'],
    ['label' => 'CV'],
];

$pageStyles = <<<'CSS'
    .cv-top { background: linear-gradient(135deg, #0f2533 0%, #0d3d54 100%); border-radius: var(--radius); padding: 32px 28px; color: white; display: flex; gap: 24px; align-items: center; margin-bottom: 24px; flex-wrap: wrap; }
    .cv-avatar { width: 110px; height: 110px; border-radius: 50%; overflow: hidden; border: 4px solid var(--primary); flex-shrink: 0; background: #e0f7fa; }
    .cv-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .cv-intro h1 { color: white; font-size: 22px; }
    .cv-intro h2 { color: var(--primary); font-size: 14px; font-weight: 400; border: none; padding: 0; margin-top: 4px; }
    .cv-intro p  { color: #a0c4d5; font-size: 13px; margin-top: 6px; line-height: 1.7; }
    .cv-contact  { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 12px; font-size: 12px; color: #a0c4d5; }
    .cv-contact span { display: flex; align-items: center; gap: 4px; }
    .cv-section { margin-bottom: 24px; }
    .cv-section h2 { font-size: 15px; color: var(--dark); padding-bottom: 8px; border-bottom: 2px solid var(--primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .cv-item { margin-bottom: 18px; padding-left: 14px; border-left: 3px solid var(--border); }
    .cv-item h3 { font-size: 15px; color: var(--dark); }
    .cv-item h4 { font-size: 13px; color: var(--primary); font-weight: 400; margin-top: 2px; }
    .cv-item .period { font-size: 12px; color: var(--light-gray); margin-top: 2px; display: flex; align-items: center; gap: 4px; }
    .cv-item p { font-size: 13px; color: var(--gray); margin-top: 6px; line-height: 1.7; }
    .cv-item ul { font-size: 13px; color: var(--gray); padding-left: 18px; line-height: 1.9; margin-top: 4px; }
    .cv-item-img { width: 100%; height: 120px; object-fit: cover; border-radius: var(--radius-sm); margin-top: 10px; opacity: 0.88; }
    .skills-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; }
    @media(max-width:600px){ .skills-grid { grid-template-columns: 1fr 1fr; } }
    .skill-item { background: var(--bg); border-radius: 8px; padding: 12px 14px; }
    .skill-item h4 { font-size: 13px; color: var(--dark); margin-bottom: 6px; }
    .skill-bar-wrap { background: var(--border); border-radius: 20px; height: 6px; }
    .skill-bar { background: var(--primary); border-radius: 20px; height: 6px; }
    .skill-pct { font-size: 11px; color: var(--light-gray); margin-top: 4px; text-align: right; }
    .project-card { background: var(--bg); border: 1px solid var(--border); border-radius: 8px; overflow: hidden; margin-bottom: 12px; }
    .project-card-img { width: 100%; height: 100px; object-fit: cover; opacity: 0.82; }
    .project-card-body { padding: 12px 16px; }
    .project-card h3 { font-size: 14px; color: var(--dark); }
    .project-card h4 { font-size: 12px; color: var(--primary); margin-top: 2px; }
    .project-card p { font-size: 13px; color: var(--gray); margin-top: 6px; line-height: 1.7; }
    @media print {
      .site-header, footer, .page-header, .no-print { display: none !important; }
      body { background: white; }
      .card { box-shadow: none; border: 1px solid #ddd; }
    }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <main>
    <div class="cv-top">
      <div class="cv-avatar">
        <img src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&q=80"
             alt="Foto de perfil – Rolando Flores Arce">
      </div>
      <div class="cv-intro">
        <h1>Rolando Maximo Tupac Flores Arce</h1>
        <h2>
          <i class="fas fa-code"></i> Desarrollador de Software ·
          <i class="fas fa-tools"></i> Técnico en Reparación de PC ·
          <i class="fas fa-graduation-cap"></i> Estudiante
        </h2>
        <p>
          Estudiante de la <?= e($escuela) ?> — <?= e($ciudad) ?>, Argentina.<br>
          Fundador de <strong><?= e($marca) ?></strong>, marca personal de reparación de computadoras con<br>
          más de 100 reparaciones y miles de consultas resueltas.
        </p>
        <div class="cv-contact">
          <span><i class="fas fa-calendar-alt"></i> <?= e($nacimiento->format('d/m/Y')) ?></span>
          <span><i class="fas fa-map-marker-alt"></i> <?= e($ciudad) ?>, Argentina</span>
          <span><i class="fas fa-envelope"></i> <?= e($soporte) ?></span>
        </div>
      </div>
    </div>

    <div style="text-align:right;margin-bottom:16px" class="no-print">
      <button class="btn btn-outline" type="button" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir / Guardar PDF
      </button>
    </div>

    <div class="card cv-section">
      <h2><i class="fas fa-briefcase"></i> Experiencia laboral y proyectos</h2>

      <div class="cv-item">
        <h3><?= e($marca) ?> — Reparación de computadoras</h3>
        <h4>Fundador y Técnico Principal</h4>
        <div class="period"><i class="fas fa-calendar-alt"></i> 2022 — Presente · <?= e($ciudad) ?>, Argentina</div>
        <p>Marca personal de servicios técnicos de hardware y software para computadoras de escritorio y notebooks.</p>
        <ul>
          <li>Más de <strong>100 reparaciones</strong> de hardware completadas exitosamente</li>
          <li>Miles de consultas técnicas resueltas de forma presencial y remota</li>
          <li>Recuperación y optimización de sistemas operativos (Windows/Linux)</li>
          <li>Instalación y configuración de software a medida para clientes</li>
          <li>Mantenimiento preventivo y limpieza de equipos</li>
        </ul>
        <img class="cv-item-img"
             src="https://images.unsplash.com/photo-1597852074816-d933c7d2b988?w=800&q=75"
             alt="Taller de reparación de computadoras – <?= e($marca) ?>">
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin:16px 0">

      <div class="cv-item">
        <h3><?= e($app) ?> — Sistema Integral de Gestión de Asistencias y Horarios</h3>
        <h4>Desarrollador principal · Proyecto académico</h4>
        <div class="period"><i class="fas fa-calendar-alt"></i> 2025 — 2026 · <?= e(env('SCHOOL_SHORT', '')) ?></div>
        <p>Sistema web completo para la gestión de asistencias y horarios escolares.</p>
        <ul>
          <li>Backend en <strong>PHP</strong> sobre Apache, con <strong>MariaDB</strong> opcional</li>
          <li>Frontend con <strong>HTML5, CSS3 y JavaScript</strong> sin frameworks externos</li>
          <li>Configuración por <strong>variables de entorno (.env)</strong> y sesiones del lado del servidor</li>
          <li>Módulos: asistencia, horarios, notificaciones, reportes, registro de alumnos</li>
          <li>Integración con <strong>SANE</strong> para notificaciones automáticas</li>
        </ul>
        <img class="cv-item-img"
             src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=800&q=75"
             alt="Desarrollo del sistema <?= e($app) ?>">
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin:16px 0">

      <div class="cv-item">
        <h3>SANE — Sistema Automático de Notificaciones Escolares</h3>
        <h4>Desarrollador · Proyecto personal</h4>
        <div class="period"><i class="fas fa-calendar-alt"></i> 2025 — Presente</div>
        <p>Módulo complementario a <?= e($app) ?> que automatiza el envío de notificaciones.</p>
        <ul>
          <li>Desarrollado íntegramente en <strong>Python</strong></li>
          <li>Integración con servicios de mensajería y correo electrónico</li>
          <li>Configuración de umbrales y reglas de disparo automático</li>
        </ul>
      </div>
    </div>

    <div class="card cv-section">
      <h2><i class="fas fa-graduation-cap"></i> Educación</h2>
      <div class="cv-item">
        <h3><?= e($escuela) ?></h3>
        <h4>Educación Secundaria Técnica</h4>
        <div class="period"><i class="fas fa-calendar-alt"></i> 2021 — Presente · <?= e($ciudad) ?>, Argentina</div>
        <img class="cv-item-img"
             src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=800&q=75"
             alt="<?= e(env('SCHOOL_SHORT', '')) ?> – Escuela técnica"
             style="margin-bottom:10px">
        <ul>
          <li>Materias destacadas: Diseño UI/UX, Programación, Bases de datos, Redes</li>
          <li>Proyecto final integrador: sistema <?= e($app) ?></li>
        </ul>
      </div>
    </div>

    <div class="card cv-section">
      <h2><i class="fas fa-cogs"></i> Habilidades técnicas</h2>

      <h3 style="font-size:14px;color:var(--gray);margin-bottom:12px">
        <i class="fas fa-code"></i> Lenguajes de programación
      </h3>
      <div class="skills-grid">
<?php foreach ($lenguajes as $nombre => $nivel): ?>
        <div class="skill-item">
          <h4><?= e($nombre) ?></h4>
          <div class="skill-bar-wrap"><div class="skill-bar" style="width:<?= (int) $nivel ?>%"></div></div>
          <div class="skill-pct"><?= (int) $nivel ?>%</div>
        </div>
<?php endforeach; ?>
      </div>

      <br>
      <h3 style="font-size:14px;color:var(--gray);margin-bottom:12px">
        <i class="fas fa-database"></i> Bases de datos
      </h3>
      <div class="skills-grid">
<?php foreach ($basesDatos as $nombre => $nivel): ?>
        <div class="skill-item">
          <h4><?= e($nombre) ?></h4>
          <div class="skill-bar-wrap"><div class="skill-bar" style="width:<?= (int) $nivel ?>%"></div></div>
          <div class="skill-pct"><?= (int) $nivel ?>%</div>
        </div>
<?php endforeach; ?>
      </div>

      <br>
      <h3 style="font-size:14px;color:var(--gray);margin-bottom:12px">
        <i class="fas fa-tools"></i> Técnico en hardware
      </h3>
      <p style="font-size:13px;color:var(--gray);line-height:1.8">
        Diagnóstico de fallas en hardware · Soldadura y reemplazo de componentes ·
        Instalación y configuración de SO (Windows / Linux) · Redes básicas ·
        Recuperación de datos · Optimización de sistemas
      </p>
    </div>

    <div class="card cv-section">
      <h2><i class="fas fa-rocket"></i> Proyectos destacados</h2>

<?php foreach ($proyectos as [$imagen, $titulo, $stack, $descripcion]): ?>
      <div class="project-card">
        <img class="project-card-img" src="<?= e($imagen) ?>" alt="<?= e($titulo) ?>">
        <div class="project-card-body">
          <h3><?= e($titulo) ?></h3>
          <h4><?= $stack ?></h4>
          <p><?= e($descripcion) ?></p>
        </div>
      </div>
<?php endforeach; ?>
    </div>

    <div class="card cv-section">
      <h2><i class="fas fa-info-circle"></i> Información adicional</h2>
<pre style="background:var(--bg);padding:12px;border-radius:6px;font-size:12px;border-left:3px solid var(--primary)">Nombre   : Rolando Maximo Tupac Flores Arce
Nacido   : <?= e($nacimiento->format('d/m/Y')) ?> (<?= $edad ?> años)
Ciudad   : <?= e($ciudad) ?>, Argentina
Escuela  : <?= e($escuela) ?>

Contacto : <?= e($soporte) ?>

Proyectos: <?= e($app) ?> · SANE · <?= e($marca) ?></pre>
      <br>
      <p style="font-size:13px;color:var(--gray);line-height:1.8">
        Idiomas: <strong>Español</strong> (nativo) · <strong>Inglés técnico</strong> (lectura de documentación).<br>
        Disponibilidad: tiempo parcial, tardes y fines de semana.<br>
        Intereses: programación, electrónica, robótica, sistemas embebidos.
      </p>
    </div>
  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
