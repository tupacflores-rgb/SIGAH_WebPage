<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
// Página pública.

$app     = (string) env('APP_NAME', 'SIGAH');
$marca   = (string) env('SUPPORT_BRAND', 'Pixel Fix');
$escuela = (string) env('SCHOOL_NAME', '');

/* ------------------------------------------------------------------
 |  Nuevo comentario (se guarda en data/comments.json)
 | ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $slug   = (string) ($_POST['post'] ?? '');
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $texto  = trim((string) ($_POST['comentario'] ?? ''));

    if ($slug === '' || $nombre === '' || $texto === '') {
        flash_set('error', 'Completá tu nombre y el comentario antes de publicar.');
    } elseif (mb_strlen($texto) > 1000) {
        flash_set('error', 'El comentario es demasiado largo (máximo 1000 caracteres).');
    } else {
        $comentarios = read_json('comments.json');
        $comentarios[$slug][] = [
            'nombre' => mb_substr($nombre, 0, 60),
            'texto'  => $texto,
            'fecha'  => date('c'),
        ];

        if (write_json('comments.json', $comentarios)) {
            flash_set('success', 'Comentario publicado. ¡Gracias por participar!');
        } else {
            flash_set('error', 'No se pudo guardar el comentario. Revisá los permisos de la carpeta data/.');
        }
    }

    redirect('blog.php#' . ($slug !== '' ? $slug : 'top'));
}

$comentariosGuardados = read_json('comments.json');

$categorias = ['Todos', 'Python', 'HTML/CSS', 'PHP', 'Reparación PC', 'Bases de datos', $app];

/* ------------------------------------------------------------------
 |  Entradas del blog
 | ------------------------------------------------------------------ */
$cuerpoSigah = <<<HTML
        <h3>El problema que quería resolver</h3>
        <p>
          En mi escuela, la {$escuela}, el registro de asistencia se hacía en papel.
          Eso generaba errores, demoras y era imposible generar reportes rápidos.
          Así nació la idea de <strong>{$app}</strong>.
        </p>

        <img class="post-inline-img"
             src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=800&q=75"
             alt="Aula escolar – contexto del problema de asistencia">

        <h3>De páginas sueltas a una aplicación PHP</h3>
        <p>
          La primera versión eran archivos <code>.html</code> independientes que repetían el mismo
          encabezado y el mismo pie. Con PHP pasaron a compartir includes y a leer su configuración
          de un único archivo <code>.env</code>.
        </p>

        <h4><i class="fas fa-code"></i> Estructura del proyecto:</h4>
        <pre>SIGAH/
├── .env                 ← variables globales
├── index.php            ← login
├── app/                 ← bootstrap, helpers, auth, base de datos
├── partials/            ← header, footer, page-header
├── data/                ← JSON con alumnos, usuarios e institución
└── assets/              ← css y js</pre>

        <h3>Resultado</h3>
        <p>Más de 100 reparaciones de PC documentadas con {$marca} y sistemas escolares gestionados con {$app}.</p>
HTML;

