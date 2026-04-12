# Fase 1 - Definicion Funcional del Sistema

Proyecto: `team-di-paola-kick-boxing-sys`  
Repositorio: `https://github.com/GonzaloEzx/team-di-paola-kick-boxing-sys.git`  
Fecha: 2026-04-12  
Estado: definicion funcional, operativa y estructural. Sin programacion.

## Objetivo

Definir como debe funcionar el sistema para una academia de artes marciales y kick boxing, priorizando operacion diaria simple, control administrativo y crecimiento ordenado.

La Fase 1 no implementa codigo. Define:

- actores;
- flujos reales de uso;
- modulos;
- modelo de dominio;
- experiencia operativa;
- reglas de negocio;
- arquitectura propuesta;
- KPIs;
- decisiones criticas.

Principio rector: APB, a prueba de boludos. El sistema debe ser rapido, claro y dificil de usar mal.

## 1. Actores

### Admin

Responsable de administrar el negocio completo.

Responsabilidades:

- crear y editar alumnos, staff, planes, productos y clases;
- controlar pagos, cuotas vencidas, caja y ventas;
- revisar metricas del negocio;
- corregir errores operativos;
- configurar reglas del sistema;
- auditar acciones criticas.

Permisos:

- acceso total;
- crear, editar, desactivar y consultar todo;
- ver reportes financieros;
- modificar configuracion general;
- autorizar excepciones operativas.

Necesidades reales:

- saber quien debe plata;
- saber cuanto ingreso;
- saber cuantos alumnos activos hay;
- controlar stock;
- detectar problemas rapido sin revisar planillas;
- corregir errores sin borrar historial.

### Staff / Recepcion

Responsable de la operacion diaria.

Responsabilidades:

- buscar alumnos;
- hacer check-in;
- registrar pagos;
- vender productos;
- ver cuotas vencidas;
- cargar alumnos nuevos si el admin lo permite;
- resolver casos simples de acceso.

Permisos:

- ver alumnos;
- registrar asistencia;
- registrar pagos;
- registrar ventas;
- consultar estado de membresia;
- no borrar informacion critica;
- no modificar configuracion global.

Necesidades reales:

- operar rapido;
- ver estado del alumno en segundos;
- evitar errores;
- no navegar por muchas pantallas;
- tener botones claros: `Entra`, `No entra`, `Registrar pago`, `Vender producto`.

### Profesor

Responsable de clases y asistencia deportiva.

Responsabilidades:

- ver sus clases;
- ver alumnos presentes;
- marcar asistencia si recepcion no lo hizo;
- consultar cupos o inscriptos si aplica;
- registrar observaciones simples.

Permisos:

- ver clases asignadas;
- marcar asistencia;
- ver datos basicos del alumno;
- no acceder a caja completa;
- no modificar configuracion.

Necesidades reales:

- saber quien vino;
- no perder tiempo administrativo;
- tener una pantalla simple por clase o por dia.

### Alumno

Usuario final del gimnasio.

Responsabilidades:

- pagar cuota;
- asistir;
- eventualmente reservar clase si el sistema lo permite;
- consultar estado propio si se habilita portal.

Permisos:

- ver su membresia;
- ver pagos propios;
- ver asistencias propias;
- ver horarios;
- actualizar datos basicos si se habilita.

Necesidades reales:

- saber si esta al dia;
- ver horarios;
- recibir aviso de vencimiento;
- no depender siempre de WhatsApp.

## 2. Flujos operativos

### 2.1 Alta de alumno

Objetivo: desde que una persona entra al gimnasio hasta que queda con membresia activa.

Flujo recomendado:

1. Recepcion o Admin abre `Alumnos > Nuevo alumno`.
2. Carga datos minimos:
   - nombre;
   - apellido;
   - telefono;
   - DNI opcional pero recomendado;
   - fecha de nacimiento opcional;
   - contacto de emergencia opcional;
   - observaciones medicas opcionales.
3. Selecciona un plan:
   - mensual;
   - libre;
   - cantidad de clases;
   - semanal;
   - promocional;
   - otro plan configurado.
4. Define fecha de inicio.
5. El sistema calcula vencimiento inicial segun el plan.
6. Se registra el primer pago o queda como `pendiente de pago`.
7. Si paga:
   - se crea membresia activa;
   - se registra pago;
   - se registra movimiento de caja;
   - el alumno queda habilitado para check-in.
