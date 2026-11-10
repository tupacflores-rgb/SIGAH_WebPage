<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

// Vacía $_SESSION, borra la cookie y ejecuta session_destroy().
auth_logout();

redirect('index.php?logout=1');
