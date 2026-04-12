# Fase 0 - Auditoria del Proyecto Base

Proyecto nuevo: `team-di-paola-kick-boxing-sys`  
Proyecto base auditado: `ejemplo/piel-morena-sys`  
Fecha de auditoria: 2026-04-12  
Estado: documentacion de analisis, sin implementacion de funcionalidades nuevas

## 1. Resumen ejecutivo

El proyecto base `piel-morena-sys` es reutilizable como punto de partida, pero no como copia directa. La base tecnica tiene valor: autenticacion, sesiones, panel administrativo, CRUDs, productos, caja, usuarios, staff, configuracion, notificaciones y estructura API. El dominio funcional, en cambio, esta fuertemente ligado a una estetica: citas, servicios esteticos, tratamientos, jornadas, depilacion, faciales, pestanas, manicuria, peluqueria, testimonios y branding.

La recomendacion es reutilizar el proyecto como esqueleto operativo, no como modelo de negocio final. Para `team-di-paola-kick-boxing-sys` conviene hacer una migracion controlada:

- limpiar artefactos locales y datos del negocio anterior;
- consolidar el esquema SQL real;
- renombrar configuracion, marca y constantes;
- conservar los modulos transversales;
- redisenar el dominio central alrededor de alumnos, staff, membresias, pagos, asistencias, clases, productos y ventas.

No conviene iniciar la Fase 1 programando funcionalidades nuevas sin antes estabilizar nombres, tablas y limites de reutilizacion. La mayor transformacion necesaria esta en el modulo de reservas/citas/servicios: un gimnasio funciona principalmente con membresias, cuotas recurrentes, clases y asistencias, no con turnos individuales de estetica.

## 2. Mapa de estructura actual

Estructura principal detectada:

```text
piel-morena-sys/
├── index.php                  # landing publica one-page
├── login.php                  # login
├── registro.php               # registro cliente
├── mi-cuenta.php              # perfil cliente + historial de citas
├── reservar.php               # wizard publico de reserva
├── README.md
├── package.json               # solo dependencia @playwright/test
├── package-lock.json
├── config/
│   ├── config.php             # constantes, URLs, datos negocio, entorno
│   ├── config.local.php       # override local existente
│   ├── config.local.example.php
│   ├── database.php           # conexion PDO real local/produccion
│   └── database.example.php
├── includes/
│   ├── init.php               # bootstrap: sesion, config, DB, helpers
│   ├── auth.php               # login, roles, registro, Google, codigos, password
│   ├── functions.php          # JSON, sanitizacion, CSRF, helpers
│   ├── mail_helper.php        # emails + notificaciones
│   ├── header.php             # layout publico
│   └── footer.php             # footer publico
├── admin/
│   ├── index.php              # dashboard admin
│   ├── includes/
│   │   ├── admin_header.php   # sidebar, permisos, layout admin
│   │   └── admin_footer.php
│   ├── views/
│   │   ├── caja.php
│   │   ├── citas.php
│   │   ├── clientes.php
│   │   ├── configuracion.php
│   │   ├── empleados.php
│   │   ├── galeria.php
│   │   ├── jornadas.php
│   │   ├── mensajes.php
│   │   ├── mi-horario.php
│   │   ├── mis-citas.php
│   │   ├── productos.php
│   │   ├── promociones.php
│   │   ├── reportes.php
│   │   ├── servicios.php
│   │   └── testimonios.php
│   └── assets/
│       ├── css/
│       │   ├── admin.css
│       │   └── premium-admin.css
│       └── js/
│           └── admin.js
├── api/
│   ├── admin/
│   ├── analytics/
│   ├── auth/
│   ├── caja/
│   ├── citas/
│   ├── clientes/
│   ├── jornadas/
│   ├── notificaciones/
│   ├── promociones/
│   ├── servicios/
│   └── contacto.php
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── cron/
│   └── recordatorios.php
├── database/
│   ├── cleanup_pre_golive.sql
│   ├── schema.sql
│   ├── schema.hostinger.sql
│   ├── seed.sql
│   ├── seed.hostinger.sql
│   ├── temp_datos_extra.sql
│   └── migrations/
├── docs/
├── templates/
│   └── email/
├── uploads/
├── node_modules/
├── .playwright-cli/
├── .playwright-mcp/
├── .agents/
├── .claude/
└── .git/
```

