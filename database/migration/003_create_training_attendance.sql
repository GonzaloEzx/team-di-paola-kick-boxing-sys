-- Migration 003: Activities, classes and attendance
-- Execute after 002_create_memberships_payments.sql.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS actividades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_actividades_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actividad_id BIGINT UNSIGNED NOT NULL,
    profesor_staff_id BIGINT UNSIGNED NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    cupo_maximo INT UNSIGNED NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'programada',
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_clases_fecha (fecha),
    KEY idx_clases_profesor_fecha (profesor_staff_id, fecha),
    KEY idx_clases_estado (estado),
    KEY idx_clases_actividad (actividad_id),
    CONSTRAINT fk_clases_actividad
        FOREIGN KEY (actividad_id) REFERENCES actividades(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_clases_profesor_staff
        FOREIGN KEY (profesor_staff_id) REFERENCES staff(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS asistencias_clase (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clase_id BIGINT UNSIGNED NOT NULL,
    alumno_id BIGINT UNSIGNED NOT NULL,
    membresia_id BIGINT UNSIGNED NULL,
    registrado_por_usuario_id BIGINT UNSIGNED NOT NULL,
    fecha_hora_checkin DATETIME NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'registrada',
    es_excepcion TINYINT(1) NOT NULL DEFAULT 0,
    motivo_excepcion VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_asistencias_clase_alumno (clase_id, alumno_id),
    KEY idx_asistencias_clase_alumno (alumno_id),
    KEY idx_asistencias_clase_fecha (fecha_hora_checkin),
    KEY idx_asistencias_clase_membresia (membresia_id),
    KEY idx_asistencias_clase_usuario (registrado_por_usuario_id),
    CONSTRAINT fk_asistencias_clase_clase
        FOREIGN KEY (clase_id) REFERENCES clases(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_asistencias_clase_alumno
        FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_asistencias_clase_membresia
        FOREIGN KEY (membresia_id) REFERENCES membresias(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_asistencias_clase_usuario
        FOREIGN KEY (registrado_por_usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS asistencias_libre (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alumno_id BIGINT UNSIGNED NOT NULL,
    membresia_id BIGINT UNSIGNED NULL,
    registrado_por_usuario_id BIGINT UNSIGNED NOT NULL,
    fecha_asistencia DATE NOT NULL,
    fecha_hora_checkin DATETIME NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'registrada',
    registro_activo TINYINT(1) GENERATED ALWAYS AS (
        CASE WHEN estado = 'registrada' THEN 1 ELSE NULL END
    ) STORED,
    es_excepcion TINYINT(1) NOT NULL DEFAULT 0,
    motivo_excepcion VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_asistencias_libre_alumno_dia_activa (alumno_id, fecha_asistencia, registro_activo),
    KEY idx_asistencias_libre_alumno_fecha (alumno_id, fecha_asistencia, estado),
    KEY idx_asistencias_libre_fecha (fecha_asistencia),
    KEY idx_asistencias_libre_membresia (membresia_id),
    KEY idx_asistencias_libre_usuario (registrado_por_usuario_id),
    CONSTRAINT fk_asistencias_libre_alumno
        FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_asistencias_libre_membresia
        FOREIGN KEY (membresia_id) REFERENCES membresias(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_asistencias_libre_usuario
        FOREIGN KEY (registrado_por_usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
