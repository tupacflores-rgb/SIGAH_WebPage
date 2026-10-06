<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

if (!(bool) env('ALLOW_SELF_REGISTER', true) && !auth_check()) {
    flash_set('error', 'El registro de usuarios está deshabilitado. Pedile un alta al administrador.');
    redirect('index.php');
}

$roles = [
    'admin'   => 'Administrador',
    'docente' => 'Docente',
    'alumno'  => 'Alumno',
];

$rules   = password_rules();
$errors  = [];
$general = null;
$success = null;

$form = [
    'nombre'  => '',
    'email'   => '',
    'usuario' => '',
    'rol'     => '',
];

/* ------------------------------------------------------------------
 |  Alta de usuario (validación del lado del servidor)
 | ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $form['nombre']  = trim((string) ($_POST['nombre'] ?? ''));
    $form['email']   = trim((string) ($_POST['email'] ?? ''));
    $form['usuario'] = trim((string) ($_POST['usuario'] ?? ''));
    $form['rol']     = (string) ($_POST['rol'] ?? '');

    $password        = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if ($form['nombre'] === '') {
        $errors['nombre'] = 'Por favor ingresá tu nombre completo';
    } elseif (mb_strlen($form['nombre']) < 3) {
        $errors['nombre'] = 'El nombre debe tener al menos 3 caracteres';
    }

    if ($form['email'] === '') {
        $errors['email'] = 'Por favor ingresá tu correo electrónico';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Ingresá un correo válido (ej: usuario@sigah.edu.ar)';
    } elseif (users_find($form['email']) !== null) {
        $errors['email'] = 'Este correo ya está registrado';
    }

    if ($form['usuario'] === '') {
        $errors['usuario'] = 'Por favor ingresá un nombre de usuario';
    } elseif (mb_strlen($form['usuario']) < 3) {
        $errors['usuario'] = 'El nombre de usuario debe tener al menos 3 caracteres';
    } elseif (preg_match('/\s/', $form['usuario'])) {
        $errors['usuario'] = 'El nombre de usuario no puede contener espacios';
    } elseif (users_find($form['usuario']) !== null) {
        $errors['usuario'] = 'Este nombre de usuario ya está en uso';
    }

    if (!isset($roles[$form['rol']])) {
        $errors['rol'] = 'Por favor seleccioná un rol';
    }

    if ($password === '') {
        $errors['password'] = 'Por favor ingresá una contraseña';
    } else {
        $passwordErrors = password_errors($password);

        if ($passwordErrors) {
            $errors['password'] = 'La contraseña no cumple los requisitos: ' . implode(' · ', $passwordErrors);
        }
    }

    if ($passwordConfirm === '') {
        $errors['password_confirm'] = 'Por favor confirmá tu contraseña';
    } elseif ($password !== $passwordConfirm) {
        $errors['password_confirm'] = 'Las contraseñas no coinciden';
    }

    if (!$errors) {
        $created = users_create([
            'id'            => users_next_id(),
            'nombre'        => $form['nombre'],
            'email'         => $form['email'],
            'usuario'       => $form['usuario'],
            'contrasena'    => password_hash($password, PASSWORD_DEFAULT),
            'rol'           => $form['rol'],
            'estado'        => 'activo',
            'fechaCreacion' => date('Y-m-d'),
        ]);

        if ($created) {
            flash_set('success', 'Usuario registrado correctamente. Ya podés iniciar sesión.');
            redirect('index.php');
        }

        $general = 'No se pudo guardar el usuario. Revisá que la carpeta data/ tenga permisos de escritura.';
    } else {
        $general = 'Revisá los campos resaltados.';
    }
}

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Crear usuario';
$bodyClass  = 'page-login';
$showHero   = true;
$pageStyles = <<<'CSS'
    body.page-login { flex-direction: column; }
    .login-hero {
      position: fixed;
      inset: 0;
      z-index: 0;
      background: url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1400&q=80') center/cover no-repeat;
      filter: brightness(0.22) saturate(0.7);
    }
    .login-wrap {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 32px 16px;
      position: relative;
      z-index: 1;
    }
    .login-card {
      background: var(--surface);
      border-radius: var(--radius);
      padding: 36px 32px;
      width: 420px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.35);
      text-align: center;
      max-height: 90vh;
      overflow-y: auto;
    }
    .login-card h1 { color: var(--primary); font-size: 24px; margin-bottom: 6px; }
    .login-card h2 { color: var(--light-gray); font-size: 13px; font-weight: 400; margin-bottom: 20px; line-height: 1.5; border: none; padding: 0; }
    .login-card .form-group { text-align: left; }
    .error-message { background: #ffebee; border-left: 3px solid var(--danger); color: var(--danger); padding: 10px 12px; border-radius: 4px; margin-bottom: 14px; font-size: 12px; text-align: left; }
    .password-requirements {
      background: #f5f5f5;
      border-radius: 6px;
      padding: 12px;
      margin-top: 12px;
      font-size: 11px;
      text-align: left;
      display: none;
    }
    .password-requirements.show { display: block; }
    .req-item { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
    .req-item:last-child { margin-bottom: 0; }
    .req-icon { width: 14px; text-align: center; }
    .req-check { color: var(--success); }
    .req-pending { color: #ccc; }
    .button-group { display: flex; gap: 10px; margin-top: 20px; }
    .button-group .btn { flex: 1; }
    .back-link { margin-top: 16px; font-size: 13px; text-align: center; }
    .back-link a { color: var(--primary); text-decoration: none; }
    .back-link a:hover { text-decoration: underline; }
    @media (max-width: 600px) { .login-card { width: 90%; } }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <div class="login-wrap">
    <div class="login-card">
      <h1><i class="fas fa-user-plus"></i> Crear Usuario</h1>
      <h2>Completá el formulario para registrarte<br>en <?= e(env('APP_NAME', 'SIGAH')) ?></h2>

<?php if ($general): ?>
      <div class="error-message" role="alert"><?= e($general) ?></div>
<?php endif; ?>

      <form method="post" action="<?= e(url('add-user.php')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
          <label for="nombreInput">Nombre completo</label>
          <input type="text" id="nombreInput" name="nombre" value="<?= e($form['nombre']) ?>"
                 placeholder="Ej: Juan Pérez García" autocomplete="name">
          <div class="field-error<?= isset($errors['nombre']) ? ' show' : '' ?>"><?= e($errors['nombre'] ?? '') ?></div>
        </div>

        <div class="form-group">
          <label for="emailInput">Correo electrónico</label>
          <input type="email" id="emailInput" name="email" value="<?= e($form['email']) ?>"
                 placeholder="correo@sigah.edu.ar" autocomplete="email">
          <div class="field-error<?= isset($errors['email']) ? ' show' : '' ?>"><?= e($errors['email'] ?? '') ?></div>
        </div>

        <div class="form-group">
          <label for="usuarioInput">Nombre de usuario</label>
          <input type="text" id="usuarioInput" name="usuario" value="<?= e($form['usuario']) ?>"
                 placeholder="nombre_usuario (sin espacios)" autocomplete="username">
          <div class="field-error<?= isset($errors['usuario']) ? ' show' : '' ?>"><?= e($errors['usuario'] ?? '') ?></div>
        </div>

        <div class="form-group">
          <label for="rolSelect">Rol</label>
          <select id="rolSelect" name="rol">
            <option value="">-- Seleccioná tu rol --</option>
<?php foreach ($roles as $value => $label): ?>
            <option value="<?= e($value) ?>"<?= $form['rol'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
          </select>
          <div class="field-error<?= isset($errors['rol']) ? ' show' : '' ?>"><?= e($errors['rol'] ?? '') ?></div>
        </div>

        <div class="form-group">
          <label for="passwordInput">Contraseña</label>
          <input type="password" id="passwordInput" name="password"
                 placeholder="••••••••" autocomplete="new-password">
          <div class="field-error<?= isset($errors['password']) ? ' show' : '' ?>"><?= e($errors['password'] ?? '') ?></div>
        </div>

        <div id="passwordReqs" class="password-requirements">
          <div class="req-item">
            <span class="req-icon req-pending" id="reqLength"><i class="fas fa-times"></i></span>
            <span>Mínimo <?= (int) ($rules['minCaracteres'] ?? 8) ?> caracteres</span>
          </div>
          <div class="req-item">
            <span class="req-icon req-pending" id="reqNumber"><i class="fas fa-times"></i></span>
            <span>Al menos un número (0-9)</span>
          </div>
          <div class="req-item">
            <span class="req-icon req-pending" id="reqUpper"><i class="fas fa-times"></i></span>
            <span>Al menos una mayúscula (A-Z)</span>
          </div>
          <div class="req-item">
            <span class="req-icon req-pending" id="reqLower"><i class="fas fa-times"></i></span>
            <span>Al menos una minúscula (a-z)</span>
          </div>
          <div class="req-item">
            <span class="req-icon req-pending" id="reqSpecial"><i class="fas fa-times"></i></span>
            <span>Carácter especial (@$!%*?&amp;)</span>
          </div>
        </div>

        <div class="form-group mt-16">
          <label for="passwordConfirmInput">Confirmar contraseña</label>
          <input type="password" id="passwordConfirmInput" name="password_confirm"
                 placeholder="••••••••" autocomplete="new-password">
          <div class="field-error<?= isset($errors['password_confirm']) ? ' show' : '' ?>"><?= e($errors['password_confirm'] ?? '') ?></div>
        </div>

        <div class="button-group">
          <a class="btn btn-outline" href="<?= e(url('index.php')) ?>">Cancelar</a>
          <button class="btn btn-success" type="submit"><i class="fas fa-check"></i> Registrarse</button>
        </div>
      </form>

      <div class="back-link">
        ¿Ya tenés cuenta? <a href="<?= e(url('index.php')) ?>">Iniciá sesión acá</a>
      </div>
    </div>
  </div>

<?php
$minLength = (int) ($rules['minCaracteres'] ?? 8);

/**
 * El chequeo visual de requisitos sigue siendo del lado del cliente,
 * pero la validación que decide es la de PHP (arriba).
 */
$pageScripts = <<<JS
(function () {
  var input = document.getElementById('passwordInput');
  var box   = document.getElementById('passwordReqs');
  if (!input || !box) return;

  function update() {
    var value = input.value;

    box.classList.toggle('show', value.length > 0);

    var checks = {
      reqLength:  value.length >= {$minLength},
      reqNumber:  /\d/.test(value),
      reqUpper:   /[A-Z]/.test(value),
      reqLower:   /[a-z]/.test(value),
      reqSpecial: /[@\$!%*?&]/.test(value)
    };

    Object.keys(checks).forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.innerHTML = checks[id] ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
      el.className = 'req-icon ' + (checks[id] ? 'req-check' : 'req-pending');
    });
  }

  input.addEventListener('input', update);
  update();
})();
JS;

include __DIR__ . '/partials/footer.php';
