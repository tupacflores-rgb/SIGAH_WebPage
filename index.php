<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

/* ------------------------------------------------------------------
 |  Si ya hay sesión, no tiene sentido mostrar el login
 | ------------------------------------------------------------------ */
if (auth_check()) {
    redirect('home.php');
}

$errors  = [];
$general = flash_get('error');
$success = flash_get('success');
$email   = '';

/* ------------------------------------------------------------------
 |  Procesar el formulario
 | ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // --- Acceso de prueba -------------------------------------------------
    if (isset($_POST['demo'])) {
        if (!(bool) env('ALLOW_DEMO_LOGIN', true)) {
            $general = 'El acceso de prueba está deshabilitado en este entorno.';
        } else {
            auth_login([
                'id'      => 0,
                'nombre'  => 'Usuario de prueba',
                'email'   => 'demo@sigah.edu.ar',
                'usuario' => 'demo',
                'rol'     => 'admin',
                'demo'    => true,
            ]);

            flash_set('success', 'Acceso de prueba activado.');
            redirect('home.php');
        }
    } else {
        // --- Inicio de sesión normal --------------------------------------
        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '') {
            $errors['email'] = 'Por favor ingresá tu correo o usuario';
        }
        if ($password === '') {
            $errors['password'] = 'Por favor ingresá tu contraseña';
        }

        if (!$errors) {
            $user = users_find($email);

            if ($user === null) {
                $errors['email'] = 'Usuario o correo no encontrado';
                $general = 'Usuario o correo no encontrado';
            } elseif (!password_matches($password, (string) ($user['contrasena'] ?? ''))) {
                $errors['password'] = 'Contraseña incorrecta';
                $general = 'Contraseña incorrecta';
            } elseif (($user['estado'] ?? 'activo') !== 'activo') {
                $general = 'La cuenta está deshabilitada. Contactá al administrador.';
            } else {
                auth_login($user);

                flash_set('success', '¡Bienvenido ' . $user['nombre'] . '!');
                redirect('home.php');
            }
        } else {
            $general = 'Revisá los campos resaltados';
        }
    }
}

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Inicio de sesión';
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
      width: 370px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.35);
      text-align: center;
    }
    .avatar {
      width: 82px;
      height: 82px;
      border-radius: 50%;
      margin: 0 auto 14px;
      border: 3px solid var(--primary);
      overflow: hidden;
      background: #e8f6fa;
    }
    .avatar img { width: 100%; height: 100%; object-fit: cover; }
    .school-banner {
      width: 100%;
      height: 90px;
      border-radius: var(--radius-sm);
      overflow: hidden;
      margin-bottom: 18px;
      position: relative;
    }
    .school-banner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.55) saturate(0.8);
    }
    .school-banner-text {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: white;
    }
    .school-banner-text span { font-size: 11px; color: #cde; }
    .login-card h1 { color: var(--primary); font-size: 26px; margin-bottom: 2px; }
    .login-card h2 { color: var(--light-gray); font-size: 13px; font-weight: 400; margin-bottom: 20px; line-height: 1.5; border: none; padding: 0; }
    .login-card .form-group { text-align: left; }
    .forgot { font-size: 12px; color: var(--light-gray); margin-top: 14px; }
    .forgot a { color: var(--primary); text-decoration: none; }
    .error-message { background: #ffebee; border-left: 3px solid var(--danger); color: var(--danger); padding: 10px 12px; border-radius: 4px; margin-bottom: 14px; font-size: 12px; text-align: left; }
    .success-message { background: #e8f5e9; border-left: 3px solid var(--success); color: var(--success); padding: 10px 12px; border-radius: 4px; margin-bottom: 14px; font-size: 12px; text-align: left; }
    .signup-link { margin-top: 16px; font-size: 13px; color: var(--gray); }
    .signup-link a { color: var(--primary); font-weight: 600; text-decoration: none; }
    .signup-link a:hover { text-decoration: underline; }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <div class="login-wrap">
    <div class="login-card">
      <div class="school-banner">
        <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=800&q=80"
             alt="Fachada de la <?= e(env('SCHOOL_SHORT', '')) ?> – Institución educativa">
        <div class="school-banner-text">
          <strong style="font-size:13px"><?= e(env('SCHOOL_SHORT', '')) ?></strong>
          <span>Rep. de la India — <?= e(env('SCHOOL_CITY', '')) ?></span>
        </div>
      </div>

      <div class="avatar">
        <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?w=200&q=80"
             alt="Logo <?= e(env('APP_NAME', 'SIGAH')) ?> – Sistema de gestión escolar">
      </div>

      <h1><?= e(env('APP_NAME', 'SIGAH')) ?></h1>
      <h2>Sistema Integral de Gestión<br>de Asistencias y Horarios</h2>

<?php if ($general): ?>
      <div class="error-message" role="alert"><?= e($general) ?></div>
<?php endif; ?>
<?php if ($success): ?>
      <div class="success-message" role="status"><?= e($success) ?></div>
<?php endif; ?>

      <form method="post" action="<?= e(url('index.php')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
          <label for="emailInput">Correo electrónico o Usuario</label>
          <input type="text" id="emailInput" name="email"
                 value="<?= e($email) ?>"
                 placeholder="usuario@sigah.edu.ar o usuario_nombre"
                 autocomplete="username" autofocus>
          <div class="field-error<?= isset($errors['email']) ? ' show' : '' ?>"><?= e($errors['email'] ?? '') ?></div>
        </div>

        <div class="form-group">
          <label for="passwordInput">Contraseña</label>
          <input type="password" id="passwordInput" name="password"
                 placeholder="••••••••" autocomplete="current-password">
          <div class="field-error<?= isset($errors['password']) ? ' show' : '' ?>"><?= e($errors['password'] ?? '') ?></div>
        </div>

        <button class="btn btn-primary btn-block" type="submit">
          <i class="fas fa-right-to-bracket"></i> Ingresar al sistema
        </button>
      </form>

<?php if ((bool) env('ALLOW_DEMO_LOGIN', true)): ?>
      <form method="post" action="<?= e(url('index.php')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-outline btn-block mt-8" type="submit" name="demo" value="1">
          <i class="fas fa-door-open"></i> Entrar como prueba
        </button>
      </form>
<?php endif; ?>

      <p class="forgot"><a href="<?= e(url('faq.php')) ?>">¿Olvidaste tu contraseña?</a></p>

<?php if ((bool) env('ALLOW_SELF_REGISTER', true)): ?>
      <div class="signup-link">
        <span>¿No tienes cuenta?</span> <a href="<?= e(url('add-user.php')) ?>">Crear usuario</a>
      </div>
<?php endif; ?>
    </div>
  </div>

<?php
$pageScripts = '';

if ((bool) env('SHOW_WELCOME_ALERT', false)) {
    $pageScripts .= "window.addEventListener('load', function () { alert('¡Bienvenido a " . e((string) env('APP_NAME', 'SIGAH')) . "!'); });\n";
}

include __DIR__ . '/partials/footer.php';
