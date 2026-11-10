<?php
declare(strict_types=1);

// DELETE — baja de alumno con DELETE preparado (solo por POST + CSRF)
require __DIR__ . '/../app/bootstrap.php';

$rolesPermitidos = ['admin'];
require __DIR__ . '/../auth_guard.php';
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/index.php');
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);

try {
    $stmt = $pdo->prepare('DELETE FROM alumnos WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        flash_set('success', 'Alumno eliminado correctamente.');
    } else {
        flash_set('error', 'El alumno no existe o ya fue eliminado.');
    }
} catch (PDOException $e) {
    error_log('SIGAH · eliminar alumno: ' . $e->getMessage());
    flash_set('error', 'No se pudo eliminar el alumno.');
}

redirect('admin/index.php');
