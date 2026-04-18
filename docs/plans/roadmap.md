# Roadmap de Fases — Team Di Paola

> Estado: propuesta inicial
> Última revisión: 2026-04-18
> Alcance: fases 5 a 14. Fase 15 (portal alumno) queda fuera de V1.

## Criterios de orden

El orden responde a dependencias técnicas reales, no a comodidad:

1. Sin auth no hay forma de atribuir acciones a un usuario → nada de pagos, check-ins ni ventas se puede auditar.
2. Sin alumnos no hay membresías.
3. Sin membresías y periodos no hay pagos de cuota.
4. Sin periodos pagados no hay control de acceso real.
5. Sin actividades/clases, la asistencia solo puede ser libre.
6. Productos/ventas son paralelos a la cadena de membresía: pueden hacerse en cualquier momento después de auth.
7. Dashboard y reportes van al final: requieren datos reales de pagos, asistencias y ventas para no ser teatro.

## Secuencia propuesta

| Fase | Tema | Depende de | Reutilizable desde `ejemplo/` |
|---|---|---|---|
| 5 | Auth backend (login, sesión, `usuario_roles`, middleware) | Fase 4 | Parcial: `password_hash`, patrones de sesión, CSRF. Reescribir lógica de roles. |
| 6 | Layout admin base + pantalla login | Fase 5 | Parcial: estructura HTML sidebar/topbar, `apiCall` JS, helpers SweetAlert. Reescribir permisos embebidos. |
| 7 | CRUD Alumnos | Fase 5 | Parcial: patrón DataTable + modal CRUD. Reescribir modelo de datos, nada del dominio estético. |
| 8 | Planes y Membresías | Fase 7 | Nada reutilizable directo. Modelo nuevo. |
| 9 | Periodos liquidables + Pagos + Caja básica | Fase 8 | Nada reutilizable directo. `caja_movimientos` viejo no sirve porque no tiene `movimiento_relacionado_id`, `origen` ni vínculo con `pagos`. |
| 10 | Check-in y Asistencias (clase y libre) | Fase 9 | Nada. Modelo nuevo. |
| 11 | Actividades y Clases | Fase 5 (independiente de 7–10) | Nada reutilizable directo. |
| 12 | Productos + Stock | Fase 5 | Parcial: estructura CRUD de `api/admin/productos.php`. Reescribir para incluir `stock_movimientos`. |
| 13 | Ventas | Fase 12 + Fase 9 (por caja) | Nada. Modelo nuevo con transacción atómica productos + stock + caja. |
| 14 | Dashboard operativo | Fases 7–13 | Parcial: layout del dashboard viejo, `Chart.js` config. Reescribir todas las métricas. |

## Notas

- Las fases 11 (Clases) y 12 (Productos) podrían adelantarse en paralelo a 7–10 si hace falta entregar algo visible rápido. Técnicamente no dependen de la cadena de membresía.
- El orden puede rotar entre 11 y 12 sin costo.
- Portal alumno (ver membresía propia, pagos, asistencias) queda fuera de V1. Se retomará después de Fase 14 si la operación interna ya funciona.
- Cada fase debe terminar con: migraciones aplicadas (si aplica), `php -l` limpio, smoke test manual documentado y criterios QA marcados como OK.

## Regla de copia desde `ejemplo/`

- Se puede copiar literalmente: helpers genéricos sin dominio (sanitización, CSRF, formato de dinero/fecha), estructuras HTML, configuraciones `Chart.js`/`DataTables`.
- Se reescribe siempre: cualquier código que toque `usuarios.rol` ENUM, `citas`, `servicios`, `empleados`, `jornadas`, `promociones`, `clientes` (de estética), o `caja_movimientos` viejo.
- En duda, reescribir. Es más barato que arrastrar acoplamiento.
