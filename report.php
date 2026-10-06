<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require_login();

/**
 * NOTA: el archivo report.html original era una copia de add-user.html
 * (el contenido de reportes se había perdido). Esta página reconstruye el
 * módulo de Reportes que describen el menú, el FAQ y CredentialTest.txt.
 */

$institucion = read_json('institution.json');
$cursos      = array_map(static fn (array $c): string => (string) ($c['nombre'] ?? ''), $institucion['cursos'] ?? []);
$cursos      = array_values(array_filter($cursos));

$umbralRiesgo = (int) env('ATTENDANCE_RISK_THRESHOLD', 85);

$cursoFiltro = (string) ($_GET['curso'] ?? '');
if ($cursoFiltro !== '' && !in_array($cursoFiltro, $cursos, true)) {
    $cursoFiltro = '';
}

$estudiantes = read_json('students.json')['estudiantes'] ?? [];

$filtrados = $cursoFiltro === ''
    ? $estudiantes
    : array_values(array_filter($estudiantes, static fn (array $a): bool => ($a['curso'] ?? '') === $cursoFiltro));

usort(
    $filtrados,
    static fn (array $a, array $b): int => ((float) ($b['asistencia'] ?? 0)) <=> ((float) ($a['asistencia'] ?? 0))
);

/* ------------------------------------------------------------------
 |  Métricas
 | ------------------------------------------------------------------ */
$total      = count($filtrados);
$asistArray = array_map(static fn (array $a): float => (float) ($a['asistencia'] ?? 0), $filtrados);
$promArray  = array_map(static fn (array $a): float => (float) ($a['promedio'] ?? 0), $filtrados);

$asistenciaMedia = $total ? round(array_sum($asistArray) / $total, 1) : 0.0;
$promedioMedio   = $total ? round(array_sum($promArray) / $total, 1) : 0.0;
$totalAusencias  = array_sum(array_map(static fn (array $a): int => (int) ($a['ausencias'] ?? 0), $filtrados));
$totalTardanzas  = array_sum(array_map(static fn (array $a): int => (int) ($a['tardanzas'] ?? 0), $filtrados));

$enRiesgo = array_values(array_filter(
    $filtrados,
    static fn (array $a): bool => (float) ($a['asistencia'] ?? 100) < $umbralRiesgo
));

/* Asistencia media por curso (para el gráfico) */
$porCurso = [];
foreach ($estudiantes as $alumno) {
    $curso = (string) ($alumno['curso'] ?? 'Sin curso');
    $porCurso[$curso][] = (float) ($alumno['asistencia'] ?? 0);
}
ksort($porCurso);

$grafico = [];
foreach ($porCurso as $curso => $valores) {
    $grafico[$curso] = [
        'promedio' => round(array_sum($valores) / count($valores), 1),
        'alumnos'  => count($valores),
    ];
}

/* ------------------------------------------------------------------
 |  Variables LOCALES de esta página
 | ------------------------------------------------------------------ */
$titulo_pagina = 'Reportes';
$pageIcon    = 'fa-chart-bar';
$pageHeading = 'Reportes de Asistencia';
$pageDesc    = 'Estadísticas calculadas a partir de los datos cargados en el sistema.';
$breadcrumb  = [
    ['label' => env('APP_NAME', 'SIGAH'), 'url' => 'index.php'],
    ['label' => 'Dashboard', 'url' => 'home.php'],
    ['label' => 'Reportes'],
];

