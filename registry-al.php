<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require_login();

$institucion = read_json('institution.json');
$cursos      = array_map(static fn (array $c): string => (string) ($c['nombre'] ?? ''), $institucion['cursos'] ?? []);
$cursos      = array_values(array_filter($cursos)) ?: ['1° A'];
$turnos      = ['Mañana', 'Tarde', 'Noche'];

$umbralRiesgo = (int) env('ATTENDANCE_RISK_THRESHOLD', 85);

/* ------------------------------------------------------------------
 |  Altas, bajas y modificaciones
 | ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $datos       = read_json('students.json');
    $estudiantes = $datos['estudiantes'] ?? [];
    $accion      = (string) ($_POST['accion'] ?? '');

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);

        $antes       = count($estudiantes);
        $estudiantes = array_values(array_filter(
            $estudiantes,
            static fn (array $a): bool => (int) ($a['id'] ?? 0) !== $id
        ));

        $datos['estudiantes'] = $estudiantes;

        if ($antes !== count($estudiantes) && write_json('students.json', $datos)) {
            flash_set('success', 'Alumno eliminado correctamente.');
        } else {
            flash_set('error', 'No se pudo eliminar el alumno.');
        }
    } elseif ($accion === 'guardar') {
        $id     = (int) ($_POST['id'] ?? 0);
        $campos = [
            'nombre'          => trim((string) ($_POST['nombre'] ?? '')),
            'apellido'        => trim((string) ($_POST['apellido'] ?? '')),
            'dni'             => trim((string) ($_POST['dni'] ?? '')),
            'fechaNacimiento' => trim((string) ($_POST['fechaNacimiento'] ?? '')),
            'email'           => trim((string) ($_POST['email'] ?? '')),
            'telefono'        => trim((string) ($_POST['telefono'] ?? '')),
            'curso'           => (string) ($_POST['curso'] ?? $cursos[0]),
            'turno'           => (string) ($_POST['turno'] ?? $turnos[0]),
            'observaciones'   => trim((string) ($_POST['observaciones'] ?? '')),
        ];

        $errores = [];

        if ($campos['nombre'] === '')   { $errores[] = 'el nombre'; }
        if ($campos['apellido'] === '') { $errores[] = 'el apellido'; }
        if ($campos['dni'] === '')      { $errores[] = 'el DNI'; }
        if ($campos['email'] === '')    { $errores[] = 'el correo'; }

        if (!in_array($campos['curso'], $cursos, true)) { $campos['curso'] = $cursos[0]; }
        if (!in_array($campos['turno'], $turnos, true)) { $campos['turno'] = $turnos[0]; }

        if ($errores) {
            flash_set('error', 'Faltan datos obligatorios: ' . implode(', ', $errores) . '.');
        } elseif ($campos['email'] !== '' && !filter_var($campos['email'], FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'El correo electrónico no tiene un formato válido.');
        } else {
            $indice = null;

            foreach ($estudiantes as $i => $alumno) {
                if ((int) ($alumno['id'] ?? 0) === $id) {
                    $indice = $i;
                    break;
                }
            }

            if ($indice !== null) {
                $estudiantes[$indice] = array_merge($estudiantes[$indice], $campos);
                flash_set('success', 'Alumno actualizado correctamente.');
            } else {
                $ids = array_map(static fn (array $a): int => (int) ($a['id'] ?? 0), $estudiantes);

                $estudiantes[] = array_merge($campos, [
                    'id'         => ($ids ? max($ids) : 0) + 1,
                    'foto'       => 'https://i.pravatar.cc/150?img=' . random_int(1, 70),
                    'estado'     => 'activo',
                    'asistencia' => 100,
                    'ausencias'  => 0,
                    'tardanzas'  => 0,
                    'promedio'   => 0,
                ]);

                flash_set('success', 'Alumno agregado correctamente.');
            }

            $datos['estudiantes'] = $estudiantes;

            if (!write_json('students.json', $datos)) {
                flash_set('error', 'No se pudo escribir data/students.json. Revisá los permisos de la carpeta.');
            }
        }
    }

    redirect('registry-al.php?' . http_build_query([
        'q'     => (string) ($_POST['q'] ?? ''),
        'curso' => (string) ($_POST['cursoFiltro'] ?? ''),
    ]));
}

/* ------------------------------------------------------------------
 |  Listado + filtros (se resuelven en el servidor)
 | ------------------------------------------------------------------ */