8. Si no paga:
   - alumno queda creado;
   - membresia queda pendiente o inactiva;
   - acceso no permitido hasta pago.

Estado final esperado:

- alumno creado;
- membresia asociada;
- estado visible: `activo`, `pendiente`, `vencido` o `inactivo`.

### 2.2 Pago de cuota

Objetivo: registrar un cobro y actualizar el estado de acceso del alumno.

Flujo recomendado:

1. Recepcion busca alumno.
2. Sistema muestra estado:
   - al dia;
   - vence pronto;
   - vencido;
   - sin membresia.
3. Staff presiona `Registrar pago`.
4. Selecciona:
   - concepto: cuota, inscripcion, producto, deuda u otro;
   - periodo abonado;
   - metodo de pago;
   - monto;
   - fecha.
5. Sistema registra el pago.
6. Sistema actualiza membresia:
   - extiende vencimiento;
   - marca cuota como pagada;
   - habilita acceso si corresponde.
7. Sistema registra movimiento de caja.
8. Se muestra confirmacion simple.

Estados sugeridos de cuota:

| Estado | Significado |
|---|---|
| `pendiente` | Cuota generada pero no pagada. |
| `pagada` | Cuota abonada. |
| `vencida` | Fecha de vencimiento superada. |
| `anulada` | Registro corregido por error administrativo. |

### 2.3 Control de acceso / check-in

Objetivo: validar rapido si el alumno puede entrar.

Flujo real de recepcion:

1. Alumno llega.
2. Recepcion busca por:
   - nombre;
   - apellido;
   - DNI;
   - telefono;
   - codigo o ID si se implementa despues.
3. Sistema muestra una tarjeta grande:
   - verde: puede entrar;
   - amarillo: vence pronto o advertencia;
   - rojo: no puede entrar;
   - gris: sin membresia o inactivo.
4. Si puede entrar:
   - recepcion presiona `Registrar ingreso`;
   - se crea asistencia;
   - queda registro de fecha, hora, alumno, staff y clase si aplica.
5. Si no puede entrar:
   - sistema muestra motivo claro:
     - cuota vencida;
     - sin plan activo;
     - alumno inactivo;
     - cupo completo;
     - plan sin clases disponibles.
6. Recepcion puede:
   - registrar pago;
   - pedir autorizacion admin;
   - registrar excepcion con motivo, si se habilita.

Regla APB: la pantalla no debe exigir mas de dos acciones para dejar entrar a un alumno al dia.

### 2.4 Gestion de clases

Objetivo: organizar actividades grupales sin heredar el modelo de citas del sistema anterior.

Flujo recomendado:

1. Admin configura actividades:
   - Kick Boxing;
   - Boxeo;
   - Funcional;
   - entrenamiento personalizado;
   - seminarios o eventos.
2. Admin define horarios recurrentes:
   - actividad;
   - dia de semana;
   - hora inicio;
   - duracion;
   - profesor;
   - cupo opcional.
3. El sistema genera o muestra clases por fecha.
4. Profesor ve sus clases del dia.
5. Recepcion o profesor marca asistencias.
6. Si hay cupo:
   - se valida cupo antes de reservar o antes de check-in.
7. Si no hay reserva:
   - el check-in asigna asistencia a la clase del horario actual o a `acceso libre`.

Decision recomendada: manejar clases sin reserva obligatoria al inicio, con reserva opcional solo para clases con cupo limitado o eventos.

### 2.5 Venta de productos

Objetivo: vender indumentaria o productos impactando stock y caja.

Flujo recomendado:

1. Staff abre `Ventas` o accede desde la ficha del alumno.
2. Selecciona producto:
   - remera;
   - guantes;
   - vendas;
   - short;
   - merchandising;
   - otro producto activo.
3. Indica cantidad.
4. Sistema valida stock.
5. Selecciona metodo de pago.
6. Sistema registra venta.
7. Sistema descuenta stock.
8. Sistema registra movimiento de caja.
9. Si stock queda bajo, aparece alerta.

Regla clave: stock nunca puede quedar negativo.

### 2.6 Operacion diaria del staff

Pantalla principal recomendada para recepcion: `Operacion diaria`.

Debe incluir:

- buscador rapido de alumno;
- boton check-in;
- alumnos con cuota vencida;
- pagos del dia;
- asistencias del dia;
- ventas rapidas;
- clases de hoy;
- alertas de stock bajo.