$posts = [
    [
        'slug'    => 'como-desarrolle-sigah',
        'cover'   => 'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?w=900&q=80',
        'alt'     => 'Desarrollo de ' . $app,
        'badges'  => [['badge-primary', 'fab fa-php', 'PHP'], ['', '', $app]],
        'titulo'  => 'Cómo desarrollé ' . $app . ': de la idea al sistema funcional',
        'autor'   => 'Rolando Flores Arce',
        'fecha'   => '10 de abril, 2026',
        'lectura' => '6 min de lectura',
        'cuerpo'  => $cuerpoSigah,
        'comentarios' => [
            ['María González', 'https://i.pravatar.cc/34?img=47', 'Excelente trabajo, Rolando. El sistema nos facilita muchísimo la tarea diaria.', 'Hace 2 días'],
            ['Carlos López',   'https://i.pravatar.cc/34?img=12', '¿Planeás publicar el código en GitHub? Sería buenísimo para aprender.', 'Hace 1 día'],
            ['Ana Torres',     'https://i.pravatar.cc/34?img=23', 'Me interesa el módulo de notificaciones automáticas. ¿Cómo está hecho SANE?', 'Hace 5 horas'],
        ],
    ],
    [
        'slug'    => 'fallas-comunes-notebooks',
        'cover'   => 'https://images.unsplash.com/photo-1597852074816-d933c7d2b988?w=900&q=80',
        'alt'     => 'Reparación de notebooks – ' . $marca,
        'badges'  => [['badge-warning', 'fas fa-tools', 'Reparación PC']],
        'titulo'  => 'Las 5 fallas más comunes en notebooks — y cómo repararlas',
        'autor'   => 'Rolando Flores Arce',
        'fecha'   => '25 de marzo, 2026',
        'lectura' => '4 min de lectura',
        'cuerpo'  => '
        <h3>Experiencia tras más de 100 reparaciones</h3>
        <p>Con <strong>' . $marca . '</strong> atendí cientos de consultas y realicé más de 100 reparaciones exitosas.</p>

        <img class="post-inline-img"
             src="https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=75"
             alt="Componentes internos de notebook para reparación">

        <h4><i class="fas fa-desktop"></i> 1. Pantalla con líneas o sin imagen</h4>
        <p>Suele ser el cable de pantalla o la placa de video integrada. Primero revisá el cable flat.</p>
        <h4><i class="fas fa-thermometer-half"></i> 2. Sobrecalentamiento y apagado repentino</h4>
        <p>El 80% de los casos se resuelve con limpieza de pasta térmica y cooler.</p>
        <h4><i class="fas fa-power-off"></i> 3. No enciende (sin imagen)</h4>
        <p>Probá sin batería solo con el cargador. Si tampoco enciende, revisar BIOS o placa base.</p>

        <h3>Consejo de ' . $marca . '</h3>
        <p>El 60% de las reparaciones se resuelven con software y limpieza, sin gastar en repuestos.</p>',
        'comentarios' => [
            ['Lucía Ramírez', 'https://i.pravatar.cc/34?img=32', 'Justamente tuve ese problema esta semana y pude solucionarlo con este artículo. ¡Gracias!', 'Hace 3 horas'],
        ],
    ],
    [
        'slug'    => 'mariadb-vs-sqlite',
        'cover'   => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=900&q=80',
        'alt'     => 'MariaDB vs SQLite – bases de datos',
        'badges'  => [['badge-success', 'fas fa-database', 'Bases de datos']],
        'titulo'  => 'MariaDB vs SQLite: ¿cuál elegir para tu proyecto?',
        'autor'   => 'Rolando Flores Arce',
        'fecha'   => '5 de marzo, 2026',
        'lectura' => '3 min de lectura',
        'cuerpo'  => '
        <h3>Comparativa práctica desde la experiencia con ' . $app . '</h3>
        <p>Para el desarrollo de ' . $app . ' probé ambas opciones. Acá un resumen:</p>
        <h4><i class="fas fa-file-code"></i> SQLite — ideal para desarrollo y apps pequeñas</h4>
        <p>Sin servidor, un solo archivo, perfecto para prototipos.</p>
        <h4><i class="fas fa-server"></i> MariaDB — para producción y múltiples usuarios</h4>
        <p>Soporta conexiones concurrentes, roles y permisos, backups automáticos.
           Es el motor que ya viene con XAMPP, así que la versión final lo usa.</p>',
        'comentarios' => [],
    ],
];

$flashSuccess = flash_get('success');
$flashError   = flash_get('error');

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Blog técnico';
$pageIcon    = 'fa-pen-fancy';
$pageHeading = 'Blog Técnico';
$pageDesc    = 'Artículos sobre programación, reparación de PC, sistemas y tecnología educativa.';
$breadcrumb  = [
    ['label' => $app, 'url' => 'index.php'],
    ['label' => 'Blog'],
];

