# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Management platform for Team Di Paola, a kick boxing / martial arts academy. Built in vanilla PHP (procedural, no framework) with MySQL/MariaDB. The system is in early development — core infrastructure (bootstrap, routing, config, DB connection) is implemented; business modules (alumnos, membresias, pagos, asistencias, clases, productos, ventas, caja) are designed but not yet coded.

The project was derived from a beauty salon system (`ejemplo/piel-morena-sys/`). That directory is a read-only reference — never modify it or adopt its domain concepts (citas, servicios estéticos, jornadas, tratamientos). The new domain centers on **memberships, recurring payments, attendance/check-in, group classes, and product sales**.

## Development Commands

```bash
# Start local dev server (from project root, NOT htdocs)
C:\xampp\php\php.exe -S localhost:8000

# Check PHP syntax
C:\xampp\php\php.exe -l <file.php>

# Create the local database
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS team_di_paola_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Run migrations in order (from cmd.exe, not PowerShell)
C:\xampp\mysql\bin\mysql.exe -u root team_di_paola_db < database\migration\001_create_core_auth_people.sql
C:\xampp\mysql\bin\mysql.exe -u root team_di_paola_db < database\migration\002_create_memberships_payments.sql
C:\xampp\mysql\bin\mysql.exe -u root team_di_paola_db < database\migration\003_create_training_attendance.sql
C:\xampp\mysql\bin\mysql.exe -u root team_di_paola_db < database\migration\004_create_sales_stock_cash_audit.sql
C:\xampp\mysql\bin\mysql.exe -u root team_di_paola_db < database\migration\005_seed_catalogos_base.sql

# Smoke test routes
http://localhost:8000/                      # Home HTML
http://localhost:8000/?route=health         # Health check (text)
http://localhost:8000/?route=api/health     # Health check (JSON)
http://localhost:8000/?route=admin/dashboard # Admin placeholder
```

No test framework is configured yet. Validate with `php -l` and manual HTTP smoke tests.

## Architecture

### Request Flow

`index.php` (front controller) → `core/bootstrap.php` → `core/router.php` (`dispatch_route()`)

Routing uses `$_GET['route']` — no URL rewriting required for dev. Routes are resolved by a `switch` in `core/router.php`. Each route loads a controller from `modules/`.

### Directory Layout

- **`core/`** — Framework plumbing (bootstrap, router, request/response helpers, view renderer). All files are loaded on every request via `bootstrap.php`.
- **`modules/`** — Business modules. Each module gets a folder with `{module}_controller.php` and `{module}_view.php`. Only `home` exists so far.
- **`config/`** — Environment detection (`config.php`), DB connection singleton (`database.php`). Local overrides via `config.local.php` (git-ignored).
- **`api/`** — Future JSON endpoints (currently empty `.gitkeep`).
- **`assets/`** — Future CSS/JS/images (currently empty `.gitkeep`).
- **`database/migration/`** — Numbered SQL migrations (001–005). These define the canonical schema.
- **`docs/plans/`** — Phase planning documents (fase-00 through fase-04).
- **`docs/operacion/`** — Local dev setup and deploy runbooks.
- **`ejemplo/`** — Read-only reference of the previous system. Git-ignored.

### Key Functions

| Function | File | Purpose |
|---|---|---|
| `dispatch_route()` | `core/router.php` | Route resolution switch |
| `getDB(): PDO` | `config/database.php` | Singleton DB connection |
| `render_view()` | `core/view.php` | Renders PHP views from `modules/` |
| `json_success()` / `json_error()` | `core/response.php` | Standard JSON response format |
| `h()` | `core/helpers.php` | HTML escaping (`htmlspecialchars`) |
| `current_route()` | `core/request.php` | Extracts and normalizes `$_GET['route']` |
| `base_url()` | `core/helpers.php` | Builds URLs from `URL_BASE` |

### JSON Response Convention

```json
// Success
{ "success": true, "data": { ... } }

// Error
{ "success": false, "error": "message", "code": "ERROR_CODE" }
```

## Database

- **Engine:** MySQL/MariaDB, InnoDB, `utf8mb4`
- **Local DB:** `team_di_paola_db` (root, no password by default)
- **Prod DB:** Hostinger — configured via `config.local.php` on server (never committed)
- **Env vars:** `TDP_DB_HOST`, `TDP_DB_NAME`, `TDP_DB_USER`, `TDP_DB_PASS` (optional overrides)
- **Timezone:** `America/Argentina/Buenos_Aires` (set in `bootstrap.php`)
- **Money:** `DECIMAL(12,2)` — never use floats for money
- **PKs:** `BIGINT UNSIGNED AUTO_INCREMENT`
- **Naming:** tables plural `snake_case`, FKs as `{tabla_singular}_id`, states as `VARCHAR` (not ENUMs)

