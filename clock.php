<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require_login();

$institucion = read_json('institution.json');
$cursos      = array_map(static fn (array $c): string => (string) ($c['nombre'] ?? ''), $institucion['cursos'] ?? []);
$cursos      = array_values(array_filter($cursos)) ?: ['1° A'];

$turnos = [
    'Mañana' => 'Mañana (07:30–12:00)',
    'Tarde'  => 'Tarde (13:00–17:30)',
    'Noche'  => 'Noche (18:00–22:00)',
];

$curso = (string) ($_GET['curso'] ?? $cursos[0]);
$turno = (string) ($_GET['turno'] ?? 'Mañana');

if (!in_array($curso, $cursos, true))      { $curso = $cursos[0]; }
if (!array_key_exists($turno, $turnos))    { $turno = 'Mañana'; }

/* ------------------------------------------------------------------
 |  Grilla semanal. Cada celda: [materia, categoría]
 |  Categorías: a = exactas y naturales · b = lenguas · c = sociales · d = talleres
 | ------------------------------------------------------------------ */
$horarios = [
    ['07:30–08:20', [['Matemáticas', 'a'], ['Lengua', 'b'],      ['Historia', 'c'],      ['Matemáticas', 'a'], ['Ed. Física', 'd']]],
    ['08:20–09:10', [['Lengua', 'b'],      ['Matemáticas', 'a'], ['Lengua', 'b'],        ['Geografía', 'c'],   ['Física', 'a']]],
    ['09:10–10:00', [['Historia', 'c'],    ['Ed. Física', 'd'],  ['Matemáticas', 'a'],   ['Lengua', 'b'],      ['Biología', 'c']]],
    ['10:00–10:20', 'recreo'],
    ['10:20–11:10', [['Inglés', 'b'],      ['Biología', 'c'],    ['Ed. Artística', 'd'], ['Física', 'a'],      ['Inglés', 'b']]],
    ['11:10–12:00', [['Física', 'a'],      ['Inglés', 'b'],      ['Química', 'c'],       ['Ed. Artística', 'd'], ['Historia', 'c']]],
];

$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

$docentes = [
    ['https://i.pravatar.cc/32?img=12', 'Prof. López',   'Matemáticas / Física'],
    ['https://i.pravatar.cc/32?img=47', 'Prof. Torres',  'Lengua / Inglés'],
    ['https://i.pravatar.cc/32?img=33', 'Prof. Ramírez', 'Historia / Geografía'],
    ['https://i.pravatar.cc/32?img=22', 'Prof. Díaz',    'Ed. Física / Artística'],
];

$leyenda = [
    'a' => ['#b2ebf2', 'Exactas y Nat.'],
    'b' => ['#c8e6c9', 'Lenguas'],
    'c' => ['#fff9c4', 'Sociales'],
    'd' => ['#ffcdd2', 'Talleres'],
];

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Horario';
$pageIcon    = 'fa-clock';
$pageHeading = 'Horario de Clases';
$pageDesc    = 'Visualizá y gestioná los horarios por curso y turno.';
$breadcrumb  = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard', 'url' => 'home.php'],
    ['label' => 'Horario'],
];

$pageStyles = <<<'CSS'
    .sch-table th { background: var(--dark); color: white; text-align: center; font-size: 13px; }
    .sch-table td { text-align: center; height: 52px; font-size: 12px; font-weight: 600; }
    td.hora { background: var(--bg); color: var(--gray); font-size: 11px; white-space: nowrap; padding: 0 8px; }
    td.mat-a { background: #b2ebf2; color: #006064; }
    td.mat-b { background: #c8e6c9; color: #1b5e20; }
    td.mat-c { background: #fff9c4; color: #f57f17; }
    td.mat-d { background: #ffcdd2; color: #b71c1c; }
    td.libre { background: #fafafa; color: #999; font-size: 11px; }
    .legend { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 14px; font-size: 12px; }
    .legend div { display: flex; align-items: center; gap: 6px; }
    .leg-dot { width: 14px; height: 14px; border-radius: 3px; }
    .clock-banner { width: 100%; height: 100px; object-fit: cover; border-radius: var(--radius); margin-bottom: 20px; opacity: 0.8; display: block; }
    .teacher-list { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 16px; }
    .teacher-item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--gray); }
    .teacher-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border); }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <main class="wide">
    <img class="clock-banner"
         src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1000&q=75"
         alt="Horario de clases – <?= e(env('SCHOOL_SHORT', '')) ?>">

    <div class="card">
      <h2><i class="fas fa-sliders-h"></i> Filtros</h2>
      <p>Seleccioná el curso y turno para ver su horario semanal.</p><br>

      <form method="get" action="<?= e(url('clock.php')) ?>">
        <div class="row2">
          <div class="form-group">
            <label for="cursoHorario">Curso</label>
            <select id="cursoHorario" name="curso">
<?php foreach ($cursos as $opcion): ?>
              <option value="<?= e($opcion) ?>"<?= $opcion === $curso ? ' selected' : '' ?>><?= e($opcion) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="turnoHorario">Turno</label>
            <select id="turnoHorario" name="turno">
<?php foreach ($turnos as $valor => $texto): ?>
              <option value="<?= e($valor) ?>"<?= $valor === $turno ? ' selected' : '' ?>><?= e($texto) ?></option>
<?php endforeach; ?>
            </select>
          </div>
        </div>
        <button class="btn btn-primary" type="submit"><i class="fas fa-eye"></i> Ver horario</button>
      </form>
    </div>

    <div class="card">
      <h2><i class="fas fa-calendar-week"></i> Grilla semanal — <?= e($curso) ?> <?= e($turno) ?></h2>
      <p>Horario correspondiente al ciclo lectivo <?= e($institucion['cicloLectivo']['anio'] ?? date('Y')) ?>.</p><br>

      <div class="table-wrap">
        <table class="sch-table">
          <thead>
            <tr>
              <th>Hora</th>
<?php foreach ($dias as $dia): ?>
              <th><?= e($dia) ?></th>
<?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
<?php foreach ($horarios as [$hora, $celdas]): ?>
            <tr>
              <td class="hora"><?= e($hora) ?></td>
<?php if ($celdas === 'recreo'): ?>
              <td class="libre" colspan="<?= count($dias) ?>">— Recreo —</td>
<?php else: ?>
<?php foreach ($celdas as [$materia, $cat]): ?>
              <td class="mat-<?= e($cat) ?>"><?= e($materia) ?></td>
<?php endforeach; ?>
<?php endif; ?>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="legend">
<?php foreach ($leyenda as [$color, $texto]): ?>
        <div><div class="leg-dot" style="background:<?= e($color) ?>"></div><?= e($texto) ?></div>
<?php endforeach; ?>
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin:18px 0 14px">

      <p class="text-muted mb-8"><i class="fas fa-chalkboard-teacher"></i> Docentes a cargo — <?= e($curso) ?></p>

      <div class="teacher-list">
<?php foreach ($docentes as [$foto, $nombre, $materias]): ?>
        <div class="teacher-item">
          <img class="teacher-avatar" src="<?= e($foto) ?>" alt="Avatar de <?= e($nombre) ?>">
          <span><?= e($nombre) ?> — <?= e($materias) ?></span>
        </div>
<?php endforeach; ?>
      </div>
    </div>
  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
