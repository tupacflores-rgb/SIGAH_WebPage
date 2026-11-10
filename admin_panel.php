<?php
declare(strict_types=1);

// Panel exclusivo del rol "admin" (TP8: control de acceso por roles)
require __DIR__ . '/app/bootstrap.php';

$rolesPermitidos = ['admin'];
require __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/config/conexion.php';

$ok = flash_get('success');
$stmt = $pdo->prepare('SELECT id, nombre, email, usuario, rol, estado, fecha_creacion FROM usuarios ORDER BY id');
$stmt->execute();
$usuarios = $stmt->fetchAll();

$titulo_pagina = 'Panel de administración';
$pageIcon      = 'fa-shield-halved';
$pageHeading   = 'Panel de administración';
$pageDesc      = 'Usuarios registrados. Solo accesible para administradores.';
$breadcrumb    = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard', 'url' => 'home.php'],
    ['label' => 'Panel admin'],
];

include __DIR__ . '/partials/header.php';
?>

  <main>
<?php if ($ok): ?>
    <div class="alert alert-success" role="status"><?= e($ok) ?></div>
<?php endif; ?>
    <div class="card">
      <h2><i class="fas fa-users-gear"></i> Usuarios del sistema (<?= count($usuarios) ?>)</h2>
      <p class="mb-16">
        Sesión de <strong><?= e($_SESSION['usuario_nombre'] ?? '') ?></strong>
        · rol <span class="badge badge-primary"><?= e($_SESSION['rol'] ?? '') ?></span>
        · <a href="<?= e(url('admin/index.php')) ?>">Administrar alumnos</a>
        · <a class="btn btn-primary" href="<?= e(url('add-user.php')) ?>"><i class="fas fa-user-plus"></i> Crear usuario</a>
      </p>
      <div class="table-wrap">
        <table>
          <thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Alta</th></tr></thead>
          <tbody>
<?php foreach ($usuarios as $u): ?>
            <tr>
              <td><?= e($u['id']) ?></td>
              <td><?= e($u['nombre']) ?></td>
              <td><?= e($u['email']) ?></td>
              <td><?= e($u['usuario']) ?></td>
              <td><?= e($u['rol']) ?></td>
              <td><?= e($u['estado']) ?></td>
              <td><?= e($u['fecha_creacion']) ?></td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

<?php include __DIR__ . '/partials/footer.php';
