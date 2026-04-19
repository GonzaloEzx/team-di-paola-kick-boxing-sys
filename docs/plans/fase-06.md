# Fase 06 — Identidad visual: layout admin + login pulido

> Estado: en progreso
> Fecha inicio: 2026-04-19
> Alcance: aplicar la identidad **Bestia Competitiva** (docs/brand/identidad-marca-deportiva-agresiva.md) al sistema.

## 1. Objetivo

Dejar el sistema con:

- tokens CSS (paleta y tipografía) alineados al brief;
- shell admin (topbar + main + footer) reutilizable en todos los módulos admin;
- login rediseñado con alta carga de marca;
- dashboard rediseñado usando el shell, con tarjetas placeholder de los módulos por venir;
- logos oficiales integrados (insignia completa en login, monograma DPC en topbar).

## 2. Fuera de alcance

- Íconos propios / iconografía completa (se usa inline SVG mínimo o Unicode en esta fase).
- Modo impresión.
- Menú lateral completo de módulos (se muestra como tarjetas placeholder).
- DPC Shop / piezas comerciales.
- Redibujo del logo (se usan los JPG actuales; reemplazo futuro con SVG queda como deuda).
- Tipografías auto-hospedadas (se cargan desde Google Fonts; hostear localmente queda como deuda de performance).

## 3. Decisiones

1. **Tipografía**: `Oswald` para titulares, `Inter` para sistema. Cargadas desde Google Fonts vía `<link preconnect>`.
2. **Paleta** (del brief):
   - `--color-ink: #303841` (fondo oscuro principal)
   - `--color-accent: #F6C90E` (amarillo acento / CTA / titulares)
   - `--color-surface: #3A4750` (superficie secundaria / tablas / paneles)
   - `--color-text: #EEEEEE` (texto sobre oscuro)
   - `--color-accent-hi: #FFDE42` (hover)
   - `--color-shadow: #1B0C0C` (sombras extremas / competencia)
3. **Carga de marca por pantalla**: login alta, dashboard media, formularios/tablas baja. Regla del brief: *marca fuerte, interfaz usable*.
4. **Logos**:
   - `assets/img/logo-full.jpg` = insignia circular → login.
   - `assets/img/logo-dpc.jpg` = monograma → topbar del sistema.
   - Se usan como raster por ahora; reemplazo con SVG queda como deuda visual para una fase futura.
5. **Topbar**: fija arriba, color `--color-ink`, altura 64px, monograma DPC + `TEAM DI PAOLA` en Oswald mayúsculas + menú user (nombre + logout) a la derecha.
6. **Login**: layout split — columna izquierda insignia + diagonal amarilla decorativa (hidden en mobile); columna derecha form.
7. **Shell reutilizable**: `core/layout.php` expone `layout_header(array $opts)` y `layout_footer()`. Cualquier view admin los llama.
8. **Dashboard placeholder**: grid de tarjetas con los módulos planificados (alumnos, membresías, pagos, asistencias, clases, ventas, caja, productos). Cada tarjeta es estática ahora; se enlaza cuando el módulo exista.
9. **CSS entregado como 2 archivos**: `tokens.css` (variables) + `base.css` (reset, tipografía, componentes compartidos). Cada view agrega estilos inline o `<style>` para lo específico (evita archivos CSS por componente prematuros).

## 4. Archivos a crear/modificar

```
assets/
  css/
    tokens.css            # variables
    base.css              # reset + tipografía + utilidades + componentes shell
  img/
    logo-full.jpg         # insignia circular (login)
    logo-dpc.jpg          # monograma (topbar)

core/
  layout.php              # layout_header(), layout_footer()
  bootstrap.php           # require layout.php

modules/
  auth/login_view.php     # rediseñado
  admin/
    admin_controller.php  # nuevo — route_admin_dashboard movido desde router
    admin_dashboard_view.php  # nueva vista con shell

core/router.php           # delegar admin/dashboard al controller
```

## 5. Criterios QA

| Criterio | Local | Prod |
|---|---|---|
| Fuentes Oswald + Inter cargan | — | — |
| Paleta refleja `#303841` / `#F6C90E` | — | — |
| Logo insignia visible en login | — | — |
| Monograma DPC visible en topbar | — | — |
| Shell admin renderiza con topbar fija | — | — |
| Dashboard muestra tarjetas placeholder | — | — |
| Form login validación + error styling | — | — |
| Logout desde topbar destruye sesión y redirige | — | — |
| Responsive: login y topbar funcionan <600px | — | — |
| Compatibilidad PHP 7.4 (sin `match`, `str_*with`, nullsafe) | — | — |

## 6. Deuda asumida

| Deuda | Motivo |
|---|---|
| Logos como JPG, no SVG | Los SVG aún no existen; reemplazo futuro sin romper HTML. |
| Google Fonts externo (no self-hosted) | Simplicidad en V1. Si hay restricción de performance/privacidad se migra. |
| Sin íconos propios | Fase 6 prioriza identidad estructural. Iconografía se define más adelante. |
| Menú de módulos hardcoded en dashboard | Cuando existan los módulos se migra a data + loop. |
| CSS inline parcial en views | Se factoriza cuando haya patrón repetido (min. 3 ocurrencias). |

## 7. Siguiente paso

Fase 7: módulo Alumnos (ABM + listado + búsqueda). Primer módulo de negocio usando el shell.