Archivos criticos:

| Archivo | Rol actual |
|---|---|
| `includes/init.php` | Inicializa constante de seguridad, sesion, config, DB, helpers, auth y mail helper. |
| `config/config.php` | Define entorno, URL base, rutas, datos del negocio, sesion, timezone y Google OAuth. |
| `config/database.php` | Conexion PDO real con defaults locales/produccion y soporte por variables de entorno. |
| `includes/auth.php` | Login, logout, registro, roles, Google OAuth, codigos de verificacion, cambio/reset de password. |
| `includes/functions.php` | Sanitizacion, respuesta JSON, helpers de sesion, CSRF, formato de precio/fecha/hora. |
| `admin/includes/admin_header.php` | Control de acceso al panel y layout de sidebar/topbar. |
| `admin/assets/js/admin.js` | Helpers JS de admin: sidebar, DataTables, SweetAlert, `apiCall`, badges, formatos. |
| `database/schema.sql` | Esquema base, pero no parece totalmente sincronizado con migraciones recientes. |
| `database/migrations/` | Cambios incrementales importantes: Google ID, verificacion, jornadas, promociones/packs. |
| `api/admin/*.php` | Endpoints CRUD y operaciones administrativas. |
| `api/auth/*.php` | Endpoints de autenticacion. |
| `templates/email/*.php` | Templates HTML de emails transaccionales. |
| `cron/recordatorios.php` | Recordatorios de citas para el negocio anterior. |

Artefactos que no conviene arrastrar a una base limpia:

- `.git/`;
- `node_modules/`;
- `.playwright-cli/`;
- `.playwright-mcp/`;
- `.agents/`;
- `.claude/`;
- `docs/temp/`;
- `docs/para-test/`;
- uploads reales del negocio anterior;
- logos, imagenes, galeria y assets de marca anterior;
- seeds con datos de estetica.

## 3. Modulos detectados

### Stack tecnologico

| Capa | Tecnologia detectada |
|---|---|
| Backend | PHP vanilla procedural |
| Base de datos | MySQL/MariaDB con PDO |
| Frontend publico | Bootstrap 5.3, Bootstrap Icons, jQuery, SweetAlert2, Google Fonts |
| Admin UI | DataTables, Chart.js, FullCalendar, SweetAlert2 |
| Auth | Sesiones PHP, `password_hash`, `password_verify`, Google OAuth opcional |
| Email | Funcion nativa `mail()` con templates HTML |
| Cron | Script PHP en `cron/recordatorios.php` |
| Testing/dev | `@playwright/test` en `package.json` |

No se encontro evidencia de Laravel, Symfony, Composer, Vite, Webpack, router central, ORM ni framework backend.

### Modulos funcionales

| Modulo | Archivos/tablas principales | Estado para reutilizacion |
|---|---|---|
| Landing publica | `index.php`, `includes/header.php`, `includes/footer.php`, `assets/css/*`, `assets/js/*` | Reutilizable como estructura, contenido a reemplazar. |
| Auth | `api/auth/*`, `includes/auth.php`, `login.php`, `registro.php` | Muy reutilizable con rebranding y hardening. |
| Mi cuenta | `mi-cuenta.php`, `api/clientes/actualizar-perfil.php` | Reutilizable como portal de alumno, requiere rediseño funcional. |
| Usuarios | tabla `usuarios` | Reutilizable como base. |
| Roles | `usuarios.rol ENUM('admin','empleado','cliente')` | Parcial; puede quedar corto para permisos finos. |
| Admin | `admin/index.php`, `admin/views/*`, `api/admin/*` | Muy reutilizable como layout y CRUDs. |
| Clientes | `admin/views/clientes.php`, `api/admin/clientes.php` | Adaptar a alumnos/clientes. |
| Empleados | `admin/views/empleados.php`, `api/admin/empleados.php` | Adaptar a staff/profesores. |
| Servicios | `servicios`, `categorias_servicios`, `api/admin/servicios.php` | Refactor fuerte. |
| Citas/reservas | `citas`, `reservar.php`, `api/citas/*`, `admin/views/citas.php` | Rediseñar antes de reutilizar. |
| Jornadas | `jornadas`, `api/admin/jornadas.php`, `api/jornadas/disponibles.php` | Adaptable a eventos/seminarios. |
| Productos | `productos`, `api/admin/productos.php` | Muy reutilizable. |
| Caja | `caja_movimientos`, `cierres_caja`, `api/admin/caja.php`, `api/caja/*` | Reutilizable como base financiera. |
| Promociones/packs | `promociones`, `promocion_servicios`, `api/admin/promociones.php` | Adaptable a packs/promos del gimnasio. |
| Reportes | `admin/views/reportes.php`, `api/analytics/*` | Reutilizable con metricas nuevas. |
| Contacto | `contacto_mensajes`, `api/contacto.php`, `admin/views/mensajes.php` | Reutilizable. |
| Notificaciones | `notificaciones`, `api/notificaciones/*` | Reutilizable con nuevos tipos. |
| Emails | `templates/email/*`, `includes/mail_helper.php` | Reutilizable con nuevos textos. |
| Uploads | `api/admin/upload.php`, `uploads/` | Reutilizable con revision de seguridad. |

