<?php
declare(strict_types=1);

// READ — listado de alumnos (módulo CRUD del TP7)
require __DIR__ . '/../app/bootstrap.php';

$rolesPermitidos = ['admin', 'docente'];
require __DIR__ . '/../auth_guard.php';
require_once __DIR__ . '/../config/conexion.php';

$busqueda = trim((string) ($_GET['q'] ?? ''));

if ($busqueda !== '') {
    $stmt = $pdo->prepare(
        'SELECT a.*, c.nombre AS curso
           FROM alumnos a LEFT JOIN cursos c ON c.id = a.curso_id
          WHERE a.apellido LIKE :q1 OR a.nombre LIKE :q2 OR a.dni LIKE :q3
          ORDER BY a.id DESC'
    );
    $like = '%' . $busqueda . '%';
    $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
} else {
    $stmt = $pdo->prepare(
        'SELECT a.*, c.nombre AS curso
           FROM alumnos a LEFT JOIN cursos c ON c.id = a.curso_id
          ORDER BY a.id DESC'
    );
    $stmt->execute();
}
$registros = $stmt->fetchAll();

$ok  = flash_get('success');
$bad = flash_get('error');

$titulo_pagina = 'Administración de alumnos';
$pageIcon      = 'fa-database';
$pageHeading   = 'Administración de alumnos';
$pageDesc      = 'CRUD de alumnos conectado a MySQL con PDO.';
$breadcrumb    = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard', 'url' => 'home.php'],
    ['label' => 'Administración'],
];

include __DIR__ . '/../partials/header.php';
?>

  <main>
<?php if ($ok): ?>
    <div class="alert alert-success" role="status"><?= e($ok) ?></div>
<?php endif; ?>
<?php if ($bad): ?>
    <div class="alert alert-error" role="alert"><?= e($bad) ?></div>
<?php endif; ?>

    <div class="card">
      <h2><i class="fas fa-user-graduate"></i> Alumnos registrados (<?= count($registros) ?>)</h2>

      <form method="get" action="<?= e(url('admin/index.php')) ?>" class="mb-16" style="display:flex;gap:8px;flex-wrap:wrap">
        <input type="search" name="q" value="<?= e($busqueda) ?>" placeholder="Buscar por apellido, nombre o DNI" aria-label="Buscar alumnos" style="flex:1;min-width:220px">
        <button class="btn btn-dark" type="submit"><i class="fas fa-search"></i> Buscar</button>
        <a class="btn btn-primary" href="<?= e(url('admin/crear.php')) ?>"><i class="fas fa-plus"></i> Agregar alumno</a>
      </form>

      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>ID</th><th>Apellido y nombre</th><th>DNI</th><th>Curso</th><th>Turno</th><th>Estado</th><th>Acciones</th></tr>
          </thead>
          <tbody>
<?php if (!$registros): ?>
            <tr><td colspan="7">No hay alumnos para mostrar.</td></tr>
<?php endif; ?>
<?php foreach ($registros as $item): ?>
            <tr>
              <td><?= e($item['id']) ?></td>
              <td><?= e($item['apellido'] . ', ' . $item['nombre']) ?></td>
              <td><?= e($item['dni']) ?></td>
              <td><?= e($item['curso'] ?? '—') ?></td>
              <td><?= e($item['turno']) ?></td>
              <td><span class="badge <?= $item['estado'] === 'activo' ? 'badge-success' : 'badge-warning' ?>"><?= e($item['estado']) ?></span></td>
              <td style="white-space:nowrap">
                <a class="btn btn-outline" href="<?= e(url('admin/editar.php?id=' . (int) $item['id'])) ?>"><i class="fas fa-edit"></i> Editar</a>
                <form method="post" action="<?= e(url('admin/eliminar.php')) ?>" style="display:inline"
                      onsubmit="return confirm('¿Confirma eliminar este registro?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                  <button class="btn btn-outline" type="submit" style="color:var(--danger);border-color:var(--danger)"><i class="fas fa-trash"></i> Eliminar</button>
                </form>
              </td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

<?php include __DIR__ . '/../partials/footer.php';
