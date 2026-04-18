-- Migration 005: Base catalogs
-- Execute after 004_create_sales_stock_cash_audit.sql.

SET NAMES utf8mb4;

INSERT INTO roles (codigo, nombre, activo)
VALUES
    ('admin', 'Administrador', 1),
    ('recepcion', 'Recepcion', 1),
    ('profesor', 'Profesor', 1),
    ('alumno', 'Alumno', 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    activo = VALUES(activo);

INSERT INTO actividades (nombre, descripcion, activo)
SELECT 'Kick Boxing', 'Clase grupal de kick boxing.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM actividades WHERE nombre = 'Kick Boxing'
);

INSERT INTO actividades (nombre, descripcion, activo)
SELECT 'Boxeo', 'Clase grupal de boxeo.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM actividades WHERE nombre = 'Boxeo'
);

INSERT INTO actividades (nombre, descripcion, activo)
SELECT 'Funcional', 'Entrenamiento funcional complementario.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM actividades WHERE nombre = 'Funcional'
);

INSERT INTO actividades (nombre, descripcion, activo)
SELECT 'Entrenamiento libre', 'Ingreso libre sin clase programada.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM actividades WHERE nombre = 'Entrenamiento libre'
);