## 4. Reutilizable vs no reutilizable

### Reutilizable con cambios menores

- `includes/init.php`: bootstrap general de sesion/config/DB.
- `config/config.php`: patron de entorno y constantes, pero requiere renombrar `PIEL_MORENA` y datos de negocio.
- `config/database.example.php`: plantilla util.
- `includes/functions.php`: `responder_json`, sanitizacion, helpers de sesion, formato.
- `includes/auth.php`: login, sesiones, roles, registro, recuperacion de password.
- `api/auth/*`: base para login, registro, logout, password, codigos y Google OAuth.
- Tabla `usuarios`: base para admin, staff y alumnos.
- `admin/includes/admin_header.php`: layout admin reutilizable tras rebranding.
- `admin/assets/js/admin.js`: helpers de API, DataTables y SweetAlert.
- CRUD de clientes/empleados: buena base para alumnos y staff.
- Productos e inventario: muy reutilizable para indumentaria, guantes, vendas, merchandising o suplementos.
- Caja: reutilizable como base de pagos, ventas, ingresos y egresos.
- Contacto/mensajes: reutilizable.
- Notificaciones y emails: reutilizables tras cambiar textos y tipos.
- Tabla `configuracion`: util para datos del gimnasio, horarios, redes, parametros operativos.

### Reutilizable con adaptacion fuerte

- `citas` / reservas: puede inspirar reservas de clases o historial de actividad, pero no cubre membresias ni asistencias por si solo.
- `servicios` / `categorias_servicios`: puede mapearse a actividades, clases, planes o servicios, pero el naming esta ligado a estetica.
- `jornadas`: puede servir para seminarios, eventos, examenes o clases especiales.
- Promociones/packs: puede virar a packs de clases, combos comerciales o promociones por temporada.
- `mi-cuenta.php`: puede convertirse en portal del alumno con membresia, pagos, asistencias y clases.
- Landing: estructura reutilizable, contenido casi totalmente reemplazable.
- Dashboard: layout reutilizable, KPIs a rehacer.
- Reportes: base tecnica util, metricas a redisenar.

### No reutilizable o a reemplazar

- Branding completo de Piel Morena: nombre, logos, imagenes, favicon, colores, textos, SEO, redes.
- Schema.org actual de `BeautySalon`.
- Catalogo de tratamientos esteticos: depilacion, faciales, corporales, frio, pestanas, manicuria, peluqueria.
- Testimonios y galeria actuales.
- Seeds de estetica: `seed.sql`, `seed.hostinger.sql`, `temp_datos_extra.sql`.
- Documentacion del negocio anterior salvo como referencia tecnica.
- Cron de recordatorio de citas tal cual.
- Analytics de consultas de precio como metrica principal.
- Tabla `consultas_precio` como eje del sistema.
- Reglas de jornadas orientadas a equipamiento alquilado/personal eventual de estetica.

## 5. Mapeo negocio viejo -> negocio nuevo

