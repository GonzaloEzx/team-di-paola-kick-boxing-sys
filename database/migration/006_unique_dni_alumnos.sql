-- 006 — UNIQUE sobre alumnos.dni
--
-- Reemplaza el indice no unico `idx_alumnos_dni` por un UNIQUE KEY para
-- prevenir duplicados por carga concurrente. MySQL/MariaDB permiten
-- multiples NULL en UNIQUE, por lo que alumnos sin DNI no se bloquean
-- entre si.
--
-- Requiere: MariaDB 10.1.4+ / MySQL 8+ para IF EXISTS en DROP INDEX.
-- Si falla en una version mas vieja, correr manualmente:
--   ALTER TABLE alumnos DROP INDEX idx_alumnos_dni;
--   ALTER TABLE alumnos ADD UNIQUE KEY uq_alumnos_dni (dni);

ALTER TABLE alumnos
    DROP INDEX IF EXISTS idx_alumnos_dni,
    ADD UNIQUE KEY uq_alumnos_dni (dni);
