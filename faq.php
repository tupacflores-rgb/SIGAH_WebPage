<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
// Página pública: no requiere sesión iniciada.

$app     = (string) env('APP_NAME', 'SIGAH');
$escuela = (string) env('SCHOOL_NAME', '');
$soporte = (string) env('SUPPORT_EMAIL', '');
$marca   = (string) env('SUPPORT_BRAND', 'Pixel Fix');

/* ------------------------------------------------------------------
 |  Contenido del FAQ (el HTML de las respuestas se imprime tal cual)
 | ------------------------------------------------------------------ */
$secciones = [
    [
        'titulo' => 'Sobre ' . $app,
        'icono'  => 'fa-laptop',
        'imagen' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=900&q=75',
        'alt'    => $app . ' – Sistema Integral de Gestión Escolar',
        'items'  => [
            [
                '¿Qué es ' . $app . '?',
                '<p>' . e($app) . ' es el <strong>Sistema Integral de Gestión de Asistencias y Horarios</strong>, '
                . 'desarrollado por Rolando Maximo Tupac Flores Arce como proyecto de la materia Diseño UI/UX en la '
                . e($escuela) . '.</p>'
                . '<p>Permite a instituciones educativas registrar asistencias, gestionar horarios de clases, '
                . 'generar reportes y enviar notificaciones automáticas a través del módulo SANE.</p>',
            ],
            [
                '¿Con qué tecnologías está desarrollado?',
                '<img class="faq-img" src="https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=800&q=75" alt="Tecnologías del backend">'
                . '<pre>Servidor web : Apache (XAMPP)
Backend      : PHP ' . e(PHP_VERSION) . '
Base de datos: MariaDB (opcional) / archivos JSON
Frontend     : HTML5 + CSS3 + JavaScript
Notificaciones: SANE</pre>'
                . '<p>No utiliza frameworks externos pesados: el frontend es 100% HTML, CSS y JS nativo, '
                . 'y el backend son páginas PHP con includes compartidos.</p>',
            ],
            [
                '¿Qué módulos incluye el sistema?',
                '<ul>
                    <li><i class="fas fa-sign-in-alt"></i> <strong>Inicio de sesión</strong> — Acceso con roles y sesión en el servidor</li>
                    <li><i class="fas fa-tachometer-alt"></i> <strong>Dashboard</strong> — Estadísticas y accesos rápidos</li>
                    <li><i class="fas fa-user-plus"></i> <strong>Registro de alumnos</strong> — Alta, edición y baja</li>
                    <li><i class="fas fa-clipboard-list"></i> <strong>Asistencia</strong> — Toma y seguimiento por curso y materia</li>
                    <li><i class="fas fa-clock"></i> <strong>Horario</strong> — Grilla semanal</li>
                    <li><i class="fas fa-chart-bar"></i> <strong>Reportes</strong> — Gráficos y exportación a PDF</li>
                    <li><i class="fas fa-bell"></i> <strong>Notificaciones</strong> — Alertas automáticas (SANE)</li>
                    <li><i class="fas fa-cogs"></i> <strong>Configuración</strong> — Institución, ciclo lectivo y avisos</li>
                 </ul>',
            ],
            [
                '¿Qué es SANE?',
                '<p>SANE es el <strong>Sistema Automático de Notificaciones Escolares</strong>, un módulo complementario a ' . e($app) . '.</p>'
                . '<p>Detecta automáticamente situaciones de riesgo (como alto ausentismo) y genera notificaciones '
                . 'para padres, docentes y administradores sin intervención manual.</p>',
            ],
            [
                '¿Dónde se guardan los datos?',
                '<p>Por defecto, en archivos JSON dentro de la carpeta <code>data/</code> del proyecto. '
                . 'Si activás <code>DB_ENABLED=true</code> en el archivo <code>.env</code> e importás '
                . '<code>sql/sigah.sql</code>, ' . e($app) . ' puede usar la base <strong>MariaDB</strong> que trae XAMPP.</p>',
            ],
        ],
    ],
    [
        'titulo' => 'Sobre ' . $marca,
        'icono'  => 'fa-tools',
        'imagen' => 'https://images.unsplash.com/photo-1597852074816-d933c7d2b988?w=900&q=75',
        'alt'    => $marca . ' – Taller de reparación de PCs',
        'items'  => [
            [
                '¿Qué es ' . $marca . '?',
                '<p><strong>' . e($marca) . '</strong> es la marca personal de servicios técnicos de Rolando Flores Arce, '
                . 'especializada en reparación y mantenimiento de computadoras de escritorio y notebooks.</p>'
                . '<p>Con más de <strong>100 reparaciones exitosas</strong> y miles de consultas resueltas, '
                . 'es referente en soporte técnico en ' . e((string) env('SCHOOL_CITY', 'Salta')) . ', Argentina.</p>',
            ],
            [
                '¿Qué servicios ofrece?',
                '<ul>
                    <li><i class="fas fa-microchip"></i> Reparación de hardware (placa base, pantalla, teclado, disco)</li>
                    <li><i class="fas fa-database"></i> Recuperación de datos y sistemas operativos</li>
                    <li><i class="fab fa-windows"></i> Instalación y reinstalación de Windows/Linux</li>
                    <li><i class="fas fa-bolt"></i> Optimización y limpieza de equipos lentos</li>
                    <li><i class="fas fa-network-wired"></i> Configuración de redes domésticas y laborales</li>
                    <li><i class="fas fa-headset"></i> Consultoría técnica personalizada</li>
                 </ul>',
            ],
            [
                '¿Cómo puedo solicitar un servicio?',
                '<p>Podés contactar por correo electrónico o de forma presencial en ' . e((string) env('SCHOOL_CITY', 'Salta')) . '.</p>'
                . '<p><i class="fas fa-envelope"></i> <strong>' . e($soporte) . '</strong></p>'
                . '<p>Respondemos en menos de 24 horas hábiles.</p>',
            ],
        ],
    ],
    [
        'titulo' => 'Uso del sistema',
        'icono'  => 'fa-book',
        'imagen' => null,
        'alt'    => '',
        'items'  => [
            [
                '¿Cómo registro la asistencia diaria?',
                '<p>Desde el <a href="' . e(url('home.php')) . '">Dashboard</a>, entrá al módulo '
                . '<a href="' . e(url('asist.php')) . '"><strong>Asistencia</strong></a>. Elegí curso, fecha y materia, '
                . 'tocá el estado de cada alumno para cambiarlo y presioná <em>Guardar asistencia</em>. '
                . 'El registro queda guardado en el servidor.</p>',
            ],
            [
                '¿Cómo genero un reporte de asistencia?',
                '<p>Desde el módulo <a href="' . e(url('report.php')) . '">Reportes</a> elegí el curso. '
                . 'El sistema calcula las estadísticas y el gráfico automáticamente. '
                . 'Con <em>Exportar / Imprimir PDF</em> usás el diálogo de impresión del navegador para guardarlo.</p>',
            ],
            [
                '¿Qué pasa si un alumno alcanza el límite de ausencias?',
                '<p>El módulo <strong>SANE</strong> detecta cuando un alumno baja del umbral configurado '
                . '(<code>ATTENDANCE_RISK_THRESHOLD</code> en el <code>.env</code>, hoy '
                . (int) env('ATTENDANCE_RISK_THRESHOLD', 85) . '%). Se genera una alerta en '
                . '<a href="' . e(url('notif.php')) . '">Notificaciones</a> y aparece en el reporte.</p>',
            ],
            [
                'Olvidé mi contraseña, ¿qué hago?',
                '<p>Esta versión no envía correos de recuperación. Pedile a un usuario con rol '
                . '<strong>administrador</strong> que te dé de alta de nuevo desde '
                . '<a href="' . e(url('add-user.php')) . '">Crear usuario</a>, o escribinos a '
                . '<strong>' . e($soporte) . '</strong>.</p>',
            ],
        ],
    ],
];

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Preguntas frecuentes';
$pageIcon    = 'fa-question-circle';
$pageHeading = 'Preguntas Frecuentes';
$pageDesc    = 'Respuestas a las dudas más comunes sobre ' . $app . ' y ' . $marca . '.';
$breadcrumb  = [
    ['label' => $app, 'url' => 'index.php'],
    ['label' => 'FAQ'],
];

