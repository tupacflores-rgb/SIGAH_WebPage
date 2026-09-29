<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require_login();

$datos       = read_json('institution.json');
$institucion = $datos['institucion']  ?? [];
$ciclo       = $datos['cicloLectivo'] ?? [];
$cursos      = $datos['cursos']       ?? [];
$avisos      = $datos['notificaciones'] ?? [
    'alertasAusentismo' => true,
    'correoDiario'      => false,
    'reporteMensual'    => true,
];

/* ------------------------------------------------------------------
 |  Guardado (ahora persiste en data/institution.json, no en localStorage)
 | ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $accion = (string) ($_POST['accion'] ?? '');

    /** Guarda el archivo y deja el mensaje flash correspondiente. */
    $persistir = static function (array $datos, string $ok): void {
        if (write_json('institution.json', $datos)) {
            flash_set('success', $ok);
        } else {
            flash_set('error', 'No se pudo escribir data/institution.json. Revisá los permisos de la carpeta.');
        }
    };

    if ($accion === 'institucion') {
        foreach (['nombre', 'cue', 'provincia', 'localidad', 'direccion', 'codigoPostal', 'telefono', 'email'] as $campo) {
            $institucion[$campo] = trim((string) ($_POST[$campo] ?? ($institucion[$campo] ?? '')));
        }

        $datos['institucion'] = $institucion;
        $persistir($datos, 'Datos de la institución actualizados correctamente.');
    } elseif ($accion === 'ciclo') {
        $ciclo['anio']        = (int) ($_POST['anio'] ?? date('Y'));
        $ciclo['fechaInicio'] = (string) ($_POST['fechaInicio'] ?? '');
        $ciclo['fechaFin']    = (string) ($_POST['fechaFin'] ?? '');
        $ciclo['estado']      = in_array((string) ($_POST['estado'] ?? ''), ['activo', 'finalizado', 'pausado'], true)
            ? (string) $_POST['estado']
            : 'activo';
        $ciclo['turnosMañana'] = isset($_POST['turnoManana']);
        $ciclo['turnosTarde']  = isset($_POST['turnoTarde']);
        $ciclo['turnosNoche']  = isset($_POST['turnoNoche']);

        $datos['cicloLectivo'] = $ciclo;
        $persistir($datos, 'Ciclo lectivo actualizado correctamente.');
    } elseif ($accion === 'notificaciones') {
        $datos['notificaciones'] = [
            'alertasAusentismo' => isset($_POST['alertasAusentismo']),
            'correoDiario'      => isset($_POST['correoDiario']),
            'reporteMensual'    => isset($_POST['reporteMensual']),
        ];

        $persistir($datos, 'Preferencias de notificación guardadas.');
    }

    redirect('config.php');
}

$flashSuccess = flash_get('success');
$flashError   = flash_get('error');

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Configuración';
$pageIcon    = 'fa-cogs';
$pageHeading = 'Configuración';
$pageDesc    = 'Administrá la institución, usuarios, notificaciones y opciones del sistema.';
$breadcrumb  = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard', 'url' => 'home.php'],
    ['label' => 'Configuración'],
];