| Concepto viejo en `piel-morena-sys` | Equivalente posible en `team-di-paola-kick-boxing-sys` | Accion recomendada |
|---|---|---|
| `usuarios` | Usuarios del sistema | Reutilizar |
| `rol admin` | Administrador general | Reutilizar |
| `rol empleado` | Staff, recepcion, profesor | Adaptar |
| `rol cliente` | Alumno, cliente, socio | Adaptar o renombrar semanticamente |
| `clientes.php` | Alumnos/clientes | Adaptar |
| `empleados.php` | Staff/profesores | Adaptar |
| `servicios` | Clases, actividades, planes o servicios | Refactorizar |
| `categorias_servicios` | Disciplinas/categorias: kick boxing, boxeo, funcional | Adaptar |
| `citas` | Reservas de clase, asistencias o clases tomadas | Redisenar; no reutilizar directo |
| Estados de cita | Estados de reserva/asistencia | Adaptar parcialmente |
| `horarios` | Horarios de staff o grilla semanal | Adaptar fuerte |
| `jornadas` | Seminarios, eventos, clases especiales, examenes | Adaptar |
| `productos` | Indumentaria, guantes, vendas, merchandising | Reutilizar |
| `caja_movimientos` | Pagos, ventas, egresos | Reutilizar como base |
| `cierres_caja` | Cierre diario de caja | Reutilizar |
| `promociones` | Promociones, packs de clases, combos | Adaptar |
| `promocion_servicios` | Componentes de pack/beneficio | Adaptar |
| `notificaciones` | Avisos de cuota, vencimientos, clases | Reutilizar |
| `contacto_mensajes` | Consultas web | Reutilizar |
| `testimonios` | Testimonios de alumnos | Adaptar contenido |
| `consultas_precio` | Interes comercial web | Opcional o eliminar |
| `mi-cuenta.php` | Portal del alumno | Adaptar fuerte |
| `reservar.php` | Reserva o inscripcion a clase | Rehacer sobre nuevo modelo |
| `recordatorios.php` | Recordatorios de cuota/clase | Adaptar |
| `BeautySalon` | `SportsActivityLocation`, `Gym` o `LocalBusiness` | Reemplazar |
| Piel Morena Estetica | Team Di Paola Kick Boxing | Reemplazar globalmente |

## 6. Entidades sugeridas para el nuevo sistema

| Entidad | Proposito |
|---|---|
| `usuarios` | Identidad comun para admin, staff y alumnos. |
| `roles` | Permisos mas flexibles que un ENUM fijo. |
| `usuario_roles` | Permitir mas de un rol por usuario si hace falta. |
| `staff` | Perfil operativo de profesores, recepcionistas o personal administrativo. |
| `alumnos` | Datos propios del alumno: DNI, fecha de nacimiento, contacto de emergencia, observaciones, apto fisico. |
| `planes` | Tipos de membresia: mensual, libre, por cantidad de clases, semanal, promo familiar. |
| `membresias` | Vinculo activo entre alumno y plan. |
| `cuotas` o `periodos_membresia` | Periodos a cobrar, fecha de vencimiento y estado de deuda. |
| `pagos` | Pagos de cuotas, membresias, inscripciones u otros conceptos. |
| `actividades` | Kick boxing, boxeo, funcional, entrenamiento personalizado u otras disciplinas. |
| `horarios_clase` | Grilla semanal recurrente de actividades. |
| `clases` | Instancia concreta de una clase con fecha, horario, cupo y profesor. |
| `reservas_clase` | Reserva de cupo si se decide manejar cupos por clase. |
| `asistencias` | Check-in del alumno por fecha/hora, clase o acceso libre. |
| `productos` | Indumentaria, guantes, vendas, remeras, merchandising, suplementos. |
| `ventas` | Cabecera de venta, cliente opcional, total, metodo de pago. |
| `venta_items` | Detalle de productos vendidos. |
| `stock_movimientos` | Entradas, salidas y ajustes de stock con trazabilidad. |
| `caja_movimientos` | Ingresos y egresos financieros. Puede conservarse adaptada. |
| `cierres_caja` | Cierre diario o por turno. |
| `sucursales` o `sedes` | Opcional, recomendable si puede crecer. |
| `notificaciones` | Avisos internos o al alumno: vencimientos, pagos, clases, promociones. |
| `auditoria` | Registro de acciones administrativas: quien hizo que y cuando. |
| `configuracion` | Parametros generales del gimnasio. |