$pageStyles = <<<'CSS'
    .faq-banner { width: 100%; height: 120px; object-fit: cover; border-radius: var(--radius); margin-bottom: 20px; opacity: 0.85; display: block; }
    .faq-item { border-bottom: 1px solid var(--border); }
    .faq-item:last-child { border-bottom: none; }
    .faq-question { width: 100%; text-align: left; background: none; border: none; padding: 16px 0; font-size: 14px; font-weight: 600; color: var(--dark); cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 10px; font-family: inherit; }
    .faq-question:hover { color: var(--primary); }
    .faq-arrow { font-size: 18px; color: var(--primary); flex-shrink: 0; }
    .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
    .faq-answer.open { max-height: 900px; }
    .faq-answer p { font-size: 13px; color: var(--gray); line-height: 1.8; padding-bottom: 12px; }
    .faq-answer pre { background: var(--bg); border-left: 3px solid var(--primary); padding: 10px 12px; border-radius: 6px; font-size: 11px; margin-bottom: 14px; line-height: 1.7; }
    .faq-answer ul { font-size: 13px; color: var(--gray); padding-left: 20px; line-height: 1.9; padding-bottom: 14px; }
    .faq-answer code { background: var(--bg); padding: 1px 5px; border-radius: 4px; }
    .faq-img { width: 100%; height: 110px; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 12px; opacity: 0.82; }
    .contact-box { border-radius: var(--radius); overflow: hidden; }
    .contact-box-img { width: 100%; height: 110px; object-fit: cover; filter: brightness(0.4); display: block; }
    .contact-box-body { background: #0f2533; padding: 24px; text-align: center; color: white; }
    .contact-box-body h2 { color: var(--primary); border: none; font-size: 18px; padding: 0; margin-bottom: 10px; }
    .contact-box-body p { color: #aaa; font-size: 13px; margin-bottom: 20px; line-height: 1.8; }
CSS;

include __DIR__ . '/partials/header.php';

$indice = 0;
?>

  <main>
<?php foreach ($secciones as $seccion): ?>
    <div class="card">
      <h2><i class="fas <?= e($seccion['icono']) ?>"></i> <?= e($seccion['titulo']) ?></h2>

<?php if (!empty($seccion['imagen'])): ?>
      <img class="faq-banner" src="<?= e($seccion['imagen']) ?>" alt="<?= e($seccion['alt']) ?>">
<?php endif; ?>

<?php foreach ($seccion['items'] as [$pregunta, $respuesta]): ?>
<?php $indice++; ?>
      <div class="faq-item">
        <button class="faq-question" type="button"
                aria-expanded="false" aria-controls="faq-answer-<?= $indice ?>">
          <span><?= e($pregunta) ?></span>
          <span class="faq-arrow"><i class="fas fa-plus"></i></span>
        </button>
        <div class="faq-answer" id="faq-answer-<?= $indice ?>">
          <?= $respuesta ?>
        </div>
      </div>
<?php endforeach; ?>
    </div>
<?php endforeach; ?>

    <div class="contact-box">
      <img class="contact-box-img"
           src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=900&q=75"
           alt="Contacto y soporte técnico <?= e($marca) ?>">
      <div class="contact-box-body">
        <h2><i class="fas fa-comment-dots"></i> ¿No encontraste tu respuesta?</h2>
        <p>
          Escribinos y te respondemos a la brevedad.<br>
          También podés revisar el <a href="<?= e(url('blog.php')) ?>" style="color:var(--primary)">Blog técnico</a>.
        </p>
        <a href="mailto:<?= e($soporte) ?>" class="btn btn-primary" style="padding:12px 28px;font-size:14px">
          <i class="fas fa-envelope"></i> Contactar a soporte
        </a>
      </div>
    </div>
  </main>

<?php
$pageScripts = <<<'JS'
(function () {
  document.querySelectorAll('.faq-question').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var answer = btn.nextElementSibling;
      var isOpen = answer.classList.contains('open');

      document.querySelectorAll('.faq-answer').forEach(function (a) { a.classList.remove('open'); });
      document.querySelectorAll('.faq-question').forEach(function (b) {
        b.setAttribute('aria-expanded', 'false');
        var arrow = b.querySelector('.faq-arrow');
        if (arrow) arrow.innerHTML = '<i class="fas fa-plus"></i>';
      });

      if (!isOpen) {
        answer.classList.add('open');
        btn.setAttribute('aria-expanded', 'true');
        var arrow = btn.querySelector('.faq-arrow');
        if (arrow) arrow.innerHTML = '<i class="fas fa-minus"></i>';
      }
    });
  });
})();
JS;

include __DIR__ . '/partials/footer.php';
