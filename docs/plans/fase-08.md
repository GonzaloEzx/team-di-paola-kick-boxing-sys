# Fase 08 — Planes y Membresías

> Estado: en verificación prod
> Fecha inicio: 2026-04-19
> Fecha cierre local: 2026-04-19
> Alcance: ABM de planes + alta / cancelación de membresías + generación del primer periodo liquidable. Reemplaza placeholder "Membresía" en la ficha del alumno.

## 1. Objetivo

Dejar el sistema con:

- ABM de planes (catálogo comercial) con soporte para ambos `tipo_acceso`;
- alta de membresía desde la ficha del alumno, **generando en la misma transacción** un primer `periodos_liquidables` con `estado='pendiente'`;
- guard de "máximo una membresía viva por alumno" validada en transacción;
- cancelación de membresía que anula periodos pendientes;
- ficha del alumno con sección "Membresía actual" + histórico colapsable.

## 2. Fuera de alcance

- Pagos (Fase 9) — los periodos quedan `pendiente` hasta que Fase 9 implemente cobro.
- Generación de periodos subsiguientes / renovación automática (Fase 9 o posterior).
- Suspender/reactivar membresía con motivo (Fase 9 junto con pagos vencidos).
- Consumo de `cantidad_clases` contra asistencias (Fase 10).
- Dashboard global de próximos vencimientos (Fase 14).
- Edición de membresía: si está mal, se cancela y se crea otra. Evita estados inconsistentes entre periodos generados y plan cambiado.

## 3. Decisiones fijadas

1. **Primer periodo al crear membresía**: al insertar la membresía se inserta también **un `periodos_liquidables`** con `monto = plan.precio`, `saldo = plan.precio`, `estado='pendiente'`, fechas calculadas desde `fecha_inicio` + `plan.duracion_dias`. Atomicidad con transacción.
2. **Ficha con histórico**: sección "Membresía actual" al tope + sección colapsable "Historico" con membresías canceladas/vencidas.
3. **Planes con ambos `tipo_acceso`**: `libre` y `cantidad_clases`. Validación de consumo contra clases queda para Fase 10.
4. **Seed inicial**: migración `007_seed_planes_base.sql` con 3 planes de ejemplo (DB vacía es mal primer contacto).
5. **Suspender/reactivar** queda fuera (Fase 9).
6. **Estado de membresía al nacer**: `pendiente` (nace con periodo impago). Pasará a `activa` cuando Fase 9 registre el pago.
7. **Cancelar**: membresía pasa a `cancelada`, sus periodos `pendiente` pasan a `anulado` en la misma transacción. Periodos `pagado` se mantienen como historial.
8. **Regla anti-duplicados**: al crear una membresía, dentro de transacción con `SELECT ... FOR UPDATE` sobre el alumno, se verifica que no exista otra en estados `(pendiente, activa, suspendida)`. Si existe → error `MEMBRESIA_EXISTENTE`.

## 4. Modelo de datos

Ya existente en migración `002_create_memberships_payments.sql`. No se modifica schema — solo se agrega seed.

Estados de membresía en uso en Fase 8:
- `pendiente`: recién creada, sin ningún periodo pagado.
- `activa`: se asignará en Fase 9 al confirmar pago de periodo vigente.
- `suspendida`: Fase 9.
- `vencida`: Fase 9 (recalculada cuando expira sin pago).
- `cancelada`: baja manual.

Estados de periodo en uso en Fase 8:
- `pendiente`: recién creado.
- `anulado`: membresía cancelada.
- `pagado` / `vencido`: Fase 9.

## 5. Cálculos

Al crear membresía con `plan_id` y `fecha_inicio`:

```
fecha_fin = fecha_inicio + (plan.duracion_dias - 1 día)   // cache admin, se recalcula en Fase 9 al pagar
periodo_desde = fecha_inicio
periodo_hasta = fecha_fin
fecha_vencimiento = fecha_inicio                          // V1: se cobra al momento de inscripción
monto = plan.precio
saldo = plan.precio
clases_disponibles = (plan.tipo_acceso == 'cantidad_clases') ? plan.cantidad_clases : null
```

## 6. Rutas

### Admin (HTML)

