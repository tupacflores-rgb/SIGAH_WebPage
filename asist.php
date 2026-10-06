<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require_login();

/* ------------------------------------------------------------------
 |  Datos base
 | ------------------------------------------------------------------ */
$institucion = read_json('institution.json');
$cursos      = array_map(static fn (array $c): string => (string) ($c['nombre'] ?? ''), $institucion['cursos'] ?? []);
$cursos      = array_values(array_filter($cursos)) ?: ['1° A'];

$turnos   = ['Mañana', 'Tarde', 'Noche'];
$materias = ['Matemáticas', 'Lengua', 'Historia', 'Geografía', 'Inglés', 'Física', 'Biología', 'Química', 'Ed. Física'];

$estadosValidos = ['presente', 'ausente', 'tardanza'];
$etiquetas      = [
    'presente' => ['Presente', 'fa-check-circle'],
    'ausente'  => ['Ausente',  'fa-times-circle'],
    'tardanza' => ['Tardanza', 'fa-clock'],
];

/* ------------------------------------------------------------------
 |  Parámetros del registro (se conservan en la URL)
 | ------------------------------------------------------------------ */
$curso   = (string) ($_REQUEST['curso']   ?? $cursos[0]);
$fecha   = (string) ($_REQUEST['fecha']   ?? date('Y-m-d'));
$materia = (string) ($_REQUEST['materia'] ?? $materias[0]);
$turno   = (string) ($_REQUEST['turno']   ?? $turnos[0]);

if (!in_array($curso, $cursos, true))      { $curso   = $cursos[0]; }
if (!in_array($materia, $materias, true))  { $materia = $materias[0]; }
if (!in_array($turno, $turnos, true))      { $turno   = $turnos[0]; }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) { $fecha = date('Y-m-d'); }

$claveRegistro = $fecha . '|' . $curso . '|' . $materia;

/* ------------------------------------------------------------------
 |  Alumnos del curso seleccionado
 | ------------------------------------------------------------------ */
$todos   = read_json('students.json')['estudiantes'] ?? [];
$alumnos = array_values(array_filter(
    $todos,
    static fn (array $a): bool => ($a['curso'] ?? '') === $curso
));

usort(
    $alumnos,
    static fn (array $a, array $b): int => strcmp((string) ($a['apellido'] ?? ''), (string) ($b['apellido'] ?? ''))
);

/* ------------------------------------------------------------------
 |  Guardar asistencia
 | ------------------------------------------------------------------ */
$guardado = flash_get('success');
$error    = flash_get('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    csrf_check();

    $estados = (array) ($_POST['estado'] ?? []);
    $registro = [];

    foreach ($alumnos as $alumno) {
        $id     = (string) $alumno['id'];
        $estado = (string) ($estados[$id] ?? 'presente');

        $registro[$id] = in_array($estado, $estadosValidos, true) ? $estado : 'presente';
    }

    $asistencias = read_json('attendance.json');
    $asistencias[$claveRegistro] = [
        'fecha'       => $fecha,
        'curso'       => $curso,
        'materia'     => $materia,
        'turno'       => $turno,
        'registradoPor' => auth_name(),
        'actualizado' => date('c'),
        'estados'     => $registro,
    ];

    if (write_json('attendance.json', $asistencias)) {
        flash_set('success', 'Asistencia guardada para ' . $curso . ' — ' . $materia . ' (' . $fecha . ').');
    } else {
        flash_set('error', 'No se pudo guardar. Verificá que la carpeta data/ tenga permisos de escritura.');
    }

    redirect('asist.php?' . http_build_query(compact('curso', 'fecha', 'materia', 'turno')));
}

/* ------------------------------------------------------------------
 |  Estado actual (lo guardado o "presente" por defecto)
 | ------------------------------------------------------------------ */
$guardados = read_json('attendance.json')[$claveRegistro]['estados'] ?? [];

$estadoDe = static function (array $alumno) use ($guardados): string {
    return (string) ($guardados[(string) $alumno['id']] ?? 'presente');
};

$resumen = ['presente' => 0, 'ausente' => 0, 'tardanza' => 0];
foreach ($alumnos as $alumno) {
    $resumen[$estadoDe($alumno)]++;
}

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Asistencia';
$pageIcon    = 'fa-clipboard-list';
$pageHeading = 'Registro de Asistencia';
$pageDesc    = 'Tomá asistencia por curso, fecha y materia.';
$breadcrumb  = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard', 'url' => 'home.php'],
    ['label' => 'Asistencia'],
];