$pageStyles = <<<'CSS'
    .kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    @media (max-width: 700px) { .kpi-row { grid-template-columns: 1fr 1fr; } }
    .kpi { background: var(--surface); border-radius: var(--radius); box-shadow: var(--shadow); padding: 16px 14px; text-align: center; }
    .kpi .n { font-size: 24px; font-weight: 700; line-height: 1.2; }
    .kpi .l { font-size: 11px; color: var(--light-gray); margin-top: 4px; }

    .chart { display: flex; flex-direction: column; gap: 12px; margin-top: 6px; }
    .chart-row { display: grid; grid-template-columns: 70px 1fr 56px; align-items: center; gap: 10px; }
    .chart-label { font-size: 12px; font-weight: 600; color: var(--dark); }
    .chart-track { background: var(--border); border-radius: 20px; height: 14px; overflow: hidden; }
    .chart-fill { height: 100%; border-radius: 20px; background: var(--primary); }
    .chart-fill.low { background: var(--warning); }
    .chart-value { font-size: 12px; font-weight: 700; text-align: right; color: var(--gray); }

    .riesgo-item { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--border); }
    .riesgo-item:last-child { border-bottom: none; }
    .riesgo-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid var(--warning); }

    .empty-state { text-align: center; padding: 26px 12px; color: var(--light-gray); }

    @media print {
      .site-header, footer, .page-header, .no-print { display: none !important; }
      body { background: white; }
      .card { box-shadow: none; border: 1px solid #ddd; }
    }
CSS;

include __DIR__ . '/partials/header.php';
?>

  <main class="wide">
    <div class="card no-print">
      <h2><i class="fas fa-sliders-h"></i> Filtros del reporte</h2>
      <form method="get" action="<?= e(url('report.php')) ?>">
        <div class="row2">
          <div class="form-group">
            <label for="cursoReporte">Curso</label>
            <select id="cursoReporte" name="curso">
              <option value="">-- Todos los cursos --</option>
<?php foreach ($cursos as $opcion): ?>
              <option value="<?= e($opcion) ?>"<?= $opcion === $cursoFiltro ? ' selected' : '' ?>><?= e($opcion) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="cicloReporte">Ciclo lectivo</label>
            <input type="text" id="cicloReporte" value="<?= e($institucion['cicloLectivo']['anio'] ?? date('Y')) ?>" disabled>
          </div>
        </div>
        <div class="flex-between">
          <button class="btn btn-outline" type="button" onclick="window.print()">
            <i class="fas fa-print"></i> Exportar / Imprimir PDF
          </button>
          <button class="btn btn-primary" type="submit"><i class="fas fa-chart-line"></i> Generar reporte</button>
        </div>
      </form>
    </div>

    <h2 style="font-size:15px;color:var(--gray);margin-bottom:12px;border:none;padding:0">
      <i class="fas fa-gauge-high"></i> Resumen
      <?= $cursoFiltro !== '' ? '— ' . e($cursoFiltro) : '— todos los cursos' ?>
    </h2>

    <div class="kpi-row mb-16">
      <div class="kpi">
        <div class="n" style="color:var(--primary)"><?= $total ?></div>
        <div class="l"><i class="fas fa-users"></i> Alumnos</div>
      </div>
      <div class="kpi">
        <div class="n" style="color:<?= $asistenciaMedia >= $umbralRiesgo ? 'var(--success)' : 'var(--warning)' ?>">
          <?= e(number_format($asistenciaMedia, 1, ',', '.')) ?>%
        </div>
        <div class="l"><i class="fas fa-check-circle"></i> Asistencia media</div>
      </div>
      <div class="kpi">
        <div class="n" style="color:var(--danger)"><?= $totalAusencias ?></div>
        <div class="l"><i class="fas fa-times-circle"></i> Ausencias</div>
      </div>
      <div class="kpi">
        <div class="n" style="color:var(--warning)"><?= $totalTardanzas ?></div>
        <div class="l"><i class="fas fa-clock"></i> Tardanzas</div>
      </div>
    </div>

    <div class="card">
      <h2><i class="fas fa-chart-simple"></i> Asistencia media por curso</h2>
<?php if (!$grafico): ?>
      <div class="empty-state"><p>Todavía no hay alumnos cargados.</p></div>
<?php else: ?>
      <div class="chart">
<?php foreach ($grafico as $curso => $datos): ?>
        <div class="chart-row">
          <span class="chart-label"><?= e($curso) ?></span>
          <div class="chart-track"
               role="img"
               aria-label="<?= e($curso) ?>: <?= e($datos['promedio']) ?> por ciento de asistencia">
            <div class="chart-fill<?= $datos['promedio'] < $umbralRiesgo ? ' low' : '' ?>"
                 style="width: <?= (float) $datos['promedio'] ?>%"></div>
          </div>
          <span class="chart-value"><?= e(number_format((float) $datos['promedio'], 1, ',', '.')) ?>%</span>
        </div>
<?php endforeach; ?>
      </div>
      <p class="text-muted mt-16">
        Las barras en naranja están por debajo del umbral de <?= $umbralRiesgo ?>%
        (se configura con <code>ATTENDANCE_RISK_THRESHOLD</code> en el .env).
      </p>
<?php endif; ?>
    </div>

    <div class="card">
      <h2><i class="fas fa-triangle-exclamation"></i> Alumnos en riesgo (<?= count($enRiesgo) ?>)</h2>
<?php if (!$enRiesgo): ?>
      <p>Ningún alumno está por debajo del <?= $umbralRiesgo ?>% de asistencia. 👏</p>
<?php else: ?>
<?php foreach ($enRiesgo as $alumno): ?>
      <div class="riesgo-item">
        <img class="riesgo-avatar" src="<?= e($alumno['foto'] ?? '') ?>"
             alt="Avatar de <?= e($alumno['apellido'] . ', ' . $alumno['nombre']) ?>">
        <div style="flex:1">
          <div style="font-weight:700;color:var(--dark)"><?= e($alumno['apellido'] . ', ' . $alumno['nombre']) ?></div>
          <div class="text-muted">
            <?= e($alumno['curso'] ?? '') ?> ·
            <?= (int) ($alumno['ausencias'] ?? 0) ?> ausencias ·
            <?= (int) ($alumno['tardanzas'] ?? 0) ?> tardanzas
          </div>
        </div>
        <span class="badge badge-warning"><?= e($alumno['asistencia'] ?? 0) ?>%</span>
      </div>
<?php endforeach; ?>
<?php endif; ?>
    </div>

    <div class="card">
      <h2><i class="fas fa-table"></i> Detalle por alumno</h2>
<?php if (!$filtrados): ?>
      <div class="empty-state"><p>No hay alumnos para el filtro seleccionado.</p></div>
<?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Alumno</th>
              <th>Curso</th>
              <th>Asistencia</th>
              <th>Aus.</th>
              <th>Tard.</th>
              <th>Promedio</th>
            </tr>
          </thead>
          <tbody>
<?php foreach ($filtrados as $alumno): ?>
            <tr>
              <td><?= e($alumno['apellido'] . ', ' . $alumno['nombre']) ?></td>
              <td><?= e($alumno['curso'] ?? '') ?></td>
              <td style="color:<?= ((float) ($alumno['asistencia'] ?? 0)) >= $umbralRiesgo ? 'var(--success)' : 'var(--warning)' ?>;font-weight:700">
                <?= e($alumno['asistencia'] ?? 0) ?>%
              </td>
              <td><?= (int) ($alumno['ausencias'] ?? 0) ?></td>
              <td><?= (int) ($alumno['tardanzas'] ?? 0) ?></td>
              <td><?= e(number_format((float) ($alumno['promedio'] ?? 0), 1, ',', '.')) ?></td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <p class="text-muted mt-16">
        Promedio general del grupo: <strong><?= e(number_format($promedioMedio, 1, ',', '.')) ?></strong> ·
        Reporte generado el <?= e(fecha_larga()) ?> por <?= e(auth_name()) ?>.
      </p>
<?php endif; ?>
    </div>
  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
