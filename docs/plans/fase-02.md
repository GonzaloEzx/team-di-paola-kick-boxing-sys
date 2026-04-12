# Fase 2 - Diseno Tecnico, Modelo de Datos y Contratos API

Proyecto: `team-di-paola-kick-boxing-sys`  
Repositorio: `https://github.com/GonzaloEzx/team-di-paola-kick-boxing-sys.git`  
Fecha: 2026-04-12  
Estado: diseno tecnico estabilizado con refinamientos de Fase 2.1. Sin codigo.

## Objetivo

Definir la base estructural real del sistema antes de programar:

- modelo de datos;
- tablas;
- campos;
- relaciones;
- constraints;
- reglas de integridad;
- catalogos y estados;
- contratos API iniciales;
- transacciones criticas;
- criterios de naming;
- preparacion para QA y automatizacion.

Este documento toma como base la auditoria de Fase 0 y la definicion funcional de Fase 1.

## Decisiones obligatorias

Estas decisiones quedan fijadas para la implementacion:

1. Un alumno puede existir sin `usuario_id`. El vinculo con usuario se crea despues.
2. El dominio de pagos se separa en:
   - pago;
   - conceptos de pago;
   - comprobante opcional;
   - impacto en caja.
3. `asistencia a clase` y `asistencia libre` son conceptos distintos.
4. Existe periodo liquidable con:
   - `periodo_desde`;
   - `periodo_hasta`;
   - `fecha_vencimiento`;
   - `estado`;
   - `monto`.
5. Roles iniciales:
   - `admin`;
   - `recepcion`;
   - `profesor`;
   - `alumno`.
6. `usuario_roles` es la unica fuente de verdad para permisos. `staff` es solo perfil operativo.
7. La habilitacion de acceso se determina por periodos liquidables pagados/vigentes, no por `membresias.fecha_fin`.
8. En V1 no se permiten pagos parciales de cuotas, aunque el modelo queda preparado para soportarlos en una fase futura.
9. La asistencia libre permite como maximo una asistencia activa por alumno por dia.

## Criterios generales

- Motor esperado: MySQL/MariaDB.
- Engine: InnoDB.
- Charset: `utf8mb4`.
- Nombres: `snake_case`.
- Tablas: plural.
- PK: `id BIGINT UNSIGNED AUTO_INCREMENT`.
- Dinero: `DECIMAL(12,2)`.
- Timezone operativo: `America/Argentina/Buenos_Aires`.
- Soft delete/baja logica para entidades criticas.
- Transacciones obligatorias en pagos, ventas, check-in y anulaciones.

## 1. Modelo de datos

### 1.1 `usuarios`

Identidad de acceso al sistema. No todos los alumnos necesitan usuario.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `email` | VARCHAR(150) | No | Email unico de login. |
| `password_hash` | VARCHAR(255) | Si | Password hasheado, nullable si luego hay OAuth. |
| `nombre` | VARCHAR(100) | No | Nombre visible. |
| `apellido` | VARCHAR(100) | No | Apellido visible. |
| `telefono` | VARCHAR(30) | Si | Contacto. |
| `activo` | TINYINT(1) | No | Acceso habilitado. |
| `ultimo_acceso_at` | DATETIME | Si | Ultimo login. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Ultima modificacion. |

Claves:

- PK: `id`.
- Unique: `email`.

Indices:

- `idx_usuarios_activo`.

### 1.2 `roles`

Catalogo de roles iniciales.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | SMALLINT UNSIGNED | No | PK. |
| `codigo` | VARCHAR(30) | No | `admin`, `recepcion`, `profesor`, `alumno`. |
| `nombre` | VARCHAR(80) | No | Nombre legible. |
| `activo` | TINYINT(1) | No | Disponible para asignar. |

Claves:

- PK: `id`.
- Unique: `codigo`.

### 1.3 `usuario_roles`

Relacion N a N para permitir evolucion futura.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `usuario_id` | BIGINT UNSIGNED | No | FK a `usuarios`. |
| `rol_id` | SMALLINT UNSIGNED | No | FK a `roles`. |
| `created_at` | DATETIME | No | Alta. |

Claves:

- PK compuesta: `usuario_id`, `rol_id`.
- FK: `usuario_id -> usuarios.id`.
- FK: `rol_id -> roles.id`.

### 1.4 `alumnos`

Perfil operativo del alumno. Puede existir sin usuario.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `usuario_id` | BIGINT UNSIGNED | Si | FK nullable a `usuarios`. |
| `nombre` | VARCHAR(100) | No | Nombre. |
| `apellido` | VARCHAR(100) | No | Apellido. |
| `dni` | VARCHAR(30) | Si | Documento. |
| `telefono` | VARCHAR(30) | Si | Contacto principal. |
| `email` | VARCHAR(150) | Si | Contacto, no necesariamente login. |
| `fecha_nacimiento` | DATE | Si | Edad/categoria. |
| `contacto_emergencia_nombre` | VARCHAR(150) | Si | Persona de emergencia. |
| `contacto_emergencia_telefono` | VARCHAR(30) | Si | Telefono de emergencia. |
| `observaciones` | TEXT | Si | Medicas o administrativas. |
| `estado` | VARCHAR(30) | No | `activo`, `inactivo`, `suspendido`. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Claves:

- PK: `id`.
- FK: `usuario_id -> usuarios.id`.
- Unique recomendado: `usuario_id` cuando no sea null.
- Unique recomendado por app: `dni` cuando se informe.

Indices:

- `idx_alumnos_nombre_apellido`.
- `idx_alumnos_dni`.
- `idx_alumnos_estado`.

### 1.5 `staff`

Perfil operativo de personal interno. No define permisos. Los permisos dependen exclusivamente de `usuario_roles`.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `usuario_id` | BIGINT UNSIGNED | No | FK a `usuarios`. |
| `nombre_publico` | VARCHAR(150) | Si | Nombre visible en clases. |
| `bio` | TEXT | Si | Presentacion publica opcional. |
| `especialidad` | VARCHAR(150) | Si | Especialidad operativa, por ejemplo kick boxing, funcional o recepcion. No otorga permisos. |
| `telefono_interno` | VARCHAR(30) | Si | Contacto operativo interno. |
| `activo` | TINYINT(1) | No | Disponible. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Claves:

- PK: `id`.
- FK: `usuario_id -> usuarios.id`.
- Unique: `usuario_id`.

Decision:

- `staff.tipo` se elimina para evitar duplicacion semantica con roles.
- Un usuario puede tener perfil `staff` y no tener permiso de profesor si no posee rol `profesor`.
- Un profesor se reconoce por `usuario_roles`, no por una columna en `staff`.
- Si en el futuro hace falta clasificacion funcional no vinculada a permisos, usar `staff_etiquetas` y `staff_staff_etiquetas` como metadatos operativos.