$pageStyles = <<<'CSS'
    .est-btn { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; border: none; cursor: pointer; white-space: nowrap; }
    .est-btn.presente { background: #e8f5e9; color: #2e7d32; }
    .est-btn.ausente  { background: #ffebee; color: #c62828; }
    .est-btn.tardanza { background: #fff3e0; color: #e65100; }
    .alumno-avatar {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 8px;
      border: 1.5px solid var(--border);
    }
    .alumno-cell { display: flex; align-items: center; }
    .empty-state { text-align: center; padding: 26px 12px; color: var(--light-gray); }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <main class="wide">
    <img class="asist-banner"
         src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1000&q=75"
         alt="Aula escolar – registro de asistencia">

<?php if ($guardado): ?>
    <div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i> <?= e($guardado) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error" role="alert"><i class="fas fa-triangle-exclamation"></i> <?= e($error) ?></div>
<?php endif; ?>

    <div class="card">
      <h2><i class="fas fa-sliders-h"></i> Parámetros del registro</h2>
      <p>Seleccioná el curso, la fecha y la materia antes de registrar la asistencia.</p><br>

      <form method="get" action="<?= e(url('asist.php')) ?>">
        <div class="row2">
          <div class="form-group">
            <label for="cursoAsist">Curso</label>
            <select id="cursoAsist" name="curso">
<?php foreach ($cursos as $opcion): ?>
              <option value="<?= e($opcion) ?>"<?= $opcion === $curso ? ' selected' : '' ?>><?= e($opcion) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="fechaAsist">Fecha</label>
            <input type="date" id="fechaAsist" name="fecha" value="<?= e($fecha) ?>">
          </div>
        </div>

        <div class="row2">
          <div class="form-group">
            <label for="materiaAsist">Materia</label>
            <select id="materiaAsist" name="materia">
<?php foreach ($materias as $opcion): ?>
              <option value="<?= e($opcion) ?>"<?= $opcion === $materia ? ' selected' : '' ?>><?= e($opcion) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="turnoAsist">Turno</label>
            <select id="turnoAsist" name="turno">
<?php foreach ($turnos as $opcion): ?>
              <option value="<?= e($opcion) ?>"<?= $opcion === $turno ? ' selected' : '' ?>><?= e($opcion) ?></option>
<?php endforeach; ?>
            </select>
          </div>
        </div>

        <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Aplicar</button>
      </form>
    </div>

    <div class="card">
      <h2><i class="fas fa-chart-bar"></i> Estadísticas del día</h2>
      <div class="grid-3">
        <div style="background:#e8f5e9;border-radius:8px;padding:14px;text-align:center">
          <div style="font-size:24px;font-weight:700;color:#2e7d32"><?= $resumen['presente'] ?></div>
          <div style="font-size:12px;color:#666"><i class="fas fa-check-circle"></i> Presentes</div>
        </div>
        <div style="background:#ffebee;border-radius:8px;padding:14px;text-align:center">
          <div style="font-size:24px;font-weight:700;color:#c62828"><?= $resumen['ausente'] ?></div>
          <div style="font-size:12px;color:#666"><i class="fas fa-times-circle"></i> Ausentes</div>
        </div>
        <div style="background:#fff3e0;border-radius:8px;padding:14px;text-align:center">
          <div style="font-size:24px;font-weight:700;color:#e65100"><?= $resumen['tardanza'] ?></div>
          <div style="font-size:12px;color:#666"><i class="fas fa-clock"></i> Tardanzas</div>
        </div>
      </div>
    </div>

    <div class="card">
      <h2><i class="fas fa-users"></i> Lista de alumnos — <?= e($curso) ?></h2>

<?php if (!$alumnos): ?>
      <div class="empty-state">
        <i class="fas fa-user-slash" style="font-size:28px"></i>
        <p>No hay alumnos cargados en <?= e($curso) ?>.</p>
        <p><a href="<?= e(url('registry-al.php')) ?>">Agregalos desde Registro de Alumnos</a>.</p>
      </div>
<?php else: ?>
      <p>Hacé clic en el estado de cada alumno para cambiarlo y después guardá.</p><br>

      <form method="post" action="<?= e(url('asist.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="curso"   value="<?= e($curso) ?>">
        <input type="hidden" name="fecha"   value="<?= e($fecha) ?>">
        <input type="hidden" name="materia" value="<?= e($materia) ?>">
        <input type="hidden" name="turno"   value="<?= e($turno) ?>">

        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Alumno</th><th>DNI</th><th>Estado</th></tr>
            </thead>
            <tbody>
<?php foreach ($alumnos as $i => $alumno): ?>
<?php $estado = $estadoDe($alumno); ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td>
                  <div class="alumno-cell">
                    <img class="alumno-avatar" src="<?= e($alumno['foto'] ?? '') ?>"
                         alt="Avatar de <?= e($alumno['apellido'] . ', ' . $alumno['nombre']) ?>">
                    <?= e($alumno['apellido'] . ', ' . $alumno['nombre']) ?>
                  </div>
                </td>
                <td><?= e($alumno['dni'] ?? '') ?></td>
                <td>
                  <input type="hidden" name="estado[<?= (int) $alumno['id'] ?>]"
                         id="estado-<?= (int) $alumno['id'] ?>" value="<?= e($estado) ?>">
                  <button type="button" class="est-btn <?= e($estado) ?>"
                          data-target="estado-<?= (int) $alumno['id'] ?>"
                          aria-label="Cambiar estado de <?= e($alumno['apellido']) ?>">
                    <i class="fas <?= e($etiquetas[$estado][1]) ?>"></i> <?= e($etiquetas[$estado][0]) ?>
                  </button>
                </td>
              </tr>
<?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <br>
        <div class="flex-between">
          <a class="btn btn-outline" href="<?= e(url('home.php')) ?>"><i class="fas fa-times"></i> Cancelar</a>
          <button class="btn btn-success" type="submit" name="guardar" value="1">
            <i class="fas fa-save"></i> Guardar asistencia
          </button>
        </div>
      </form>
<?php endif; ?>
    </div>
  </main>

<?php
$pageScripts = <<<'JS'
(function () {
  var estados = ['presente', 'ausente', 'tardanza'];
  var textos  = { presente: 'Presente', ausente: 'Ausente', tardanza: 'Tardanza' };
  var iconos  = { presente: 'fa-check-circle', ausente: 'fa-times-circle', tardanza: 'fa-clock' };

  document.querySelectorAll('.est-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.dataset.target);
      if (!input) return;

      var siguiente = estados[(estados.indexOf(input.value) + 1) % estados.length];

      input.value = siguiente;
      btn.className = 'est-btn ' + siguiente;
      btn.innerHTML = '<i class="fas ' + iconos[siguiente] + '"></i> ' + textos[siguiente];
    });
  });
})();
JS;

include __DIR__ . '/partials/footer.php';
