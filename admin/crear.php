<?php
declare(strict_types=1);

// CREATE — alta de alumno con INSERT preparado
require __DIR__ . '/../app/bootstrap.php';

$rolesPermitidos = ['admin', 'docente'];
require __DIR__ . '/../auth_guard.php';
require_once __DIR__ . '/../config/conexion.php';
require __DIR__ . '/_alumno.php';

$cursos  = alumno_cursos($pdo);
$errores = [];
$general = null;
$datos   = [
    'apellido' => '', 'nombre' => '', 'dni' => '', 'fecha_nacimiento' => '', 'email' => '',
    'telefono' => '', 'curso_id' => '', 'turno' => 'Mañana', 'estado' => 'activo', 'observaciones' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$datos, $errores] = alumno_validar($_POST, $cursos);

    if (!$errores) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO alumnos (apellido, nombre, dni, fecha_nacimiento, email, telefono, curso_id, turno, estado, observaciones)
                 VALUES (:apellido, :nombre, :dni, :fecha_nacimiento, :email, :telefono, :curso_id, :turno, :estado, :observaciones)'
            );
            $stmt->execute(alumno_params($datos));

            flash_set('success', 'Alumno creado correctamente.');
            redirect('admin/index.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores['dni'] = 'Ya existe un alumno con ese DNI.';
            } else {
                error_log('SIGAH · crear alumno: ' . $e->getMessage());
                $general = 'No se pudo guardar el alumno.';
            }
        }
    }
    $general ??= 'Revisá los campos resaltados.';
}

$titulo_pagina = 'Nuevo alumno';
$pageIcon      = 'fa-user-plus';
$pageHeading   = 'Nuevo alumno';
$breadcrumb    = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Administración', 'url' => 'admin/index.php'],
    ['label' => 'Nuevo'],
];

include __DIR__ . '/../partials/header.php';
?>

  <main>
    <div class="card">
      <h2><i class="fas fa-user-plus"></i> Datos del alumno</h2>
<?php if ($general): ?>
      <div class="alert alert-error" role="alert"><?= e($general) ?></div>
<?php endif; ?>
<?php alumno_form($datos, $errores, $cursos, url('admin/crear.php'), 'Guardar alumno'); ?>
    </div>
  </main>

<?php include __DIR__ . '/../partials/footer.php';
