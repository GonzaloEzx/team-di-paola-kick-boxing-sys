# Deploy Runbook Hostinger — Team Di Paola Kick Boxing

> Estado: vigente
> Audiencia: operación, desarrollo, agentes
> Fuente de verdad: sí, para publicación en el subdominio de pruebas
> Última revisión: 2026-04-18

## Objetivo

Mantener un flujo de deploy simple y repetible contra el subdominio de pruebas `skyneosec.kescom.com.ar` en Hostinger.

## Contexto actual

- Desarrollo local desde la raíz del repo.
- Arranque local con `C:\xampp\php\php.exe -S localhost:8000`.
- Base local: `team_di_paola_db`.
- Ambiente de pruebas online: Hostinger, subdominio `skyneosec.kescom.com.ar`.
- Método de publicación: `git push origin main` + `ssh ... git pull origin main`.

## Flujo objetivo de trabajo

1. Hacer cambios en local.
2. Validar sintaxis (`php -l`) y smoke manual de lo tocado.
3. Commit a `main`.
4. Push a GitHub.
5. Deploy por SSH contra el document root.
6. Verificar el sitio online.

## Datos reales de producción de pruebas

Fuente: `docs/hosting-data/cap-datos-hostinguer.png`.

| Campo | Valor |
|---|---|
| Dominio | `https://skyneosec.kescom.com.ar` |
| IP servidor | `147.79.89.169` |
| Servidor | `server853` |
| Región | South America (Brazil) |
| Usuario SSH / FTP | `u347774250` |
| Puerto SSH | `65002` |
| Document root | `~/domains/kescom.com.ar/public_html/skyneosec` |
| Base MySQL | `u347774250_skyneosec` |
| Usuario MySQL | `u347774250_skyneosec` |
| phpMyAdmin | `https://auth-db853.hstgr.io/index.php?db=u347774250_skyneosec` |

La contraseña de MySQL/SSH vive solo en `config/config.local.php` del servidor. No se versiona.

## Comandos de trabajo

### Local

```bash
git add .
git commit -m "tipo: descripcion"
git push origin main
```

### Remoto interactivo

```bash
ssh -p 65002 u347774250@147.79.89.169
cd ~/domains/kescom.com.ar/public_html/skyneosec
git pull origin main
```

### Deploy directo en una línea

```bash
ssh -p 65002 u347774250@147.79.89.169 "cd ~/domains/kescom.com.ar/public_html/skyneosec && git pull origin main"
```

## Configuración productiva esperada en servidor

1. `config/config.hostinger.example.php` copiado como `config/config.local.php`.
2. `CAMBIAR_EN_SERVIDOR` reemplazado por la contraseña real de MySQL.
3. `config/config.local.php` fuera de Git (verificado con `git status`).
4. `.htaccess` presente y sirviendo `index.php` como DirectoryIndex.

## Migraciones de base de datos

Las migraciones se corren manualmente en ambos ambientes (local y Hostinger). No hay tracking automático hoy.

### En Hostinger

Opción A — por phpMyAdmin: importar cada archivo SQL de `database/migration/` en orden.

Opción B — por SSH, usando la password leída desde `config/config.local.php`:

```bash
ssh -p 65002 u347774250@147.79.89.169
cd ~/domains/kescom.com.ar/public_html/skyneosec
DB_PASS=$(php -r '$c = require "config/config.local.php"; echo $c["database"]["pass"];')
mysql -u u347774250_skyneosec -p"$DB_PASS" u347774250_skyneosec < database/migration/001_create_core_auth_people.sql
# Repetir por cada migración en orden.
```

## Checklist previo al deploy

- [ ] `php -l` sobre todos los PHP modificados
- [ ] smoke manual local de rutas afectadas
- [ ] `git status` limpio antes de commitear lo intencional
- [ ] revisar URLs o assets hardcodeados
- [ ] si hay migración nueva: preparada para correr en remoto después del pull

## Smoke test post deploy

### Básico

- [ ] `curl -sI https://skyneosec.kescom.com.ar/` responde 200
- [ ] `curl -s https://skyneosec.kescom.com.ar/?route=api/health` responde `{"success":true,"data":{"app":"ok","db":"ok",...}}`
- [ ] home HTML muestra ENV: production, DB: OK

### Cuando haya UI

- [ ] login admin correcto
- [ ] dashboard carga
- [ ] módulos implementados abren sin errores PHP

## Cuando usar File Manager

Solo en emergencia:

- SSH no responde.
- Checkout remoto quedó inconsistente y hay que rescatar un archivo puntual.

Flujo normal siempre por SSH + git.

## Rollback

### Volver a un commit anterior

```bash
ssh -p 65002 u347774250@147.79.89.169 "cd ~/domains/kescom.com.ar/public_html/skyneosec && git log --oneline -5"
# elegir commit bueno
ssh -p 65002 u347774250@147.79.89.169 "cd ~/domains/kescom.com.ar/public_html/skyneosec && git reset --hard <commit>"
```

### Resincronización limpia con GitHub

```bash
ssh -p 65002 u347774250@147.79.89.169 "cd ~/domains/kescom.com.ar/public_html/skyneosec && git fetch origin && git reset --hard origin/main"
```

## Notas

- El dominio `skyneosec.kescom.com.ar` es ambiente de pruebas. Cuando exista un dominio definitivo, actualizar este runbook y `config/config.hostinger.example.php`.
- Si el browser redirige a `pielmorenaestetica.com.ar`, es cache 301 del navegador del sistema anterior. Probar en incógnito o limpiar site data.
