USE mi_proyecto_web;

-- 1) Insertar datos maestros/secundarios
INSERT INTO roles (nombre, descripcion) VALUES
('administrador', 'Usuario con permisos completos del sistema'),
('docente', 'Docente responsable de cursos y asistencias');

INSERT INTO cursos (nombre, descripcion) VALUES
('Matemática', 'Curso de álgebra y aritmética básica'),
('Historia', 'Curso de historia contemporánea y social');

-- 2) Insertar usuarios
INSERT INTO usuarios (nombre, apellido, email, password, rol_id, telefono, estado) VALUES
('Rolando', 'Flores', 'rolando@sigah.edu.ar', 'hash_rolando', 1, '3815550011', 'activo'),
('Lucía', 'Lopez', 'lucia.lopez@sigah.edu.ar', 'hash_lucia', 2, '3815550022', 'activo');

-- 3) Insertar alumnos
INSERT INTO alumnos (usuario_id, nombre, apellido, dni, email, fecha_nacimiento) VALUES
(1, 'María', 'García', '30123456', 'maria.garcia@gmail.com', '2009-05-19'),
(2, 'Pedro', 'Martinez', '30234567', 'pedro.martinez@gmail.com', '2008-11-07');

-- 4) Relación muchos a muchos: alumno_curso
INSERT INTO alumno_curso (alumno_id, curso_id, turno, estado) VALUES
(1, 1, 'mañana', 'activo'),
(2, 2, 'tarde', 'activo');

-- 5) Read: consulta con WHERE y ORDER BY
SELECT a.id, a.nombre, a.apellido, a.dni
FROM alumnos a
WHERE a.estado IS NOT NULL
ORDER BY a.apellido ASC, a.nombre ASC;

-- 6) Read con INNER JOIN
SELECT
    ac.id,
    CONCAT(al.nombre, ' ', al.apellido) AS alumno,
    c.nombre AS curso,
    ac.turno,
    ac.estado
FROM alumno_curso ac
INNER JOIN alumnos al ON ac.alumno_id = al.id
INNER JOIN cursos c ON ac.curso_id = c.id
WHERE ac.estado = 'activo'
ORDER BY c.nombre ASC;

-- 7) Update de un registro específico con WHERE
UPDATE alumno_curso
SET estado = 'finalizado'
WHERE id = 1;

-- 8) Insertar asistencia para validar la tabla de negocio
INSERT INTO asistencias (alumno_id, curso_id, fecha, estado, observacion) VALUES
(1, 1, '2026-10-06', 'presente', 'Asistencia normal'),
(2, 2, '2026-10-06', 'ausente', 'Sin justificación');

-- 9) Delete con WHERE
DELETE FROM asistencias
WHERE alumno_id = 2 AND fecha = '2026-10-06';

-- 10) Consulta final de verificación
SELECT
    u.id,
    u.email,
    r.nombre AS rol,
    CONCAT(a.nombre, ' ', a.apellido) AS alumno,
    c.nombre AS curso
FROM usuarios u
INNER JOIN roles r ON u.rol_id = r.id
LEFT JOIN alumnos a ON a.usuario_id = u.id
LEFT JOIN alumno_curso ac ON ac.alumno_id = a.id
LEFT JOIN cursos c ON c.id = ac.curso_id
ORDER BY u.id;
