# Fase 03 - Bootstrap de Configuracion y Entorno

## 1. Resumen

Se implemento el bootstrap inicial del sistema `team-di-paola-kick-boxing-sys` para poder ejecutar el proyecto PHP desde la raiz, sin depender de `htdocs`.

El objetivo de esta fase fue dejar una base simple, clara y preparada para desarrollo local y futuro deploy manual en Hostinger:

- configuracion desacoplada por entorno;
- overrides locales no versionados;
- conexion a MySQL con PDO;
- estructura minima de base de datos;
- `index.php` raiz como punto de entrada inicial;
- validacion visible de entorno y conexion a base de datos.

La base local validada es:

```text
team_di_paola_db
```

## 2. Estructura creada

```text
/
  config/
    config.hostinger.example.php
    config.php
    config.local.example.php
    database.php

  database/
    schema.sql
    seed.sql

  docs/
    plans/
      fase-00.md
      fase-01.md
      fase-02.md
      fase-03.md

  index.php
  .htaccess
  .gitignore
  README.md
```

## 3. Archivos implementados

### `config/config.php`

Responsabilidades:

- detectar automaticamente si el sistema corre en entorno local;
- definir la constante `ENVIRONMENT`;
- definir la constante `URL_BASE`;
- cargar opcionalmente `config/config.local.php` si existe;
- permitir overrides sin exponer configuracion local en Git.

Regla de deteccion local:

```text
localhost
127.0.0.1
[::1]
```

Si el host coincide con alguno de esos valores, el entorno se define como:

```text
development
```

En caso contrario:

```text
production
```

### `config/config.local.example.php`

Plantilla versionada para overrides locales.

Contenido esperado:

```php
<?php

declare(strict_types=1);

// Copiar como config.local.php solo si se necesitan overrides locales.
// config.local.php no debe subirse al repositorio.
return [
    'environment' => 'development',
    'url_base' => 'http://localhost:8000',
    'database' => [
        'host' => 'localhost',
        'name' => 'team_di_paola_db',
        'user' => 'root',
        'pass' => '',
    ],
];
```

Este archivo no contiene datos sensibles. Sirve como guia para crear manualmente `config/config.local.php` si algun entorno local necesita valores distintos.

### `config/config.hostinger.example.php`

Plantilla versionada para produccion en Hostinger.

Define:

```text
environment: production
url_base: https://ecscom.com.ar
database.host: localhost
database.name: u347774250_dipaoladb
database.user: u347774250_dipaoladb
database.pass: CAMBIAR_EN_SERVIDOR
```

Uso esperado:

1. copiar este archivo como `config/config.local.php` dentro del servidor;
2. reemplazar `CAMBIAR_EN_SERVIDOR` por la password real;
3. mantener `config/config.local.php` fuera de Git.

### `config/config.local.php`

No se creo el archivo real.

Decision:

- `config.local.php` debe existir solo en maquinas locales cuando sea necesario;
- no debe subirse al repositorio;
- queda ignorado por `.gitignore`.

Uso esperado:

```text
config/config.local.php
```

Puede retornar un array con overrides:

```php
return [
    'environment' => 'development',
    'url_base' => 'http://localhost:8000',
    'database' => [
        'host' => 'localhost',
        'name' => 'team_di_paola_db',
        'user' => 'root',
        'pass' => '',
    ],
];
```

### `config/database.php`

Responsabilidades:

- exponer la funcion `getDB(): PDO`;
- crear una conexion PDO a MySQL;
- usar singleton simple con `static $pdo`;
- usar charset `utf8mb4`;
- activar errores con `PDO::ERRMODE_EXCEPTION`;
- permitir overrides por variables de entorno.
- permitir overrides por el bloque `database` de `config/config.local.php`.

Valores por defecto en `development`:

```text
host: localhost
db: team_di_paola_db
user: root
pass:
```

Variables de entorno soportadas:

```text
TDP_DB_HOST
TDP_DB_NAME
TDP_DB_USER
TDP_DB_PASS
```

Esto permite que en produccion se configuren credenciales sin hardcodearlas dentro del codigo.

En Hostinger tambien queda soportado el flujo APB de archivo local no versionado:

```php
return [
    'environment' => 'production',
    'url_base' => 'https://ecscom.com.ar',
    'database' => [
        'host' => 'localhost',
        'name' => 'u347774250_dipaoladb',
        'user' => 'u347774250_dipaoladb',
        'pass' => 'password_real_solo_en_servidor',
    ],
];
```

## 3.1 Proteccion Apache / Hostinger

Se agrego `.htaccess` en la raiz.

Responsabilidades:

- desactivar listado de directorios;
- usar `index.php` como documento principal;
- bloquear acceso web directo a:
  - `config/`;
  - `database/`;
  - `docs/`;
  - `ejemplo/`;