$busqueda     = trim((string) ($_GET['q'] ?? ''));
$cursoFiltro  = (string) ($_GET['curso'] ?? '');
$estudiantes  = read_json('students.json')['estudiantes'] ?? [];
$totalAlumnos = count($estudiantes);

$filtrados = array_values(array_filter($estudiantes, static function (array $a) use ($busqueda, $cursoFiltro): bool {
    if ($cursoFiltro !== '' && ($a['curso'] ?? '') !== $cursoFiltro) {
        return false;
    }

    if ($busqueda === '') {
        return true;
    }

    $aguja = mb_strtolower($busqueda);
    $pajar = mb_strtolower(implode(' ', [
        $a['nombre'] ?? '', $a['apellido'] ?? '', $a['dni'] ?? '', $a['curso'] ?? '', $a['email'] ?? '',
    ]));

    return str_contains($pajar, $aguja);
}));

usort(
    $filtrados,
    static fn (array $a, array $b): int => strcmp((string) ($a['apellido'] ?? ''), (string) ($b['apellido'] ?? ''))
);

$flashSuccess = flash_get('success');
$flashError   = flash_get('error');

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Registro de alumnos';
$pageIcon    = 'fa-user-plus';
$pageHeading = 'Registro de Alumnos';
$pageDesc    = 'Gestionar alumnos: crear, editar o eliminar registros del sistema.';
$breadcrumb  = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard', 'url' => 'home.php'],
    ['label' => 'Registro'],
];

$pageStyles = <<<'CSS'
    .reg-banner { width: 100%; height: 110px; object-fit: cover; border-radius: var(--radius); margin-bottom: 20px; opacity: 0.82; display: block; }
    .alumno-item { background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 14px; margin-bottom: 10px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .alumno-avatar { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border); flex-shrink: 0; }
    .alumno-info { flex: 1; min-width: 220px; }
    .alumno-info .nombre { font-weight: 700; color: var(--dark); }
    .alumno-info .datos { font-size: 12px; color: var(--gray); }
    .alumno-acciones { display: flex; gap: 6px; }
    .alumno-acciones .btn { padding: 6px 12px; font-size: 12px; }
    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
    .modal.show { display: flex; }
    .modal-content { background: var(--surface); border-radius: var(--radius); padding: 24px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; }
    .modal-header { font-size: 18px; font-weight: 700; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; color: var(--dark); }
    .modal-close { font-size: 20px; cursor: pointer; background: none; border: none; color: var(--light-gray); }
    .empty-state { text-align: center; padding: 26px 12px; color: var(--light-gray); }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <main>
    <img class="reg-banner" src="https://images.unsplash.com/photo-1509062522246-3755977927d7?w=900&q=75"
         alt="Banner de registro de alumnos – <?= e(env('SCHOOL_SHORT', '')) ?>">

<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i> <?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert-error" role="alert"><i class="fas fa-triangle-exclamation"></i> <?= e($flashError) ?></div>
<?php endif; ?>

    <div class="card">
      <div class="flex-between">
        <h2 style="margin:0;border:none;padding:0"><i class="fas fa-users"></i> Alumnos del sistema (<?= $totalAlumnos ?>)</h2>
        <button class="btn btn-success" type="button" id="btnNuevoAlumno"><i class="fas fa-plus"></i> Agregar alumno</button>
      </div>
    </div>

    <div class="card">
      <form method="get" action="<?= e(url('registry-al.php')) ?>">
        <div class="form-group">
          <label for="searchInput">Buscar alumno (nombre, DNI, curso, correo)</label>
          <input type="search" id="searchInput" name="q" value="<?= e($busqueda) ?>"
                 placeholder="Ej: Flores, 00.000.000, 1° A...">
        </div>
        <div class="form-group">
          <label for="cursoFilter">Filtrar por curso</label>
          <select id="cursoFilter" name="curso">
            <option value="">-- Todos los cursos --</option>