### Preparacion actual frente a necesidades nuevas

| Necesidad nueva | Estado del proyecto base | Diagnostico |
|---|---|---|
| Multirol | Parcial | Existe `usuarios.rol ENUM('admin','empleado','cliente')`, pero no permisos finos. |
| Staff operativo | Parcial | Hay empleados, horarios y citas asignadas; falta modelo de profesor/clase/asistencia. |
| Suscripciones/membresias | No preparado | No existen planes, membresias, cuotas ni vencimientos. |
| Pagos/cuotas | Parcial | Caja registra movimientos, pero no deuda ni periodo abonado. |
| Asistencia/control de acceso | No existe | Hay que implementarlo desde modelo nuevo. |
| Venta de productos | Preparado basico | Hay productos, stock y venta con decremento. |
| Trazabilidad administrativa | Parcial | Caja guarda `id_usuario`; falta auditoria general. |
| Sitio publico | Preparado como estructura | Contenido, marca y SEO deben rehacerse. |
| Panel admin | Muy aprovechable | Layout, tablas, CRUDs y helpers sirven como base. |

## 7. Riesgos y deuda tecnica

### Riesgos de acoplamiento al negocio anterior

- Marca hardcodeada en muchos lugares: `Piel Morena`, `piel_morena`, dominio anterior, emails, Instagram, textos, imagenes y docs.
- Constante de seguridad `PIEL_MORENA`, que deberia renombrarse.
- Textos de landing orientados a estetica: belleza, tratamientos, depilacion, faciales, pestanas, manicuria.
- Seeds con servicios reales o de prueba del negocio anterior.
- Templates de email con textos de cita estetica.
- Cron de recordatorio de citas.
- Schema.org actual de salon de belleza.

### Riesgos tecnicos

- `schema.sql` y `schema.hostinger.sql` no parecen estar sincronizados con migraciones recientes. Ejemplo: los schemas conservan una forma vieja de `promociones` con descuentos, mientras la API actual usa packs con `precio_pack`, `id_servicio_generado` y tabla pivot `promocion_servicios`.
- No hay esquema canonico unico claramente confiable.
- Las migraciones incluyen supuestos por IDs concretos de produccion, por ejemplo servicios/categorias especificas de estetica.
- Lógica de negocio mezclada en vistas PHP y endpoints.
- No hay capa formal de servicios/modelos.
- No hay router central ni middleware unico de permisos.
- Permisos repetidos archivo por archivo.
- CSRF existe como helper, pero no se ve aplicado de forma sistematica en APIs JSON.
- Login sin rate limit explicito.
- Upload valida extension/MIME/tamano, pero conviene revisar proteccion de ejecucion en `uploads`.
- Algunos modulos usan borrado fisico o deletes internos.
- ENUMs rigidos para roles, estados y metodos de pago.
- El modelo actual de citas controla solapamiento global por horario; no contempla cupos, profesor, sala, clase grupal o multiples asistencias.
- No hay auditoria transversal.
- Hay dependencias y carpetas locales que no deben formar parte de una base limpia.

## 8. Plan recomendado de migracion

1. Crear una copia limpia del proyecto base.
   - Excluir `.git`, `node_modules`, `.agents`, `.claude`, `.playwright-*`, docs temporales y uploads reales.
   - No usar seeds ni assets de marca anterior como base final.

2. Congelar inventario tecnico real.
   - Vistas activas.
   - Endpoints activos.
   - Tablas esperadas por codigo.
   - Migraciones aplicables.
   - Dependencias externas.

3. Consolidar base de datos.
   - Definir si manda `schema.sql`, `schema.hostinger.sql` o migraciones.
   - Generar un schema canonico nuevo.
   - Crear seed minimo para el gimnasio.
   - Eliminar supuestos de IDs de produccion del negocio anterior.

