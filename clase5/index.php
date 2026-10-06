<?php
declare(strict_types=1);

function sanitize_text(string $value): string
{
    return trim(htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}

function sanitize_email(string $value): string
{
    return trim((string) filter_var($value, FILTER_SANITIZE_EMAIL));
}

function validate_required(string $fieldName, string $value): ?string
{
    if (trim($value) === '') {
        return 'El campo ' . $fieldName . ' es obligatorio.';
    }

    return null;
}

$registroError = [];
$searchError = [];
$registroSuccess = '';
$searchSuccess = '';
$searchResults = [];

$postValues = [
    'nombre' => '',
    'email' => '',
    'asunto' => '',
    'mensaje' => '',
    'acepto' => false,
];

$searchValues = [
    'q' => '',
    'categoria' => 'todos',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro'])) {
    $postValues['nombre'] = sanitize_text((string) ($_POST['nombre'] ?? ''));
    $postValues['email'] = sanitize_email((string) ($_POST['email'] ?? ''));
    $postValues['asunto'] = sanitize_text((string) ($_POST['asunto'] ?? ''));
    $postValues['mensaje'] = sanitize_text((string) ($_POST['mensaje'] ?? ''));
    $postValues['acepto'] = isset($_POST['acepto']);

    $rawNombre = trim((string) ($_POST['nombre'] ?? ''));
    $rawEmail = trim((string) ($_POST['email'] ?? ''));
    $rawAsunto = trim((string) ($_POST['asunto'] ?? ''));
    $rawMensaje = trim((string) ($_POST['mensaje'] ?? ''));

    if (($error = validate_required('nombre', $rawNombre)) !== null) {
        $registroError['nombre'] = $error;
    }

    if (($error = validate_required('email', $rawEmail)) !== null) {
        $registroError['email'] = $error;
    } elseif (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
        $registroError['email'] = 'Ingresá un correo electrónico válido.';
    }

    if (($error = validate_required('asunto', $rawAsunto)) !== null) {
        $registroError['asunto'] = $error;
    }

    if (($error = validate_required('mensaje', $rawMensaje)) !== null) {
        $registroError['mensaje'] = $error;
    } elseif (mb_strlen($rawMensaje, 'UTF-8') < 10) {
        $registroError['mensaje'] = 'El mensaje debe contener al menos 10 caracteres.';
    }

    if (!$postValues['acepto']) {
        $registroError['acepto'] = 'Debés aceptar la política de privacidad para continuar.';
    }

    if ($registroError === []) {
        $registroSuccess = 'Formulario enviado correctamente. Nos pondremos en contacto pronto.';
        $postValues = [
            'nombre' => '',
            'email' => '',
            'asunto' => '',
            'mensaje' => '',
            'acepto' => false,
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['buscar'])) {
    $searchValues['q'] = sanitize_text((string) ($_GET['q'] ?? ''));
    $categoria = sanitize_text((string) ($_GET['categoria'] ?? 'todos'));
    $searchValues['categoria'] = in_array($categoria, ['todos', 'cursos', 'eventos', 'soporte'], true) ? $categoria : 'todos';

    if ($searchValues['q'] === '') {
        $searchError['q'] = 'La búsqueda no puede estar vacía.';
    } else {
        $catalogo = [
            ['titulo' => 'Introducción a PHP', 'categoria' => 'cursos', 'descripcion' => 'Curso básico de programación con PHP y formularios.'],
            ['titulo' => 'Workshop de seguridad web', 'categoria' => 'eventos', 'descripcion' => 'Taller para detectar y corregir vulnerabilidades XSS y SQL Injection.'],
            ['titulo' => 'Soporte para estudiantes', 'categoria' => 'soporte', 'descripcion' => 'Ayuda técnica para usuarios que necesitan asistencia inicial.'],
            ['titulo' => 'Validación de formularios', 'categoria' => 'cursos', 'descripcion' => 'Práctica de validación del lado del servidor y sanitización.'],
        ];

        foreach ($catalogo as $item) {
            $okCategoria = $searchValues['categoria'] === 'todos' || $item['categoria'] === $searchValues['categoria'];
            $coincideTexto = stripos($item['titulo'], $searchValues['q']) !== false || stripos($item['descripcion'], $searchValues['q']) !== false || stripos($item['categoria'], $searchValues['q']) !== false;

            if ($okCategoria && $coincideTexto) {
                $searchResults[] = [
                    'titulo' => sanitize_text($item['titulo']),
                    'categoria' => sanitize_text($item['categoria']),
                    'descripcion' => sanitize_text($item['descripcion']),
                ];
            }
        }

        if ($searchResults === []) {
            $searchSuccess = 'No se encontraron resultados para la búsqueda ingresada.';
        } else {
            $searchSuccess = 'Se encontraron ' . count($searchResults) . ' resultados relevantes.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clase 5 - Formularios seguros</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <header class="hero">
            <p class="eyebrow">Clase 5</p>
            <h1>Formularios seguros con PHP</h1>
            <p class="lead">Procesamiento de datos en <strong>POST</strong> y <strong>GET</strong>, validación del lado del servidor y sanitización estricta para prevenir XSS.</p>
        </header>

        <section class="panel">
            <div class="panel-header">
                <h2>1. Formulario por POST</h2>
                <span class="badge">Registro / contacto</span>
            </div>

            <?php if ($registroSuccess !== ''): ?>
                <div class="alert alert-success" role="status"><?= htmlspecialchars($registroSuccess, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post" action="index.php" novalidate>
                <input type="hidden" name="registro" value="1">

                <div class="form-grid">
                    <label>
                        <span>Nombre</span>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($postValues['nombre'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Tu nombre completo">
                        <?php if (isset($registroError['nombre'])): ?>
                            <small class="error"><?= htmlspecialchars($registroError['nombre'], ENT_QUOTES, 'UTF-8') ?></small>
                        <?php endif; ?>
                    </label>

                    <label>
                        <span>Email</span>
                        <input type="email" name="email" value="<?= htmlspecialchars($postValues['email'], ENT_QUOTES, 'UTF-8') ?>" placeholder="usuario@ejemplo.com">
                        <?php if (isset($registroError['email'])): ?>
                            <small class="error"><?= htmlspecialchars($registroError['email'], ENT_QUOTES, 'UTF-8') ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="full-width">
                        <span>Asunto</span>
                        <input type="text" name="asunto" value="<?= htmlspecialchars($postValues['asunto'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Consulta, sugerencia o comentario">
                        <?php if (isset($registroError['asunto'])): ?>
                            <small class="error"><?= htmlspecialchars($registroError['asunto'], ENT_QUOTES, 'UTF-8') ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="full-width">
                        <span>Mensaje</span>
                        <textarea name="mensaje" rows="5" placeholder="Escribí tu mensaje aquí"><?= htmlspecialchars($postValues['mensaje'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php if (isset($registroError['mensaje'])): ?>
                            <small class="error"><?= htmlspecialchars($registroError['mensaje'], ENT_QUOTES, 'UTF-8') ?></small>
                        <?php endif; ?>
                    </label>
                </div>

                <label class="checkbox-row">
                    <input type="checkbox" name="acepto" value="1" <?= $postValues['acepto'] ? 'checked' : '' ?>>
                    <span>Acepto la política de privacidad.</span>
                </label>
                <?php if (isset($registroError['acepto'])): ?>
                    <small class="error"><?= htmlspecialchars($registroError['acepto'], ENT_QUOTES, 'UTF-8') ?></small>
                <?php endif; ?>

                <button type="submit">Enviar formulario</button>
            </form>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2>2. Formulario por GET</h2>
                <span class="badge">Búsqueda / filtro</span>
            </div>

            <form method="get" action="index.php" class="search-form">
                <label>
                    <span>Buscar</span>
                    <input type="search" name="q" value="<?= htmlspecialchars($searchValues['q'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Buscar cursos, eventos o soporte">
                    <?php if (isset($searchError['q'])): ?>
                        <small class="error"><?= htmlspecialchars($searchError['q'], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </label>

                <label>
                    <span>Categoría</span>
                    <select name="categoria">
                        <option value="todos" <?= $searchValues['categoria'] === 'todos' ? 'selected' : '' ?>>Todas</option>
                        <option value="cursos" <?= $searchValues['categoria'] === 'cursos' ? 'selected' : '' ?>>Cursos</option>
                        <option value="eventos" <?= $searchValues['categoria'] === 'eventos' ? 'selected' : '' ?>>Eventos</option>
                        <option value="soporte" <?= $searchValues['categoria'] === 'soporte' ? 'selected' : '' ?>>Soporte</option>
                    </select>
                </label>

                <button type="submit" name="buscar" value="1">Buscar</button>
            </form>

            <?php if ($searchSuccess !== ''): ?>
                <div class="alert alert-info" role="status"><?= htmlspecialchars($searchSuccess, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($searchResults !== []): ?>
                <ul class="results">
                    <?php foreach ($searchResults as $result): ?>
                        <li>
                            <strong><?= htmlspecialchars($result['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="tag"><?= htmlspecialchars($result['categoria'], ENT_QUOTES, 'UTF-8') ?></span>
                            <p><?= htmlspecialchars($result['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
