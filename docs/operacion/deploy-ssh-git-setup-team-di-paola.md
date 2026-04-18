# Deploy con SSH + Git — Setup Real Team Di Paola

> Estado: vigente
> Audiencia: operación, desarrollo, agentes
> Fuente de verdad: sí, para setup de deploy por SSH
> Última revisión: 2026-04-18

## Objetivo

Dejar documentado el deploy por SSH + Git hacia el subdominio de pruebas `skyneosec.kescom.com.ar` en Hostinger.

## Datos del servidor

| Campo | Valor |
|---|---|
| Proveedor | Hostinger |
| Servidor | `server853` |
| IP | `147.79.89.169` |
| Puerto SSH | `65002` |
| Usuario SSH | `u347774250` |
| Autenticación | SSH key ed25519 (nombre en panel: `elGonza`) |
| Document root | `~/domains/kescom.com.ar/public_html/skyneosec` |
| URL producción | `https://skyneosec.kescom.com.ar` |
| Base MySQL | `u347774250_skyneosec` |
| Usuario MySQL | `u347774250_skyneosec` |
| phpMyAdmin | `https://auth-db853.hstgr.io/index.php?db=u347774250_skyneosec` |

> La contraseña de MySQL vive solo en `config/config.local.php` del servidor. No se versiona ni se documenta en este archivo.

> El subdominio es ambiente de pruebas / staging. Cuando exista un dominio definitivo, este documento debe actualizarse.

## Conexión SSH

```bash
ssh -p 65002 u347774250@147.79.89.169
```

Con la SSH key cargada localmente, la sesión abre sin password.

## Flujo de deploy

### Paso 1 — local

```bash
git add .
git commit -m "tipo: descripcion"
git push origin main
```

### Paso 2 — remoto en una línea

```bash
ssh -p 65002 u347774250@147.79.89.169 "cd ~/domains/kescom.com.ar/public_html/skyneosec && git pull origin main"
```

## Repo remoto

```
https://github.com/GonzaloEzx/team-di-paola-kick-boxing-sys.git
```

## Setup inicial en servidor (solo una vez)

El document root debe estar vacío antes de clonar.

```bash
ssh -p 65002 u347774250@147.79.89.169
cd ~/domains/kescom.com.ar/public_html/skyneosec
git clone https://github.com/GonzaloEzx/team-di-paola-kick-boxing-sys.git .
cp config/config.hostinger.example.php config/config.local.php
# Editar config.local.php y reemplazar CAMBIAR_EN_SERVIDOR por la password real
nano config/config.local.php
```

## Comandos útiles

### Estado del checkout remoto

```bash
ssh -p 65002 u347774250@147.79.89.169 "cd ~/domains/kescom.com.ar/public_html/skyneosec && git status"
```

### Últimos commits

```bash
ssh -p 65002 u347774250@147.79.89.169 "cd ~/domains/kescom.com.ar/public_html/skyneosec && git log --oneline -5"
```

### Resincronización dura con main (rollback o recuperación)

```bash
ssh -p 65002 u347774250@147.79.89.169 "cd ~/domains/kescom.com.ar/public_html/skyneosec && git fetch origin && git reset --hard origin/main"
```

### Smoke test sin cache

```bash
curl -sI https://skyneosec.kescom.com.ar/ | head -20
curl -s https://skyneosec.kescom.com.ar/?route=api/health
```

## Notas operativas

- `config/config.local.php` queda fuera de Git. Cualquier cambio en esa config se hace directamente por SSH en el servidor.
- El panel de archivos web de Hostinger (`srv853-files.hstgr.io/.../files/public_html/skyneosec/`) escribe al document root real (`~/domains/kescom.com.ar/public_html/skyneosec/`), aunque la URL del panel sugiera `~/public_html/`. Es una convención de Hostinger.
- El directorio `~/public_html/skyneosec/` (fuera de `domains/`) es distinto y no se sirve en ninguna URL pública. No usarlo.
