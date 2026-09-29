-- ==================================================================
--  SIGAH — Esquema para MariaDB / MySQL (el motor que trae XAMPP)
--  ------------------------------------------------------------------
--  Importalo desde phpMyAdmin:  http://localhost/phpmyadmin
--    1. Pestaña "Importar"  →  elegí este archivo  →  Continuar
--    2. En el .env poné:  DB_ENABLED=true
--
--  OJO: la web funciona sin esto (usa los JSON de data/).
--  Este script es el punto de partida para migrar a base de datos.
-- ==================================================================

CREATE DATABASE IF NOT EXISTS `sigah`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `sigah`;

-- ------------------------------------------------------------------
--  Usuarios del sistema
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`         VARCHAR(120)  NOT NULL,
  `email`          VARCHAR(160)  NOT NULL,
  `usuario`        VARCHAR(60)   NOT NULL,
  `contrasena`     VARCHAR(255)  NOT NULL COMMENT 'Hash de password_hash()',
  `rol`            ENUM('admin','docente','alumno') NOT NULL DEFAULT 'docente',
  `estado`         ENUM('activo','inactivo')        NOT NULL DEFAULT 'activo',
  `fecha_creacion` DATE NOT NULL DEFAULT (CURRENT_DATE),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_email`   (`email`),
  UNIQUE KEY `uq_usuarios_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
--  Cursos
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cursos` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`     VARCHAR(20)  NOT NULL,
  `turno`      ENUM('Mañana','Tarde','Noche') NOT NULL DEFAULT 'Mañana',
  `capacidad`  SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  `preceptor`  VARCHAR(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cursos_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
--  Alumnos
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `alumnos` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `apellido`         VARCHAR(120) NOT NULL,
  `nombre`           VARCHAR(120) NOT NULL,
  `dni`              VARCHAR(20)  NOT NULL,
  `fecha_nacimiento` DATE         DEFAULT NULL,
  `email`            VARCHAR(160) DEFAULT NULL,
  `telefono`         VARCHAR(40)  DEFAULT NULL,
  `curso_id`         INT UNSIGNED DEFAULT NULL,
  `turno`            ENUM('Mañana','Tarde','Noche') NOT NULL DEFAULT 'Mañana',
  `foto`             VARCHAR(255) DEFAULT NULL,
  `estado`           ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  `observaciones`    TEXT         DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_alumnos_dni` (`dni`),
  KEY `idx_alumnos_curso` (`curso_id`),
  CONSTRAINT `fk_alumnos_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
--  Asistencia (una fila por alumno / fecha / materia)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `asistencias` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alumno_id`  INT UNSIGNED NOT NULL,
  `fecha`      DATE         NOT NULL,
  `materia`    VARCHAR(60)  NOT NULL,
  `turno`      ENUM('Mañana','Tarde','Noche') NOT NULL DEFAULT 'Mañana',
  `estado`     ENUM('presente','ausente','tardanza') NOT NULL DEFAULT 'presente',
  `registrado_por` VARCHAR(120) DEFAULT NULL,
  `actualizado`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asistencia` (`alumno_id`, `fecha`, `materia`),
  CONSTRAINT `fk_asistencias_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