4. Rebranding tecnico.
   - `PIEL_MORENA` -> constante nueva, por ejemplo `TEAM_DI_PAOLA`.
   - `NOMBRE_NEGOCIO`, `URL_BASE`, email, telefono, direccion, redes.
   - Logos, favicon, imagenes, metadatos y Schema.org.
   - Titulos de paginas y textos visibles.

5. Rebranding funcional.
   - Decidir si el sistema usara "cliente", "alumno" o "socio".
   - Decidir si el staff se llamara "empleado", "staff" o "profesor".
   - No renombrar `citas` automaticamente hasta definir si el concepto nuevo sera clase, reserva o asistencia.

6. Separar modulos a conservar.
   - Auth.
   - Admin layout.
   - Usuarios.
   - Staff/alumnos.
   - Productos.
   - Caja.
   - Contacto.
   - Configuracion.
   - Notificaciones.

7. Aislar modulos del negocio anterior.
   - Citas.
   - Servicios esteticos.
   - Jornadas esteticas.
   - Promociones/packs actuales.
   - Analytics de consultas de precio.

8. Disenar modelo nuevo antes de implementar.
   - Planes.
   - Membresias.
   - Cuotas.
   - Pagos.
   - Asistencias.
   - Clases.
   - Horarios.
   - Staff/profesores.
   - Productos/ventas.
   - Auditoria.

9. Rehacer dashboard.
   - Alumnos activos.
   - Cuotas vencidas.
   - Pagos del mes.
   - Asistencias del dia.
   - Clases de hoy.
   - Stock bajo.
   - Ventas del mes.

10. Iniciar implementacion incremental recien despues de estabilizar dominio y schema.

## 9. Propuesta de Fase 1

La Fase 1 deberia ser preparacion tecnica y funcional del nuevo sistema, con cambios controlados y sin intentar resolver todo el gimnasio de una vez.

### Objetivos de Fase 1

- Crear una base limpia del proyecto.
- Eliminar acoplamientos evidentes del negocio anterior.
- Mantener funcionando login, panel admin y CRUDs esenciales.
- Definir el schema inicial del gimnasio.
- Preparar el terreno para membresias, pagos y asistencias.

### Alcance recomendado

1. Crear copia limpia del sistema base.
2. Renombrar configuracion general y constante de seguridad.
3. Reemplazar datos principales del negocio.
4. Consolidar schema SQL inicial.
5. Mantener y adaptar:
   - login;
   - registro;
   - panel admin;
   - CRUD usuarios;
   - CRUD staff;
   - CRUD alumnos/clientes;
   - productos;
   - caja basica;
   - contacto;
   - configuracion.
6. Crear o preparar tablas nuevas:
   - `planes`;
   - `membresias`;
   - `cuotas`;
   - `pagos`;
   - `actividades`;
   - `clases`;
   - `asistencias`;
   - `auditoria`.
7. Dejar en pausa:
   - reservas/citas esteticas;
   - jornadas;
   - promociones/packs viejos;
   - landing completa anterior;
   - analytics de consultas de precio.

### Resultado esperado de Fase 1

Al terminar la Fase 1 deberia existir un sistema base ya identificado como `team-di-paola-kick-boxing-sys`, con login y admin operativos, estructura limpia, base de datos coherente y dominio inicial preparado para construir:

- gestion de alumnos;
- gestion de staff;
- planes y membresias;
- pagos/cuotas;
- asistencias;
- productos/ventas;
- dashboard administrativo del gimnasio.

## Conclusion

El proyecto `piel-morena-sys` tiene una base tecnica suficientemente buena para reutilizar. La reutilizacion inteligente consiste en conservar la infraestructura operativa y descartar o redisenar el dominio estetico.

La base mas valiosa es:

- autenticacion;
- sesiones;
- panel admin;
- usuarios;
- staff/clientes;
- productos;
- caja;
- configuracion;
- contacto;
- notificaciones;
- helpers generales.

La parte mas riesgosa de reutilizar sin rediseño es:

- citas;
- servicios;
- jornadas;
- promociones/packs;
- landing y textos;
- analiticas basadas en consultas de precio.

Para el gimnasio, el centro del sistema debe pasar a ser membresias, pagos recurrentes, asistencia/control de acceso, clases/actividades y venta de productos.
