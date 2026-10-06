<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require_login();

$usuario = auth_user() ?? [];

/* Datos completos del usuario (los de la sesión son mínimos a propósito) */
$ficha = users_find((string) ($usuario['email'] ?? '')) ?? [];

$roles = [
    'admin'   => ['Administrador/a', 'badge-primary'],
    'docente' => ['Docente',         'badge-success'],
    'alumno'  => ['Alumno/a',        'badge-warning'],
];

[$rolTexto, $rolBadge] = $roles[$usuario['rol'] ?? ''] ?? ['Invitado', 'badge'];

$loginAt = auth_login_at();

$titulo_pagina = 'Perfil';
$pageIcon    = 'fa-user';
$pageHeading = 'Perfil de usuario';
$pageDesc    = 'Información de tu cuenta y datos de la sesión actual.';
$breadcrumb  = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard', 'url' => 'home.php'],
    ['label' => 'Perfil'],
];

$pageStyles = <<<'CSS'
    .profile-top { text-align: center; padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
    .photo { width: 110px; height: 110px; border-radius: 50%; margin: 0 auto 12px; border: 4px solid var(--primary); overflow: hidden; background: #e0f7fa; display: flex; align-items: center; justify-content: center; font-size: 38px; font-weight: 700; color: var(--primary); }
    .photo img { width: 100%; height: 100%; object-fit: cover; }
    .info-row { display: flex; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-row .lbl { color: var(--light-gray); }
    .info-row .val { font-weight: 600; color: var(--dark); text-align: right; word-break: break-word; }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <main>
    <div class="card">
      <h2><i class="fas fa-id-card"></i> Datos del usuario</h2>

      <div class="profile-top">
        <div class="photo">
<?php if (!empty($ficha['foto'])): ?>
          <img src="<?= e($ficha['foto']) ?>" alt="Foto de perfil de <?= e($usuario['nombre'] ?? '') ?>">
<?php else: ?>
          <?= e(mb_strtoupper(mb_substr((string) ($usuario['nombre'] ?? '?'), 0, 1))) ?>
<?php endif; ?>
        </div>

        <h3 style="font-size:18px;color:var(--dark)"><?= e($usuario['nombre'] ?? '') ?></h3>
        <span class="badge <?= e($rolBadge) ?>"><?= e($rolTexto) ?></span>
      </div>

      <div class="info-row">
        <span class="lbl"><i class="fas fa-user"></i> Usuario</span>
        <span class="val"><?= e($usuario['usuario'] ?? '—') ?></span>
      </div>
      <div class="info-row">
        <span class="lbl"><i class="fas fa-envelope"></i> Correo</span>
        <span class="val"><?= e($usuario['email'] ?? '—') ?></span>
      </div>
      <div class="info-row">
        <span class="lbl"><i class="fas fa-calendar-plus"></i> Alta</span>
        <span class="val"><?= e($ficha['fechaCreacion'] ?? '—') ?></span>
      </div>
      <div class="info-row">
        <span class="lbl"><i class="fas fa-school"></i> Institución</span>
        <span class="val"><?= e(env('SCHOOL_NAME', '')) ?></span>
      </div>
    </div>

    <div class="card">
      <h2><i class="fas fa-key"></i> Estado de la cuenta</h2>
      <p>Información sobre tu sesión y permisos en el sistema.</p><br>

      <div class="info-row">
        <span class="lbl">Rol</span>
        <span class="val"><?= e($rolTexto) ?></span>
      </div>
      <div class="info-row">
        <span class="lbl">Estado</span>
        <span class="val" style="color:var(--success)">
          <i class="fas fa-check-circle"></i> <?= e($ficha['estado'] ?? 'activo') ?>
        </span>
      </div>
      <div class="info-row">
        <span class="lbl">Inicio de sesión</span>
        <span class="val"><?= $loginAt ? e(date('d/m/Y H:i', $loginAt)) . ' hs' : '—' ?></span>
      </div>
      <div class="info-row">
        <span class="lbl">Tipo de acceso</span>
        <span class="val"><?= !empty($usuario['demo']) ? 'Cuenta de prueba' : 'Cuenta registrada' ?></span>
      </div>
      <div class="info-row">
        <span class="lbl">Identificador de sesión</span>
        <span class="val"><?= e(substr(session_id(), 0, 12)) ?>…</span>
      </div>

      <br>
      <div class="flex-between">
        <a class="btn btn-primary" href="<?= e(url('config.php')) ?>"><i class="fas fa-cogs"></i> Ir a configuración</a>
        <form method="post" action="<?= e(url('logout.php')) ?>" style="margin:0">
          <?= csrf_field() ?>
          <button class="btn btn-outline" type="submit"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
        </form>
      </div>
    </div>

    <div class="card">
      <h2><i class="fas fa-list"></i> Entorno de ejecución</h2>
      <p>Datos útiles para diagnosticar problemas en el servidor local.</p><br>

      <img class="activity-img"
           src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=75"
           alt="Panel de actividad del sistema <?= e(env('APP_NAME', 'SIGAH')) ?>">

<pre style="background:rgba(0,0,0,0.04);padding:12px;border-radius:6px;font-size:12px;line-height:1.9;border-left:3px solid var(--success)">Servidor     : <?= e($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?>

PHP          : <?= e(PHP_VERSION) ?>

Entorno      : <?= e(env('APP_ENV', 'local')) ?>

URL base     : <?= e(base_path_url() === '' ? '/' : base_path_url()) ?>

Sesión       : <?= e(session_name()) ?>

Origen datos : <?= db_ready() ? 'MariaDB' : 'Archivos JSON (data/)' ?></pre>
    </div>
  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
