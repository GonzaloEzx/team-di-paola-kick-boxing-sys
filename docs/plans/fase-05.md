# Fase 05 — Auth Backend: Login, Sesión, Roles, Middleware

> Estado: completada
> Fecha inicio: 2026-04-18
> Fecha cierre: 2026-04-19
> Alcance: backend de autenticación y autorización. UI minimal para login.

## 1. Objetivo

Dejar el sistema con:

- login funcional contra `usuarios` con `password_hash` / `password_verify`;
- sesión PHP con regeneración de ID;
- resolución de permisos desde `usuario_roles` (fuente única de verdad);
- middleware de control de acceso reutilizable por módulo;
- usuario admin creado en local y prod.

## 2. Fuera de alcance (según planificado)

- Registro público de alumnos.
- Recuperación de password por email.
- Google OAuth.
- Verificación por código de 6 dígitos.
- Layout admin completo (sidebar/topbar) — Fase 6.
- Cambio de password desde el perfil — Fase posterior.

## 3. Decisiones ejecutadas

1. Sesiones PHP nativas, no JWT.
2. Permisos desde `usuario_roles` en todo momento.
3. Un único login para todos los roles.
4. Middleware por función: `require_login()`, `require_rol()`, `user_has_rol()`.
5. Admin inicial creado vía `bin/crear_admin.php` (CLI, sin hash versionado en SQL).
6. CSRF en endpoints que mutan estado; login queda exento por no haber sesión previa.
7. Sin rate limit en V1 (deuda documentada).
8. Detección de formato de respuesta: por prefijo `api/` en la ruta o header `Accept: application/json`.

## 4. Archivos creados

```
core/
  auth.php              # sesion, credenciales, roles
  permissions.php       # require_login, require_rol, user_has_rol, request_wants_json
  csrf.php              # token, verify, require_csrf

modules/auth/
  auth_controller.php   # login_form, login_submit, logout_handler
  login_view.php        # form HTML minimal con CSRF

api/auth/
  login.php             # api_auth_login (JSON)
  logout.php            # api_auth_logout (JSON, requiere CSRF)

bin/
  crear_admin.php       # CLI idempotente para crear/actualizar admin
```

Modificados:

- `core/bootstrap.php`: requires de auth.php, permissions.php, csrf.php.
- `core/router.php`: rutas `auth/login`, `auth/logout`, `api/auth/login`, `api/auth/logout`. `admin/dashboard` protegido con `require_rol(['admin', 'recepcion'])`.
- `CLAUDE.md`: nota sobre PHP 7.4 mínimo.

## 5. Rutas implementadas

| Ruta | Método | Protección | Respuesta |
|---|---|---|---|
| `auth/login` | GET | pública | HTML form |
| `auth/login` | POST | pública + CSRF | 302 a `admin/dashboard` o re-render form con error |
| `auth/logout` | GET/POST | pública | 302 a `auth/login` |
| `api/auth/login` | POST | pública | JSON 200 / 401 / 403 |
| `api/auth/logout` | POST | requiere login + CSRF | JSON 200 |
| `admin/dashboard` | GET | `require_rol(['admin','recepcion'])` | 200 / 302 / 403 |

## 6. Contratos API (verificados en prod)

### POST `api/auth/login` (credenciales válidas)

```json
{
  "success": true,
  "data": {
    "usuario_id": 1,
    "nombre": "Gonzalo",
    "apellido": "Estevez",
    "roles": ["admin"],
    "csrf_token": "ba5c15df..."
  }
}
```

### POST `api/auth/login` (credenciales inválidas) — 401

```json
{ "success": false, "error": "Credenciales invalidas", "code": "LOGIN_INVALIDO" }
```

### POST `api/auth/login` (usuario inactivo) — 403

```json
{ "success": false, "error": "Usuario inactivo", "code": "USUARIO_INACTIVO" }
```

### POST `api/auth/logout` sin CSRF — 403

```json
{ "success": false, "error": "Token CSRF invalido", "code": "CSRF_INVALIDO" }
```

### POST `api/auth/logout` con CSRF — 200

```json
{ "success": true, "data": { "logged_out": true } }
```

## 7. Criterios QA

| Criterio | Local | Prod |
|---|---|---|
| Login con credenciales válidas crea sesión | OK | OK |
| Login con password incorrecta devuelve 401 `LOGIN_INVALIDO` | OK | OK |
| Login con `activo=0` devuelve 403 `USUARIO_INACTIVO` | OK | — |
| Sesión sobrevive entre requests | OK | OK |
| `require_login` redirige 302 a login si falta sesión (HTML) | OK | OK |
| `require_login` devuelve 401 JSON si falta sesión (API / Accept JSON) | OK | OK |
| `require_rol` bloquea a usuario con solo rol `alumno` (403 `SIN_PERMISO`) | OK | — |
| Logout vía API con CSRF destruye sesión | OK | OK |
| Logout vía API sin CSRF devuelve 403 `CSRF_INVALIDO` | OK | OK |
| Logout vía GET browser flow redirige a login | OK | — |
| Flow HTML form-based login completo | OK | — |
| `session_regenerate_id(true)` al loguearse | OK | OK |

Los items sin prueba en prod se validaron solo en local por eficiencia (mismo código). El flow completo (login + dashboard + logout + redirect post-logout) se verificó end-to-end en prod.

## 8. Deuda técnica asumida

| Deuda | Motivo |
|---|---|
| Sin rate limit en login | Sistema interno, riesgo bajo. |
| Admin inicial tiene password débil (`12345678`) | Usuario consciente, pendiente de cambio manual. |
| Sin endpoint de cambio de password | Fuera de alcance V1; se usa `bin/crear_admin.php` idempotente mientras tanto. |
| Sin verificación de email | Usuarios los crea el admin. |
| Sin sessions en DB | Suficiente con PHP nativo en Hostinger shared. Si GC vuelve problema, migrar. |
| Ambiente de pruebas comparte hosting con pielmorena y otros proyectos | Aceptado. No afecta siempre que no se cambie la versión de PHP del servidor compartido. |

## 9. Issue resuelto durante la fase

**PHP 7.4 en Hostinger vs PHP 8 en local.** El código original usaba `str_starts_with`, `str_contains` y `match` (PHP 8.0+). Funcionaba en local, fallaba con 500 en prod. Resuelto en commit `1e7954c`:

- `str_starts_with($s, $p)` → `strpos($s, $p) === 0`
- `str_contains($s, $p)` → `strpos($s, $p) !== false`
- `match` → ternario

Nota registrada en `CLAUDE.md` y en memoria para evitar reincidencia.

## 10. Siguiente paso

Fase 6: layout admin base + login view pulida. El form actual es funcional pero minimal. Con Fase 6 arrancamos a usar skills de diseño (`frontend-design`, `polish`, `typeset`) para la UI.
