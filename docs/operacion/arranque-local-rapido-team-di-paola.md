# Arranque Local Rápido — Team Di Paola Kick Boxing

> Estado: uso local  
> Audiencia: desarrollo  
> Fuente de verdad: no  
> Última revisión: 2026-04-12

## Comando recomendado en esta máquina

Desde la **raíz del proyecto** `team-di-paola-kick-boxing-sys`, levantar el sitio con el PHP de XAMPP:

```powershell
C:\xampp\php\php.exe -S localhost:8000
```

Luego abrir:

```text
http://localhost:8000
```

## Importante

- Levantar el servidor **desde la carpeta raíz del proyecto**, no desde `htdocs`.
- No usar el PHP global de otra instalación si puede faltar alguna extensión.
- El flujo local para este proyecto será:
  - código en carpeta raíz del repo;
  - servidor local con `php -S`;
  - base local `team_di_paola_db`;
  - deploy a Hostinger más adelante.

## Requisito previo

Antes de levantar el sitio:

- `MySQL` debe estar corriendo en XAMPP
- la base local `team_di_paola_db` debe existir
- la configuración local debe apuntar a esa base

## Resumen corto

Checklist rápido:

1. iniciar `MySQL` en XAMPP  
2. ubicarse en la raíz del proyecto  
3. correr `C:\xampp\php\php.exe -S localhost:8000`  
4. abrir `http://localhost:8000`