Dia tipico:

1. Abre panel.
2. Revisa alertas: vencimientos, stock bajo, pagos pendientes.
3. Recibe alumnos.
4. Busca alumno.
5. Hace check-in o registra pago.
6. Vende productos si corresponde.
7. Consulta clases del dia.
8. Al cierre, revisa caja y movimientos.

## 3. Modulos

### Auth

Proposito:

- login, logout, sesiones y recuperacion de acceso.

Funcionalidades:

- login admin/staff/profesor;
- login alumno opcional;
- cambio de password;
- recuperacion de password;
- expiracion de sesion.

Dependencias:

- usuarios;
- roles.

### Usuarios

Proposito:

- identidad comun del sistema.

Funcionalidades:

- crear usuario;
- activar/desactivar;
- asignar rol;
- datos de acceso;
- ultimo acceso.

Dependencias:

- roles.

### Alumnos

Proposito:

- gestionar personas que entrenan.

Funcionalidades:

- alta rapida;
- edicion;
- estado visible;
- historial de pagos;
- historial de asistencias;
- observaciones;
- contacto de emergencia.

Dependencias:

- usuarios;
- membresias;
- pagos;
- asistencias.

### Staff

Proposito:

- gestionar recepcion, profesores y personal interno.

Funcionalidades:

- alta/edicion;
- asignacion de rol operativo;
- asignacion de clases;
- estado activo/inactivo.

Dependencias:

- usuarios;
- clases.

### Membresias

Proposito:

- controlar si un alumno puede acceder.

Funcionalidades:

- asignar plan;
- fecha de inicio;
- fecha de vencimiento;
- estado;
- renovacion;
- suspension;
- baja.

Dependencias:

- alumnos;
- planes;
- pagos.

### Planes

Proposito:

- definir reglas comerciales.

Funcionalidades:

- nombre;
- precio;
- duracion;
- cantidad de clases permitidas si aplica;
- acceso libre o limitado;
- activo/inactivo.

Dependencias:

- membresias.

### Pagos

Proposito:

- registrar cuotas y cobros.

Funcionalidades:

- registrar pago;
- asociar a alumno;
- asociar a membresia o cuota;
- metodo de pago;
- comprobante opcional;
- anulacion controlada.

Dependencias:

- alumnos;
- membresias;
- caja.

### Asistencias

Proposito:

- registrar presencia/check-in.

Funcionalidades:

- check-in rapido;
- asistencia por clase;
- asistencia libre;
- historial;
- validacion de acceso.

Dependencias:

- alumnos;
- membresias;
- clases;
- staff.

### Clases

Proposito:

- organizar actividad deportiva.

Funcionalidades:

- actividad;
- horario;
- profesor;
- cupo;
- asistentes;
- estado: programada, realizada, cancelada.

Dependencias:

- staff;
- actividades;
- asistencias.

### Productos

Proposito:

- administrar indumentaria y stock.

Funcionalidades:

- CRUD producto;
- precio;
- stock;
- stock minimo;
- activo/inactivo.

Dependencias:

- ventas;
- stock.

### Ventas

Proposito:

- registrar ventas de productos.

Funcionalidades:

- seleccionar productos;
- cantidades;
- metodo de pago;
- descuento opcional;
- impacto en stock;
- impacto en caja.

Dependencias:

- productos;
- caja;
- alumnos opcional.

### Caja

Proposito:

- controlar movimientos financieros.

Funcionalidades:

- ingresos;
- egresos;
- pagos de cuota;
- ventas;
- cierres;
- resumen diario/mensual.

Dependencias:

- pagos;
- ventas;
- staff/admin.

### Dashboard

Proposito:

- mostrar salud del negocio.

Funcionalidades:

- KPIs;
- alertas;
- accesos rapidos;
- metricas financieras;
- metricas operativas.

Dependencias:

- alumnos;
- pagos;
- asistencias;
- caja;
- productos.

### Configuracion

Proposito:

- parametrizar el sistema.

Funcionalidades:

- datos del gimnasio;
- metodos de pago;
- tolerancia de vencimiento;
- horarios generales;
- reglas de check-in;
- alertas.

Dependencias:

- todos los modulos operativos.

## 4. Modelo de dominio

### Relaciones principales