### 1.6 `planes`

Reglas comerciales de membresia.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `nombre` | VARCHAR(120) | No | Ejemplo: Mensual libre. |
| `descripcion` | TEXT | Si | Detalle. |
| `precio` | DECIMAL(12,2) | No | Precio base. |
| `duracion_dias` | INT UNSIGNED | No | Vigencia del plan. |
| `tipo_acceso` | VARCHAR(30) | No | `libre` o `cantidad_clases`. |
| `cantidad_clases` | INT UNSIGNED | Si | Limite si aplica. |
| `activo` | TINYINT(1) | No | Disponible. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Indices:

- `idx_planes_activo`.
- `idx_planes_tipo_acceso`.

### 1.7 `membresias`

Vinculo entre alumno y plan. Agrupa periodos liquidables y conserva el estado administrativo de la relacion comercial.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `alumno_id` | BIGINT UNSIGNED | No | FK a `alumnos`. |
| `plan_id` | BIGINT UNSIGNED | No | FK a `planes`. |
| `fecha_inicio` | DATE | No | Inicio. |
| `fecha_fin` | DATE | Si | Cache administrativa calculada desde el ultimo periodo pagado. No es fuente de verdad para acceso. |
| `estado` | VARCHAR(30) | No | `pendiente`, `activa`, `vencida`, `suspendida`, `cancelada`. |
| `clases_disponibles` | INT UNSIGNED | Si | Saldo para planes por clase. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Claves:

- FK: `alumno_id -> alumnos.id`.
- FK: `plan_id -> planes.id`.

Indices:

- `idx_membresias_alumno_estado`.
- `idx_membresias_fecha_fin`.

Regla importante:

- No puede haber mas de una membresia activa por alumno. En MySQL se recomienda validarlo por transaccion/aplicacion y reforzarlo con indice funcional si la version lo permite.
- La habilitacion de acceso se calcula desde `periodos_liquidables`, no desde `fecha_fin`.
- `fecha_fin` debe actualizarse como `MAX(periodo_hasta)` de periodos pagados de la membresia para lectura administrativa rapida.

Regla de vigencia:

Un alumno esta habilitado para ingresar solo si cumple todo esto:

1. `alumnos.estado = 'activo'`;
2. tiene `membresias.estado = 'activa'`;
3. existe al menos un `periodos_liquidables` de esa membresia con `estado = 'pagado'`, `periodo_desde <= fecha_operativa` y `periodo_hasta >= fecha_operativa`.

Estados calculados de membresia:

- `suspendida`: bloqueo manual con motivo.
- `cancelada`: baja definitiva administrativa.
- `activa`: existe periodo pagado vigente.
- `vencida`: no existe periodo pagado vigente y hay periodos vencidos.
- `pendiente`: membresia creada sin ningun periodo pagado.

### 1.8 `periodos_liquidables`

Cuotas o periodos a cobrar. Separa deuda de pago.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `alumno_id` | BIGINT UNSIGNED | No | FK a `alumnos`. |
| `membresia_id` | BIGINT UNSIGNED | No | FK a `membresias`. |
| `periodo_desde` | DATE | No | Inicio liquidado. |
| `periodo_hasta` | DATE | No | Fin liquidado. |
| `fecha_vencimiento` | DATE | No | Vencimiento de pago. |
| `estado` | VARCHAR(30) | No | `pendiente`, `pagado`, `vencido`, `anulado`. |
| `monto` | DECIMAL(12,2) | No | Importe esperado. |
| `saldo` | DECIMAL(12,2) | No | Saldo pendiente. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Claves:

- FK: `alumno_id -> alumnos.id`.
- FK: `membresia_id -> membresias.id`.

Indices:

- `idx_periodos_alumno_estado`.
- `idx_periodos_vencimiento`.
- `idx_periodos_membresia`.

### 1.9 `pagos`

Cabecera del pago. No mezcla conceptos ni caja.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `alumno_id` | BIGINT UNSIGNED | Si | FK a `alumnos`, nullable para cobros no asociados. |
| `registrado_por_usuario_id` | BIGINT UNSIGNED | No | Usuario que cobro. |
| `fecha_pago` | DATETIME | No | Fecha/hora del pago. |
| `metodo_pago` | VARCHAR(30) | No | Metodo. |
| `monto_total` | DECIMAL(12,2) | No | Total del pago. |
| `estado` | VARCHAR(30) | No | `registrado`, `anulado`. |
| `observaciones` | TEXT | Si | Comentarios. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Claves:

- FK: `alumno_id -> alumnos.id`.
- FK: `registrado_por_usuario_id -> usuarios.id`.

Indices:

- `idx_pagos_fecha`.
- `idx_pagos_alumno`.
- `idx_pagos_estado`.

### 1.10 `pago_conceptos`

Detalle del pago. Permite pagar cuota, inscripcion, deuda u otros conceptos.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `pago_id` | BIGINT UNSIGNED | No | FK a `pagos`. |
| `periodo_liquidable_id` | BIGINT UNSIGNED | Si | FK a periodo si aplica. |
| `tipo_concepto` | VARCHAR(40) | No | `cuota`, `inscripcion`, `deuda`, `ajuste`, `otro`. |
| `descripcion` | VARCHAR(255) | No | Texto visible. |
| `monto` | DECIMAL(12,2) | No | Importe del concepto. |

Claves:

- FK: `pago_id -> pagos.id`.
- FK: `periodo_liquidable_id -> periodos_liquidables.id`.

Indices:

- `idx_pago_conceptos_pago`.
- `idx_pago_conceptos_periodo`.

### 1.11 `pago_comprobantes`

Comprobante opcional, separado del pago.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `pago_id` | BIGINT UNSIGNED | No | FK a `pagos`. |
| `archivo_url` | VARCHAR(255) | No | Ruta o URL del archivo. |
| `tipo_archivo` | VARCHAR(50) | Si | MIME o extension. |
| `created_at` | DATETIME | No | Alta. |

Claves:

- FK: `pago_id -> pagos.id`.

Decision:

- Permitir 1 comprobante por pago al inicio. Si se necesitan varios, se elimina la restriccion unique.

### 1.12 `caja_movimientos`