| Ruta | Método | Propósito |
|---|---|---|
| `admin/planes` | GET | Listado de planes |
| `admin/planes/nuevo` | GET | Form alta |
| `admin/planes/nuevo` | POST | Crear |
| `admin/planes/editar&id=X` | GET | Form edición |
| `admin/planes/editar&id=X` | POST | Actualizar |
| `admin/planes/toggle&id=X` | POST | Activar/desactivar |
| `admin/membresias/nueva&alumno_id=X` | GET | Form nueva membresía |
| `admin/membresias/nueva&alumno_id=X` | POST | Crear membresía (transacción) |
| `admin/membresias/cancelar&id=X` | POST | Cancelar membresía |

### API (JSON)

| Ruta | Método | Propósito |
|---|---|---|
| `api/planes` | GET | Listar (q, activo, page, limit) |
| `api/planes` | POST | Crear |
| `api/planes/show&id=X` | GET | Detalle |
| `api/planes/update&id=X` | POST | Actualizar |
| `api/planes/toggle&id=X` | POST | Activar/desactivar |
| `api/membresias` | GET | Listar por `alumno_id` |
| `api/membresias` | POST | Crear |
| `api/membresias/cancel&id=X` | POST | Cancelar |

Todas protegidas con `require_rol(['admin', 'recepcion'])`. Mutaciones requieren CSRF.

## 7. Validaciones

### Planes
| Campo | Regla |
|---|---|
| `nombre` | requerido, trim, min 2, max 120 |
| `precio` | requerido, decimal >= 0 |
| `duracion_dias` | requerido, entero > 0, <= 3650 |
| `tipo_acceso` | en (`libre`, `cantidad_clases`) |
| `cantidad_clases` | requerido si `tipo_acceso=cantidad_clases`, entero > 0 |
| `descripcion` | opcional |

### Membresía
| Campo | Regla |
|---|---|
| `alumno_id` | existe, estado `activo` |
| `plan_id` | existe, `activo=1` |
| `fecha_inicio` | DATE válida, no más de 30 días en el pasado ni en el futuro |
| anti-duplicado | no existe membresía del alumno en (`pendiente`, `activa`, `suspendida`) |

## 8. Archivos

```
database/migration/
  007_seed_planes_base.sql

modules/planes/
  planes_controller.php
  planes_list_view.php
  planes_form_view.php

modules/membresias/
  membresias_controller.php       # create (txn), cancel (txn), fetch helpers
  membresias_form_view.php        # form nueva membresía

api/planes/
  list.php, show.php, create.php, update.php, toggle.php

api/membresias/
  list_by_alumno.php, create.php, cancel.php

modules/alumnos/alumnos_detail_view.php  # placeholder membresía → datos + histórico
modules/admin/admin_dashboard_view.php   # tarjeta Planes activa
core/router.php                          # + rutas
```

## 9. Criterios QA

| Criterio | Local | Prod |
|---|---|---|
| Seed 007 aplicado y 3 planes visibles | OK | — |
| ABM planes funciona (alta, edición, toggle) | OK | — |
| Form nueva membresía muestra solo planes activos | OK | — |
| Alta de membresía inserta membresía + periodo en una transacción | OK | — |
| Segunda membresía sobre alumno con una viva devuelve `MEMBRESIA_EXISTENTE` | OK | — |
| Cancelar membresía anula periodos pendientes y mantiene pagados | OK | — |
| Ficha del alumno muestra membresía actual + histórico | OK | — |
| Validación: `cantidad_clases` requerido si `tipo_acceso=cantidad_clases` | OK | — |
| Planes inactivos no aparecen en select de nueva membresía | OK | — |
| API `GET /api/membresias?alumno_id=X` devuelve lista | OK | — |
| API `POST /api/membresias` con 2a activa devuelve 409 | OK (guard en controller idéntico) | — |
| Compatibilidad PHP 7.4 | OK (sin features 8+) | — |

## 10. Deuda asumida

| Deuda | Motivo |
|---|---|
| `fecha_fin` no se recalcula al pagar (aún) | Fase 9 lo agrega. |
| Sin generar periodos siguientes | Fase 9 o feature posterior. |
| Sin suspender/reactivar membresía | Fase 9. |
| Sin consumo de clases | Fase 10 (asistencias). |
| Sin dashboard de vencimientos | Fase 14. |

## 11. Siguiente paso

Fase 9: Pagos + Caja. Cobra periodos pendientes, actualiza `membresias.estado → activa`, refresca `fecha_fin`, registra `caja_movimientos`.