<?php foreach ($cursos as $opcion): ?>
            <option value="<?= e($opcion) ?>"<?= $opcion === $cursoFiltro ? ' selected' : '' ?>><?= e($opcion) ?></option>
<?php endforeach; ?>
          </select>
        </div>
        <div class="flex-between">
          <a class="btn btn-outline" href="<?= e(url('registry-al.php')) ?>"><i class="fas fa-rotate-left"></i> Limpiar</a>
          <button class="btn btn-primary" type="submit"><i class="fas fa-magnifying-glass"></i> Buscar</button>
        </div>
      </form>
    </div>

    <div class="card">
<?php if (!$filtrados): ?>
      <div class="empty-state">
        <i class="fas fa-user-slash" style="font-size:28px"></i>
        <p>No se encontraron alumnos con esos criterios.</p>
      </div>
<?php else: ?>
      <p class="text-muted mb-16">Mostrando <?= count($filtrados) ?> de <?= $totalAlumnos ?> alumnos.</p>

<?php foreach ($filtrados as $alumno): ?>
      <div class="alumno-item">
        <img class="alumno-avatar" src="<?= e($alumno['foto'] ?? '') ?>"
             alt="Avatar de <?= e($alumno['apellido'] . ', ' . $alumno['nombre']) ?>">

        <div class="alumno-info">
          <div class="nombre"><?= e($alumno['apellido'] . ', ' . $alumno['nombre']) ?></div>
          <div class="datos">
            <i class="fas fa-id-card"></i> DNI: <?= e($alumno['dni'] ?? '') ?>
            | <?= e($alumno['curso'] ?? '') ?> <?= e($alumno['turno'] ?? '') ?>
          </div>
          <div class="datos">
            <i class="fas fa-envelope"></i> <?= e($alumno['email'] ?? '') ?>
            | <i class="fas fa-phone"></i> <?= e($alumno['telefono'] ?? '') ?>
          </div>
          <div class="datos" style="color:<?= ((float) ($alumno['asistencia'] ?? 0)) >= $umbralRiesgo ? 'var(--success)' : 'var(--warning)' ?>">
            <i class="fas fa-chart-line"></i> Asistencia: <?= e($alumno['asistencia'] ?? 0) ?>%
            | Promedio: <?= e($alumno['promedio'] ?? 0) ?>
          </div>
        </div>

        <div class="alumno-acciones">
          <button class="btn btn-outline btn-editar" type="button"
                  data-alumno="<?= e(json_encode($alumno, JSON_UNESCAPED_UNICODE)) ?>">
            <i class="fas fa-edit"></i> Editar
          </button>

          <form method="post" action="<?= e(url('registry-al.php')) ?>" class="form-eliminar" style="margin:0">
            <?= csrf_field() ?>
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="<?= (int) $alumno['id'] ?>">
            <input type="hidden" name="q" value="<?= e($busqueda) ?>">
            <input type="hidden" name="cursoFiltro" value="<?= e($cursoFiltro) ?>">
            <button class="btn btn-outline" type="submit"
                    style="color:var(--danger);border-color:var(--danger)"
                    data-nombre="<?= e($alumno['apellido'] . ', ' . $alumno['nombre']) ?>">
              <i class="fas fa-trash"></i> Eliminar
            </button>
          </form>
        </div>
      </div>
