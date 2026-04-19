# Fase 07 — CRUD Alumnos

> Estado: en progreso
> Fecha inicio: 2026-04-19
> Alcance: primer módulo de negocio. ABM + listado + detalle de alumnos.

## 1. Objetivo

Dejar el sistema con:

- listado de alumnos con paginación prev/next, búsqueda y filtro por estado;
- alta y edición compartiendo form con todos los campos del schema;
- ficha de detalle con secciones placeholder para membresía, pagos y asistencias (Fases 8-10);
- cambio de estado (activar / suspender / inactivar) con motivo opcional;
- API JSON paralela siguiendo contrato de `fase-02.md` §5.1;
- `UNIQUE (dni)` a nivel DB para prevenir duplicados por carga concurrente.

## 2. Fuera de alcance

- Crear usuario de login desde el alumno (flujo de invitación, queda para fase posterior).
- Foto / adjuntos.
- Auditoría detallada (tabla `auditoria` existe pero su integración va en módulo propio más adelante).
- Import CSV / carga masiva.
- Export CSV (agregable en 1 ruta después si hay demanda).
- Vista del alumno sobre sus propios datos (portal alumno, fuera de V1).

## 3. Decisiones fijadas

1. **Estado de membresía en listado**: columna con "pendiente módulo" hasta Fase 8. Cero deuda de JOIN prematuro.
2. **DNI único**: migración `006_unique_dni_alumnos.sql` con `UNIQUE (dni)`. MySQL permite múltiples NULL, así que alumnos sin DNI no se bloquean.
3. **Form completo**: todas las columnas del schema (`alumnos` de `001_create_core_auth_people.sql`). Dividido en 4 secciones: Personal, Contacto, Emergencia, Observaciones.
4. **Paginación**: prev/next + "Página X de Y". 20 alumnos por página.
5. **Ficha con placeholders**: secciones de Membresía, Pagos recientes y Asistencias recientes ya diseñadas visualmente con mensaje "módulo pendiente". Esto fija el layout de ficha para próximas fases.
6. **Soft delete**: cambio de estado (`activo` / `inactivo` / `suspendido`). No hay `DELETE` físico en UI.
7. **Permisos**: `admin` y `recepcion` en todas las rutas admin y API. Rutas protegidas con `require_rol(['admin', 'recepcion'])`.
8. **Router**: subrutas via segundo token en `route` (ej. `admin/alumnos/ver`, `admin/alumnos/editar`). ID por query param `id`. Consistente con el router por switch actual.

## 4. Modelo de datos

Ya existe en migración `001_create_core_auth_people.sql`. Columnas:

- `id`, `usuario_id` (nullable), `nombre`, `apellido`, `dni`, `telefono`, `email`, `fecha_nacimiento`
- `contacto_emergencia_nombre`, `contacto_emergencia_telefono`, `observaciones`
- `estado` (`activo` / `inactivo` / `suspendido`), `created_at`, `updated_at`

## 5. Rutas

### Admin (HTML)

| Ruta | Método | Propósito |
|---|---|---|
| `admin/alumnos` | GET | Listado con filtros |
| `admin/alumnos/nuevo` | GET | Form alta |
| `admin/alumnos/nuevo` | POST | Crear |
| `admin/alumnos/editar&id=X` | GET | Form edición |
| `admin/alumnos/editar&id=X` | POST | Actualizar |
| `admin/alumnos/ver&id=X` | GET | Ficha detalle |
| `admin/alumnos/estado&id=X` | POST | Cambio de estado |

### API (JSON)

| Ruta | Método | Propósito |
|---|---|---|
| `api/alumnos` | GET | Listar (q, estado, page, limit) |
| `api/alumnos` | POST | Crear |
| `api/alumnos/show&id=X` | GET | Detalle |
| `api/alumnos/update&id=X` | POST | Actualizar |
| `api/alumnos/estado&id=X` | POST | Cambio de estado |

Todas las rutas API requieren `require_rol(['admin', 'recepcion'])`. Mutaciones requieren CSRF.

## 6. Validaciones

| Campo | Regla |
|---|---|
| `nombre` | requerido, trim, min 2, max 100 |
| `apellido` | requerido, trim, min 2, max 100 |
| `dni` | opcional, trim, max 30, único (app + DB) |
| `telefono` | opcional, trim, max 30 |
| `email` | opcional, `filter_var FILTER_VALIDATE_EMAIL`, max 150 |
| `fecha_nacimiento` | opcional, DATE válida, no futura |
| `contacto_emergencia_nombre` | opcional, trim, max 150 |
| `contacto_emergencia_telefono` | opcional, trim, max 30 |
| `observaciones` | opcional, TEXT |
| `estado` | en (`activo`, `inactivo`, `suspendido`) |

Si `dni` duplicado → HTTP 409 `DNI_DUPLICADO` en API; en form HTML re-render con error.

## 7. Archivos

```
database/migration/
  006_unique_dni_alumnos.sql

modules/alumnos/
  alumnos_controller.php
  alumnos_list_view.php
  alumnos_form_view.php
  alumnos_detail_view.php

api/alumnos/
  list.php
  show.php
  create.php
  update.php
  change_state.php

core/router.php                # + rutas admin/alumnos*, api/alumnos*
modules/admin/admin_dashboard_view.php  # tarjeta Alumnos activa
```

## 8. Criterios QA

| Criterio | Local | Prod |
|---|---|---|
| Migración 006 aplicada sin errores | — | — |
| Listado pagina 20 por página con prev/next | — | — |
| Búsqueda por nombre / apellido / DNI / email devuelve resultados correctos | — | — |
| Filtro por estado filtra bien | — | — |
| Alta con datos válidos crea alumno y redirige a detalle | — | — |
| Alta con DNI duplicado muestra error sin crear | — | — |
| Alta con nombre vacío devuelve error | — | — |
| Edición precarga valores y guarda cambios | — | — |
| Cambio de estado actualiza el alumno y muestra badge correcto | — | — |
| Detalle renderiza 4 secciones + 3 placeholders | — | — |
| API GET /api/alumnos con `q=` filtra | — | — |
| API POST /api/alumnos crea y devuelve `{success, data:{id}}` | — | — |
| API POST con DNI duplicado devuelve 409 `DNI_DUPLICADO` | — | — |
| Rutas protegidas con `require_rol` bloquean sin permisos | — | — |
| Compatibilidad PHP 7.4 | — | — |

## 9. Deuda asumida

| Deuda | Motivo |
|---|---|
| Placeholders de membresía/pagos/asistencias en ficha | Dependen de Fases 8-10. Se conectan ahí. |
| Sin integración con tabla `auditoria` | Módulo auditoría llega más tarde. |
| Tabla HTML nativa sin DataTables | Evita dep JS pesada. Si volumen >200 con filtros lentos, migrar. |
| Sin export CSV | Agregable en 1 ruta cuando haya demanda. |
| Sin crear usuario de login desde alumno | Requiere flujo de invitación con email. |

## 10. Siguiente paso

Fase 8: Planes y Membresías. Reemplazará los placeholders de la ficha de alumno.
