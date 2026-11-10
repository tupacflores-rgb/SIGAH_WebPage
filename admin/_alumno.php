<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/**
 * Funciones compartidas por crear.php y editar.php (CRUD de alumnos).
 * Toda consulta usa sentencias preparadas (PDO).
 */

const ALUMNO_TURNOS  = ['Mañana', 'Tarde', 'Noche'];
const ALUMNO_ESTADOS = ['activo', 'inactivo'];

/** @return array<int,array<string,mixed>> */
function alumno_cursos(PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT id, nombre FROM cursos ORDER BY nombre');
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Toma los datos de $_POST y los valida.
 *
 * @param  array<int,array<string,mixed>> $cursos
 * @return array{0:array<string,string>,1:array<string,string>} [datos, errores]
 */
function alumno_validar(array $post, array $cursos): array
{
    $d = [
        'apellido'         => trim((string) ($post['apellido'] ?? '')),
        'nombre'           => trim((string) ($post['nombre'] ?? '')),
        'dni'              => trim((string) ($post['dni'] ?? '')),
        'fecha_nacimiento' => trim((string) ($post['fecha_nacimiento'] ?? '')),
        'email'            => trim((string) ($post['email'] ?? '')),
        'telefono'         => trim((string) ($post['telefono'] ?? '')),
        'curso_id'         => trim((string) ($post['curso_id'] ?? '')),
        'turno'            => (string) ($post['turno'] ?? 'Mañana'),
        'estado'           => (string) ($post['estado'] ?? 'activo'),
        'observaciones'    => trim((string) ($post['observaciones'] ?? '')),
    ];
    $err = [];

    if ($d['apellido'] === '') { $err['apellido'] = 'Ingresá el apellido.'; }
    if ($d['nombre'] === '')   { $err['nombre']   = 'Ingresá el nombre.'; }
    if ($d['dni'] === '')      { $err['dni']      = 'Ingresá el DNI.'; }

    if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $err['email'] = 'El correo no es válido.';
    }

    if ($d['fecha_nacimiento'] !== '') {
        $f = DateTime::createFromFormat('Y-m-d', $d['fecha_nacimiento']);
        if (!$f || $f->format('Y-m-d') !== $d['fecha_nacimiento']) {
            $err['fecha_nacimiento'] = 'La fecha no es válida.';
        }
    }

    $idsCurso = array_map(static fn (array $c): string => (string) $c['id'], $cursos);
    if ($d['curso_id'] !== '' && !in_array($d['curso_id'], $idsCurso, true)) {
        $err['curso_id'] = 'Elegí un curso de la lista.';
    }
    if (!in_array($d['turno'], ALUMNO_TURNOS, true))   { $err['turno']  = 'Turno inválido.'; }
    if (!in_array($d['estado'], ALUMNO_ESTADOS, true)) { $err['estado'] = 'Estado inválido.'; }

    return [$d, $err];
}

/** Parámetros nombrados para INSERT/UPDATE a partir de los datos validados. */
function alumno_params(array $d): array
{
    return [
        ':apellido'         => $d['apellido'],
        ':nombre'           => $d['nombre'],
        ':dni'              => $d['dni'],
        ':fecha_nacimiento' => $d['fecha_nacimiento'] !== '' ? $d['fecha_nacimiento'] : null,
        ':email'            => $d['email'] !== '' ? $d['email'] : null,
        ':telefono'         => $d['telefono'] !== '' ? $d['telefono'] : null,
        ':curso_id'         => $d['curso_id'] !== '' ? (int) $d['curso_id'] : null,
        ':turno'            => $d['turno'],
        ':estado'           => $d['estado'],
        ':observaciones'    => $d['observaciones'] !== '' ? $d['observaciones'] : null,
    ];
}

/** Dibuja un input de texto con su mensaje de error. */
function alumno_campo(array $d, array $err, string $name, string $label, string $type = 'text', bool $req = false): void
{
?>
        <div class="form-group">
          <label for="<?= e($name) ?>"><?= e($label) ?></label>
          <input type="<?= e($type) ?>" id="<?= e($name) ?>" name="<?= e($name) ?>"
                 value="<?= e($d[$name] ?? '') ?>"<?= $req ? ' required' : '' ?>>
          <div class="field-error<?= isset($err[$name]) ? ' show' : '' ?>"><?= e($err[$name] ?? '') ?></div>
        </div>
<?php
}

/** Dibuja el formulario compartido (alta y edición). */
function alumno_form(array $d, array $err, array $cursos, string $accionUrl, string $boton): void
{
?>
      <form method="post" action="<?= e($accionUrl) ?>" novalidate>
        <?= csrf_field() ?>
<?php
    alumno_campo($d, $err, 'apellido', 'Apellido/s', 'text', true);
    alumno_campo($d, $err, 'nombre', 'Nombre/s', 'text', true);
    alumno_campo($d, $err, 'dni', 'DNI', 'text', true);
    alumno_campo($d, $err, 'fecha_nacimiento', 'Fecha de nacimiento', 'date');
    alumno_campo($d, $err, 'email', 'Correo electrónico', 'email');
    alumno_campo($d, $err, 'telefono', 'Teléfono', 'tel');
?>

        <div class="form-group">
          <label for="curso_id">Curso</label>
          <select id="curso_id" name="curso_id">
            <option value="">— Sin curso —</option>
<?php foreach ($cursos as $c): ?>
            <option value="<?= e($c['id']) ?>"<?= (string) $c['id'] === (string) ($d['curso_id'] ?? '') ? ' selected' : '' ?>><?= e($c['nombre']) ?></option>
<?php endforeach; ?>
          </select>
          <div class="field-error<?= isset($err['curso_id']) ? ' show' : '' ?>"><?= e($err['curso_id'] ?? '') ?></div>
        </div>

        <div class="form-group">
          <label for="turno">Turno</label>
          <select id="turno" name="turno">
<?php foreach (ALUMNO_TURNOS as $t): ?>
            <option value="<?= e($t) ?>"<?= $t === ($d['turno'] ?? '') ? ' selected' : '' ?>><?= e($t) ?></option>
<?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="estado">Estado</label>
          <select id="estado" name="estado">
<?php foreach (ALUMNO_ESTADOS as $s): ?>
            <option value="<?= e($s) ?>"<?= $s === ($d['estado'] ?? '') ? ' selected' : '' ?>><?= e(ucfirst($s)) ?></option>
<?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="observaciones">Observaciones</label>
          <textarea id="observaciones" name="observaciones" rows="3"><?= e($d['observaciones'] ?? '') ?></textarea>
        </div>

        <button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> <?= e($boton) ?></button>
        <a class="btn btn-outline" href="<?= e(url('admin/index.php')) ?>">Cancelar</a>
      </form>
<?php
}
