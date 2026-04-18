# Fase 05 — Auth Backend: Login, Sesión, Roles, Middleware

> Estado: planificación
> Fecha: 2026-04-18
> Alcance: backend de autenticación y autorización. Sin UI definitiva (solo form minimal para probar).

## 1. Objetivo

Dejar el sistema con:

- login funcional contra `usuarios` con `password_hash` / `password_verify`;
- sesión PHP con regeneración de ID;
- resolución de permisos desde `usuario_roles` (fuente única de verdad, no desde columnas en `staff`);
- middleware de control de acceso reutilizable por módulo;
- usuario admin seed para poder entrar la primera vez.

Al terminar la fase, cualquier módulo futuro va a poder decir "requiere rol admin" o "requiere rol recepcion o admin" en una línea.

## 2. Fuera de alcance

- Registro público de alumnos.
- Recuperación de password por email.
- Google OAuth.
- Verificación por código de 6 dígitos.
- Layout admin completo (sidebar/topbar) — eso es Fase 6.
- Cambio de password desde el perfil.

Justificación: la academia opera con altas manuales desde recepción. Registro público y recuperación automática son features de Fase 15+ si se habilita portal alumno.

## 3. Decisiones críticas

1. **Sesiones PHP nativas**, no JWT. Más simple, sin deuda, alcanza para V1.
2. **Permisos desde `usuario_roles` siempre**. Ni `staff.especialidad` ni `alumnos.usuario_id` otorgan acceso por sí mismos.
3. **Un único login**. No hay login separado para alumno vs admin. Si el usuario tiene rol `alumno` y nada más, podrá autenticarse pero no acceder a pantallas admin. El portal de alumno es Fase 15+.
4. **Middleware por función, no por clase**. Funciones como `require_login()` y `require_rol('admin')` que hacen `exit` con redirect o 403 JSON según contexto.
5. **Seed de admin inicial** vía migración nueva (`006_seed_admin_inicial.sql`) con password temporal. El admin debe cambiarla en primer login — pero el cambio de password NO es parte de Fase 5. En Fase 5 la password del seed queda hardcodeada en la migración y se documenta que hay que cambiarla manualmente con SQL. Deuda técnica conocida y asumida.
6. **CSRF solo en endpoints que mutan estado**. Token en sesión, header `X-CSRF-Token` en requests JSON. El form de login queda exento (sin sesión aún no hay token).
7. **Sin rate limit en V1**. Se documenta como deuda técnica. El riesgo es bajo por ser sistema interno.

## 4. Reutilización desde `ejemplo/`

### Seguro de copiar (patrón, no archivo literal)

- `password_hash` / `password_verify` con cost 12 — patrón estándar, viene de `ejemplo/includes/auth.php`.
- Regeneración de session ID al loguearse — patrón.
- Validación de email con `filter_var(..., FILTER_VALIDATE_EMAIL)`.
- Sanitización con `htmlspecialchars` — ya existe como `h()` en `core/helpers.php`.

### Reescribir

- Todo lo que toque `usuarios.rol` como ENUM — el esquema nuevo no lo tiene.
- `includes/auth.php` completo: mezcla login, Google OAuth, códigos, recuperación, roles hardcodeados.
- `admin/includes/admin_header.php`: hace permisos inline en HTML.

### Archivos fuente en `ejemplo/` que pueden consultarse como referencia

- `ejemplo/piel-morena-sys/includes/auth.php` (login, `password_verify`, sesión).
- `ejemplo/piel-morena-sys/includes/functions.php` (CSRF, sanitización).
- `ejemplo/piel-morena-sys/api/auth/login.php` (endpoint login).
- `ejemplo/piel-morena-sys/api/auth/logout.php`.

## 5. Archivos a crear

```
core/
  auth.php              # sesión, current_user, login, logout, verify password
  permissions.php       # require_login, require_rol, user_has_rol
  csrf.php              # generar y validar token

modules/
  auth/
    auth_controller.php # handlers: login_form, login_submit, logout
    login_view.php      # form HTML minimal

api/
  auth/
    login.php           # POST JSON — alternativa por API
    logout.php

database/
  migration/
    006_seed_admin_inicial.sql
```

Rutas nuevas en `core/router.php`:

| Ruta | Método | Función |
|---|---|---|
| `auth/login` | GET | Renderiza `login_view.php`. Si ya hay sesión, redirect a `admin/dashboard`. |
| `auth/login` | POST | Procesa credenciales. |
| `auth/logout` | GET/POST | Destruye sesión, redirect a `auth/login`. |
| `admin/dashboard` | GET | Ya existe como placeholder. Se le agrega `require_login()` + `require_rol(['admin','recepcion'])`. |

## 6. Tablas afectadas

Ninguna modificación de esquema. Las tablas necesarias ya existen desde la migración 001:

- `usuarios`
- `roles`
- `usuario_roles`

### Seed requerido (migración 006)