```text
Usuario
├── puede tener Rol
├── puede tener Perfil Staff
└── puede tener Perfil Alumno

Alumno
├── tiene Membresias
├── tiene Pagos
├── tiene Asistencias
└── puede tener Ventas asociadas

Plan
└── define reglas para Membresia

Membresia
├── pertenece a Alumno
├── usa Plan
├── genera o recibe Pagos
└── habilita o bloquea Check-In

Clase
├── pertenece a Actividad
├── tiene Profesor/Staff
├── tiene cupo opcional
└── tiene Asistencias

Producto
└── aparece en VentaItem

Venta
├── puede pertenecer a Alumno
├── tiene VentaItems
├── descuenta Stock
└── genera Movimiento de Caja

Pago
├── pertenece a Alumno
├── impacta Membresia
└── genera Movimiento de Caja
```

### Entidades clave

| Entidad | Representa | Relaciones principales |
|---|---|---|
| `usuarios` | Identidad y acceso al sistema. | Roles, alumno, staff. |
| `roles` | Tipo de permiso operativo. | Usuarios. |
| `alumnos` | Persona que entrena. | Usuario opcional, membresias, pagos, asistencias, ventas. |
| `staff` | Personal interno, recepcion o profesor. | Usuario, clases, acciones registradas. |
| `planes` | Regla comercial de acceso. | Membresias. |
| `membresias` | Estado contractual del alumno. | Alumno, plan, pagos, check-in. |
| `cuotas` | Periodo a cobrar o vencimiento. | Membresia, pagos. |
| `pagos` | Cobro realizado. | Alumno, membresia/cuota, caja. |
| `actividades` | Disciplina o tipo de entrenamiento. | Clases. |
| `clases` | Instancia concreta de actividad. | Actividad, profesor, asistencias. |
| `asistencias` | Presencia/check-in del alumno. | Alumno, clase, staff. |
| `productos` | Articulos vendibles. | Ventas, stock. |
| `ventas` | Operacion comercial de productos. | Alumno opcional, items, caja. |
| `venta_items` | Detalle de productos vendidos. | Venta, producto. |
| `stock_movimientos` | Entrada, salida o ajuste de stock. | Producto, venta, usuario. |
| `caja_movimientos` | Impacto financiero. | Pagos, ventas, egresos. |
| `cierres_caja` | Resumen diario o por turno. | Caja, usuario. |
| `auditoria` | Acciones criticas. | Usuario actor, entidad afectada. |

Importante: no se adopta el modelo de `citas` como nucleo del nuevo sistema. El dominio debe girar alrededor de membresias, asistencias y clases grupales.

## 5. UX operativa

### Recepcion

Pantalla principal: `Operacion diaria`.

Debe tener:

- buscador grande;
- resultado con tarjeta visual;
- estado de acceso;
- boton check-in;
- boton registrar pago;
- boton vender producto;
- ultimas asistencias;
- pagos del dia;
- alertas.

Estados visuales:

| Color | Estado | Significado |
|---|---|---|
| Verde | Al dia | Puede entrar. |
| Amarillo | Advertencia | Vence pronto o requiere atencion, pero puede entrar. |
| Rojo | Bloqueado | No puede entrar. |
| Gris | Inactivo | Sin membresia, inactivo o baja. |
| Azul | Clase/reserva | Clase valida o reserva confirmada si aplica. |

Informacion minima al buscar alumno:

- nombre;
- foto opcional;
- plan;
- vencimiento;
- estado;
- deuda;
- ultima asistencia;
- acciones rapidas.

Acciones rapidas:

- `Registrar ingreso`;
- `Registrar pago`;
- `Ver historial`;
- `Vender producto`;
- `Registrar excepcion`, solo si el rol lo permite.

### Admin

Pantalla principal: dashboard.

Debe mostrar:

- alumnos activos;
- cuotas vencidas;
- ingresos del dia;
- ingresos del mes;
- asistencias del dia;
- ventas del dia;
- stock bajo;
- clases de hoy;
- nuevos alumnos del mes.

El admin necesita control, no solo operacion. Debe poder entrar desde cada metrica al detalle correspondiente.

### Profesor

Pantalla principal: `Mis clases`.

Debe mostrar:

- clases de hoy;
- horario;
- cantidad de presentes;
- listado de alumnos;
- boton marcar asistencia;
- observaciones simples.

El profesor no deberia necesitar navegar por caja, productos o configuracion.

### Alumno

Portal opcional, no prioridad inicial.