Impacto financiero. Se crea desde pagos, ventas o movimientos manuales.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `tipo` | VARCHAR(20) | No | `ingreso`, `egreso`, `ajuste`. |
| `origen` | VARCHAR(30) | No | `pago`, `venta`, `manual`, `anulacion`. |
| `pago_id` | BIGINT UNSIGNED | Si | FK a `pagos`. |
| `venta_id` | BIGINT UNSIGNED | Si | FK a `ventas`. |
| `movimiento_relacionado_id` | BIGINT UNSIGNED | Si | FK a `caja_movimientos`. Vincula anulaciones o ajustes con el movimiento original. |
| `monto` | DECIMAL(12,2) | No | Importe. |
| `metodo_pago` | VARCHAR(30) | Si | Metodo. |
| `concepto` | VARCHAR(255) | No | Descripcion. |
| `fecha_movimiento` | DATETIME | No | Fecha/hora. |
| `usuario_id` | BIGINT UNSIGNED | No | Responsable. |
| `estado` | VARCHAR(30) | No | `registrado`, `anulado`. |
| `created_at` | DATETIME | No | Alta. |

Claves:

- FK: `pago_id -> pagos.id`.
- FK: `venta_id -> ventas.id`.
- FK: `movimiento_relacionado_id -> caja_movimientos.id`.
- FK: `usuario_id -> usuarios.id`.

Indices:

- `idx_caja_fecha`.
- `idx_caja_origen`.
- `idx_caja_usuario`.
- `idx_caja_movimiento_relacionado`.

Regla:

- Las anulaciones deben crear un movimiento compensatorio o marcar el movimiento original como anulado, pero siempre deben quedar vinculadas mediante `movimiento_relacionado_id` cuando exista un movimiento original.

### 1.13 `actividades`

Tipos de entrenamiento.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `nombre` | VARCHAR(120) | No | Kick Boxing, Boxeo, Funcional. |
| `descripcion` | TEXT | Si | Detalle. |
| `activo` | TINYINT(1) | No | Disponible. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Indices:

- `idx_actividades_activo`.

### 1.14 `clases`

Instancia concreta de clase grupal.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `actividad_id` | BIGINT UNSIGNED | No | FK a `actividades`. |
| `profesor_staff_id` | BIGINT UNSIGNED | Si | FK a `staff`. |
| `fecha` | DATE | No | Dia. |
| `hora_inicio` | TIME | No | Inicio. |
| `hora_fin` | TIME | No | Fin. |
| `cupo_maximo` | INT UNSIGNED | Si | Null significa sin cupo. |
| `estado` | VARCHAR(30) | No | `programada`, `realizada`, `cancelada`. |
| `observaciones` | TEXT | Si | Notas. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Claves:

- FK: `actividad_id -> actividades.id`.
- FK: `profesor_staff_id -> staff.id`.

Indices:

- `idx_clases_fecha`.
- `idx_clases_profesor_fecha`.
- `idx_clases_estado`.

### 1.15 `asistencias_clase`

Asistencia vinculada a una clase.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `clase_id` | BIGINT UNSIGNED | No | FK a `clases`. |
| `alumno_id` | BIGINT UNSIGNED | No | FK a `alumnos`. |
| `membresia_id` | BIGINT UNSIGNED | Si | Membresia validada. |
| `registrado_por_usuario_id` | BIGINT UNSIGNED | No | Staff/profesor que registro. |
| `fecha_hora_checkin` | DATETIME | No | Momento real. |
| `estado` | VARCHAR(30) | No | `registrada`, `anulada`. |
| `es_excepcion` | TINYINT(1) | No | Si se permitio fuera de regla. |
| `motivo_excepcion` | VARCHAR(255) | Si | Requerido si hay excepcion. |
| `created_at` | DATETIME | No | Alta. |

Claves:

- FK: `clase_id -> clases.id`.
- FK: `alumno_id -> alumnos.id`.
- FK: `membresia_id -> membresias.id`.
- FK: `registrado_por_usuario_id -> usuarios.id`.
- Unique: `clase_id`, `alumno_id`.

Indices:

- `idx_asistencias_clase_alumno`.
- `idx_asistencias_clase_fecha`.

### 1.16 `asistencias_libre`

Ingreso no asociado a clase concreta.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `alumno_id` | BIGINT UNSIGNED | No | FK a `alumnos`. |
| `membresia_id` | BIGINT UNSIGNED | Si | Membresia validada. |
| `registrado_por_usuario_id` | BIGINT UNSIGNED | No | Staff que registro. |
| `fecha_asistencia` | DATE | No | Fecha operativa local del check-in. |
| `fecha_hora_checkin` | DATETIME | No | Momento real. |
| `estado` | VARCHAR(30) | No | `registrada`, `anulada`. |
| `es_excepcion` | TINYINT(1) | No | Si se permitio fuera de regla. |
| `motivo_excepcion` | VARCHAR(255) | Si | Requerido si hay excepcion. |
| `created_at` | DATETIME | No | Alta. |

Claves:

- FK: `alumno_id -> alumnos.id`.
- FK: `membresia_id -> membresias.id`.
- FK: `registrado_por_usuario_id -> usuarios.id`.

Indices:

- `idx_asistencias_libre_alumno_fecha` (`alumno_id`, `fecha_asistencia`, `estado`).
- `idx_asistencias_libre_fecha`.

Regla:

- Maximo una asistencia libre activa por alumno por dia.
- La validacion debe buscar `estado = 'registrada'` para el mismo `alumno_id` y `fecha_asistencia`.
- Si una asistencia previa fue `anulada`, se puede registrar una nueva asistencia libre el mismo dia.
- En V1 la asistencia libre y la asistencia a clase no se bloquean entre si.

### 1.17 `productos`

Inventario vendible.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `nombre` | VARCHAR(150) | No | Producto. |
| `descripcion` | TEXT | Si | Detalle. |
| `precio` | DECIMAL(12,2) | No | Precio actual. |
| `stock` | INT | No | Stock disponible. |
| `stock_minimo` | INT | No | Alerta. |
| `estado` | VARCHAR(30) | No | `activo`, `inactivo`. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Indices:

- `idx_productos_estado`.
- `idx_productos_stock`.

### 1.18 `ventas`

Cabecera de venta.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `alumno_id` | BIGINT UNSIGNED | Si | Cliente asociado opcional. |
| `usuario_id` | BIGINT UNSIGNED | No | Staff que vendio. |
| `fecha_venta` | DATETIME | No | Fecha/hora. |
| `metodo_pago` | VARCHAR(30) | No | Metodo. |
| `total` | DECIMAL(12,2) | No | Total. |
| `estado` | VARCHAR(30) | No | `registrada`, `anulada`. |
| `created_at` | DATETIME | No | Alta. |
| `updated_at` | DATETIME | No | Modificacion. |

Claves:

- FK: `alumno_id -> alumnos.id`.
- FK: `usuario_id -> usuarios.id`.

Indices:

- `idx_ventas_fecha`.
- `idx_ventas_alumno`.
- `idx_ventas_estado`.

### 1.19 `venta_items`