- bloquear archivos de configuracion comunes como `.env`, `.gitignore`, `composer.json`, `composer.lock`, `package.json` y `package-lock.json`.

Esto es importante porque el flujo de deploy inicial apunta a `public_html`. Si el repo completo vive dentro del document root, los archivos operativos no deben quedar servidos publicamente.

### `database/schema.sql`

Archivo placeholder para el esquema inicial.

Contenido:

```sql
-- esquema inicial del sistema Team Di Paola
```

### `database/seed.sql`

Archivo placeholder para datos iniciales de prueba.

Contenido:

```sql
-- datos iniciales de prueba
```

### `index.php`

Punto de entrada inicial en la raiz del proyecto.

Responsabilidades:

1. cargar `config/config.php`;
2. cargar `config/database.php`;
3. intentar conexion a la base de datos;
4. mostrar estado del sistema;
5. mostrar errores claros en `development`;
6. evitar exponer detalles sensibles en `production`.

Salida esperada cuando todo funciona:

```text
Sistema Team Di Paola activo
ENV: development
DB: OK
```

Si falla la conexion:

```text
Sistema Team Di Paola activo
ENV: development
DB: ERROR
Detalle: ...
```

En `production`, el detalle tecnico no debe exponer credenciales.

## 4. Ajuste en `.gitignore`

Se agrego:

```gitignore
# Local machine overrides
config/config.local.php
```

Motivo:

- permite tener configuracion local propia;
- evita subir valores especificos de una computadora o servidor;
- mantiene versionado solo el ejemplo seguro `config.local.example.php`.

## 5. Comandos de validacion ejecutados

Se valido sintaxis PHP:

```powershell
C:\xampp\php\php.exe -l config\config.php
C:\xampp\php\php.exe -l config\database.php
C:\xampp\php\php.exe -l config\config.local.example.php
C:\xampp\php\php.exe -l index.php
```

Resultado:

```text
No syntax errors detected
```

Se valido ejecucion desde CLI:

```powershell
C:\xampp\php\php.exe index.php
```

Resultado:

```text
Sistema Team Di Paola activo
ENV: development
DB: OK
```

## 6. Como probar en navegador

Desde la raiz del proyecto:

```powershell
C:\xampp\php\php.exe -S localhost:8000
```

Abrir:

```text
http://localhost:8000
```

Resultado esperado:

```text
Sistema Team Di Paola activo
ENV: development
DB: OK
```

## 7. Criterios QA

| Criterio | Estado |
| --- | --- |
| El proyecto corre desde la raiz | OK |
| No depende de `htdocs` | OK |
| Existe `config/config.php` | OK |
| Existe `config/database.php` | OK |
| Existe `config/config.local.example.php` | OK |
| `config.local.php` queda ignorado | OK |
| Existe plantilla Hostinger sin password real | OK |
| `.htaccess` protege carpetas internas | OK |
| Existe `database/schema.sql` | OK |
| Existe `database/seed.sql` | OK |
| `index.php` prueba la DB | OK |
| PDO usa `utf8mb4` | OK |
| PDO usa excepciones | OK |
| La DB `team_di_paola_db` responde localmente | OK |

## 8. Decisiones tecnicas tomadas

### Desarrollo local desde raiz

Se mantiene el servidor embebido de PHP:

```powershell
C:\xampp\php\php.exe -S localhost:8000
```

Esto evita acoplar el proyecto a la estructura interna de XAMPP o `htdocs`.

### Configuracion simple por constantes

Se definieron:

```text
ENVIRONMENT
URL_BASE
```

Es una solucion suficiente para esta etapa y evita introducir frameworks o contenedores de configuracion innecesarios.

### PDO como unica via de conexion

La conexion se centraliza en `getDB()`.

Esto prepara el proyecto para:

- reutilizar la misma conexion;
- testear endpoints futuros;
- evitar conexiones duplicadas;
- mantener un unico lugar para credenciales y opciones PDO.

### Variables de entorno para produccion

Las variables `TDP_DB_*` permiten que Hostinger o cualquier entorno futuro configure credenciales fuera del repositorio.

### Errores visibles solo en desarrollo

En `development`, el error de conexion muestra detalle tecnico para acelerar debugging.

En `production`, el mensaje debe ser generico para evitar exponer informacion interna.

## 9. Estado final de la Fase 03

La Fase 03 queda completa.

El proyecto ya tiene una base minima ejecutable:

- arranca desde la raiz;
- detecta entorno;
- soporta override local;
- conecta a MySQL;
- valida la base `team_di_paola_db`;
- queda preparado para avanzar a una proxima fase de estructura de aplicacion o modelo de datos.
