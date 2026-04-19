# Fase 04.5 — Sincronización local / Hostinger previa a Fase 5

> Estado: completada
> Fecha inicio: 2026-04-18
> Fecha cierre: 2026-04-19
> Alcance: preparar el ambiente de Hostinger (`skyneosec.kescom.com.ar`) para recibir el código del proyecto Team Di Paola y ejecutar Fase 5.

## 1. Motivación

Antes de arrancar Fase 5 (Auth) hace falta:

- confirmar que el ambiente online funciona end-to-end;
- tener plantillas y docs apuntando al dominio real (`skyneosec.kescom.com.ar`), no a ejemplos heredados (`ecscom.com.ar`);
- dejar la DB de Hostinger con el mismo esquema que la local;
- documentar el flow de deploy con SSH key ya configurada.

Sin esto, el primer deploy de Fase 5 iba a descubrir que el document root del subdominio no es donde se había asumido, que el `.htaccess` padre hace rewrite raro, que la DB tenía restos del sistema viejo, etc. Todo eso lo resolvemos en este paso intermedio.

## 2. Estado inicial verificado

| Item | Estado |
|---|---|
| SSH a Hostinger con key | OK (ed25519, sin password) |
| Document root real del subdominio | `~/domains/kescom.com.ar/public_html/skyneosec/` (vacío) |
| Directorio `~/public_html/skyneosec/` | Existe pero no se sirve en ninguna URL pública. Ignorable. |
| Base MySQL `u347774250_skyneosec` | Vacía |
| Redirect del dominio a pielmorenaestetica | Sin evidencia server-side. `curl -I` devuelve 403, no 301. El redirect que se veía en browser era cache 301 del sistema viejo. |

## 3. Cambios locales (commit previo)

Archivos actualizados para reflejar la realidad:

- `config/config.hostinger.example.php`
  - `url_base`: `https://skyneosec.kescom.com.ar`
  - `database.name` y `database.user`: `u347774250_skyneosec`
- `docs/operacion/deploy-ssh-git-setup-team-di-paola.md`
  - Datos del servidor (document root real, URL, DB).
  - Sección "Setup inicial en servidor".
  - Nota sobre el mapping engañoso del File Manager de Hostinger.
- `docs/operacion/deploy-runbook-hostinger-team-di-paola.md`
  - Tabla de datos reales.
  - Sección de migraciones por SSH con lectura segura de password desde `config.local.php`.
  - Nota sobre cache 301 de pielmorena.
- `docs/plans/fase-04.5.md` (este archivo).

No se toca `docs/plans/fase-03.md`: es histórico de la fase ya completada. Los valores ilustrativos de esa fase (ecscom.com.ar) se conservan como registro de lo que se decidió en su momento.

## 4. Checklist de ejecución

### Parte A — Local (no destructivo)

- [x] Actualizar `config/config.hostinger.example.php`.
- [x] Actualizar `docs/operacion/deploy-ssh-git-setup-team-di-paola.md`.
- [x] Actualizar `docs/operacion/deploy-runbook-hostinger-team-di-paola.md`.
- [x] Crear `docs/plans/fase-04.5.md` (este archivo).
- [x] `git add` + commit + push a `main` (commits `9a0eeef` y `06c5c42`).

### Parte B — Clonar repo en el document root de Hostinger

- [x] Clonar el repo en `~/domains/kescom.com.ar/public_html/skyneosec/`.
- [x] Copiar `config/config.hostinger.example.php` → `config/config.local.php` en el server.
- [x] Editar `config/config.local.php` en el server con la password real (sed remoto).
- [x] Verificar con `curl` que `https://skyneosec.kescom.com.ar/` devuelve home HTML con ENV: production.

### Parte C — Migraciones en DB Hostinger

- [x] Aplicar en orden `001` → `005` sobre la base `u347774250_skyneosec`.
- [x] Verificar que `https://skyneosec.kescom.com.ar/?route=api/health` devuelve `db: ok`.

### Parte D — Limpieza

- [ ] (Opcional) Eliminar `~/public_html/skyneosec/` del server (está vacío y confunde).
- [x] Marcar Fase 4.5 como completada.

## 5. Criterios QA

| Criterio | Cómo validar | Estado |
|---|---|---|
| El dominio sirve el nuevo código | `curl -sI https://skyneosec.kescom.com.ar/` devuelve 200 | OK |
| `api/health` responde JSON correcto | `curl -s .../?route=api/health` devuelve `{"success":true,"data":{"app":"ok","db":"ok","environment":"production"}}` | OK |
| `config/config.local.php` no se pushea | `ssh ... "cd docroot && git status"` limpio después del setup | OK |
| Migraciones aplicadas | `SHOW TABLES` en `u347774250_skyneosec` muestra las 21 tablas | OK |
| Roles seed presentes | `SELECT codigo FROM roles` devuelve `admin`, `recepcion`, `profesor`, `alumno` | OK |

## 6. Resultado final verificado

```
GET https://skyneosec.kescom.com.ar/?route=api/health
HTTP/1.1 200 OK
{"success":true,"data":{"app":"ok","db":"ok","environment":"production"}}
```

Ambiente online funcional. Próximo paso: Fase 5 — Auth backend.

## 7. Riesgos y deuda asumida

| Riesgo | Decisión |
|---|---|
| Password de DB queda en `config/config.local.php` en disco del server | Aceptado. Es el estándar de este proyecto. `.htaccess` bloquea acceso web a `config/`. |
| El `.htaccess` padre (`~/public_html/.htaccess`) hace URL rewriting de `/foo` → `/foo.php` | No afecta, aplica a otro directorio. |
| Sin tracking automático de migraciones | Aceptado. Manual por ahora, se agrega tabla `schema_migrations` cuando el volumen lo justifique. |
| Cache 301 en browsers antiguos que usaron el sistema anterior | Aceptado. Se resuelve con incógnito o clear site data. No hay forma server-side de forzar el unlearning del 301. |
