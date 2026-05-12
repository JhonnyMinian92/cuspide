# Revision entidad por entidad

Fecha: 2026-05-12  
Base revisada: `cuspide`  
Resultado: todas las entidades tienen repositorio y las relaciones nuevas quedaron reflejadas en base de datos.

## Cambios estructurales aplicados

- Se agregaron `Horario`, `Calendario`, `PlanEntrenamiento`, `PlanEntrenamientoSesion` y `PlanEntrenamientoEjercicio`.
- Se agregaron repositorios faltantes para pagos, ventas, inventario, productos, categorias deportivas y planes de pago.
- Se agregaron claves foraneas reales para las tablas nuevas y para las tablas que antes solo tenian indices.
- Se mantuvo compatibilidad con tablas/campos historicos existentes.

## Decision por entidad

| Entidad | Revision y decision |
| --- | --- |
| Actividad | Se agrego `nombre` y `estado`; el horario ya no se mezcla en observaciones. |
| ActividadMatricula | Se agrego `horario` para saber en que franja participa la matricula. |
| Alergia | Queda correcta con tipo, nivel y ficha medica; no requiere cambio inmediato. |
| Asignatura | Queda como catalogo academico; los horarios se resuelven por `Clase`/`Horario`. |
| Asistencia | Se agrego `calendario`, `horario`, `plan_entrenamiento_sesion`, `hora_entrada` y `hora_salida`. |
| Boton | Queda como configuracion de UI; sin cambios de dominio. |
| BotonUser | Ya contempla `institucion`; se agrego FK para esa relacion. |
| Calificacion | Queda vinculada a evaluacion y matricula; no requiere horario propio. |
| Calendario | Nueva entidad para eventos reales fechados y control operativo. |
| CategoriaDeportiva | Queda como segmentacion deportiva por edad/deporte/institucion; repositorio agregado. |
| Ciudad | Catalogo estable; sin cambios. |
| Clase | Queda como grupo/oferta academica o deportiva; su recurrencia pasa a `Horario`. |
| ClaseMatricula | Se agrego `horario` para seleccion de franja. |
| Correo | Contacto por persona; sin cambios. |
| CuotaMatricula | Repositorio agregado; relacion a plan de pago y estado reflejada en DB. |
| Deporte | Catalogo principal de deporte por institucion; sin cambios. |
| Enfermedad | Catalogo medico por tipo/nivel; sin cambios. |
| EnfermedadPersona | Relacion medica puntual; sin cambios. |
| Estado | Catalogo transversal; sin cambios. |
| EstadoFisico | Relacion a perfil medico y tipo fisico; sin cambios. |
| EstadoPermiso | Catalogo de permisos; sin cambios. |
| Etnia | Catalogo; sin cambios. |
| Evaluacion | Queda para mediciones/calificaciones; puede programarse mediante `Calendario` si se necesita evento. |
| FichaMedica | Ya se alineo con persona y datos clinicos basicos; sin cambio adicional. |
| FormaPago | Repositorio agregado y FK a estado. |
| Genero | Catalogo; sin cambios. |
| Horario | Nueva entidad para recurrencia semanal, cupos, entrenador, ubicacion y vigencia. |
| Incidencia | Registro disciplinario/operativo; sin cambios. |
| Institucion | Nodo multi-institucion; sin cambios. |
| Inventario | Repositorio agregado y FKs a producto, variante e institucion. |
| Matricula | Queda como contrato base de una persona con la institucion; horarios se asignan en clases/actividades matriculadas. |
| Membresia | Queda como paquete/beneficio; sin cambios. |
| Modalidad | Catalogo de modalidad; sin cambios. |
| MovimientoInventario | Repositorio agregado; relaciones a producto, variante e institucion reflejadas. |
| Nacionalidad | Catalogo; sin cambios. |
| Nivel | Catalogo de nivel general; sin cambios. |
| NivelAsignatura | Catalogo usado por actividad/clase/plan; sin cambios. |
| Operadora | Catalogo telefonico; sin cambios. |
| Pago | Repositorio agregado; relaciones a venta, matricula, forma de pago, persona e institucion reflejadas. |
| PagoDetalle | Repositorio agregado; detalle por pago/cuota/venta. |
| PerfilMedico | Queda como cabecera historica medica; sin cambios. |
| PeriodicidadPago | Repositorio agregado y FK a estado. |
| PeriodoAcademico | Queda como rango institucional; `Clase` lo referencia. |
| Permiso | Rango de ausencia/autorizacion por persona; sin cambios. |
| Persona | Identidad central; sin cambios para no mezclar rol, horario o matricula. |
| PersonaInstitucion | Relacion persona-institucion-tipo; sin cambios. |
| PlanEntrenamiento | Nueva cabecera de plan individual o grupal. |
| PlanEntrenamientoEjercicio | Nuevo detalle ordenado de ejercicios por sesion. |
| PlanEntrenamientoSesion | Nueva entidad para sesiones planificadas por semana/dia/calendario. |
| PlanPagoMatricula | Repositorio agregado; relacion a matricula, periodicidad y estado. |
| Producto | Repositorio agregado; relacion a categoria, institucion y estado. |
| ProductoCategoria | Repositorio agregado; relacion a institucion y estado. |
| ProductoVariante | Repositorio agregado; relacion a producto y estado. |
| Talla | Registro antropometrico/indumentaria por persona; sin cambios. |
| Telefono | Contacto por persona; sin cambios. |
| TipoAlergia | Catalogo; sin cambios. |
| TipoContacto | Catalogo; sin cambios. |
| TipoEnfermedad | Catalogo; sin cambios. |
| TipoEstadoFisico | Catalogo; sin cambios. |
| TipoPermiso | Catalogo; sin cambios. |
| TipoPersona | Catalogo de rol/vinculo institucional; sin cambios. |
| Torneo | Evento deportivo de rango amplio; se puede complementar con `Calendario` para fechas puntuales. |
| TorneoParticipante | Relacion torneo-matricula; sin cambios. |
| TypeUser | Catalogo de usuario; sin cambios. |
| User | Credenciales; sin cambios. |
| Venta | Repositorio agregado; relaciones a persona, matricula, institucion y estado. |
| VentaDetalle | Repositorio agregado; relaciones a venta, producto y variante. |

## Observacion de escala

La agenda queda separada en dos capas: `Horario` para recurrencia y `Calendario` para ocurrencias reales. Esa separacion permite miles de opciones sin duplicar logica: una clase o actividad puede tener varios horarios, cada horario puede generar eventos reales, y cada evento puede alimentar asistencia o una sesion del plan de entrenamiento.