--  Configuración de la institución (clave/valor)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracion` (
  `clave` VARCHAR(60)  NOT NULL,
  `valor` TEXT         DEFAULT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================================
--  Datos de ejemplo
-- ==================================================================

INSERT IGNORE INTO `cursos` (`id`, `nombre`, `turno`, `capacidad`, `preceptor`) VALUES
  (1, '1° A', 'Mañana', 30, 'Prof. Carmen Ruiz'),
  (2, '1° B', 'Mañana', 30, 'Prof. Daniel Flores'),
  (3, '2° A', 'Mañana', 30, 'Prof. Ricardo Santos'),
  (4, '2° B', 'Mañana', 30, 'Prof. Susana Gutiérrez'),
  (5, '3° A', 'Mañana', 30, 'Prof. Héctor Mendoza'),
  (6, '3° B', 'Mañana', 30, 'Prof. Lorena Castillo');

-- ------------------------------------------------------------------
--  IMPORTANTE — contraseñas
--  Acá NO hay hashes reales a propósito: un hash de bcrypt solo sirve
--  si lo genera tu propio PHP. Generalos así y pegá el resultado:
--
--      C:\xampp\php\php.exe sql\generar-hashes.php
--
--  Ese script imprime los UPDATE listos para copiar en phpMyAdmin.
--  Usuarios de ejemplo (mismas claves que data/credentials.json):
--      rolando_flores / Sigah123@
--      prof_lopez     / Docente456@
--      prof_torres    / Docente789@
-- ------------------------------------------------------------------
INSERT IGNORE INTO `usuarios` (`id`, `nombre`, `email`, `usuario`, `contrasena`, `rol`, `estado`, `fecha_creacion`) VALUES
  (1, 'Rolando Flores', 'rolando@sigah.edu.ar', 'rolando_flores', 'PENDIENTE-GENERAR-HASH', 'admin',   'activo', '2026-01-15'),
  (2, 'Prof. López',    'lopez@sigah.edu.ar',   'prof_lopez',     'PENDIENTE-GENERAR-HASH', 'docente', 'activo', '2026-02-10'),
  (3, 'Prof. Torres',   'torres@sigah.edu.ar',  'prof_torres',    'PENDIENTE-GENERAR-HASH', 'docente', 'activo', '2026-02-15');

INSERT IGNORE INTO `alumnos`
  (`id`, `apellido`, `nombre`, `dni`, `fecha_nacimiento`, `email`, `telefono`, `curso_id`, `turno`, `foto`) VALUES
  (1,  'Flores Arce', 'Rolando Maximo Tupac', '00.000.000', '2009-01-05', 'rolando@estudiante.edu.ar',        '+54 387 000-0000', 1, 'Mañana', 'https://i.pravatar.cc/150?img=12'),
  (2,  'González',    'Lara María',           '11.111.111', '2009-03-15', 'lara.gonzalez@estudiante.edu.ar',  '+54 387 111-1111', 1, 'Mañana', 'https://i.pravatar.cc/150?img=47'),
  (3,  'López',       'Facundo Andrés',       '22.222.222', '2008-11-20', 'facundo.lopez@estudiante.edu.ar',  '+54 387 222-2222', 1, 'Mañana', 'https://i.pravatar.cc/150?img=33'),
  (4,  'Torres',      'Valentina Sofía',      '33.333.333', '2009-06-10', 'valentina.torres@estudiante.edu.ar','+54 387 333-3333', 2, 'Mañana', 'https://i.pravatar.cc/150?img=22'),
  (5,  'Ramírez',     'Carlos Alberto',       '44.444.444', '2008-09-05', 'carlos.ramirez@estudiante.edu.ar', '+54 387 444-4444', 2, 'Mañana', 'https://i.pravatar.cc/150?img=55'),
  (6,  'Díaz',        'María Fernanda',       '55.555.555', '2009-02-14', 'maria.diaz@estudiante.edu.ar',     '+54 387 555-5555', 3, 'Mañana', 'https://i.pravatar.cc/150?img=65'),
  (7,  'Sánchez',     'Martín Lucas',         '66.666.666', '2008-07-22', 'martin.sanchez@estudiante.edu.ar', '+54 387 666-6666', 3, 'Mañana', 'https://i.pravatar.cc/150?img=74'),
  (8,  'Morales',     'Jesús Pablo',          '77.777.777', '2009-04-08', 'jesus.morales@estudiante.edu.ar',  '+54 387 777-7777', 4, 'Mañana', 'https://i.pravatar.cc/150?img=82'),
  (9,  'Aguilar',     'Marcos Emanuel',       '88.888.888', '2009-05-19', 'marcos.aguilar@estudiante.edu.ar', '+54 387 888-8888', 4, 'Tarde',  'https://i.pravatar.cc/150?img=11'),
  (10, 'Benítez',     'Sofía Abril',          '99.999.999', '2008-12-02', 'sofia.benitez@estudiante.edu.ar',  '+54 387 999-9999', 5, 'Tarde',  'https://i.pravatar.cc/150?img=23'),
  (11, 'Carrizo',     'Tomás Ignacio',        '10.101.010', '2008-08-30', 'tomas.carrizo@estudiante.edu.ar',  '+54 387 101-0101', 5, 'Tarde',  'https://i.pravatar.cc/150?img=15'),
  (12, 'Medina',      'Lautaro Gabriel',      '12.121.212', '2008-04-12', 'lautaro.medina@estudiante.edu.ar', '+54 387 121-2121', 6, 'Tarde',  'https://i.pravatar.cc/150?img=60');

INSERT IGNORE INTO `configuracion` (`clave`, `valor`) VALUES
  ('institucion_nombre', 'EET N°3100 Rep. de la India'),
  ('institucion_cue',    '3100000'),
  ('ciclo_anio',         '2026'),
  ('ciclo_estado',       'activo');
