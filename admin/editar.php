<?php
declare(strict_types=1);

// UPDATE — edición de alumno con UPDATE preparado
require __DIR__ . '/../app/bootstrap.php';

$rolesPermitidos = ['admin', 'docente'];
require __DIR__ . '/../auth_guard.php';
require_once __DIR__ . '/../config/conexion.php';
require __DIR__ . '/_alumno.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM alumnos WHERE id = :id');
$stmt->execute([':id' => $id]);
$actual = $stmt->fetch();

if (!$actual) {
    flash_set('error', 'El alumno solicitado no existe.');
    redirect('admin/index.php');
}

$cursos  = alumno_cursos($pdo);
$errores = [];
$general = null;
$datos   = array_map(static fn ($v): string => (string) ($v ?? ''), $actual);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$datos, $errores] = alumno_validar($_POST, $cursos);

    if (!$errores) {
        try {
            $upd = $pdo->prepare(
                'UPDATE alumnos
                    SET apellido = :apellido, nombre = :nombre, dni = :dni,
                        fecha_nacimiento = :fecha_nacimiento, email = :email, telefono = :telefono,
                        curso_id = :curso_id, turno = :turno, estado = :estado, observaciones = :observaciones
                  WHERE id = :id'
            );
            $upd->execute(alumno_params($datos) + [':id' => $id]);

            flash_set('success', 'Alumno actualizado correctamente.');
            redirect('admin/index.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores['dni'] = 'Ya existe otro alumno con ese DNI.';
            } else {
                error_log('SIGAH · editar alumno: ' . $e->getMessage());
                $general = 'No se pudo actualizar el alumno.';
            }
        }
    }
    $general ??= 'Revisá los campos resaltados.';
}

$titulo_pagina = 'Editar alumno';
$pageIcon      = 'fa-user-pen';
$pageHeading   = 'Editar alumno';
$breadcrumb    = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Administración', 'url' => 'admin/index.php'],
    ['label' => 'Editar'],
];

include __DIR__ . '/../partials/header.php';
?>

  <main>
    <div class="card">
      <h2><i class="fas fa-user-pen"></i> Alumno #<?= (int) $id ?></h2>
<?php if ($general): ?>
      <div class="alert alert-error" role="alert"><?= e($general) ?></div>
<?php endif; ?>
<?php alumno_form($datos, $errores, $cursos, url('admin/editar.php?id=' . $id), 'Guardar cambios'); ?>
    </div>
  </main>

<?php include __DIR__ . '/../partials/footer.php';
