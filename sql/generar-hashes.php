<?php
declare(strict_types=1);

/**
 * Genera los hashes de contraseña para los usuarios de ejemplo.
 *
 * Uso desde la consola de Windows, parado en la carpeta del proyecto:
 *
 *     C:\xampp\php\php.exe sql\generar-hashes.php
 *
 * Copiá los UPDATE que imprime y ejecutalos en phpMyAdmin
 * (pestaña SQL de la base `sigah`).
 *
 * Para una contraseña propia:
 *
 *     C:\xampp\php\php.exe sql\generar-hashes.php "MiClaveNueva1@"
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo se ejecuta desde la consola.');
}

$usuarios = [
    'rolando_flores' => 'Sigah123@',
    'prof_lopez'     => 'Docente456@',
    'prof_torres'    => 'Docente789@',
];

if (isset($argv[1])) {
    $clave = (string) $argv[1];

    echo "Hash para \"{$clave}\":" . PHP_EOL;
    echo password_hash($clave, PASSWORD_DEFAULT) . PHP_EOL;
    exit(0);
}

echo "-- Pegá esto en phpMyAdmin → base `sigah` → pestaña SQL" . PHP_EOL . PHP_EOL;

foreach ($usuarios as $usuario => $clave) {
    $hash = password_hash($clave, PASSWORD_DEFAULT);

    printf(
        "UPDATE `usuarios` SET `contrasena` = '%s' WHERE `usuario` = '%s'; -- %s%s",
        $hash,
        $usuario,
        $clave,
        PHP_EOL
    );
}

echo PHP_EOL . "-- Listo. Borrá los comentarios con las contraseñas antes de compartir el archivo." . PHP_EOL;