```sql
-- Seed admin inicial. Password temporal: "cambiar123".
-- Hash generado con password_hash('cambiar123', PASSWORD_DEFAULT).
-- CAMBIAR EN PRIMER LOGIN vía UPDATE manual hasta que exista "cambiar password" (fuera de Fase 5).

INSERT INTO usuarios (email, password_hash, nombre, apellido, activo)
VALUES ('admin@teamdipaola.local', '<hash_generado>', 'Admin', 'Inicial', 1);

INSERT INTO usuario_roles (usuario_id, rol_id)
SELECT u.id, r.id
FROM usuarios u, roles r
WHERE u.email = 'admin@teamdipaola.local' AND r.codigo = 'admin';
```

Nota: la migración 005 debe haber creado ya los roles (`admin`, `recepcion`, `profesor`, `alumno`). Verificar antes de ejecutar 006.

## 7. Contrato de API (JSON endpoints)

### POST `/?route=api/auth/login`

Request:
```json
{ "email": "admin@teamdipaola.local", "password": "cambiar123" }
```

Response OK (200):
```json
{
  "success": true,
  "data": {
    "usuario_id": 1,
    "nombre": "Admin",
    "apellido": "Inicial",
    "roles": ["admin"]
  }
}
```

Response credenciales inválidas (401):
```json
{ "success": false, "error": "Credenciales inválidas", "code": "LOGIN_INVALIDO" }
```

Response usuario inactivo (403):
```json
{ "success": false, "error": "Usuario inactivo", "code": "USUARIO_INACTIVO" }
```

### POST `/?route=api/auth/logout`

Requiere sesión. Destruye sesión.

```json
{ "success": true, "data": { "logged_out": true } }
```

## 8. Criterios QA

| Criterio | Cómo probar |
|---|---|
| Login con credenciales válidas crea sesión | POST a `api/auth/login` → cookie de sesión en respuesta |
| Login con password inválida devuelve 401 | POST con password incorrecta |
| Login con usuario `activo = 0` devuelve 403 | `UPDATE usuarios SET activo = 0` y reintentar |
| Sesión sobrevive entre requests | GET a `admin/dashboard` con cookie de sesión → 200 |
| `require_login()` redirige a `auth/login` si no hay sesión | GET a `admin/dashboard` sin cookie → 302 a login |
| `require_rol('admin')` bloquea usuario sin rol admin | Crear usuario con solo rol `alumno` y probar acceso a `admin/dashboard` |
| Logout destruye sesión | POST logout → GET `admin/dashboard` → redirect a login |
| Session ID se regenera al loguearse | Comparar `session_id()` antes y después del login |
| CSRF: POST sin token válido a endpoint protegido devuelve 403 | Probar `POST api/auth/logout` sin header `X-CSRF-Token` |
| Password nunca se loguea ni se devuelve en responses | Grep en logs y responses |

## 9. Riesgos y deuda técnica asumida

| Riesgo | Mitigación o aceptación |
|---|---|
| Sin rate limit, alguien puede probar passwords por fuerza bruta | Aceptado en V1. Sistema interno. Se documenta para Fase de hardening. |
| Password temporal en migración versionada | Aceptado. Se cambia manualmente post-deploy. Cambio de password desde UI queda para fase posterior. |
| Sin verificación de email | Aceptado. Los usuarios los crea el admin desde backend. |
| Sesión PHP en shared hosting puede tener GC agresivo | Aceptado. Si se vuelve problema, mover a sesiones en DB. |
| `session_regenerate_id(true)` puede fallar si hay race condition con requests paralelos post-login | Bajo riesgo en V1. Aceptado. |
| No hay política de expiración de sesión explícita más allá del GC de PHP | Documentado. Se define si la operación lo pide. |

## 10. Checklist de implementación

1. [ ] Generar hash de password temporal con `php -r "echo password_hash('cambiar123', PASSWORD_DEFAULT);"`.
2. [ ] Escribir `database/migration/006_seed_admin_inicial.sql` con el hash.
3. [ ] Ejecutar migración 006 en base local.
4. [ ] Implementar `core/auth.php`.
5. [ ] Implementar `core/permissions.php`.
6. [ ] Implementar `core/csrf.php`.
7. [ ] Agregar require de los nuevos archivos en `core/bootstrap.php`.
8. [ ] Implementar `modules/auth/auth_controller.php` y `login_view.php`.
9. [ ] Implementar `api/auth/login.php` y `api/auth/logout.php`.
10. [ ] Registrar rutas nuevas en `core/router.php`.
11. [ ] Proteger `admin/dashboard` con `require_login()` + `require_rol`.
12. [ ] `php -l` en todos los archivos nuevos.
13. [ ] Smoke test manual de todos los criterios QA.
14. [ ] Documentar en este archivo: criterios QA marcados como OK y comandos ejecutados.

## 11. Dependencias para Fase 6

- `current_user()` y `user_has_rol()` deben estar disponibles globalmente.
- La sesión debe exponer al menos: `usuario_id`, `nombre`, `apellido`, `roles[]`.
- Debe existir una forma de decir "este controller requiere login" en una sola línea.
