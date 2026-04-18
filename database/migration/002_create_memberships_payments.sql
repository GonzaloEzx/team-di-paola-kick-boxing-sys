-- Migration 002: Memberships, billable periods and payments
-- Execute after 001_create_core_auth_people.sql.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS planes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    duracion_dias INT UNSIGNED NOT NULL,
    tipo_acceso VARCHAR(30) NOT NULL DEFAULT 'libre',
    cantidad_clases INT UNSIGNED NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_planes_activo (activo),
    KEY idx_planes_tipo_acceso (tipo_acceso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS membresias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alumno_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    clases_disponibles INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_membresias_alumno_estado (alumno_id, estado),
    KEY idx_membresias_fecha_fin (fecha_fin),
    KEY idx_membresias_plan (plan_id),
    CONSTRAINT fk_membresias_alumno
        FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_membresias_plan
        FOREIGN KEY (plan_id) REFERENCES planes(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS periodos_liquidables (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alumno_id BIGINT UNSIGNED NOT NULL,
    membresia_id BIGINT UNSIGNED NOT NULL,
    periodo_desde DATE NOT NULL,
    periodo_hasta DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    monto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    saldo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_periodos_alumno_estado (alumno_id, estado),
    KEY idx_periodos_vencimiento (fecha_vencimiento),
    KEY idx_periodos_membresia (membresia_id),
    KEY idx_periodos_vigencia (estado, periodo_desde, periodo_hasta),
    CONSTRAINT fk_periodos_alumno
        FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_periodos_membresia
        FOREIGN KEY (membresia_id) REFERENCES membresias(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alumno_id BIGINT UNSIGNED NULL,
    registrado_por_usuario_id BIGINT UNSIGNED NOT NULL,
    fecha_pago DATETIME NOT NULL,
    metodo_pago VARCHAR(30) NOT NULL,
    monto_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado VARCHAR(30) NOT NULL DEFAULT 'registrado',
    observaciones TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_pagos_fecha (fecha_pago),
    KEY idx_pagos_alumno (alumno_id),
    KEY idx_pagos_estado (estado),
    KEY idx_pagos_usuario (registrado_por_usuario_id),
    CONSTRAINT fk_pagos_alumno
        FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_pagos_usuario
        FOREIGN KEY (registrado_por_usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pago_conceptos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pago_id BIGINT UNSIGNED NOT NULL,
    periodo_liquidable_id BIGINT UNSIGNED NULL,
    tipo_concepto VARCHAR(40) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    KEY idx_pago_conceptos_pago (pago_id),
    KEY idx_pago_conceptos_periodo (periodo_liquidable_id),
    CONSTRAINT fk_pago_conceptos_pago
        FOREIGN KEY (pago_id) REFERENCES pagos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pago_conceptos_periodo
        FOREIGN KEY (periodo_liquidable_id) REFERENCES periodos_liquidables(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pago_comprobantes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pago_id BIGINT UNSIGNED NOT NULL,
    archivo_url VARCHAR(255) NOT NULL,
    tipo_archivo VARCHAR(50) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pago_comprobantes_pago (pago_id),
    CONSTRAINT fk_pago_comprobantes_pago
        FOREIGN KEY (pago_id) REFERENCES pagos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