Detalle de venta.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `venta_id` | BIGINT UNSIGNED | No | FK a `ventas`. |
| `producto_id` | BIGINT UNSIGNED | No | FK a `productos`. |
| `cantidad` | INT UNSIGNED | No | Cantidad. |
| `precio_unitario` | DECIMAL(12,2) | No | Precio congelado. |
| `subtotal` | DECIMAL(12,2) | No | Cantidad por precio. |

Claves:

- FK: `venta_id -> ventas.id`.
- FK: `producto_id -> productos.id`.

Indices:

- `idx_venta_items_venta`.
- `idx_venta_items_producto`.

### 1.20 `stock_movimientos`

Historial trazable de cambios de stock. `productos.stock` es el saldo actual, pero la reconstruccion y auditoria se hacen desde esta tabla.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `producto_id` | BIGINT UNSIGNED | No | FK a `productos`. |
| `venta_id` | BIGINT UNSIGNED | Si | FK a `ventas` cuando el movimiento nace de una venta. |
| `venta_item_id` | BIGINT UNSIGNED | Si | FK a `venta_items` cuando aplica. |
| `usuario_id` | BIGINT UNSIGNED | No | Usuario responsable. |
| `tipo` | VARCHAR(30) | No | `entrada`, `salida`, `ajuste`, `anulacion`. |
| `cantidad` | INT | No | Positiva para entrada/anulacion, negativa para salida; ajuste puede ser positivo o negativo. |
| `stock_anterior` | INT | No | Stock antes del movimiento. |
| `stock_nuevo` | INT | No | Stock posterior al movimiento. |
| `motivo` | VARCHAR(255) | Si | Obligatorio en ajustes manuales y anulaciones. |
| `created_at` | DATETIME | No | Momento del movimiento. |

Claves:

- PK: `id`.
- FK: `producto_id -> productos.id`.
- FK: `venta_id -> ventas.id`.
- FK: `venta_item_id -> venta_items.id`.
- FK: `usuario_id -> usuarios.id`.

Indices:

- `idx_stock_producto_fecha`.
- `idx_stock_venta`.
- `idx_stock_tipo`.
- `idx_stock_usuario`.

Reglas:

- Cada `venta_item` debe generar un `stock_movimientos` de tipo `salida` en la misma transaccion de venta.
- Una anulacion de venta no borra movimientos anteriores: crea movimientos tipo `anulacion` con cantidad positiva.
- Los ajustes manuales requieren permiso y motivo.
- `stock_nuevo` debe coincidir con `productos.stock` al confirmar la transaccion.

### 1.21 `auditoria`

Trazabilidad de acciones criticas.

| Campo | Tipo | Null | Descripcion |
|---|---:|---:|---|
| `id` | BIGINT UNSIGNED | No | PK. |
| `usuario_id` | BIGINT UNSIGNED | Si | Actor. |
| `accion` | VARCHAR(80) | No | Ejemplo: `registrar_pago`. |
| `entidad` | VARCHAR(80) | No | Tabla o modulo afectado. |
| `entidad_id` | BIGINT UNSIGNED | Si | ID afectado. |
| `payload_json` | JSON | Si | Datos relevantes. |
| `ip` | VARCHAR(45) | Si | IP. |
| `user_agent` | VARCHAR(255) | Si | Navegador. |
| `created_at` | DATETIME | No | Momento. |

Claves:

- FK: `usuario_id -> usuarios.id`.

Indices:

- `idx_auditoria_entidad`.
- `idx_auditoria_usuario`.
- `idx_auditoria_fecha`.

## 2. Relaciones

### Relaciones principales

| Relacion | Cardinalidad | Regla |
|---|---:|---|
| `usuarios` -> `roles` | N a N | Mediante `usuario_roles`. |
| `usuarios` -> `alumnos` | 1 a 0/1 | Un alumno puede no tener usuario. |
| `usuarios` -> `staff` | 1 a 0/1 | Staff requiere usuario. |
| `alumnos` -> `membresias` | 1 a N | Historial de membresias. |
| `planes` -> `membresias` | 1 a N | Un plan puede tener muchas membresias. |
| `membresias` -> `periodos_liquidables` | 1 a N | Cada membresia genera periodos/cuotas. |
| `periodos_liquidables` -> `pago_conceptos` | 1 a N | En V1 una cuota se cancela completa; la relacion conserva soporte futuro para parciales o varios pagos. |
| `pagos` -> `pago_conceptos` | 1 a N | Un pago puede cubrir varios conceptos. |
| `pagos` -> `pago_comprobantes` | 1 a 0/1 | Comprobante opcional. |
| `pagos` -> `caja_movimientos` | 1 a 1 normal | Cada pago registrado impacta caja. |
| `actividades` -> `clases` | 1 a N | Una actividad tiene muchas clases. |
| `staff` -> `clases` | 1 a N | Profesor asignado; el permiso de profesor se valida por `usuario_roles`. |
| `clases` -> `asistencias_clase` | 1 a N | Presentes por clase. |
| `alumnos` -> `asistencias_clase` | 1 a N | Historial de clases tomadas. |
| `alumnos` -> `asistencias_libre` | 1 a N | Historial de ingresos libres. |
| `productos` -> `venta_items` | 1 a N | Producto vendido en items. |
| `ventas` -> `venta_items` | 1 a N | Venta con detalle. |
| `ventas` -> `caja_movimientos` | 1 a 1 normal | Cada venta registrada impacta caja. |
| `productos` -> `stock_movimientos` | 1 a N | Historial de stock por producto. |
| `ventas` -> `stock_movimientos` | 1 a N | Una venta genera salidas de stock por item. |
| `venta_items` -> `stock_movimientos` | 1 a 1 normal | Cada item vendido debe tener movimiento de stock. |
| `caja_movimientos` -> `caja_movimientos` | 1 a N | Anulaciones o ajustes pueden referenciar el movimiento original. |

### Reglas de dependencia

- No borrar fisicamente alumnos con pagos o asistencias.
- No borrar fisicamente pagos ni caja.
- Producto con ventas debe pasar a `inactivo`, no borrarse.
- Clase con asistencias debe cancelarse o anular asistencias, no eliminarse.
- Membresia con periodos o pagos debe conservar historial.
- Alumno puede existir sin usuario, pero usuario no debe vincularse a mas de un alumno.
- Staff requiere usuario porque opera dentro del sistema.
- Staff no define permisos; cualquier permiso operativo surge de `usuario_roles`.
- Producto con stock historico conserva `stock_movimientos`; nunca se reescribe historial.

## 3. Reglas de integridad

### Reglas globales