Podria mostrar:

- estado de membresia;
- vencimiento;
- pagos propios;
- asistencias;
- horarios;
- avisos.

Decision recomendada: construir primero operacion interna y dejar portal alumno para una fase posterior.

## 6. Reglas de negocio

### Acceso

- Alumno con membresia activa puede entrar.
- Alumno con cuota vencida no entra, salvo excepcion autorizada.
- Alumno inactivo no entra.
- Alumno sin plan activo no entra.
- Si existe tolerancia de vencimiento, debe ser configurable.
- Toda excepcion debe registrar usuario, fecha y motivo.

### Membresias

- Una membresia pertenece a un alumno.
- Una membresia usa un plan.
- Una membresia tiene fecha de inicio y vencimiento.
- Pagar una cuota puede renovar o extender vencimiento.
- Un alumno puede tener historial de membresias.
- Solo una membresia deberia estar activa a la vez, salvo decision futura.
- Una membresia suspendida no habilita acceso.

### Pagos

- Todo pago debe tener monto, metodo, fecha y responsable.
- Todo pago de cuota debe impactar en membresia.
- Todo pago debe generar movimiento de caja.
- Un pago anulado no debe borrarse fisicamente.
- La anulacion debe registrar motivo y usuario.
- No se debe permitir registrar pagos negativos. Para correcciones se usan anulaciones o ajustes.

### Asistencias

- No debe duplicarse check-in del mismo alumno en la misma clase.
- Puede permitirse una asistencia libre por dia si no hay clase asociada.
- Si el plan tiene limite de clases, cada asistencia consume credito.
- Si el plan es libre, no consume credito.
- Una asistencia debe registrar quien la cargo.
- Una asistencia cargada por excepcion debe registrar motivo.

### Clases

- Una clase puede tener cupo o no.
- Si tiene cupo, no se puede superar.
- Una clase tiene profesor asignado.
- Una clase puede cancelarse.
- Una clase cancelada no deberia aceptar asistencia.
- Si hay reserva previa, una asistencia deberia poder vincularse a esa reserva.

### Productos

- Stock no puede quedar negativo.
- Venta descuenta stock.
- Devolucion o correccion debe registrar movimiento inverso.
- Stock bajo debe alertar.
- Producto inactivo no se vende.
- Producto con historial no se borra fisicamente.

### Caja

- Pago y venta generan ingreso.
- Egreso manual requiere concepto.
- Cierre de caja resume movimientos.
- No se deben borrar movimientos criticos.
- Correcciones deben hacerse por anulacion o ajuste.
- Todo movimiento debe registrar usuario responsable.

### Seguridad y permisos

- Staff no modifica configuracion global.
- Profesor no ve caja completa.
- Alumno solo ve su informacion.
- Admin puede ver y corregir todo.
- Acciones criticas deben auditarse.

## 7. Arquitectura propuesta

La base PHP actual puede evolucionar sin introducir frameworks pesados. La mejora recomendada es ordenar responsabilidades.

### Objetivos tecnicos

- Mantener PHP vanilla.
- No introducir Laravel, Symfony ni framework pesado.
- Crear helpers centrales para auth, permisos, DB y JSON.
- Agrupar logica por modulo.
- Evitar SQL y reglas duplicadas en cada vista.
- Mantener endpoints simples.
- Preparar tests por endpoint y por regla de negocio.

### Organizacion sugerida

```text
/
├── config/
├── core/
│   ├── bootstrap.php
│   ├── db.php
│   ├── auth.php
│   ├── response.php
│   ├── validation.php
│   └── permissions.php
├── modules/
│   ├── auth/
│   ├── usuarios/
│   ├── alumnos/
│   ├── staff/
│   ├── membresias/
│   ├── pagos/
│   ├── asistencias/
│   ├── clases/
│   ├── productos/
│   ├── ventas/
│   ├── caja/
│   └── dashboard/
├── admin/
├── api/
├── assets/
├── database/
└── docs/
```

### Endpoints API sugeridos

```text
api/auth/*
api/usuarios/*
api/alumnos/*
api/staff/*
api/planes/*
api/membresias/*
api/pagos/*
api/asistencias/*
api/clases/*
api/productos/*
api/ventas/*
api/caja/*
api/dashboard/*
api/configuracion/*
```

### Desacoplamiento minimo necesario