<?php endforeach; ?>
<?php endif; ?>
    </div>
  </main>

  <!-- Modal de alta / edición -->
  <div id="alumnoModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
      <div class="modal-header">
        <span id="modalTitle">Agregar alumno</span>
        <button class="modal-close" type="button" id="btnCerrarModal" aria-label="Cerrar">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form method="post" action="<?= e(url('registry-al.php')) ?>" id="alumnoForm">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" id="alumnoId" value="0">
        <input type="hidden" name="q" value="<?= e($busqueda) ?>">
        <input type="hidden" name="cursoFiltro" value="<?= e($cursoFiltro) ?>">

        <div class="row2">
          <div class="form-group">
            <label for="nombre">Nombre/s</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: Lara María" required>
          </div>
          <div class="form-group">
            <label for="apellido">Apellido/s</label>
            <input type="text" id="apellido" name="apellido" placeholder="Ej: González" required>
          </div>
        </div>

        <div class="row2">
          <div class="form-group">
            <label for="dni">DNI</label>
            <input type="text" id="dni" name="dni" placeholder="00.000.000" required>
          </div>
          <div class="form-group">
            <label for="fechaNacimiento">Fecha de nacimiento</label>
            <input type="date" id="fechaNacimiento" name="fechaNacimiento">
          </div>
        </div>

        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input type="email" id="email" name="email" placeholder="alumno@estudiante.edu.ar" required>
        </div>

        <div class="form-group">
          <label for="telefono">Teléfono</label>
          <input type="tel" id="telefono" name="telefono" placeholder="+54 387 000-0000">
        </div>

        <div class="row2">
          <div class="form-group">
            <label for="curso">Curso</label>
            <select id="curso" name="curso">
<?php foreach ($cursos as $opcion): ?>
              <option value="<?= e($opcion) ?>"><?= e($opcion) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="turno">Turno</label>
            <select id="turno" name="turno">
<?php foreach ($turnos as $opcion): ?>
              <option value="<?= e($opcion) ?>"><?= e($opcion) ?></option>
<?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="observaciones">Observaciones</label>
          <textarea id="observaciones" name="observaciones" rows="3" placeholder="Información adicional..."></textarea>
        </div>

        <div class="flex-between">
          <button class="btn btn-outline" type="button" id="btnCancelarModal">Cancelar</button>
          <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>

<?php
$pageScripts = <<<'JS'
(function () {
  var modal = document.getElementById('alumnoModal');
  var form  = document.getElementById('alumnoForm');
  var title = document.getElementById('modalTitle');

  function abrir() { modal.classList.add('show'); }
  function cerrar() { modal.classList.remove('show'); }

  function setValue(id, value) {
    var el = document.getElementById(id);
    if (el) el.value = value === undefined || value === null ? '' : value;
  }

  document.getElementById('btnNuevoAlumno').addEventListener('click', function () {
    form.reset();
    setValue('alumnoId', 0);
    title.textContent = 'Agregar alumno';
    abrir();
    document.getElementById('nombre').focus();
  });

  document.querySelectorAll('.btn-editar').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var alumno = JSON.parse(btn.dataset.alumno);

      setValue('alumnoId', alumno.id);
      setValue('nombre', alumno.nombre);
      setValue('apellido', alumno.apellido);
      setValue('dni', alumno.dni);
      setValue('fechaNacimiento', alumno.fechaNacimiento);
      setValue('email', alumno.email);
      setValue('telefono', alumno.telefono);
      setValue('curso', alumno.curso);
      setValue('turno', alumno.turno);
      setValue('observaciones', alumno.observaciones);

      title.textContent = 'Editar alumno';
      abrir();
    });
  });

  document.getElementById('btnCerrarModal').addEventListener('click', cerrar);
  document.getElementById('btnCancelarModal').addEventListener('click', cerrar);

  document.querySelectorAll('.form-eliminar').forEach(function (formEliminar) {
    formEliminar.addEventListener('submit', function (event) {
      var boton = formEliminar.querySelector('button[type="submit"]');
      var nombre = boton ? boton.dataset.nombre : 'este alumno';

      if (!window.confirm('¿Seguro que querés eliminar a ' + nombre + '?')) {
        event.preventDefault();
      }
    });
  });
})();
JS;

include __DIR__ . '/partials/footer.php';