- Un alumno no puede tener mas de una membresia `activa` al mismo tiempo.
- La fuente de verdad para habilitar acceso es un periodo liquidable `pagado` y vigente.
- `membresias.fecha_fin` no habilita acceso por si sola.
- Un periodo liquidable debe pertenecer a una membresia existente.
- No se puede registrar pago de cuota contra un periodo inexistente.
- En V1 no se permiten pagos parciales de cuotas: el monto de concepto `cuota` debe cancelar el saldo completo del periodo.
- Un pago registrado debe tener al menos un `pago_concepto`.
- La suma de `pago_conceptos.monto` debe coincidir con `pagos.monto_total`.
- Todo pago registrado debe generar movimiento de caja.
- Si un pago se anula, debe anular o compensar el movimiento de caja asociado.
- No se puede registrar asistencia si el alumno no esta habilitado, salvo excepcion registrada.
- No se puede duplicar asistencia del mismo alumno en la misma clase.
- No se debe duplicar asistencia libre activa del mismo alumno en el mismo dia operativo.
- Si la clase tiene cupo, no se puede superar con asistencias o reservas activas.
- No se puede registrar asistencia en clase `cancelada`.
- Si el plan es por cantidad de clases, el check-in debe consumir credito.
- Si el plan es libre, el check-in no consume credito.
- No se puede vender producto inactivo.
- No se puede vender mas cantidad que el stock disponible.
- Stock no puede quedar negativo.
- Todo cambio de stock debe crear un registro en `stock_movimientos`.
- Toda venta registrada debe generar movimiento de caja.
- Toda venta registrada debe generar movimientos de stock por item.
- Toda anulacion debe registrar auditoria.
- Toda excepcion de acceso debe tener motivo y usuario responsable.

### Restricciones recomendadas en base

- FKs para relaciones principales.
- Unique `usuarios.email`.
- Unique nullable controlado para `alumnos.usuario_id`.
- Unique `usuario_roles(usuario_id, rol_id)`.
- Unique `asistencias_clase(clase_id, alumno_id)`.
- Indice `asistencias_libre(alumno_id, fecha_asistencia, estado)` para bloquear duplicados activos por logica transaccional.
- Checks si la version MySQL lo soporta:
  - montos `>= 0`;
  - cantidades de venta `> 0`;
  - `stock_movimientos.cantidad <> 0`;
  - stock `>= 0`;
  - `hora_fin > hora_inicio`;
  - `periodo_hasta >= periodo_desde`.
- Indices por fechas y estados para dashboard y operacion.

### Logica recomendada en aplicacion

- Validar membresia activa con bloqueo transaccional.
- Validar acceso buscando periodo `pagado` vigente en `periodos_liquidables`.
- Validar que `usuario_roles` tenga el rol requerido; no usar columnas de perfil para permisos.
- Validar cupo con bloqueo de clase.
- Validar stock con bloqueo de producto.
- Registrar `stock_movimientos` dentro de la misma transaccion que modifica `productos.stock`.
- Actualizar periodos, pagos, membresia y caja en una unica transaccion.
- Registrar auditoria en operaciones criticas.
- No resolver reglas financieras solamente desde frontend.

## 4. Catalogos

Recomendacion: usar tabla para `roles`. Para estados internos puede iniciarse con `VARCHAR` validado por aplicacion. Evitar `ENUM` rigidos en esta etapa porque el dominio todavia puede evolucionar.

### Roles

- `admin`
- `recepcion`
- `profesor`
- `alumno`

### Estados de alumno

- `activo`
- `inactivo`
- `suspendido`

### Estados de membresia

- `pendiente`
- `activa`
- `vencida`
- `suspendida`
- `cancelada`

### Estados de periodo liquidable

- `pendiente`
- `pagado`
- `vencido`
- `anulado`

### Metodos de pago

- `efectivo`
- `transferencia`
- `tarjeta`
- `mercado_pago`
- `otro`

### Estados de pago

- `registrado`
- `anulado`

### Tipos de concepto de pago

- `cuota`
- `inscripcion`
- `deuda`
- `ajuste`
- `otro`

### Estados de clase

- `programada`
- `realizada`
- `cancelada`

### Estados de asistencia

- `registrada`
- `anulada`

### Tipos de caja

- `ingreso`
- `egreso`
- `ajuste`

### Origenes de caja

- `pago`
- `venta`
- `manual`
- `anulacion`

### Estados de producto

- `activo`
- `inactivo`

### Tipos de movimiento de stock

- `entrada`
- `salida`
- `ajuste`
- `anulacion`

### Estados de venta

- `registrada`
- `anulada`

## 5. Contratos API

Formato recomendado de respuesta exitosa:

```json
{
  "success": true,
  "data": {},
  "meta": {}
}
```

Formato recomendado de error:

```json
{
  "success": false,
  "error": "Mensaje claro",
  "code": "CODIGO_ESTABLE"
}
```

### 5.1 Alumnos

#### `GET /api/alumnos`

Proposito: listar alumnos con filtros.

Query:

- `q`;
- `estado`;
- `page`;
- `limit`.