### Core Tables (from migrations)

`usuarios`, `roles`, `usuario_roles`, `alumnos`, `staff`, `planes`, `membresias`, `periodos_liquidables`, `pagos`, `pago_conceptos`, `pago_comprobantes`, `caja_movimientos`, `actividades`, `clases`, `asistencias_clase`, `asistencias_libre`, `productos`, `ventas`, `venta_items`, `stock_movimientos`, `auditoria`

## Domain Rules

- **Roles:** `admin`, `recepcion`, `profesor`, `alumno`. Permissions come from `usuario_roles`, never from `staff` profile columns.
- **Access control:** An alumno can enter only if: `alumnos.estado = 'activo'` AND `membresias.estado = 'activa'` AND a `periodos_liquidables` row exists with `estado = 'pagado'` and date range covering today. `membresias.fecha_fin` is a cache, not the source of truth.
- **Alumno without user:** `alumnos.usuario_id` is nullable — alumnos can exist without a login account.
- **Soft delete:** Use logical deactivation (estado/activo) for alumnos, staff, planes, membresias, pagos, ventas, productos. Hard delete only for temp data without dependencies.
- **Transactions:** Mandatory for pagos, ventas, check-in, and any annulment. A pago must atomically create `pagos` + `pago_conceptos` + update `periodos_liquidables` + update `membresias` + create `caja_movimientos` + `auditoria`.
- **V1 constraint:** No partial payments — cuota payment must match `periodos_liquidables.saldo` exactly.
- **Asistencia libre:** Max one active (`estado = 'registrada'`) per alumno per calendar day.
- **Stock:** Can never go negative. Every stock change must create a `stock_movimientos` row.

## Coding Conventions

- `declare(strict_types=1)` at the top of every PHP file.
- Functions in `snake_case`. Variables in `snake_case`.
- PDO with prepared statements for all SQL. Never concatenate user input into queries.
- HTML output escaped with `h()`.
- No classes, autoloaders, ORM, or dependency injection — this is procedural PHP by design.
- New modules go in `modules/{module_name}/` with controller + view files.
- New API endpoints go in `api/{module_name}/`.
- Config never committed: `config/config.local.php` is git-ignored.
- **PHP 7.4 minimum** (Hostinger runs 7.4.33). Avoid PHP 8+ only features: no `match`, no `str_starts_with`/`str_contains`, no nullsafe `?->`, no named arguments, no union/intersection types. Use `strpos() === 0` instead of `str_starts_with`. Use ternary or `if` instead of `match`.

## Phase Status

| Phase | Description | Status |
|---|---|---|
| Fase 0 | Audit of base project (`piel-morena-sys`) | Complete |
| Fase 1 | Functional definition (actors, flows, domain model, business rules) | Complete |
| Fase 2 | Technical design (data model, API contracts, transactions, integrity rules) | Complete |
| Fase 3 | Bootstrap: config, DB connection, env detection | Complete (implemented) |
| Fase 4 | Routing and lightweight MVC structure | Complete (implemented) |
| Fase 5+ | Business modules (alumnos, membresias, pagos, etc.) | Not started |

The detailed design for every table, API contract, and transaction flow is in `docs/plans/fase-02.md`. Consult it before implementing any business module.

# Comportamiento base

Antes de cualquier respuesta, identificá y declaré:
- La suposición más peligrosa que estoy haciendo sin darme cuenta
- El punto de quiebre más probable de lo que pedí
- Si existe una solución más simple que no estoy viendo, proponela primero

No confirmes lo que ya creo. Si algo es incompleto o está mal planteado, decilo antes de resolver.
Si no sabés algo, explicá por qué es difícil saberlo, no lo rellenes.

# Comportamiento específico para código

Antes de escribir cualquier solución:
- Si el requerimiento tiene una suposición falsa o incompleta, marcala antes de codear
- Indicá siempre si la solución genera deuda técnica o no escala, aunque no te lo pida
- Si hay dos enfoques válidos, mostralos con tradeoffs reales, no elijas por mí sin explicar

# Restricciones de output

- Sin comentarios que expliquen lo obvio
- Sin abstracciones prematuras
- Sin cierres motivacionales
- Si el requerimiento es ambiguo, preguntá lo mínimo necesario antes de proceder
