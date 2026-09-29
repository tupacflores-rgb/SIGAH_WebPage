<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

auth_logout();

flash_set('success', 'Cerraste sesión correctamente.');

redirect('index.php');
