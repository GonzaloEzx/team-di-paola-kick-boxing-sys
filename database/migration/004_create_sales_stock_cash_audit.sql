-- Migration 004: Products, sales, stock, cash and audit
-- Execute after 003_create_training_attendance.sql.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS productos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 0,
    estado VARCHAR(30) NOT NULL DEFAULT 'activo',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_productos_estado (estado),
    KEY idx_productos_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ventas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alumno_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    fecha_venta DATETIME NOT NULL,
    metodo_pago VARCHAR(30) NOT NULL,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado VARCHAR(30) NOT NULL DEFAULT 'registrada',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_ventas_fecha (fecha_venta),
    KEY idx_ventas_alumno (alumno_id),
    KEY idx_ventas_estado (estado),
    KEY idx_ventas_usuario (usuario_id),
    CONSTRAINT fk_ventas_alumno
        FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_ventas_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS venta_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venta_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    KEY idx_venta_items_venta (venta_id),
    KEY idx_venta_items_producto (producto_id),
    CONSTRAINT fk_venta_items_venta
        FOREIGN KEY (venta_id) REFERENCES ventas(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_venta_items_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_movimientos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id BIGINT UNSIGNED NOT NULL,
    venta_id BIGINT UNSIGNED NULL,
    venta_item_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    motivo VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_stock_producto_fecha (producto_id, created_at),
    KEY idx_stock_venta (venta_id),
    KEY idx_stock_venta_item (venta_item_id),
    KEY idx_stock_tipo (tipo),
    KEY idx_stock_usuario (usuario_id),
    CONSTRAINT fk_stock_movimientos_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_stock_movimientos_venta
        FOREIGN KEY (venta_id) REFERENCES ventas(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_stock_movimientos_venta_item
        FOREIGN KEY (venta_item_id) REFERENCES venta_items(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_stock_movimientos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS caja_movimientos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(20) NOT NULL,
    origen VARCHAR(30) NOT NULL,
    pago_id BIGINT UNSIGNED NULL,
    venta_id BIGINT UNSIGNED NULL,
    movimiento_relacionado_id BIGINT UNSIGNED NULL,
    monto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    metodo_pago VARCHAR(30) NULL,
    concepto VARCHAR(255) NOT NULL,
    fecha_movimiento DATETIME NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'registrado',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_caja_fecha (fecha_movimiento),
    KEY idx_caja_origen (origen),
    KEY idx_caja_usuario (usuario_id),
    KEY idx_caja_pago (pago_id),
    KEY idx_caja_venta (venta_id),
    KEY idx_caja_movimiento_relacionado (movimiento_relacionado_id),
    CONSTRAINT fk_caja_movimientos_pago
        FOREIGN KEY (pago_id) REFERENCES pagos(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_caja_movimientos_venta
        FOREIGN KEY (venta_id) REFERENCES ventas(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_caja_movimientos_relacionado
        FOREIGN KEY (movimiento_relacionado_id) REFERENCES caja_movimientos(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_caja_movimientos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    accion VARCHAR(80) NOT NULL,
    entidad VARCHAR(80) NOT NULL,
    entidad_id BIGINT UNSIGNED NULL,
    payload_json JSON NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_auditoria_entidad (entidad, entidad_id),
    KEY idx_auditoria_usuario (usuario_id),
    KEY idx_auditoria_fecha (created_at),
    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
