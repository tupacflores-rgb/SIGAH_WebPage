<?php
declare(strict_types=1);

if (!defined('SIGAH')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/**
 * Capa de datos: alumnos, cursos y asistencia.
 *
 * Con DB_ENABLED=true leen y escriben en MySQL (consultas preparadas).
 * Con DB_ENABLED=false usan los archivos JSON de data/.
 * Devuelven siempre la misma estructura, así las páginas no cambian.
 */

/* ------------------------------------------------------------------
 |  Cursos
 | ------------------------------------------------------------------ */

/**
 * @return string[] Nombres de curso ("1° A", "1° B", ...)
 */
function courses_names(): array
{
    $pdo = db();

    if ($pdo !== null) {
        $stmt = $pdo->prepare('SELECT nombre FROM cursos ORDER BY nombre');
        $stmt->execute();
        $names = array_map('strval', array_column($stmt->fetchAll(), 'nombre'));

        return $names ?: ['1° A'];
    }

    $inst  = read_json('institution.json');
    $names = array_map(static fn (array $c): string => (string) ($c['nombre'] ?? ''), $inst['cursos'] ?? []);

    return array_values(array_filter($names)) ?: ['1° A'];
}

/* ------------------------------------------------------------------
 |  Alumnos
 | ------------------------------------------------------------------ */

/**
 * Alumnos con la misma forma que data/students.json.
 * En MySQL, asistencia / ausencias / tardanzas se calculan desde la tabla asistencias.
 *
 * @return array<int,array<string,mixed>>
 */
function students_all(): array
{
    $pdo = db();

    if ($pdo === null) {
        return read_json('students.json')['estudiantes'] ?? [];
    }

    $stmt = $pdo->prepare(
        "SELECT a.id, a.apellido, a.nombre, a.dni, a.fecha_nacimiento, a.email, a.telefono,
                c.nombre AS curso, a.turno, a.foto, a.estado, a.observaciones,
                (SELECT COUNT(*) FROM asistencias s WHERE s.alumno_id = a.id) AS total,
                (SELECT COUNT(*) FROM asistencias s WHERE s.alumno_id = a.id AND s.estado = 'ausente')  AS ausencias,
                (SELECT COUNT(*) FROM asistencias s WHERE s.alumno_id = a.id AND s.estado = 'tardanza') AS tardanzas
           FROM alumnos a
           LEFT JOIN cursos c ON c.id = a.curso_id
          ORDER BY a.apellido, a.nombre"
    );
    $stmt->execute();

    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $total = (int) $r['total'];
        $aus   = (int) $r['ausencias'];

        $out[] = [
            'id'              => (int) $r['id'],
            'apellido'        => (string) $r['apellido'],
            'nombre'          => (string) $r['nombre'],
            'dni'             => (string) $r['dni'],
            'fechaNacimiento' => (string) ($r['fecha_nacimiento'] ?? ''),
            'email'           => (string) ($r['email'] ?? ''),
            'telefono'        => (string) ($r['telefono'] ?? ''),
            'curso'           => (string) ($r['curso'] ?? ''),
            'turno'           => (string) $r['turno'],
            'foto'            => (string) ($r['foto'] ?? ''),
            'estado'          => (string) $r['estado'],
            'observaciones'   => (string) ($r['observaciones'] ?? ''),
            'asistencia'      => $total > 0 ? (int) round(($total - $aus) / $total * 100) : 100,
            'ausencias'       => $aus,
            'tardanzas'       => (int) $r['tardanzas'],
            'promedio'        => 0,
        ];
    }

    return $out;
}

/* ------------------------------------------------------------------
 |  Asistencia
 | ------------------------------------------------------------------ */

/**
 * Clave de un registro: "fecha|curso|materia".
 *
 * @return array{0:string,1:string,2:string}
 */
function attendance_key_parts(string $key): array
{
    $p = explode('|', $key, 3) + ['', '', ''];

    return [$p[0], $p[1], $p[2]];
}

/**
 * Estados guardados para un registro: [alumno_id => estado].
 *
 * @return array<string,string>
 */
function attendance_get(string $key): array
{
    $pdo = db();

    if ($pdo === null) {
        return read_json('attendance.json')[$key]['estados'] ?? [];
    }

    [$fecha, $curso, $materia] = attendance_key_parts($key);

    $stmt = $pdo->prepare(
        'SELECT s.alumno_id, s.estado
           FROM asistencias s
           JOIN alumnos a ON a.id = s.alumno_id
           JOIN cursos  c ON c.id = a.curso_id
          WHERE s.fecha = :fecha AND s.materia = :materia AND c.nombre = :curso'
    );
    $stmt->execute([':fecha' => $fecha, ':materia' => $materia, ':curso' => $curso]);

    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $out[(string) $r['alumno_id']] = (string) $r['estado'];
    }

    return $out;
}

/**
 * Guarda la asistencia de un curso/fecha/materia.
 *
 * @param array<string,string> $estados [alumno_id => estado]
 */
function attendance_save(string $key, string $fecha, string $curso, string $materia, string $turno, string $por, array $estados): bool
{
    $pdo = db();

    if ($pdo === null) {
        $all       = read_json('attendance.json');
        $all[$key] = [
            'fecha'         => $fecha,
            'curso'         => $curso,
            'materia'       => $materia,
            'turno'         => $turno,
            'registradoPor' => $por,
            'actualizado'   => date('c'),
            'estados'       => $estados,
        ];

        return write_json('attendance.json', $all);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO asistencias (alumno_id, fecha, materia, turno, estado, registrado_por)
             VALUES (:alumno, :fecha, :materia, :turno, :estado, :por)
             ON DUPLICATE KEY UPDATE turno = :turno2, estado = :estado2, registrado_por = :por2'
        );

        foreach ($estados as $alumnoId => $estado) {
            $stmt->execute([
                ':alumno'  => (int) $alumnoId,
                ':fecha'   => $fecha,
                ':materia' => $materia,
                ':turno'   => $turno,
                ':estado'  => $estado,
                ':por'     => $por,
                ':turno2'  => $turno,
                ':estado2' => $estado,
                ':por2'    => $por,
            ]);
        }

        $pdo->commit();

        return true;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('SIGAH · attendance_save: ' . $e->getMessage());

        return false;
    }
}

/**
 * Todos los registros, con la forma de data/attendance.json.
 *
 * @return array<string,array<string,mixed>>
 */
function attendance_all(): array
{
    $pdo = db();

    if ($pdo === null) {
        return read_json('attendance.json');
    }

    $stmt = $pdo->prepare(
        'SELECT s.fecha, c.nombre AS curso, s.materia, s.turno, s.registrado_por,
                s.actualizado, s.alumno_id, s.estado
           FROM asistencias s
           JOIN alumnos a ON a.id = s.alumno_id
           LEFT JOIN cursos c ON c.id = a.curso_id
          ORDER BY s.actualizado DESC
          LIMIT 2000'
    );
    $stmt->execute();

    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $key = $r['fecha'] . '|' . $r['curso'] . '|' . $r['materia'];

        if (!isset($out[$key])) {
            $out[$key] = [
                'fecha'         => (string) $r['fecha'],
                'curso'         => (string) $r['curso'],
                'materia'       => (string) $r['materia'],
                'turno'         => (string) $r['turno'],
                'registradoPor' => (string) $r['registrado_por'],
                'actualizado'   => (string) $r['actualizado'],
                'estados'       => [],
            ];
        }

        $out[$key]['estados'][(string) $r['alumno_id']] = (string) $r['estado'];
    }

    return $out;
}
