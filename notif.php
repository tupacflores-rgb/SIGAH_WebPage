<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require_login();

$umbralRiesgo = (int) env('ATTENDANCE_RISK_THRESHOLD', 85);
$estudiantes  = read_json('students.json')['estudiantes'] ?? [];

/* ------------------------------------------------------------------
 |  Alertas generadas a partir de los datos reales (módulo SANE)
 | ------------------------------------------------------------------ */
$nuevas = [];

foreach ($estudiantes as $alumno) {
    if ((float) ($alumno['asistencia'] ?? 100) >= $umbralRiesgo) {
        continue;
    }

    $nuevas[] = [
        'tipo'   => 'alert',
        'foto'   => $alumno['foto'] ?? null,
        'icono'  => '⚠️',
        'titulo' => 'Alto ausentismo detectado',
        'desc'   => sprintf(
            '%s, %s acumula %d ausencias en %s (%s%% de asistencia).',
            $alumno['apellido'] ?? '',
            $alumno['nombre'] ?? '',
            (int) ($alumno['ausencias'] ?? 0),
            $alumno['curso'] ?? '',
            $alumno['asistencia'] ?? 0
        ),
        'tiempo' => 'Detectado por SANE',
    ];
}

/* Última asistencia registrada desde asist.php */
$asistencias = read_json('attendance.json');

if ($asistencias) {
    uasort($asistencias, static fn (array $a, array $b): int => strcmp((string) ($b['actualizado'] ?? ''), (string) ($a['actualizado'] ?? '')));
    $ultima = reset($asistencias);

    $presentes = count(array_filter($ultima['estados'] ?? [], static fn (string $e): bool => $e === 'presente'));
    $totalReg  = count($ultima['estados'] ?? []);

    array_unshift($nuevas, [
        'tipo'   => 'asist',
        'foto'   => null,
        'icono'  => '📋',
        'titulo' => 'Asistencia registrada',
        'desc'   => sprintf(
            'Se registró asistencia para %s – %s (%d/%d presentes).',
            $ultima['curso'] ?? '',
            $ultima['materia'] ?? '',
            $presentes,
            $totalReg
        ),
        'tiempo' => isset($ultima['actualizado'])
            ? 'El ' . date('d/m/Y H:i', (int) strtotime((string) $ultima['actualizado'])) . ' hs'
            : '',
    ]);
}

$anteriores = [
    [
        'tipo'   => 'ok',
        'foto'   => null,
        'icono'  => '✅',
        'titulo' => 'Sistema migrado a PHP',
        'desc'   => 'SIGAH ahora se sirve con Apache y PHP ' . PHP_VERSION . ' sobre XAMPP.',
        'tiempo' => 'Hoy',
    ],
    [
        'tipo'   => 'asist',
        'foto'   => null,
        'icono'  => '🗂️',
        'titulo' => 'Horario actualizado',
        'desc'   => 'El horario de 3° A fue modificado: se agregó Tutoría los miércoles.',
        'tiempo' => 'Hace 2 días',
    ],
    [
        'tipo'   => 'info',
        'foto'   => 'https://i.pravatar.cc/40?img=47',
        'icono'  => '🔑',
        'titulo' => 'Contraseña actualizada',
        'desc'   => 'La contraseña de tu cuenta fue cambiada exitosamente.',
        'tiempo' => 'Hace 3 días',
    ],
];

$totalNuevas = count($nuevas);

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Notificaciones';
$pageDesc        = 'Alertas, avisos y novedades del sistema en tiempo real.';
$pageHeadingHtml = '<span class="icon">🔔</span> Notificaciones '
    . '<span class="badge badge-danger" style="font-size:13px;vertical-align:middle">' . $totalNuevas . '</span>';
$breadcrumb      = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard', 'url' => 'home.php'],
    ['label' => 'Notificaciones'],
];

$pageStyles = <<<'CSS'
    .notif { background: var(--surface); border-radius: var(--radius); padding: 14px 16px; margin-bottom: 10px; box-shadow: var(--shadow); display: flex; align-items: flex-start; gap: 12px; transition: box-shadow 0.15s; }
    .notif:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.12); }
    .notif.unread { border-left: 3px solid var(--primary); }
    .notif-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; overflow: hidden; }
    .notif-icon img { width: 100%; height: 100%; object-fit: cover; }
    .ni-asist { background: #e0f7fa; }
    .ni-alert { background: #fff3e0; }
    .ni-info  { background: #f3e5f5; }
    .ni-ok    { background: #e8f5e9; }
    .notif-body { flex: 1; }
    .notif-body .title { font-size: 14px; font-weight: 700; color: var(--dark); }
    .notif-body .desc  { font-size: 13px; color: var(--gray); margin-top: 3px; }
    .notif-body .time  { font-size: 11px; color: var(--light-gray); margin-top: 5px; }
    .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--primary); flex-shrink: 0; margin-top: 6px; }
    .notif-banner { width: 100%; height: 100px; object-fit: cover; border-radius: var(--radius); margin-bottom: 20px; opacity: 0.78; display: block; }
    .empty-state { text-align: center; padding: 26px 12px; color: var(--light-gray); }
CSS;

include __DIR__ . '/partials/header.php';

/** Imprime una tarjeta de notificación. */
function notif_card(array $n, bool $unread = false): void
{
    ?>
      <div class="notif<?= $unread ? ' unread' : '' ?>">
        <div class="notif-icon ni-<?= e($n['tipo']) ?>">
<?php if (!empty($n['foto'])): ?>
          <img src="<?= e($n['foto']) ?>" alt="">
<?php else: ?>
          <?= e($n['icono']) ?>
<?php endif; ?>
        </div>
        <div class="notif-body">
          <div class="title"><?= e($n['titulo']) ?></div>
          <div class="desc"><?= e($n['desc']) ?></div>
<?php if (!empty($n['tiempo'])): ?>
          <div class="time"><?= e($n['tiempo']) ?></div>
<?php endif; ?>
        </div>
<?php if ($unread): ?>
        <div class="dot" aria-label="Sin leer"></div>
<?php endif; ?>
      </div>
    <?php
}
?>

  <main>
    <img class="notif-banner"
         src="https://images.unsplash.com/photo-1484807352052-23338990c6c6?w=1000&q=75"
         alt="Notificaciones del sistema — <?= e(env('APP_NAME', 'SIGAH')) ?>">

    <div class="card">
      <h2>🆕 Nuevas notificaciones</h2>

<?php if (!$nuevas): ?>
      <div class="empty-state">
        <i class="fas fa-bell-slash" style="font-size:28px"></i>
        <p>No hay alertas nuevas. Todos los alumnos superan el <?= $umbralRiesgo ?>% de asistencia.</p>
      </div>
<?php else: ?>
      <p>Tenés <strong><?= $totalNuevas ?></strong> notificación<?= $totalNuevas === 1 ? '' : 'es' ?> sin leer.</p>
      <br>
<?php foreach ($nuevas as $n): ?>
<?php notif_card($n, true); ?>
<?php endforeach; ?>
<?php endif; ?>
    </div>

    <div class="card">
      <h2>🗂️ Notificaciones anteriores</h2>
      <p>Historial de alertas y avisos recientes.</p><br>

<?php foreach ($anteriores as $n): ?>
<?php notif_card($n); ?>
<?php endforeach; ?>
    </div>
  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
