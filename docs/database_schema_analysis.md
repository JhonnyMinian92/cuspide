# Analisis de entidades, repositorios y base de datos

Fecha: 2026-05-12
Base revisada: `cuspide`
Estado: script aplicado en la base local.

## Resumen

- Entidades encontradas: 67.
- Repositorios encontrados: 67.
- La base tenia tablas para la mayor parte operativa, pero no reflejaba todas las entidades.
- Habia repositorios existentes que apuntaban a columnas ausentes o a una estructura anterior.
- Se agrego el dominio de horarios, calendario y plan de entrenamiento sin framework, usando entidades PHP puras y repositorios PDO.

## Diferencias corregidas en el SQL de alineacion

- `fichas_medicas`: el repositorio usa `persona`, `grupo_sanguineo`, `alergias`, `enfermedades_preexistentes`, `medicamentos_actuales`, `contacto_emergencia_nombre` y `contacto_emergencia_telefono`; la tabla no los tenia.
- `clases`: el repositorio usa `nombre`, `periodo_academico`, `docente`, `cupo_maximo` y `estado`; la tabla tenia un modelo anterior basado en `asignatura`, `precio`, `nivel` y `observacion`.
- `boton_user`: el repositorio usa `institucion`; la tabla no la tenia.
- `actividades_matricula`: el repositorio usa `categoria_deportiva`; la tabla no la tenia y mantenia `contratacion` como obligatoria.
- `clases_matricula`: el repositorio no usa `contrato`, pero la tabla lo tenia como obligatorio.
- `estados`: el repositorio inserta sin enviar `id`; la columna `id` necesitaba `AUTO_INCREMENT`.

## Entidades sin repositorio

Ya no quedan entidades sin repositorio. Se agregaron repositorios para:

- `CategoriaDeportiva`
- `CuotaMatricula`
- `FormaPago`
- `Inventario`
- `MovimientoInventario`
- `Pago`
- `PagoDetalle`
- `PeriodicidadPago`
- `PlanPagoMatricula`
- `Producto`
- `ProductoCategoria`
- `ProductoVariante`
- `Venta`
- `VentaDetalle`

## Horarios, calendario y entrenamiento

Se agregaron nuevas entidades, repositorios, tablas, indices y claves foraneas:

- `Horario`: agenda recurrente por dia de semana, hora, clase/actividad/categoria, entrenador, cupo y ubicacion.
- `Calendario`: evento real fechado para clase, actividad, entrenamiento, evaluacion, torneo u otros tipos operativos.
- `PlanEntrenamiento`: cabecera de plan individual o grupal, vinculable a matricula, clase, actividad y categoria deportiva.
- `PlanEntrenamientoSesion`: sesiones del plan por semana/dia/fecha, vinculables al calendario y horario.
- `PlanEntrenamientoEjercicio`: detalle ordenado de ejercicios por sesion.

Ademas:

- `actividades`: ahora tiene `nombre` y `estado`.
- `actividades_matricula`: ahora puede fijar un `horario`.
- `clases_matricula`: ahora puede fijar un `horario`.
- `asistencias`: ahora puede apuntar a `calendario`, `horario`, `plan_entrenamiento_sesion`, `hora_entrada` y `hora_salida`.

## Decisiones

- No se eliminaron tablas ni columnas antiguas. `contratacion`, `nivel_academico`, `contrato`, `asignatura`, `precio`, `nivel`, `observacion`, `fecha` y `perfil_medico` quedan para compatibilidad con datos existentes.
- Las columnas antiguas que bloqueaban nuevos inserts se vuelven opcionales.
- La fila existente de `fichas_medicas` se enlaza con `persona` usando `perfil_medico`.
- La fila existente de `clases` llena `nombre` desde `asignaturas.nombre` cuando es posible.
- No se eliminaron estructuras historicas; se agregaron relaciones nuevas con claves foraneas condicionales para mantener el script reejecutable.