$pageStyles = <<<'CSS'
    .cfg-row { display: flex; align-items: center; padding: 14px 4px; border-bottom: 1px solid var(--border); gap: 12px; }
    .cfg-row:last-child { border-bottom: none; }
    .cfg-icon { font-size: 22px; width: 34px; text-align: center; color: var(--primary); }
    .cfg-text { flex: 1; }
    .cfg-text .lbl { font-size: 14px; color: var(--dark); font-weight: 600; }
    .cfg-text .desc { font-size: 12px; color: var(--light-gray); margin-top: 2px; }
    .cfg-action { background: none; border: none; color: var(--primary); cursor: pointer; font-size: 13px; font-weight: 600; font-family: inherit; }

    .switch { display: inline-flex; align-items: center; cursor: pointer; }
    .switch input { position: absolute; opacity: 0; width: 0; height: 0; }
    .switch .track { width: 44px; height: 24px; background: #ccc; border-radius: 12px; position: relative; transition: background 0.2s; display: block; }
    .switch .track::after { content: ''; position: absolute; width: 20px; height: 20px; background: white; border-radius: 50%; top: 2px; left: 2px; transition: left 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
    .switch input:checked + .track { background: var(--primary); }
    .switch input:checked + .track::after { left: 22px; }
    .switch input:focus-visible + .track { outline: 2px solid var(--primary); outline-offset: 2px; }

    .config-banner { width: 100%; height: 100px; object-fit: cover; border-radius: var(--radius); margin-bottom: 20px; opacity: 0.75; display: block; }

    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
    .modal.show { display: flex; }
    .modal-content { background: var(--surface); border-radius: var(--radius); padding: 24px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; }
    .modal-header { font-size: 18px; font-weight: 700; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; color: var(--dark); }
    .modal-close { font-size: 20px; cursor: pointer; background: none; border: none; color: var(--light-gray); }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <main>
    <img class="config-banner"
         src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1000&q=75"
         alt="Configuración del sistema <?= e(env('APP_NAME', 'SIGAH')) ?>">

<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i> <?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert-error" role="alert"><i class="fas fa-triangle-exclamation"></i> <?= e($flashError) ?></div>
<?php endif; ?>

    <div class="card">
      <h2><i class="fas fa-school"></i> Institución</h2>
      <p>Datos generales del establecimiento educativo.</p><br>

      <div class="cfg-row">
        <div class="cfg-icon"><i class="fas fa-school"></i></div>
        <div class="cfg-text">
          <div class="lbl">Datos del establecimiento</div>
          <div class="desc"><?= e($institucion['nombre'] ?? 'Nombre, CUE, dirección') ?></div>
        </div>
        <button class="cfg-action" type="button" data-modal="institucionModal">Editar <i class="fas fa-chevron-right"></i></button>
      </div>

      <div class="cfg-row">
        <div class="cfg-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="cfg-text">
          <div class="lbl">Ciclo lectivo</div>
          <div class="desc"><?= e(($ciclo['anio'] ?? '—') . ' — ' . ($ciclo['estado'] ?? '—')) ?></div>
        </div>
        <button class="cfg-action" type="button" data-modal="cicloModal">Editar <i class="fas fa-chevron-right"></i></button>
      </div>

      <div class="cfg-row">
        <div class="cfg-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="cfg-text">
          <div class="lbl">Cursos y secciones</div>
          <div class="desc"><?= count($cursos) ?> cursos habilitados</div>
        </div>
        <a class="cfg-action" href="<?= e(url('registry-al.php')) ?>">Ver alumnos <i class="fas fa-chevron-right"></i></a>
      </div>
    </div>

    <div class="card">
      <h2><i class="fas fa-users-cog"></i> Usuarios y accesos</h2>
      <p>Administrá administradores, docentes y sus contraseñas.</p><br>

      <div class="cfg-row">
        <div class="cfg-icon"><i class="fas fa-users"></i></div>
        <div class="cfg-text">
          <div class="lbl">Gestión de usuarios</div>
          <div class="desc"><?= count(users_all()) ?> usuarios registrados</div>
        </div>
        <a class="cfg-action" href="<?= e(url('add-user.php')) ?>">Crear <i class="fas fa-chevron-right"></i></a>
      </div>

      <div class="cfg-row">
        <div class="cfg-icon"><i class="fas fa-user-shield"></i></div>
        <div class="cfg-text">
          <div class="lbl">Sesión actual</div>
          <div class="desc"><?= e(auth_name()) ?> — rol <?= e(auth_role()) ?></div>
        </div>
        <a class="cfg-action" href="<?= e(url('perf-adm.php')) ?>">Ver perfil <i class="fas fa-chevron-right"></i></a>
      </div>
    </div>

    <div class="card">
      <h2><i class="fas fa-bell"></i> Notificaciones y alertas</h2>
      <p>Configurá cuándo y cómo recibir avisos del sistema.</p><br>

      <form method="post" action="<?= e(url('config.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="notificaciones">

        <div class="cfg-row">
          <div class="cfg-icon"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="cfg-text">
            <div class="lbl">Alertas de ausentismo</div>
            <div class="desc">Avisar al bajar del <?= (int) env('ATTENDANCE_RISK_THRESHOLD', 85) ?>% de asistencia</div>
          </div>
          <label class="switch">
            <input type="checkbox" name="alertasAusentismo"<?= !empty($avisos['alertasAusentismo']) ? ' checked' : '' ?>>
            <span class="track"></span>
          </label>
        </div>

        <div class="cfg-row">
          <div class="cfg-icon"><i class="fas fa-envelope"></i></div>
          <div class="cfg-text">
            <div class="lbl">Notificaciones por correo</div>
            <div class="desc">Enviar resumen diario por email</div>
          </div>
          <label class="switch">
            <input type="checkbox" name="correoDiario"<?= !empty($avisos['correoDiario']) ? ' checked' : '' ?>>
            <span class="track"></span>
          </label>
        </div>

        <div class="cfg-row">
          <div class="cfg-icon"><i class="fas fa-file-alt"></i></div>
          <div class="cfg-text">
            <div class="lbl">Reporte automático mensual</div>
            <div class="desc">Generar al cierre de cada mes</div>
          </div>
          <label class="switch">
            <input type="checkbox" name="reporteMensual"<?= !empty($avisos['reporteMensual']) ? ' checked' : '' ?>>
            <span class="track"></span>
          </label>
        </div>

        <br>
        <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Guardar preferencias</button>
      </form>
    </div>

    <div class="card">
      <h2><i class="fas fa-server"></i> Sistema</h2>
      <p>Información del entorno donde está corriendo <?= e(env('APP_NAME', 'SIGAH')) ?>.</p><br>

      <img class="about-img"
           src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=900&q=70"
           alt="<?= e(env('APP_NAME', 'SIGAH')) ?> – Sistema de gestión escolar">

<pre style="background:rgba(0,0,0,0.04);border-left:3px solid var(--primary);padding:12px;border-radius:6px;font-size:11px;line-height:1.9">Sistema   : <?= e(env('APP_NAME', 'SIGAH')) ?> v<?= e(env('APP_VERSION', '1.0.0')) ?>

Entorno   : <?= e(env('APP_ENV', 'local')) ?> (APP_DEBUG=<?= env('APP_DEBUG', false) ? 'true' : 'false' ?>)
URL base  : <?= e(base_path_url() === '' ? '/' : base_path_url()) ?>

Autor     : Rolando Maximo Tupac Flores Arce
Servidor  : PHP <?= e(PHP_VERSION) ?> · <?= e($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?>

Datos     : <?= db_ready() ? 'MariaDB (' . e((string) env('DB_NAME', 'sigah')) . ')' : 'Archivos JSON en data/' ?>

Frontend  : HTML5 + CSS3 + JavaScript
Licencia  : <?= e(env('SCHOOL_SHORT', '')) ?> — <?= date('Y') ?></pre>

      <br>
      <form method="post" action="<?= e(url('logout.php')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-outline" type="submit" style="color:var(--danger);border-color:var(--danger);width:100%">
          <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </button>
      </form>
    </div>
  </main>

  <!-- Modal: datos de la institución -->
  <div id="institucionModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="tituloInstitucion">
    <div class="modal-content">
      <div class="modal-header">
        <span id="tituloInstitucion"><i class="fas fa-school"></i> Editar datos de institución</span>
        <button class="modal-close" type="button" data-close aria-label="Cerrar"><i class="fas fa-times"></i></button>
      </div>

      <form method="post" action="<?= e(url('config.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="institucion">

        <div class="form-group">
          <label for="nombreInst">Nombre de la institución</label>
          <input type="text" id="nombreInst" name="nombre" value="<?= e($institucion['nombre'] ?? '') ?>">
        </div>
        <div class="row2">
          <div class="form-group">
            <label for="cueInst">CUE</label>
            <input type="text" id="cueInst" name="cue" value="<?= e($institucion['cue'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="cpInst">Código postal</label>
            <input type="text" id="cpInst" name="codigoPostal" value="<?= e($institucion['codigoPostal'] ?? '') ?>">
          </div>
        </div>
        <div class="row2">
          <div class="form-group">
            <label for="provinciaInst">Provincia</label>
            <input type="text" id="provinciaInst" name="provincia" value="<?= e($institucion['provincia'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="localidadInst">Localidad</label>
            <input type="text" id="localidadInst" name="localidad" value="<?= e($institucion['localidad'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="direccionInst">Dirección</label>
          <input type="text" id="direccionInst" name="direccion" value="<?= e($institucion['direccion'] ?? '') ?>">
        </div>
        <div class="row2">
          <div class="form-group">
            <label for="telefonoInst">Teléfono</label>
            <input type="tel" id="telefonoInst" name="telefono" value="<?= e($institucion['telefono'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="emailInst">Correo electrónico</label>
            <input type="email" id="emailInst" name="email" value="<?= e($institucion['email'] ?? '') ?>">
          </div>
        </div>

        <div class="flex-between" style="margin-top:20px">
          <button class="btn btn-outline" type="button" data-close>Cancelar</button>
          <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: ciclo lectivo -->
  <div id="cicloModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="tituloCiclo">
    <div class="modal-content">
      <div class="modal-header">
        <span id="tituloCiclo"><i class="fas fa-calendar-alt"></i> Editar ciclo lectivo</span>
        <button class="modal-close" type="button" data-close aria-label="Cerrar"><i class="fas fa-times"></i></button>
      </div>

      <form method="post" action="<?= e(url('config.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="ciclo">

        <div class="form-group">
          <label for="anioCiclo">Año del ciclo</label>
          <input type="number" id="anioCiclo" name="anio" min="2020" max="2100"
                 value="<?= e($ciclo['anio'] ?? date('Y')) ?>">
        </div>
        <div class="row2">
          <div class="form-group">
            <label for="fechaInicio">Fecha de inicio</label>
            <input type="date" id="fechaInicio" name="fechaInicio" value="<?= e($ciclo['fechaInicio'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="fechaFin">Fecha de fin</label>
            <input type="date" id="fechaFin" name="fechaFin" value="<?= e($ciclo['fechaFin'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="estadoCiclo">Estado</label>
          <select id="estadoCiclo" name="estado">
<?php foreach (['activo' => 'Activo', 'finalizado' => 'Finalizado', 'pausado' => 'Pausado'] as $valor => $texto): ?>
            <option value="<?= e($valor) ?>"<?= ($ciclo['estado'] ?? 'activo') === $valor ? ' selected' : '' ?>><?= e($texto) ?></option>
<?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Turnos habilitados</label>
          <label style="display:flex;align-items:center;gap:8px;font-weight:400">
            <input type="checkbox" name="turnoManana" style="width:auto"<?= !empty($ciclo['turnosMañana']) ? ' checked' : '' ?>> Mañana
          </label>
          <label style="display:flex;align-items:center;gap:8px;font-weight:400">
            <input type="checkbox" name="turnoTarde" style="width:auto"<?= !empty($ciclo['turnosTarde']) ? ' checked' : '' ?>> Tarde
          </label>
          <label style="display:flex;align-items:center;gap:8px;font-weight:400">
            <input type="checkbox" name="turnoNoche" style="width:auto"<?= !empty($ciclo['turnosNoche']) ? ' checked' : '' ?>> Noche
          </label>
        </div>

        <div class="flex-between" style="margin-top:20px">
          <button class="btn btn-outline" type="button" data-close>Cancelar</button>
          <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>

<?php
$pageScripts = <<<'JS'
(function () {
  document.querySelectorAll('[data-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var modal = document.getElementById(btn.dataset.modal);
      if (modal) modal.classList.add('show');
    });
  });

  document.querySelectorAll('[data-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var modal = btn.closest('.modal');
      if (modal) modal.classList.remove('show');
    });
  });
})();
JS;

include __DIR__ . '/partials/footer.php';
