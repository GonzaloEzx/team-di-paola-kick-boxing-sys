# Fase 04 - Routing y Estructura Base MVC Liviana

## 1. Resumen

Se implemento la columna vertebral tecnica minima del sistema `team-di-paola-kick-boxing-sys`.

El objetivo de esta fase fue ordenar el punto de entrada del proyecto y preparar una estructura simple para futuros modulos, sin implementar todavia negocio real.

Quedo implementado:

- front controller en `index.php`;
- bootstrap central en `core/bootstrap.php`;
- router simple basado en `$_GET['route']`;
- helpers de request, response, vistas y utilidades generales;
- modulo inicial `home`;
- carpetas base `api/` y `assets/`;
- rutas de health para smoke test local y futuro monitoreo simple.

No se implementaron:

- login;
- alumnos;
- pagos;
- membresias;
- clases;
- asistencias;
- ventas.

## 2. Estructura resultante

```text
/
  api/
    .gitkeep

  assets/
    .gitkeep

  config/
    config.php
    config.local.example.php
    config.hostinger.example.php
    database.php

  core/
    bootstrap.php
    helpers.php
    request.php
    response.php
    router.php
    view.php

  database/
    schema.sql
    seed.sql
    migration/
      001_create_core_auth_people.sql
      002_create_memberships_payments.sql
      003_create_training_attendance.sql
      004_create_sales_stock_cash_audit.sql
      005_seed_catalogos_base.sql

  docs/
    operacion/
    plans/
      fase-00.md
      fase-01.md
      fase-02.md
      fase-03.md
      fase-04.md

  modules/
    home/
      home_controller.php
      home_view.php

  .htaccess
  .gitignore
  index.php
  README.md
```

## 3. Archivos creados o modificados

### `index.php`

Antes contenia la prueba directa de entorno y base de datos.

Ahora funciona como front controller minimo:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

dispatch_route();
```

Responsabilidad:

- cargar el bootstrap tecnico;
- delegar la resolucion de la request al router.

### `core/bootstrap.php`

Punto de entrada tecnico interno del sistema.

Responsabilidades:

- definir `APP_ROOT`;
- cargar configuracion;
- cargar conexion DB;
- cargar helpers;
- cargar request/response/view/router;
- definir timezone operativo.

Carga:

```php
require APP_ROOT . '/config/config.php';
require APP_ROOT . '/config/database.php';
require APP_ROOT . '/core/helpers.php';
require APP_ROOT . '/core/request.php';
require APP_ROOT . '/core/response.php';
require APP_ROOT . '/core/view.php';
require APP_ROOT . '/core/router.php';
```

Timezone:

```text
America/Argentina/Buenos_Aires
```

### `core/router.php`

Router simple basado en:

```php
$_GET['route']
```

No usa clases, autoloaders ni dependencias externas.

Rutas implementadas:

| Ruta | Resultado |
|---|---|
| `/` | Home HTML |
| `/?route=home` | Home HTML |
| `/?route=health` | Health check en texto plano |
| `/?route=api/health` | Health check JSON |
| `/?route=admin/dashboard` | Placeholder de dashboard admin |

Tambien incluye:

- `route_not_found()`;
- `app_db_status()`.

### `core/request.php`

Helpers simples para leer datos de request.

Funciones:

```php
request_method(): string
query_param(string $key, $default = null)
post_param(string $key, $default = null)
current_route(): string
```

Decision:

- `current_route()` normaliza espacios y barras para que `/?route=/health/` sea tratado como `health`.

### `core/response.php`

Helpers para respuestas JSON.

Formato success:

```json
{
  "success": true,
  "data": {}
}
```

Formato error:

```json
{
  "success": false,
  "error": "mensaje",
  "code": "ERROR_CODE"
}
```

Funciones:

```php
json_success(array $data = []): void
json_error(string $message, string $code = 'ERROR', int $statusCode = 400): void
json_response(array $payload): void
```

### `core/view.php`

Renderizador simple de vistas PHP.

Funcion:

```php
render_view(string $viewPath, array $params = []): void
```

Uso:

```php
render_view('home/home_view.php', [
    'systemName' => 'Sistema Team Di Paola',
]);
```

La ruta se resuelve desde:

```text
modules/
```

### `core/helpers.php`

Funciones generales:

```php
is_local_environment(): bool
base_url(string $path = ''): string
h($value): string
redirect(string $url, int $statusCode = 302): void
```

Uso esperado:

- `is_local_environment()` para checks de entorno;
- `base_url()` para construir URLs;
- `h()` para escapar HTML;
- `redirect()` para redirecciones simples.

### `modules/home/home_controller.php`

Controlador inicial del modulo home.

Responsabilidad:

- preparar variables minimas;
- consultar estado de DB mediante `app_db_status()`;
- renderizar `home_view.php`.

### `modules/home/home_view.php`

Vista HTML inicial.

Renderiza:

- nombre del sistema;
- environment actual;
- estado de DB.

Salida esperada:

```text
Sistema Team Di Paola activo
ENV: development
DB: OK
```

### `api/.gitkeep`

Placeholder para mantener versionada la carpeta `api/`.

Uso futuro:

- endpoints API reales;
- controladores o entrypoints especificos si se decide separarlos.

### `assets/.gitkeep`

Placeholder para mantener versionada la carpeta `assets/`.

Uso futuro:

- CSS;
- JS;
- imagenes;
- assets publicos.

## 4. Comportamiento implementado

### `/`

Renderiza HTML con:

```text
Sistema Team Di Paola activo
ENV: development
DB: OK
```

### `/?route=health`

Devuelve texto plano:

```text
app: OK
db: OK
env: development
```

### `/?route=api/health`

Devuelve JSON:

```json
{
  "success": true,
  "data": {
    "app": "ok",
    "db": "ok",
    "environment": "development"
  }
}
```

### `/?route=admin/dashboard`

Renderiza placeholder HTML:

```text
Dashboard admin en construcción
```

## 5. Validaciones realizadas

### Sintaxis PHP

Se ejecuto `php -l` sobre todos los archivos PHP del proyecto, excluyendo `ejemplo/`.

Resultado:

```text
No syntax errors detected
```

### Smoke CLI

Se validaron las rutas cargando `index.php` por CLI:

```powershell
C:\xampp\php\php.exe index.php
C:\xampp\php\php.exe -r '$_GET["route"]="health"; require "index.php";'
C:\xampp\php\php.exe -r '$_GET["route"]="api/health"; require "index.php";'
C:\xampp\php\php.exe -r '$_GET["route"]="admin/dashboard"; require "index.php";'
```

Resultados:

```text
home: OK
health: OK
api/health: OK
admin/dashboard: OK
```

### Smoke HTTP real

Se levanto temporalmente:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8000
```

