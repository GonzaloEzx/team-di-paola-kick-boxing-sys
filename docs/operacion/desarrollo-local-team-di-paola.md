# Manual de Desarrollo Local — Team Di Paola Kick Boxing

> Estado: vigente  
> Audiencia: desarrollo, operación, agentes  
> Fuente de verdad: sí, para setup local  
> Relación: manual operativo del entorno local  
> Última revisión: 2026-04-12

## Objetivo

Dejar `team-di-paola-kick-boxing-sys` funcionando en una máquina local sin depender de `htdocs` y sin tocar una futura operativa de producción.

Este manual cubre:

- configuración de entorno local;
- base de datos local con XAMPP o MySQL;
- arranque rápido del sitio desde la raíz del proyecto;
- preparación para futuro deploy en Hostinger.

## Flujo local adoptado

Este proyecto trabajará con esta lógica:

- código fuente en carpeta local del repo;
- ejecución local desde la raíz del proyecto con `php -S`;
- base local independiente: `team_di_paola_db`;
- cuando exista producción, el deploy será a Hostinger por Git + SSH o flujo equivalente documentado.

## Archivos clave esperados

Estos son los archivos que deberían gobernar el entorno local:

- `config/config.php`
- `config/config.local.example.php`
- `config/config.local.php`
- `config/config.hostinger.example.php`
- `config/database.php`
- `database/schema.sql`
- `database/seed.sql`

> Si alguno todavía no existe en `team-di-paola-kick-boxing-sys`, deberá crearse en Fase 3 respetando esta misma lógica de doble entorno.

## Cómo debe funcionar el doble entorno

El proyecto debería soportar dos modos:

- `production`: para el dominio real futuro
- `development`: para `localhost`, `127.0.0.1`, `[::1]` o `php -S`

Reglas esperadas:

1. `config/config.php` detecta automáticamente si el request es local.
2. Si existe `config/config.local.php`, ese archivo puede forzar `environment` y `url_base`.
3. `config/database.php` usa defaults locales cuando `ENVIRONMENT === development`.
4. `config/database.php` también admite override por variables de entorno `TDP_DB_*`.
5. En Hostinger, si no se usan variables de entorno, `config/config.local.php` puede declarar un bloque `database`.

## Configuración recomendada

### 1. Config local opcional

Crear una plantilla versionada:

- `config/config.local.example.php`

Y usar un override no versionado:

- `config/config.local.php`

Ejemplo recomendado:

```php
<?php

return [
    "environment" => "development",
    "url_base" => "http://localhost:8000",
];
```

`config/config.local.php` debe quedar ignorado por Git.

### 2. Config productiva Hostinger

Existe una plantilla segura:

- `config/config.hostinger.example.php`

Uso esperado en servidor:

1. copiarla como `config/config.local.php`;
2. completar la contraseña real de MySQL solo en el servidor;
3. no subir `config/config.local.php` al repositorio.

### 3. Base local por defecto

En modo `development`, `config/database.php` debería intentar conectarse a:

- host: `localhost`
- base: `team_di_paola_db`
- usuario: `root`
- password: vacía

Si la base local usa otros valores, permitir override con:

- `TDP_DB_HOST`
- `TDP_DB_NAME`
- `TDP_DB_USER`
- `TDP_DB_PASS`

## Setup rápido con XAMPP

Esta es la opción más práctica en Windows.

### Componentes mínimos

Al instalar XAMPP alcanza con:

- `Apache`
- `MySQL`
- `PHP`
- `phpMyAdmin`

No hace falta instalar:

- `FileZilla FTP Server`
- `Mercury Mail Server`
- `Tomcat`
- `Perl`
- `Webalizer`
- `Fake Sendmail`

### Ruta recomendada

Instalar en:

```text
C:\xampp
```

## Crear la base local

### Opción A: phpMyAdmin

1. iniciar `MySQL` desde XAMPP  
2. abrir `http://localhost/phpmyadmin`  
3. crear la base `team_di_paola_db`  
4. importar `database/schema.sql`  
5. importar `database/seed.sql` si corresponde  

### Opción B: consola con el cliente de XAMPP

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS team_di_paola_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

Para importar desde `cmd.exe`:

```cmd
C:\xampp\mysql\bin\mysql.exe -u root team_di_paola_db < database\schema.sql
C:\xampp\mysql\bin\mysql.exe -u root team_di_paola_db < database\seed.sql
```

Para importar desde PowerShell:

```powershell
Get-Content database\schema.sql | C:\xampp\mysql\bin\mysql.exe -u root team_di_paola_db
Get-Content database\seed.sql | C:\xampp\mysql\bin\mysql.exe -u root team_di_paola_db
```

## Arranque del sitio

Con la base ya creada:

```powershell
C:\xampp\php\php.exe -S localhost:8000
```

Abrir después:

```text
http://localhost:8000
```

## Variables de entorno opcionales

Si la base local no usa `root` sin password:

```powershell
$env:TDP_DB_HOST='localhost'
$env:TDP_DB_NAME='team_di_paola_db'
$env:TDP_DB_USER='tu_usuario'
$env:TDP_DB_PASS='tu_password'
C:\xampp\php\php.exe -S localhost:8000
```

## Validaciones útiles

```powershell
C:\xampp\php\php.exe -l config\config.php
C:\xampp\php\php.exe -l index.php
C:\xampp\php\php.exe -l config\database.php
```

Si querés validar que la base exista:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "SHOW DATABASES;"
```

## Problemas frecuentes

### `mysql` no es reconocido

Solución más simple:

- usar `C:\xampp\mysql\bin\mysql.exe`
- o trabajar desde `phpMyAdmin`

### PowerShell no acepta `<`

Opciones correctas:

- usar `cmd.exe` para imports con `<`
- o usar `Get-Content ... | mysql.exe` en PowerShell

### El sitio abre pero no conecta a la base

Revisar:

- `MySQL` realmente iniciado
- nombre de base: `team_di_paola_db`
- usuario/password correctos
- `config/config.local.php` o variables `TDP_DB_*`

## Resultado esperado

Si el entorno local quedó bien:

- `C:\xampp\php\php.exe -S localhost:8000` responde desde la raíz del proyecto
- `ENVIRONMENT` queda en `development`
- el sitio conecta contra `team_di_paola_db` local
- no depende de `htdocs`
- queda listo para un futuro flujo de deploy a Hostinger