$pageStyles = <<<'CSS'
    .post-card { background: var(--surface); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-bottom: 24px; }
    .post-cover { width: 100%; height: 200px; object-fit: cover; display: block; }
    .post-thumb { background: linear-gradient(135deg, #0f2533, #0d3d54); padding: 20px 24px 16px; color: white; }
    .post-thumb h2 { color: white; font-size: 17px; border: none; padding: 0; margin: 8px 0 6px; }
    .post-thumb .post-meta { font-size: 12px; color: #7bb8cc; display: flex; gap: 14px; flex-wrap: wrap; }
    .post-body { padding: 20px 24px; }
    .post-body h3 { font-size: 14px; color: var(--dark); margin: 16px 0 8px; }
    .post-body h4 { font-size: 13px; color: var(--primary); margin: 12px 0 6px; }
    .post-body p  { font-size: 13px; color: var(--gray); line-height: 1.8; margin-bottom: 8px; }
    .post-body pre { background: #1e2a35; color: #69f0ae; font-family: 'IBM Plex Mono', monospace; font-size: 11px; padding: 12px; border-radius: 6px; overflow-x: auto; line-height: 1.8; margin: 10px 0; }
    .post-body code { background: var(--bg); padding: 1px 5px; border-radius: 4px; }
    .post-inline-img { width: 100%; height: 150px; object-fit: cover; border-radius: var(--radius-sm); margin: 12px 0; opacity: 0.88; }
    .comments-section { background: var(--bg); border-top: 1px solid var(--border); padding: 20px 24px; }
    .comments-section h3 { font-size: 14px; color: var(--dark); margin-bottom: 14px; }
    .comment { display: flex; gap: 10px; margin-bottom: 14px; }
    .comment-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0; margin-top: 2px; overflow: hidden; }
    .comment-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .comment-body { background: var(--surface); border-radius: 8px; padding: 10px 14px; flex: 1; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
    .comment-body h4 { font-size: 13px; color: var(--dark); margin-bottom: 4px; }
    .comment-body p  { font-size: 12px; color: var(--gray); line-height: 1.6; }
    .comment-body .ctime { font-size: 11px; color: var(--light-gray); margin-top: 4px; }
    .cats { display: flex; gap: 8px; flex-wrap: wrap; }
    .cat { padding: 5px 14px; border-radius: 20px; font-size: 12px; cursor: pointer; background: var(--surface); color: var(--gray); border: 1px solid var(--border); }
    .cat.active { background: var(--primary); color: white; border-color: var(--primary); }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <main class="wide" id="top">
<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i> <?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert-error" role="alert"><i class="fas fa-triangle-exclamation"></i> <?= e($flashError) ?></div>
<?php endif; ?>

    <div class="card">
      <h2><i class="fas fa-tags"></i> Categorías</h2>
      <div class="cats">
<?php foreach ($categorias as $i => $categoria): ?>
        <span class="cat<?= $i === 0 ? ' active' : '' ?>"><?= e($categoria) ?></span>
<?php endforeach; ?>
      </div>
    </div>

<?php foreach ($posts as $post): ?>
    <article class="post-card" id="<?= e($post['slug']) ?>">
      <img class="post-cover" src="<?= e($post['cover']) ?>" alt="<?= e($post['alt']) ?>">

      <div class="post-thumb">
        <div>
<?php foreach ($post['badges'] as [$clase, $icono, $texto]): ?>
          <span class="badge <?= e($clase) ?>"<?= $clase === '' ? ' style="background:rgba(255,255,255,0.1);color:#aaa"' : '' ?>>
            <?php if ($icono !== ''): ?><i class="<?= e($icono) ?>"></i> <?php endif; ?><?= e($texto) ?>
          </span>
<?php endforeach; ?>
        </div>

        <h2><?= e($post['titulo']) ?></h2>

        <div class="post-meta">
          <span><i class="fas fa-user-edit"></i> <?= e($post['autor']) ?></span>
          <span><i class="fas fa-calendar-alt"></i> <?= e($post['fecha']) ?></span>
          <span><i class="fas fa-clock"></i> <?= e($post['lectura']) ?></span>
        </div>
      </div>

      <div class="post-body">
        <?= $post['cuerpo'] ?>
      </div>

<?php
      $extra = $comentariosGuardados[$post['slug']] ?? [];
      $total = count($post['comentarios']) + count($extra);
?>
      <div class="comments-section">
        <h3><i class="fas fa-comments"></i> Comentarios (<?= $total ?>)</h3>

<?php if ($total === 0): ?>
        <p class="text-muted">Sé el primero en comentar este artículo.</p>
<?php endif; ?>

<?php foreach ($post['comentarios'] as [$nombre, $foto, $texto, $cuando]): ?>
        <div class="comment">
          <div class="comment-avatar"><img src="<?= e($foto) ?>" alt="Avatar de <?= e($nombre) ?>"></div>
          <div class="comment-body">
            <h4><?= e($nombre) ?></h4>
            <p><?= e($texto) ?></p>
            <div class="ctime"><?= e($cuando) ?></div>
          </div>
        </div>
<?php endforeach; ?>

<?php foreach (array_reverse($extra) as $comentario): ?>
        <div class="comment">
          <div class="comment-avatar"><?= e(mb_strtoupper(mb_substr((string) $comentario['nombre'], 0, 1))) ?></div>
          <div class="comment-body">
            <h4><?= e($comentario['nombre']) ?></h4>
            <p><?= nl2br(e($comentario['texto'])) ?></p>
            <div class="ctime"><?= e(date('d/m/Y H:i', (int) strtotime((string) $comentario['fecha']))) ?> hs</div>
          </div>
        </div>
<?php endforeach; ?>

        <h3 style="margin-top:16px"><i class="fas fa-edit"></i> Dejá tu comentario</h3>

        <form method="post" action="<?= e(url('blog.php')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="post" value="<?= e($post['slug']) ?>">

          <div class="form-group" style="margin-top:10px">
            <label for="nombre-<?= e($post['slug']) ?>">Tu nombre</label>
            <input type="text" id="nombre-<?= e($post['slug']) ?>" name="nombre"
                   value="<?= e(auth_check() ? auth_name() : '') ?>" placeholder="Nombre..." required>
          </div>

          <div class="form-group">
            <label for="texto-<?= e($post['slug']) ?>">Comentario</label>
            <textarea id="texto-<?= e($post['slug']) ?>" name="comentario"
                      placeholder="Escribí tu comentario..." maxlength="1000" required></textarea>
          </div>

          <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i> Publicar comentario</button>
        </form>
      </div>
    </article>
<?php endforeach; ?>
  </main>

<?php
$pageScripts = <<<'JS'
(function () {
  document.querySelectorAll('.cat').forEach(function (cat) {
    cat.addEventListener('click', function () {
      document.querySelectorAll('.cat').forEach(function (c) { c.classList.remove('active'); });
      cat.classList.add('active');
    });
  });
})();
JS;

include __DIR__ . '/partials/footer.php';