Response:

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Gonzalo",
      "apellido": "Ejemplo",
      "estado": "activo",
      "membresia_estado": "activa",
      "fecha_vencimiento": "2026-05-12"
    }
  ],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 1
  }
}
```


Validaciones:

- paginacion con limite maximo;
- filtros controlados;
- busqueda por nombre, apellido, DNI, telefono o email.

#### `POST /api/alumnos`

Proposito: crear alumno sin requerir usuario.

Request:

```json
{
  "nombre": "Gonzalo",
  "apellido": "Ejemplo",
  "dni": "12345678",
  "telefono": "3624000000",
  "email": "alumno@mail.com"
}
```

Validaciones:

- nombre requerido;
- apellido requerido;
- DNI unico si se informa;
- email valido si se informa.

#### `GET /api/alumnos/{id}`

Proposito: detalle operativo del alumno.

Response debe incluir:

- datos personales;
- membresia actual;
- periodos;
- pagos recientes;
- asistencias recientes;
- alertas.

#### `PUT /api/alumnos/{id}`

Proposito: editar datos del alumno.

Validaciones:

- alumno existe;
- DNI no duplicado si se modifica;
- email valido si se informa.

#### `PATCH /api/alumnos/{id}/estado`

Proposito: activar, inactivar o suspender alumno.

Request:

```json
{
  "estado": "suspendido",
  "motivo": "Deuda administrativa"
}
```

### 5.2 Membresias y planes

#### `GET /api/planes`

Proposito: listar planes activos o todos segun rol.

#### `POST /api/planes`

Proposito: crear plan. Solo admin.

Request:

```json
{
  "nombre": "Mensual libre",
  "precio": 25000,
  "duracion_dias": 30,
  "tipo_acceso": "libre",
  "cantidad_clases": null
}
```

#### `POST /api/alumnos/{id}/membresias`

Proposito: crear membresia para alumno.

Request:

```json
{
  "plan_id": 2,
  "fecha_inicio": "2026-04-12",
  "generar_periodo": true
}
```

Validaciones:

- alumno existe;
- plan activo;
- no hay otra membresia activa;
- fecha valida.

#### `GET /api/alumnos/{id}/membresias`

Proposito: historial de membresias del alumno.

#### `PATCH /api/membresias/{id}/suspender`

Proposito: suspender membresia con motivo.

### 5.3 Periodos liquidables

#### `GET /api/alumnos/{id}/periodos`

Proposito: listar cuotas o periodos del alumno.

#### `POST /api/membresias/{id}/periodos`

Proposito: generar periodo liquidable.

Request:

```json
{
  "periodo_desde": "2026-04-12",
  "periodo_hasta": "2026-05-12",
  "fecha_vencimiento": "2026-04-12",
  "monto": 25000
}
```

Validaciones:

- membresia existe;
- monto mayor o igual a 0;
- `periodo_hasta >= periodo_desde`;
- no duplicar periodo equivalente salvo autorizacion.

### 5.4 Pagos

#### `POST /api/pagos`

Proposito: registrar pago.

Request:

```json
{
  "alumno_id": 1,
  "metodo_pago": "efectivo",
  "fecha_pago": "2026-04-12 18:30:00",
  "conceptos": [
    {
      "tipo_concepto": "cuota",
      "periodo_liquidable_id": 10,
      "descripcion": "Cuota abril 2026",
      "monto": 25000
    }
  ],
  "comprobante_url": null,
  "observaciones": ""
}
```

Response:

```json
{
  "success": true,
  "data": {
    "pago_id": 50,
    "caja_movimiento_id": 88,
    "periodos_actualizados": [10],
    "membresia_estado": "activa"
  }
}
```

Validaciones:

- conceptos no vacios;
- monto total coincide con suma de conceptos;
- periodo existe si se informa;
- para `tipo_concepto = 'cuota'`, `periodo_liquidable_id` es obligatorio;
- para cuotas, el periodo debe estar en `pendiente` o `vencido`;
- para cuotas en V1, `pago_conceptos.monto` debe ser igual a `periodos_liquidables.saldo`;
- si el monto de cuota es menor al saldo, rechazar con codigo `PAGO_PARCIAL_NO_PERMITIDO`;
- si el monto de cuota supera el saldo, rechazar con codigo `MONTO_SUPERA_SALDO`;
- metodo permitido;
- usuario autorizado.

#### `GET /api/pagos/{id}`

Proposito: detalle de pago.

#### `POST /api/pagos/{id}/anular`

Proposito: anular pago con motivo.

Request:

```json
{
  "motivo": "Pago cargado por error"
}
```

Permisos:

- admin o permiso especial.

### 5.5 Asistencias

#### `POST /api/asistencias/check-in`

Proposito: registrar check-in inteligente, a clase o libre.

Request asistencia a clase:

```json
{
  "alumno_id": 1,
  "tipo": "clase",
  "clase_id": 20
}
```

Request asistencia libre:

```json
{
  "alumno_id": 1,
  "tipo": "libre"
}
```

Response:

```json
{
  "success": true,
  "data": {
    "resultado": "permitido",
    "asistencia_id": 300,
    "tipo": "clase",
    "membresia_estado": "activa"
  }
}
```

Validaciones:

- alumno existe;
- alumno activo;
- membresia `activa` con periodo liquidable `pagado` y vigente;
- no duplicado de alumno en la misma clase cuando `tipo = 'clase'`;
- no duplicado de asistencia libre activa para el mismo alumno y fecha operativa cuando `tipo = 'libre'`;
- cupo disponible si es clase;
- excepcion requiere motivo y permiso.

#### `GET /api/asistencias/hoy`

Proposito: listado operativo de ingresos del dia.

#### `POST /api/asistencias/{tipo}/{id}/anular`

Proposito: anular asistencia.

Validaciones:

- tipo permitido: `clase` o `libre`;
- asistencia existe;
- requiere motivo.

### 5.6 Clases

#### `GET /api/clases`

Proposito: listar clases.

Filtros:

- `fecha`;
- `profesor_id`;
- `actividad_id`;
- `estado`.

#### `POST /api/clases`

Proposito: crear clase.

Request:

```json
{
  "actividad_id": 1,
  "profesor_staff_id": 3,
  "fecha": "2026-04-12",
  "hora_inicio": "19:00",
  "hora_fin": "20:00",
  "cupo_maximo": 25
}
```

Validaciones:

- actividad activa;
- `profesor_staff_id` activo si se informa;
- el usuario vinculado al `staff` debe tener rol `profesor` en `usuario_roles`;
- fecha valida;
- `hora_fin > hora_inicio`.

#### `PATCH /api/clases/{id}/cancelar`

Proposito: cancelar clase con motivo.

#### `GET /api/clases/{id}/asistencias`

Proposito: ver presentes de una clase.

### 5.7 Productos

#### `GET /api/productos`

Proposito: listar productos.

#### `POST /api/productos`

Proposito: crear producto.

#### `PUT /api/productos/{id}`

Proposito: editar producto.

#### `PATCH /api/productos/{id}/estado`

Proposito: activar o inactivar producto.

#### `POST /api/productos/{id}/stock/ajuste`

Proposito: registrar ajuste manual de stock con trazabilidad.

Request:

```json
{
  "cantidad": 5,
  "motivo": "Ingreso por compra de reposicion"
}
```

Validaciones:

- producto existe;
- usuario autorizado;
- `cantidad` representa variacion de stock; positiva suma, negativa descuenta;
- motivo obligatorio;
- el stock resultante no puede ser negativo;
- debe crear `stock_movimientos` tipo `entrada`, `ajuste` o `anulacion` segun corresponda.

### 5.8 Ventas

#### `POST /api/ventas`

Proposito: registrar venta.

Request:

```json
{
  "alumno_id": 1,
  "metodo_pago": "transferencia",
  "items": [
    {
      "producto_id": 5,
      "cantidad": 2
    }
  ]
}
```

Response:

```json
{
  "success": true,
  "data": {
    "venta_id": 40,
    "caja_movimiento_id": 90,
    "stock_movimiento_ids": [120, 121],
    "total": 18000
  }
}
```

Validaciones:

- items no vacios;
- productos activos;
- stock suficiente;
- cada item debe generar un movimiento de stock de tipo `salida`;
- metodo permitido.

#### `POST /api/ventas/{id}/anular`

Proposito: anular venta y reponer stock con auditoria.

Validaciones:

- venta existe y no esta anulada;
- motivo obligatorio;
- debe crear movimientos de stock tipo `anulacion` con cantidad positiva;
- debe crear o actualizar movimiento de caja vinculado con `movimiento_relacionado_id`.

### 5.9 Caja

#### `GET /api/caja/movimientos`

Proposito: listar movimientos.

Filtros:

- fecha desde;
- fecha hasta;
- tipo;
- origen;
- metodo.

#### `POST /api/caja/movimientos`

Proposito: movimiento manual.

Permisos:

- admin o recepcion autorizada.

#### `GET /api/caja/resumen`

Proposito: resumen diario o mensual.

### 5.10 Dashboard

#### `GET /api/dashboard/resumen`

Proposito: KPIs principales.

Response:

```json
{
  "success": true,
  "data": {
    "alumnos_activos": 120,
    "cuotas_vencidas": 18,
    "ingresos_dia": 85000,
    "ingresos_mes": 1450000,
    "asistencias_hoy": 42,
    "ventas_hoy": 5,
    "stock_bajo": 3,
    "clases_hoy": 6
  }
}
```

## 6. Transacciones criticas

### 6.1 Registrar pago

Debe ser atomico.

Pasos:

1. Validar alumno, conceptos, periodos y metodo.
2. Iniciar transaccion.
3. Bloquear los `periodos_liquidables` afectados.
4. Para conceptos de cuota, validar que el monto sea exactamente igual al `saldo` del periodo.
5. Crear `pagos`.
6. Crear `pago_conceptos`.
7. Guardar comprobante si existe.
8. Actualizar `periodos_liquidables`:
   - `saldo = 0`;
   - `estado = 'pagado'`.
9. Actualizar `membresias`:
   - activar si corresponde;
   - recalcular `fecha_fin` como maximo `periodo_hasta` pagado;
   - recalcular estado segun periodos pagados/vigentes.
10. Crear `caja_movimientos`.
11. Crear registro en `auditoria`.
12. Confirmar transaccion.

Si falla cualquier paso, rollback.

Nota V1: los pagos parciales de cuotas se rechazan con `PAGO_PARCIAL_NO_PERMITIDO`. El modelo conserva `saldo` y `pago_conceptos` para habilitar pagos parciales en una version futura sin redisenar tablas.

### 6.2 Check-in

Debe ser atomico.

Pasos:

1. Validar alumno activo.
2. Buscar membresia `activa`.
3. Validar periodo liquidable `pagado` y vigente. Esta es la fuente de verdad de acceso.
4. Si es clase:
   - bloquear clase;
   - validar estado;
   - validar cupo;
   - validar no duplicado.
5. Si es libre:
   - calcular `fecha_asistencia` con timezone del negocio;
   - validar que no exista asistencia libre `registrada` del mismo alumno en esa fecha.
6. Si plan por clases:
   - validar creditos;
   - descontar credito.
7. Crear asistencia en tabla correspondiente.
8. Auditar.
9. Confirmar transaccion.

### 6.3 Venta

Debe ser atomica.

Pasos:

1. Validar usuario autorizado.
2. Validar items.
3. Iniciar transaccion.
4. Bloquear productos seleccionados.
5. Validar stock.
6. Crear `ventas`.
7. Crear `venta_items`.
8. Descontar `productos.stock`.
9. Crear `stock_movimientos` tipo `salida` por cada item, con cantidad negativa.
10. Crear `caja_movimientos`.
11. Auditar.
12. Confirmar transaccion.

### 6.4 Anular pago

Debe ser atomico.

Pasos:

1. Validar permiso.
2. Validar pago no anulado.
3. Iniciar transaccion.
4. Marcar pago como `anulado`.
5. Revertir o ajustar periodos afectados.
6. Crear movimiento de caja de anulacion o marcar movimiento asociado como anulado, vinculando con `movimiento_relacionado_id`.
7. Recalcular estado de membresia si corresponde.
8. Auditar con motivo.
9. Confirmar transaccion.

### 6.5 Anular venta

Debe ser atomico.

Pasos:

1. Validar permiso.
2. Validar venta no anulada.
3. Iniciar transaccion.
4. Marcar venta como `anulada`.
5. Reponer stock de items.
6. Crear `stock_movimientos` tipo `anulacion` por cada item, con cantidad positiva.
7. Crear movimiento de caja compensatorio o anular movimiento asociado, vinculando con `movimiento_relacionado_id`.
8. Auditar.
9. Confirmar transaccion.

## 7. Naming y consistencia

Reglas:

- Tablas en plural: `alumnos`, `pagos`, `membresias`.
- Columnas en `snake_case`.
- PK siempre `id`.
- FK siempre `{tabla_singular}_id`: `alumno_id`, `plan_id`, `usuario_id`.
- Fechas:
  - `fecha_*` para fechas de negocio;
  - `*_at` para timestamps tecnicos.
- Estados como `estado`.
- Booleanos con prefijo claro:
  - `activo`;
  - `es_excepcion`.
- Dinero:
  - `monto`;
  - `saldo`;
  - `total`;
  - `precio`.

Nombres canonicos:

- `alumnos`
- `staff`
- `planes`
- `membresias`
- `periodos_liquidables`
- `pagos`
- `pago_conceptos`
- `caja_movimientos`
- `actividades`
- `clases`
- `asistencias_clase`
- `asistencias_libre`
- `productos`
- `ventas`
- `venta_items`
- `stock_movimientos`
- `auditoria`

Evitar:

- `citas`;
- `servicios` como nucleo del sistema;
- `empleados` si el dominio necesita `staff`;
- terminos heredados de estetica.

## 8. Preparacion para QA y automatizacion

Este diseno facilita QA porque separa estados, transacciones y responsabilidades.

### Testing manual

Casos manuales base:

- buscar alumno y validar color de acceso;
- registrar pago y verificar membresia, caja y periodo;
- hacer check-in con alumno activo;
- intentar check-in con cuota vencida;
- vender producto con stock suficiente;
- intentar vender producto sin stock;
- verificar que una venta cree `stock_movimientos`;
- anular pago y verificar reversion;
- anular venta y verificar stock, caja y movimientos relacionados;
- cancelar clase y verificar que no acepte asistencia.

### Testing automatizado API

Beneficios del diseno:

- endpoints predecibles por modulo;
- respuestas JSON estables;
- codigos de error estables;
- entidades con IDs consistentes;
- estados controlados;
- validaciones testeables.

Casos criticos a automatizar:

- alumno sin usuario puede crearse;
- `staff` sin rol operativo no accede a endpoints protegidos;
- usuario con rol `profesor` y perfil `staff` puede operar sus clases;
- no se puede tener doble membresia activa;
- check-in habilita solo con periodo pagado vigente, aunque `membresias.fecha_fin` tenga otro valor;
- pago exacto de cuota actualiza periodo y caja;
- pago parcial de cuota falla con `PAGO_PARCIAL_NO_PERMITIDO`;
- check-in libre no duplica asistencia activa del mismo alumno en el mismo dia;
- check-in libre permite nuevo registro si el anterior fue anulado;
- check-in de clase no duplica asistencia;
- clase con cupo bloquea excedente;
- venta descuenta stock y crea `stock_movimientos` de salida;
- venta sin stock falla sin crear venta, caja ni movimiento de stock;
- anulacion de venta crea `stock_movimientos` de anulacion y caja relacionada;
- anulacion audita;
- excepcion requiere motivo.

### Trazabilidad

Campos y tablas que ayudan a diagnosticar:

- `auditoria`;
- `created_at`;
- `updated_at`;
- `registrado_por_usuario_id`;
- `usuario_id` en caja;
- `movimiento_relacionado_id` en caja;
- `stock_movimientos`;
- motivos en anulaciones y excepciones;
- estados claros y controlados.

### Impacto QA de refinamientos Fase 2.1

| Refinamiento | Caso positivo | Caso negativo |
|---|---|---|
| Permisos separados de perfil | Usuario con rol `profesor` y perfil `staff` ve sus clases. | Perfil `staff` sin rol `profesor` recibe 403 en endpoint de profesor. |
| Vigencia por periodos | Alumno activo con membresia activa y periodo pagado vigente puede hacer check-in. | Alumno con `fecha_fin` futura pero sin periodo pagado vigente no puede ingresar. |
| Stock trazable | Venta con stock suficiente descuenta producto y crea movimiento `salida`. | Venta sin stock no crea venta, caja ni movimiento de stock. |
| Caja relacionada | Anulacion crea movimiento vinculado al original. | Anulacion sobre movimiento inexistente falla sin tocar caja. |
| Pagos sin parciales V1 | Pago de cuota por saldo exacto deja periodo `pagado`. | Pago menor al saldo falla con `PAGO_PARCIAL_NO_PERMITIDO`. |
| Asistencia libre diaria | Primer check-in libre del dia queda `registrada`. | Segundo check-in libre del mismo dia falla, salvo que el primero este `anulada`. |

## 9. Decisiones tecnicas

### 9.1 Soft delete vs hard delete

Decision: baja logica para datos criticos.

Aplica a:

- alumnos;
- usuarios;
- staff;
- planes;
- membresias;
- pagos;
- ventas;
- productos con historial;
- clases con asistencias.

Hard delete solo para:

- datos temporales sin historial;
- registros creados por error antes de tener dependencias;
- catalogos o datos en ambiente de desarrollo.

Justificacion:

- caja, pagos y asistencias necesitan trazabilidad.

### 9.2 Manejo de fechas

Decision:

- `DATE` para periodos, vencimientos y fecha de clase;
- `TIME` para horarios;
- `DATETIME` para eventos reales: pago, check-in, venta, auditoria.

Timezone:

- aplicacion en `America/Argentina/Buenos_Aires`;
- guardar consistentemente en hora local del negocio salvo decision futura de UTC.

### 9.3 Manejo de dinero

Decision:

- usar `DECIMAL(12,2)`.

Justificacion:

- evita errores de float;
- suficiente para operacion local;
- compatible con reportes.

### 9.4 Control de concurrencia

Decision:

- usar transacciones y bloqueos donde importa.

Bloquear:

- producto al vender;
- clase al registrar asistencia con cupo;
- membresia/alumno al registrar check-in;
- periodo al registrar pago.

### 9.5 Uso de transacciones

Decision:

- obligatorias en pagos, ventas, check-in y anulaciones.

Justificacion:

- no puede existir pago sin caja;
- no puede existir venta sin stock/caja;
- no puede existir asistencia duplicada;
- no puede quedar periodo pagado sin pago real.

### 9.6 Catalogos y estados

Decision:

- roles como tabla;
- estados como `VARCHAR` validado por aplicacion al inicio;
- posibilidad de migrar estados a tablas catalogo si el negocio necesita edicion desde admin.

Justificacion:

- evita rigidez prematura;
- mantiene claridad;
- permite testing por valores controlados.

### 9.7 Alumno sin usuario

Decision:

- `alumnos.usuario_id` nullable.

Justificacion:

- operacion interna primero;
- no obliga a que cada alumno tenga login;
- habilita portal alumno en fase posterior.

### 9.8 Separacion pago/caja

Decision:

- pago, conceptos, comprobante y caja son entidades separadas.

Justificacion:

- permite trazabilidad;
- soporta pagos con varios conceptos;
- evita mezclar deuda con movimiento financiero;
- facilita anulacion y auditoria.

### 9.9 Separacion permisos/perfil

Decision:

- `usuario_roles` es la unica fuente de verdad para permisos.
- `staff` solo describe el perfil operativo de una persona interna.

Justificacion:

- evita duplicar `profesor` o `recepcion` en columnas de negocio;
- permite que una persona tenga mas de un rol;
- facilita tests de autorizacion claros.

### 9.10 Vigencia de membresia

Decision:

- modelo hibrido controlado: `membresias` conserva estado administrativo y `periodos_liquidables` decide habilitacion real de acceso.

Justificacion:

- pagos, vencimientos y check-in quedan alineados;
- evita que una fecha editada manualmente habilite acceso sin pago real;
- permite reportes rapidos usando `fecha_fin` como cache, sin convertirla en fuente de verdad.

### 9.11 Pagos parciales

Decision:

- V1 no permite pagos parciales de cuotas.
- La base conserva `saldo` y `pago_conceptos` para soportarlo en una fase futura.

Justificacion:

- reduce ambiguedad operativa en recepcion;
- simplifica QA inicial;
- mantiene camino de crecimiento sin romper modelo.

### 9.12 Stock historico

Decision:

- `productos.stock` guarda saldo actual.
- `stock_movimientos` guarda historial y auditoria.

Justificacion:

- permite reconstruir stock;
- hace trazables ventas, anulaciones y ajustes;
- evita editar stock sin motivo ni responsable.

### 9.13 Asistencia libre diaria

Decision:

- maximo una asistencia libre `registrada` por alumno por fecha operativa.
- La restriccion se implementa por logica transaccional apoyada por indice, no por unique rigido inicial.

Justificacion:

- permite anular y volver a registrar el mismo dia;
- evita duplicados por doble click o error de recepcion;
- conserva flexibilidad para reglas futuras.

## Cierre

La Fase 3 puede implementar primero el nucleo:

1. alumnos;
2. planes;
3. membresias;
4. periodos liquidables;
5. pagos;
6. caja;
7. check-in/asistencias;
8. productos/ventas/stock.

Esa es la columna vertebral real del sistema. El modelo evita heredar el concepto de `citas` del sistema anterior y organiza el dominio alrededor de membresias, pagos, asistencias y clases grupales.

