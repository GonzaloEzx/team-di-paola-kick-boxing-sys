-- 007 — Seed de planes base
--
-- 3 planes de ejemplo para que la DB no arranque vacia. Los valores son
-- orientativos y estan pensados para ser editados desde la UI.
--
-- INSERT IGNORE contra nombre evita duplicar si la migracion se corre
-- mas de una vez. Usa KEY unico funcional-ish: un mismo nombre no se
-- repite. Si ya existe un plan con ese nombre se ignora.

INSERT INTO planes (nombre, descripcion, precio, duracion_dias, tipo_acceso, cantidad_clases, activo)
SELECT * FROM (
    SELECT 'Mensual Libre' AS nombre, 'Acceso libre a todas las clases durante 30 dias.' AS descripcion,
           25000.00 AS precio, 30 AS duracion_dias, 'libre' AS tipo_acceso, NULL AS cantidad_clases, 1 AS activo
) t
WHERE NOT EXISTS (SELECT 1 FROM planes WHERE nombre = 'Mensual Libre');

INSERT INTO planes (nombre, descripcion, precio, duracion_dias, tipo_acceso, cantidad_clases, activo)
SELECT * FROM (
    SELECT '3 clases por semana', 'Hasta 12 clases en 30 dias.',
           20000.00, 30, 'cantidad_clases', 12, 1
) t
WHERE NOT EXISTS (SELECT 1 FROM planes WHERE nombre = '3 clases por semana');

INSERT INTO planes (nombre, descripcion, precio, duracion_dias, tipo_acceso, cantidad_clases, activo)
SELECT * FROM (
    SELECT 'Pase 8 clases', 'Pase flexible de 8 clases validas por 45 dias.',
           15000.00, 45, 'cantidad_clases', 8, 1
) t
WHERE NOT EXISTS (SELECT 1 FROM planes WHERE nombre = 'Pase 8 clases');