Se probaron:

```text
http://127.0.0.1:8000/
http://127.0.0.1:8000/?route=health
http://127.0.0.1:8000/?route=api/health
http://127.0.0.1:8000/?route=admin/dashboard
```

Resultado:

```text
[200] /
[200] /?route=health
[200] /?route=api/health
[200] /?route=admin/dashboard
```

## 6. Como probar manualmente

Desde la raiz del proyecto:

```powershell
C:\xampp\php\php.exe -S localhost:8000
```

Abrir:

```text
http://localhost:8000/
```

Probar tambien:

```text
http://localhost:8000/?route=health
http://localhost:8000/?route=api/health
http://localhost:8000/?route=admin/dashboard
```

## 7. Criterios QA

| Criterio | Estado |
|---|---|
| El proyecto corre desde la raiz | OK |
| No depende de `htdocs` | OK |
| `index.php` delega al bootstrap/router | OK |
| Existe `core/bootstrap.php` | OK |
| Existe `core/router.php` | OK |
| Existe `core/request.php` | OK |
| Existe `core/response.php` | OK |
| Existe `core/view.php` | OK |
| Existe `core/helpers.php` | OK |
| Existe modulo `modules/home` | OK |
| `/` responde | OK |
| `/?route=health` responde | OK |
| `/?route=api/health` responde JSON | OK |
| `/?route=admin/dashboard` responde placeholder | OK |
| No se implemento negocio real | OK |

## 8. Decisiones tecnicas

### Router por `$_GET['route']`

Se eligio esta forma por simplicidad operativa.

Ventajas:

- funciona bien con `php -S`;
- no requiere rewrite obligatorio;
- evita depender de configuracion Apache para desarrollo local;
- es facil de testear manualmente.

### MVC liviano, no framework

Se separo:

- entrada: `index.php`;
- preparacion: `core/bootstrap.php`;
- routing: `core/router.php`;
- request/response/view/helpers: utilidades transversales;
- modulo: `modules/home`.

No se agregaron:

- clases base;
- contenedor de dependencias;
- autoloader;
- ORM;
- framework.

### Vistas PHP simples

Las vistas son archivos PHP normales.

Motivo:

- cero dependencias;
- bajo costo de mantenimiento;
- suficiente para el estado actual del proyecto.

### Health check doble

Se agregaron dos variantes:

- `/?route=health` para lectura humana rapida;
- `/?route=api/health` para automatizacion futura.

### `app_db_status()`

La funcion centraliza el chequeo basico de DB para home y health checks.

En esta fase solo devuelve:

```text
ok
error
```

No expone detalles de credenciales ni mensajes internos.

## 9. Estado final de la Fase 04

La Fase 04 queda completa.

El sistema ya tiene una base tecnica minima para crecer por modulos:

- `core/` concentra infraestructura;
- `modules/` concentra funcionalidad;
- `api/` queda reservado para endpoints;
- `assets/` queda reservado para recursos publicos;
- `index.php` queda limpio como front controller.

La siguiente fase puede avanzar hacia layout base, autenticacion o primer modulo funcional, manteniendo esta estructura.