- Las vistas no deberian contener reglas de negocio complejas.
- Los endpoints deberian llamar funciones de modulo.
- Las funciones de modulo deberian concentrar validaciones.
- Caja, pagos y membresias no deberian actualizarse por separado sin transaccion.
- Los permisos no deberian repetirse de forma distinta en cada archivo.
- Las respuestas JSON deberian mantener formato consistente.

## 8. KPIs del dashboard

### KPIs principales

| KPI | Pregunta que responde |
|---|---|
| Alumnos activos | Cuanta base real tiene el gimnasio. |
| Alumnos inactivos | Cuantos dejaron de operar o fueron dados de baja. |
| Cuotas vencidas | Quienes deben pagar. |
| Cuotas que vencen esta semana | A quienes conviene recordarles. |
| Ingresos del dia | Caja operativa del dia. |
| Ingresos del mes | Salud financiera mensual. |
| Pagos por metodo | Distribucion efectivo/transferencia/tarjeta/otro. |
| Asistencias del dia | Movimiento real del gimnasio hoy. |
| Asistencias del mes | Uso real del servicio. |
| Clases de hoy | Operacion deportiva diaria. |
| Ocupacion promedio por clase | Demanda por horario/actividad. |
| Ventas del dia | Movimiento comercial de productos. |
| Ventas del mes | Performance de indumentaria/productos. |
| Productos con stock bajo | Necesidad de reposicion. |
| Nuevos alumnos del mes | Crecimiento comercial. |
| Bajas del mes | Retencion. |

### Alertas prioritarias

- cuotas vencidas;
- alumnos con deuda;
- stock bajo;
- clases sin profesor;
- pagos anulados;
- caja sin cerrar;
- asistencia cargada con excepcion;
- alumnos con plan por clases sin credito disponible.

## 9. Decisiones criticas

### Clases con o sin reserva

Decision: sin reserva obligatoria al inicio, con reserva opcional para clases con cupo o eventos.

Justificacion:

- es mas simple para recepcion;
- evita friccion para alumnos;
- permite arrancar rapido;
- deja abierta la posibilidad de cupos reales.

### Check-in manual o automatico

Decision: check-in manual rapido en Fase 1.

Justificacion:

- menor costo;
- menos hardware;
- mas facil de validar;
- ideal para APB;
- despues puede sumarse QR, tarjeta o lector.

### Planes flexibles o fijos

Decision: planes configurables, pero con catalogo controlado.

Justificacion:

- no hardcodear solo el plan mensual;
- permitir libre, semanal, por clases o promos;
- evitar que recepcion invente reglas distintas en cada alta.

### Roles simples o avanzados

Decision: roles simples al inicio, preparados para permisos mas finos.

Roles iniciales:

- admin;
- recepcion;
- profesor;
- alumno.

Justificacion:

- suficiente para operar;
- mas facil de testear;
- evita complejidad prematura;
- se puede evolucionar a permisos granulares.

### Caja integrada o separada

Decision: caja integrada con pagos y ventas.

Justificacion:

- cada pago debe impactar caja;
- cada venta debe impactar caja;
- evita diferencias entre cuota cobrada y caja real.

### Borrado fisico o baja logica

Decision: baja logica para entidades criticas.

Aplica a:

- alumnos;
- staff;
- pagos;
- membresias;
- productos con historial;
- caja.

Justificacion:

- trazabilidad;
- correccion de errores;
- reportes confiables.

### Alumno como usuario o entidad separada

Decision: alumno como perfil separado vinculado a usuario.

Justificacion:

- no todo alumno necesita login al inicio;
- el sistema interno puede operar igual;
- si luego se habilita portal del alumno, ya existe vinculo.

### Prioridad funcional

Decision: primero operacion interna, despues portal alumno.

Orden recomendado:

1. alumnos;
2. planes/membresias;
3. pagos/caja;
4. check-in/asistencias;
5. productos/ventas;
6. clases;
7. dashboard;
8. portal alumno.

## Cierre

Esta Fase 1 define un sistema pensado para operacion real: rapido, simple, auditable y preparado para crecer.

El nucleo del nuevo sistema no debe ser `citas`. Debe ser:

- alumnos;
- membresias;
- pagos;
- asistencias;
- clases grupales;
- productos;
- caja;
- dashboard operativo.

La implementacion deberia comenzar recien cuando estas decisiones esten aceptadas, porque condicionan nombres, tablas, permisos, pantallas y pruebas.
