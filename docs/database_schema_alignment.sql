USE cuspide;

SET FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SESSION.sql_mode;
SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, ',NO_AUTO_VALUE_ON_ZERO');

-- Compatibility fixes for repositories that already exist.
ALTER TABLE estados MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE boton_user ADD COLUMN IF NOT EXISTS institucion int(11) NULL AFTER type_user;
ALTER TABLE boton_user ADD INDEX IF NOT EXISTS IDX_BOTON_USER_INSTITUCION (institucion);

ALTER TABLE actividades_matricula ADD COLUMN IF NOT EXISTS categoria_deportiva int(11) NULL AFTER modalidad;
ALTER TABLE actividades_matricula MODIFY COLUMN contratacion int(11) NULL;
ALTER TABLE actividades_matricula ADD INDEX IF NOT EXISTS IDX_ACT_MATRICULA_CATEGORIA (categoria_deportiva);

ALTER TABLE clases ADD COLUMN IF NOT EXISTS nombre varchar(150) NULL AFTER id;
ALTER TABLE clases ADD COLUMN IF NOT EXISTS periodo_academico int(11) NULL AFTER nombre;
ALTER TABLE clases ADD COLUMN IF NOT EXISTS docente int(11) NULL AFTER institucion;
ALTER TABLE clases ADD COLUMN IF NOT EXISTS cupo_maximo int(11) NOT NULL DEFAULT 0 AFTER docente;
ALTER TABLE clases ADD COLUMN IF NOT EXISTS estado int(11) NOT NULL DEFAULT 1 AFTER cupo_maximo;
ALTER TABLE clases MODIFY COLUMN asignatura int(11) NULL;
ALTER TABLE clases MODIFY COLUMN precio double NULL;
ALTER TABLE clases MODIFY COLUMN nivel int(11) NULL;
ALTER TABLE clases MODIFY COLUMN observacion varchar(255) NULL;
UPDATE clases c
LEFT JOIN asignaturas a ON a.id = c.asignatura
SET c.nombre = COALESCE(NULLIF(c.nombre, ''), a.nombre, CONCAT('Clase ', c.id))
WHERE c.nombre IS NULL OR c.nombre = '';
ALTER TABLE clases ADD INDEX IF NOT EXISTS IDX_CLASES_PERIODO (periodo_academico);
ALTER TABLE clases ADD INDEX IF NOT EXISTS IDX_CLASES_DOCENTE (docente);
ALTER TABLE clases ADD INDEX IF NOT EXISTS IDX_CLASES_ESTADO (estado);

ALTER TABLE clases_matricula MODIFY COLUMN contrato int(11) NULL;

ALTER TABLE fichas_medicas ADD COLUMN IF NOT EXISTS persona int(11) NULL AFTER id;
ALTER TABLE fichas_medicas ADD COLUMN IF NOT EXISTS grupo_sanguineo varchar(10) NULL AFTER persona;
ALTER TABLE fichas_medicas ADD COLUMN IF NOT EXISTS alergias text NULL AFTER grupo_sanguineo;
ALTER TABLE fichas_medicas ADD COLUMN IF NOT EXISTS enfermedades_preexistentes text NULL AFTER alergias;
ALTER TABLE fichas_medicas ADD COLUMN IF NOT EXISTS medicamentos_actuales text NULL AFTER enfermedades_preexistentes;
ALTER TABLE fichas_medicas ADD COLUMN IF NOT EXISTS contacto_emergencia_nombre varchar(150) NULL AFTER medicamentos_actuales;
ALTER TABLE fichas_medicas ADD COLUMN IF NOT EXISTS contacto_emergencia_telefono varchar(20) NULL AFTER contacto_emergencia_nombre;
ALTER TABLE fichas_medicas MODIFY COLUMN fecha datetime NOT NULL DEFAULT current_timestamp();
ALTER TABLE fichas_medicas MODIFY COLUMN perfil_medico int(11) NULL;
UPDATE fichas_medicas fm
LEFT JOIN perfil_medico pm ON pm.id = fm.perfil_medico
SET fm.persona = COALESCE(fm.persona, pm.persona)
WHERE fm.persona IS NULL;
ALTER TABLE fichas_medicas ADD INDEX IF NOT EXISTS IDX_FICHAS_PERSONA (persona);

-- Tables for entities that existed in code but not in the database.
CREATE TABLE IF NOT EXISTS categorias_deportivas (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(150) NOT NULL,
  nemonico varchar(50) NOT NULL,
  deporte int(11) NOT NULL,
  edad_minima int(11) NULL,
  edad_maxima int(11) NULL,
  descripcion varchar(255) NOT NULL DEFAULT '',
  institucion int(11) NOT NULL,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY UK_CATEGORIAS_DEPORTIVAS_NEMONICO (nemonico),
  KEY IDX_CATEGORIAS_DEPORTIVAS_DEPORTE (deporte),
  KEY IDX_CATEGORIAS_DEPORTIVAS_INSTITUCION (institucion),
  KEY IDX_CATEGORIAS_DEPORTIVAS_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS formas_pago (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(100) NOT NULL,
  nemonico varchar(50) NOT NULL,
  descripcion varchar(255) NOT NULL DEFAULT '',
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY UK_FORMAS_PAGO_NEMONICO (nemonico),
  KEY IDX_FORMAS_PAGO_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS periodicidad_pago (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(100) NOT NULL,
  nemonico varchar(50) NOT NULL,
  meses int(11) NOT NULL,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY UK_PERIODICIDAD_PAGO_NEMONICO (nemonico),
  KEY IDX_PERIODICIDAD_PAGO_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS producto_categorias (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(150) NOT NULL,
  nemonico varchar(50) NOT NULL,
  descripcion varchar(255) NOT NULL DEFAULT '',
  institucion int(11) NOT NULL,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY UK_PRODUCTO_CATEGORIAS_NEMONICO (nemonico),
  KEY IDX_PRODUCTO_CATEGORIAS_INSTITUCION (institucion),
  KEY IDX_PRODUCTO_CATEGORIAS_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS productos (
  id int(11) NOT NULL AUTO_INCREMENT,
  codigo varchar(50) NOT NULL,
  nombre varchar(150) NOT NULL,
  descripcion varchar(255) NOT NULL DEFAULT '',
  categoria int(11) NOT NULL,
  precio decimal(12,2) NOT NULL DEFAULT 0.00,
  costo decimal(12,2) NOT NULL DEFAULT 0.00,
  controla_stock tinyint(1) NOT NULL DEFAULT 1,
  institucion int(11) NOT NULL,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY UK_PRODUCTOS_CODIGO (codigo),
  KEY IDX_PRODUCTOS_CATEGORIA (categoria),
  KEY IDX_PRODUCTOS_INSTITUCION (institucion),
  KEY IDX_PRODUCTOS_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS producto_variantes (
  id int(11) NOT NULL AUTO_INCREMENT,
  producto int(11) NOT NULL,
  sku varchar(80) NOT NULL,
  talla varchar(50) NULL,
  color varchar(50) NULL,
  genero varchar(50) NULL,
  precio decimal(12,2) NOT NULL DEFAULT 0.00,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY UK_PRODUCTO_VARIANTES_SKU (sku),
  KEY IDX_PRODUCTO_VARIANTES_PRODUCTO (producto),
  KEY IDX_PRODUCTO_VARIANTES_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS inventario (
  id int(11) NOT NULL AUTO_INCREMENT,
  producto int(11) NOT NULL,
  variante int(11) NULL,
  institucion int(11) NOT NULL,
  stock_actual decimal(12,2) NOT NULL DEFAULT 0.00,
  stock_minimo decimal(12,2) NOT NULL DEFAULT 0.00,
  fecha_actualizacion datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY IDX_INVENTARIO_PRODUCTO (producto),
  KEY IDX_INVENTARIO_VARIANTE (variante),
  KEY IDX_INVENTARIO_INSTITUCION (institucion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS movimientos_inventario (
  id int(11) NOT NULL AUTO_INCREMENT,
  producto int(11) NOT NULL,
  variante int(11) NULL,
  institucion int(11) NOT NULL,
  tipo varchar(50) NOT NULL,
  cantidad decimal(12,2) NOT NULL DEFAULT 0.00,
  stock_anterior decimal(12,2) NOT NULL DEFAULT 0.00,
  stock_nuevo decimal(12,2) NOT NULL DEFAULT 0.00,
  referencia varchar(100) NULL,
  fecha datetime NOT NULL DEFAULT current_timestamp(),
  observacion varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY IDX_MOV_INV_PRODUCTO (producto),
  KEY IDX_MOV_INV_VARIANTE (variante),
  KEY IDX_MOV_INV_INSTITUCION (institucion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS plan_pago_matricula (
  id int(11) NOT NULL AUTO_INCREMENT,
  matricula int(11) NOT NULL,
  periodicidad int(11) NOT NULL,
  subtotal decimal(12,2) NOT NULL DEFAULT 0.00,
  descuento decimal(12,2) NOT NULL DEFAULT 0.00,
  total decimal(12,2) NOT NULL DEFAULT 0.00,
  saldo decimal(12,2) NOT NULL DEFAULT 0.00,
  fecha_generacion datetime NOT NULL DEFAULT current_timestamp(),
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY IDX_PLAN_PAGO_MATRICULA (matricula),
  KEY IDX_PLAN_PAGO_PERIODICIDAD (periodicidad),
  KEY IDX_PLAN_PAGO_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS cuotas_matricula (
  id int(11) NOT NULL AUTO_INCREMENT,
  plan_pago int(11) NOT NULL,
  numero int(11) NOT NULL,
  fecha_vencimiento date NOT NULL,
  valor decimal(12,2) NOT NULL DEFAULT 0.00,
  saldo decimal(12,2) NOT NULL DEFAULT 0.00,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY IDX_CUOTAS_PLAN_PAGO (plan_pago),
  KEY IDX_CUOTAS_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS ventas (
  id int(11) NOT NULL AUTO_INCREMENT,
  numero varchar(50) NOT NULL,
  fecha datetime NOT NULL DEFAULT current_timestamp(),
  persona int(11) NOT NULL,
  matricula int(11) NULL,
  institucion int(11) NOT NULL,
  tipo varchar(50) NOT NULL,
  subtotal decimal(12,2) NOT NULL DEFAULT 0.00,
  descuento decimal(12,2) NOT NULL DEFAULT 0.00,
  impuesto decimal(12,2) NOT NULL DEFAULT 0.00,
  total decimal(12,2) NOT NULL DEFAULT 0.00,
  saldo decimal(12,2) NOT NULL DEFAULT 0.00,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY UK_VENTAS_NUMERO (numero),
  KEY IDX_VENTAS_PERSONA (persona),
  KEY IDX_VENTAS_MATRICULA (matricula),
  KEY IDX_VENTAS_INSTITUCION (institucion),
  KEY IDX_VENTAS_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS venta_detalle (
  id int(11) NOT NULL AUTO_INCREMENT,
  venta int(11) NOT NULL,
  producto int(11) NOT NULL,
  variante int(11) NULL,
  cantidad decimal(12,2) NOT NULL DEFAULT 0.00,
  precio_unitario decimal(12,2) NOT NULL DEFAULT 0.00,
  descuento decimal(12,2) NOT NULL DEFAULT 0.00,
  total decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id),
  KEY IDX_VENTA_DETALLE_VENTA (venta),
  KEY IDX_VENTA_DETALLE_PRODUCTO (producto),
  KEY IDX_VENTA_DETALLE_VARIANTE (variante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS pagos (
  id int(11) NOT NULL AUTO_INCREMENT,
  numero varchar(50) NOT NULL,
  fecha datetime NOT NULL DEFAULT current_timestamp(),
  persona int(11) NOT NULL,
  matricula int(11) NULL,
  venta int(11) NULL,
  forma_pago int(11) NOT NULL,
  institucion int(11) NOT NULL,
  monto decimal(12,2) NOT NULL DEFAULT 0.00,
  referencia varchar(100) NULL,
  observacion varchar(255) NOT NULL DEFAULT '',
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY UK_PAGOS_NUMERO (numero),
  KEY IDX_PAGOS_PERSONA (persona),
  KEY IDX_PAGOS_MATRICULA (matricula),
  KEY IDX_PAGOS_VENTA (venta),
  KEY IDX_PAGOS_FORMA_PAGO (forma_pago),
  KEY IDX_PAGOS_INSTITUCION (institucion),
  KEY IDX_PAGOS_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS pago_detalle (
  id int(11) NOT NULL AUTO_INCREMENT,
  pago int(11) NOT NULL,
  cuota_matricula int(11) NULL,
  venta int(11) NULL,
  valor decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id),
  KEY IDX_PAGO_DETALLE_PAGO (pago),
  KEY IDX_PAGO_DETALLE_CUOTA (cuota_matricula),
  KEY IDX_PAGO_DETALLE_VENTA (venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Scheduling, calendar and training-plan domain.
ALTER TABLE actividades ADD COLUMN IF NOT EXISTS nombre varchar(150) NOT NULL DEFAULT '' AFTER id;
ALTER TABLE actividades ADD COLUMN IF NOT EXISTS estado int(11) NOT NULL DEFAULT 1 AFTER institucion;
UPDATE actividades a
LEFT JOIN deportes d ON d.id = a.deporte
LEFT JOIN nivel_asignatura n ON n.id = a.nivel
SET a.nombre = COALESCE(NULLIF(a.nombre, ''), CONCAT(COALESCE(d.nombre, 'Actividad'), ' ', COALESCE(n.nombre, a.id)))
WHERE a.nombre IS NULL OR a.nombre = '';
ALTER TABLE actividades ADD INDEX IF NOT EXISTS IDX_ACTIVIDADES_ESTADO (estado);

CREATE TABLE IF NOT EXISTS horarios (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(150) NOT NULL,
  institucion int(11) NOT NULL,
  clase int(11) NULL,
  actividad int(11) NULL,
  categoria_deportiva int(11) NULL,
  entrenador int(11) NULL,
  dia_semana tinyint(1) NOT NULL,
  hora_inicio time NOT NULL,
  hora_fin time NOT NULL,
  fecha_inicio date NULL,
  fecha_fin date NULL,
  cupo_maximo int(11) NOT NULL DEFAULT 0,
  ubicacion varchar(150) NOT NULL DEFAULT '',
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY IDX_HORARIOS_INSTITUCION (institucion),
  KEY IDX_HORARIOS_CLASE (clase),
  KEY IDX_HORARIOS_ACTIVIDAD (actividad),
  KEY IDX_HORARIOS_CATEGORIA (categoria_deportiva),
  KEY IDX_HORARIOS_ENTRENADOR (entrenador),
  KEY IDX_HORARIOS_DIA_HORA (dia_semana, hora_inicio, hora_fin),
  KEY IDX_HORARIOS_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS planes_entrenamiento (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(150) NOT NULL,
  objetivo varchar(255) NOT NULL DEFAULT '',
  descripcion text NULL,
  matricula int(11) NULL,
  clase int(11) NULL,
  actividad int(11) NULL,
  categoria_deportiva int(11) NULL,
  entrenador int(11) NULL,
  institucion int(11) NOT NULL,
  nivel int(11) NULL,
  fecha_inicio date NOT NULL,
  fecha_fin date NOT NULL,
  version int(11) NOT NULL DEFAULT 1,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY IDX_PLAN_ENT_MATRICULA (matricula),
  KEY IDX_PLAN_ENT_CLASE (clase),
  KEY IDX_PLAN_ENT_ACTIVIDAD (actividad),
  KEY IDX_PLAN_ENT_CATEGORIA (categoria_deportiva),
  KEY IDX_PLAN_ENT_ENTRENADOR (entrenador),
  KEY IDX_PLAN_ENT_INSTITUCION (institucion),
  KEY IDX_PLAN_ENT_NIVEL (nivel),
  KEY IDX_PLAN_ENT_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS calendarios (
  id int(11) NOT NULL AUTO_INCREMENT,
  titulo varchar(150) NOT NULL,
  tipo varchar(50) NOT NULL,
  institucion int(11) NOT NULL,
  horario int(11) NULL,
  clase int(11) NULL,
  actividad int(11) NULL,
  plan_entrenamiento int(11) NULL,
  entrenador int(11) NULL,
  fecha_inicio datetime NOT NULL,
  fecha_fin datetime NOT NULL,
  ubicacion varchar(150) NOT NULL DEFAULT '',
  cupo_maximo int(11) NOT NULL DEFAULT 0,
  observacion text NULL,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY IDX_CALENDARIOS_TIPO (tipo),
  KEY IDX_CALENDARIOS_INSTITUCION (institucion),
  KEY IDX_CALENDARIOS_HORARIO (horario),
  KEY IDX_CALENDARIOS_CLASE (clase),
  KEY IDX_CALENDARIOS_ACTIVIDAD (actividad),
  KEY IDX_CALENDARIOS_PLAN (plan_entrenamiento),
  KEY IDX_CALENDARIOS_ENTRENADOR (entrenador),
  KEY IDX_CALENDARIOS_FECHAS (fecha_inicio, fecha_fin),
  KEY IDX_CALENDARIOS_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS plan_entrenamiento_sesiones (
  id int(11) NOT NULL AUTO_INCREMENT,
  plan_entrenamiento int(11) NOT NULL,
  calendario int(11) NULL,
  horario int(11) NULL,
  numero_semana int(11) NOT NULL DEFAULT 1,
  dia_semana tinyint(1) NULL,
  fecha_programada date NULL,
  titulo varchar(150) NOT NULL,
  objetivo varchar(255) NOT NULL DEFAULT '',
  duracion_minutos int(11) NOT NULL DEFAULT 0,
  intensidad varchar(50) NOT NULL DEFAULT '',
  contenido text NOT NULL,
  observacion text NULL,
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY IDX_PLAN_SESIONES_PLAN (plan_entrenamiento),
  KEY IDX_PLAN_SESIONES_CALENDARIO (calendario),
  KEY IDX_PLAN_SESIONES_HORARIO (horario),
  KEY IDX_PLAN_SESIONES_ORDEN (plan_entrenamiento, numero_semana, dia_semana),
  KEY IDX_PLAN_SESIONES_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS plan_entrenamiento_ejercicios (
  id int(11) NOT NULL AUTO_INCREMENT,
  sesion int(11) NOT NULL,
  orden int(11) NOT NULL DEFAULT 1,
  nombre varchar(150) NOT NULL,
  descripcion text NOT NULL,
  series int(11) NOT NULL DEFAULT 0,
  repeticiones varchar(50) NOT NULL DEFAULT '',
  duracion_segundos int(11) NOT NULL DEFAULT 0,
  descanso_segundos int(11) NOT NULL DEFAULT 0,
  intensidad varchar(50) NOT NULL DEFAULT '',
  estado int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY IDX_PLAN_EJERCICIOS_SESION (sesion),
  KEY IDX_PLAN_EJERCICIOS_ORDEN (sesion, orden),
  KEY IDX_PLAN_EJERCICIOS_ESTADO (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE actividades_matricula ADD COLUMN IF NOT EXISTS horario int(11) NULL AFTER categoria_deportiva;
ALTER TABLE actividades_matricula ADD INDEX IF NOT EXISTS IDX_ACT_MATRICULA_HORARIO (horario);

ALTER TABLE clases_matricula ADD COLUMN IF NOT EXISTS horario int(11) NULL AFTER modalidad;
ALTER TABLE clases_matricula ADD INDEX IF NOT EXISTS IDX_CLASE_MATRICULA_HORARIO (horario);

ALTER TABLE asistencias ADD COLUMN IF NOT EXISTS calendario int(11) NULL AFTER actividad;
ALTER TABLE asistencias ADD COLUMN IF NOT EXISTS horario int(11) NULL AFTER calendario;
ALTER TABLE asistencias ADD COLUMN IF NOT EXISTS plan_entrenamiento_sesion int(11) NULL AFTER horario;
ALTER TABLE asistencias ADD COLUMN IF NOT EXISTS hora_entrada time NULL AFTER plan_entrenamiento_sesion;
ALTER TABLE asistencias ADD COLUMN IF NOT EXISTS hora_salida time NULL AFTER hora_entrada;
ALTER TABLE asistencias ADD INDEX IF NOT EXISTS IDX_ASISTENCIAS_CALENDARIO (calendario);
ALTER TABLE asistencias ADD INDEX IF NOT EXISTS IDX_ASISTENCIAS_HORARIO (horario);
ALTER TABLE asistencias ADD INDEX IF NOT EXISTS IDX_ASISTENCIAS_PLAN_SESION (plan_entrenamiento_sesion);

DELIMITER //
CREATE OR REPLACE PROCEDURE add_fk_if_missing(IN tableName varchar(64), IN constraintName varchar(64), IN alterSql text)
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = tableName
      AND CONSTRAINT_NAME = constraintName
  ) THEN
    SET @stmt = alterSql;
    PREPARE prepared_stmt FROM @stmt;
    EXECUTE prepared_stmt;
    DEALLOCATE PREPARE prepared_stmt;
  END IF;
END//
DELIMITER ;

CALL add_fk_if_missing('actividades', 'FK_ACTIVIDADES_ESTADO', 'ALTER TABLE actividades ADD CONSTRAINT FK_ACTIVIDADES_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('boton_user', 'FK_BOTON_USER_INSTITUCION', 'ALTER TABLE boton_user ADD CONSTRAINT FK_BOTON_USER_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('clases', 'FK_CLASES_PERIODO', 'ALTER TABLE clases ADD CONSTRAINT FK_CLASES_PERIODO FOREIGN KEY (periodo_academico) REFERENCES periodos_academicos(id)');
CALL add_fk_if_missing('clases', 'FK_CLASES_DOCENTE', 'ALTER TABLE clases ADD CONSTRAINT FK_CLASES_DOCENTE FOREIGN KEY (docente) REFERENCES personas(id)');
CALL add_fk_if_missing('clases', 'FK_CLASES_ESTADO', 'ALTER TABLE clases ADD CONSTRAINT FK_CLASES_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('fichas_medicas', 'FK_FICHAS_MEDICAS_PERSONA', 'ALTER TABLE fichas_medicas ADD CONSTRAINT FK_FICHAS_MEDICAS_PERSONA FOREIGN KEY (persona) REFERENCES personas(id)');
CALL add_fk_if_missing('actividades_matricula', 'FK_ACT_MATRICULA_CATEGORIA', 'ALTER TABLE actividades_matricula ADD CONSTRAINT FK_ACT_MATRICULA_CATEGORIA FOREIGN KEY (categoria_deportiva) REFERENCES categorias_deportivas(id)');

CALL add_fk_if_missing('horarios', 'FK_HORARIOS_INSTITUCION', 'ALTER TABLE horarios ADD CONSTRAINT FK_HORARIOS_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('horarios', 'FK_HORARIOS_CLASE', 'ALTER TABLE horarios ADD CONSTRAINT FK_HORARIOS_CLASE FOREIGN KEY (clase) REFERENCES clases(id)');
CALL add_fk_if_missing('horarios', 'FK_HORARIOS_ACTIVIDAD', 'ALTER TABLE horarios ADD CONSTRAINT FK_HORARIOS_ACTIVIDAD FOREIGN KEY (actividad) REFERENCES actividades(id)');
CALL add_fk_if_missing('horarios', 'FK_HORARIOS_CATEGORIA', 'ALTER TABLE horarios ADD CONSTRAINT FK_HORARIOS_CATEGORIA FOREIGN KEY (categoria_deportiva) REFERENCES categorias_deportivas(id)');
CALL add_fk_if_missing('horarios', 'FK_HORARIOS_ENTRENADOR', 'ALTER TABLE horarios ADD CONSTRAINT FK_HORARIOS_ENTRENADOR FOREIGN KEY (entrenador) REFERENCES personas(id)');
CALL add_fk_if_missing('horarios', 'FK_HORARIOS_ESTADO', 'ALTER TABLE horarios ADD CONSTRAINT FK_HORARIOS_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');

CALL add_fk_if_missing('planes_entrenamiento', 'FK_PLAN_ENT_MATRICULA', 'ALTER TABLE planes_entrenamiento ADD CONSTRAINT FK_PLAN_ENT_MATRICULA FOREIGN KEY (matricula) REFERENCES matricula(id)');
CALL add_fk_if_missing('planes_entrenamiento', 'FK_PLAN_ENT_CLASE', 'ALTER TABLE planes_entrenamiento ADD CONSTRAINT FK_PLAN_ENT_CLASE FOREIGN KEY (clase) REFERENCES clases(id)');
CALL add_fk_if_missing('planes_entrenamiento', 'FK_PLAN_ENT_ACTIVIDAD', 'ALTER TABLE planes_entrenamiento ADD CONSTRAINT FK_PLAN_ENT_ACTIVIDAD FOREIGN KEY (actividad) REFERENCES actividades(id)');
CALL add_fk_if_missing('planes_entrenamiento', 'FK_PLAN_ENT_CATEGORIA', 'ALTER TABLE planes_entrenamiento ADD CONSTRAINT FK_PLAN_ENT_CATEGORIA FOREIGN KEY (categoria_deportiva) REFERENCES categorias_deportivas(id)');
CALL add_fk_if_missing('planes_entrenamiento', 'FK_PLAN_ENT_ENTRENADOR', 'ALTER TABLE planes_entrenamiento ADD CONSTRAINT FK_PLAN_ENT_ENTRENADOR FOREIGN KEY (entrenador) REFERENCES personas(id)');
CALL add_fk_if_missing('planes_entrenamiento', 'FK_PLAN_ENT_INSTITUCION', 'ALTER TABLE planes_entrenamiento ADD CONSTRAINT FK_PLAN_ENT_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('planes_entrenamiento', 'FK_PLAN_ENT_NIVEL', 'ALTER TABLE planes_entrenamiento ADD CONSTRAINT FK_PLAN_ENT_NIVEL FOREIGN KEY (nivel) REFERENCES nivel_asignatura(id)');
CALL add_fk_if_missing('planes_entrenamiento', 'FK_PLAN_ENT_ESTADO', 'ALTER TABLE planes_entrenamiento ADD CONSTRAINT FK_PLAN_ENT_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');

CALL add_fk_if_missing('calendarios', 'FK_CALENDARIOS_INSTITUCION', 'ALTER TABLE calendarios ADD CONSTRAINT FK_CALENDARIOS_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('calendarios', 'FK_CALENDARIOS_HORARIO', 'ALTER TABLE calendarios ADD CONSTRAINT FK_CALENDARIOS_HORARIO FOREIGN KEY (horario) REFERENCES horarios(id)');
CALL add_fk_if_missing('calendarios', 'FK_CALENDARIOS_CLASE', 'ALTER TABLE calendarios ADD CONSTRAINT FK_CALENDARIOS_CLASE FOREIGN KEY (clase) REFERENCES clases(id)');
CALL add_fk_if_missing('calendarios', 'FK_CALENDARIOS_ACTIVIDAD', 'ALTER TABLE calendarios ADD CONSTRAINT FK_CALENDARIOS_ACTIVIDAD FOREIGN KEY (actividad) REFERENCES actividades(id)');
CALL add_fk_if_missing('calendarios', 'FK_CALENDARIOS_PLAN', 'ALTER TABLE calendarios ADD CONSTRAINT FK_CALENDARIOS_PLAN FOREIGN KEY (plan_entrenamiento) REFERENCES planes_entrenamiento(id)');
CALL add_fk_if_missing('calendarios', 'FK_CALENDARIOS_ENTRENADOR', 'ALTER TABLE calendarios ADD CONSTRAINT FK_CALENDARIOS_ENTRENADOR FOREIGN KEY (entrenador) REFERENCES personas(id)');
CALL add_fk_if_missing('calendarios', 'FK_CALENDARIOS_ESTADO', 'ALTER TABLE calendarios ADD CONSTRAINT FK_CALENDARIOS_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');

CALL add_fk_if_missing('plan_entrenamiento_sesiones', 'FK_PLAN_SESIONES_PLAN', 'ALTER TABLE plan_entrenamiento_sesiones ADD CONSTRAINT FK_PLAN_SESIONES_PLAN FOREIGN KEY (plan_entrenamiento) REFERENCES planes_entrenamiento(id)');
CALL add_fk_if_missing('plan_entrenamiento_sesiones', 'FK_PLAN_SESIONES_CALENDARIO', 'ALTER TABLE plan_entrenamiento_sesiones ADD CONSTRAINT FK_PLAN_SESIONES_CALENDARIO FOREIGN KEY (calendario) REFERENCES calendarios(id)');
CALL add_fk_if_missing('plan_entrenamiento_sesiones', 'FK_PLAN_SESIONES_HORARIO', 'ALTER TABLE plan_entrenamiento_sesiones ADD CONSTRAINT FK_PLAN_SESIONES_HORARIO FOREIGN KEY (horario) REFERENCES horarios(id)');
CALL add_fk_if_missing('plan_entrenamiento_sesiones', 'FK_PLAN_SESIONES_ESTADO', 'ALTER TABLE plan_entrenamiento_sesiones ADD CONSTRAINT FK_PLAN_SESIONES_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('plan_entrenamiento_ejercicios', 'FK_PLAN_EJERCICIOS_SESION', 'ALTER TABLE plan_entrenamiento_ejercicios ADD CONSTRAINT FK_PLAN_EJERCICIOS_SESION FOREIGN KEY (sesion) REFERENCES plan_entrenamiento_sesiones(id)');
CALL add_fk_if_missing('plan_entrenamiento_ejercicios', 'FK_PLAN_EJERCICIOS_ESTADO', 'ALTER TABLE plan_entrenamiento_ejercicios ADD CONSTRAINT FK_PLAN_EJERCICIOS_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');

CALL add_fk_if_missing('actividades_matricula', 'FK_ACT_MATRICULA_HORARIO', 'ALTER TABLE actividades_matricula ADD CONSTRAINT FK_ACT_MATRICULA_HORARIO FOREIGN KEY (horario) REFERENCES horarios(id)');
CALL add_fk_if_missing('clases_matricula', 'FK_CLASE_MATRICULA_HORARIO', 'ALTER TABLE clases_matricula ADD CONSTRAINT FK_CLASE_MATRICULA_HORARIO FOREIGN KEY (horario) REFERENCES horarios(id)');
CALL add_fk_if_missing('asistencias', 'FK_ASISTENCIAS_CALENDARIO', 'ALTER TABLE asistencias ADD CONSTRAINT FK_ASISTENCIAS_CALENDARIO FOREIGN KEY (calendario) REFERENCES calendarios(id)');
CALL add_fk_if_missing('asistencias', 'FK_ASISTENCIAS_HORARIO', 'ALTER TABLE asistencias ADD CONSTRAINT FK_ASISTENCIAS_HORARIO FOREIGN KEY (horario) REFERENCES horarios(id)');
CALL add_fk_if_missing('asistencias', 'FK_ASISTENCIAS_PLAN_SESION', 'ALTER TABLE asistencias ADD CONSTRAINT FK_ASISTENCIAS_PLAN_SESION FOREIGN KEY (plan_entrenamiento_sesion) REFERENCES plan_entrenamiento_sesiones(id)');

CALL add_fk_if_missing('categorias_deportivas', 'FK_CATEGORIAS_DEPORTE', 'ALTER TABLE categorias_deportivas ADD CONSTRAINT FK_CATEGORIAS_DEPORTE FOREIGN KEY (deporte) REFERENCES deportes(id)');
CALL add_fk_if_missing('categorias_deportivas', 'FK_CATEGORIAS_INSTITUCION', 'ALTER TABLE categorias_deportivas ADD CONSTRAINT FK_CATEGORIAS_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('categorias_deportivas', 'FK_CATEGORIAS_ESTADO', 'ALTER TABLE categorias_deportivas ADD CONSTRAINT FK_CATEGORIAS_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('formas_pago', 'FK_FORMAS_PAGO_ESTADO', 'ALTER TABLE formas_pago ADD CONSTRAINT FK_FORMAS_PAGO_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('periodicidad_pago', 'FK_PERIODICIDAD_PAGO_ESTADO', 'ALTER TABLE periodicidad_pago ADD CONSTRAINT FK_PERIODICIDAD_PAGO_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('producto_categorias', 'FK_PRODUCTO_CATEGORIAS_INSTITUCION', 'ALTER TABLE producto_categorias ADD CONSTRAINT FK_PRODUCTO_CATEGORIAS_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('producto_categorias', 'FK_PRODUCTO_CATEGORIAS_ESTADO', 'ALTER TABLE producto_categorias ADD CONSTRAINT FK_PRODUCTO_CATEGORIAS_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('productos', 'FK_PRODUCTOS_CATEGORIA', 'ALTER TABLE productos ADD CONSTRAINT FK_PRODUCTOS_CATEGORIA FOREIGN KEY (categoria) REFERENCES producto_categorias(id)');
CALL add_fk_if_missing('productos', 'FK_PRODUCTOS_INSTITUCION', 'ALTER TABLE productos ADD CONSTRAINT FK_PRODUCTOS_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('productos', 'FK_PRODUCTOS_ESTADO', 'ALTER TABLE productos ADD CONSTRAINT FK_PRODUCTOS_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('producto_variantes', 'FK_PRODUCTO_VARIANTES_PRODUCTO', 'ALTER TABLE producto_variantes ADD CONSTRAINT FK_PRODUCTO_VARIANTES_PRODUCTO FOREIGN KEY (producto) REFERENCES productos(id)');
CALL add_fk_if_missing('producto_variantes', 'FK_PRODUCTO_VARIANTES_ESTADO', 'ALTER TABLE producto_variantes ADD CONSTRAINT FK_PRODUCTO_VARIANTES_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('inventario', 'FK_INVENTARIO_PRODUCTO', 'ALTER TABLE inventario ADD CONSTRAINT FK_INVENTARIO_PRODUCTO FOREIGN KEY (producto) REFERENCES productos(id)');
CALL add_fk_if_missing('inventario', 'FK_INVENTARIO_VARIANTE', 'ALTER TABLE inventario ADD CONSTRAINT FK_INVENTARIO_VARIANTE FOREIGN KEY (variante) REFERENCES producto_variantes(id)');
CALL add_fk_if_missing('inventario', 'FK_INVENTARIO_INSTITUCION', 'ALTER TABLE inventario ADD CONSTRAINT FK_INVENTARIO_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('movimientos_inventario', 'FK_MOV_INV_PRODUCTO', 'ALTER TABLE movimientos_inventario ADD CONSTRAINT FK_MOV_INV_PRODUCTO FOREIGN KEY (producto) REFERENCES productos(id)');
CALL add_fk_if_missing('movimientos_inventario', 'FK_MOV_INV_VARIANTE', 'ALTER TABLE movimientos_inventario ADD CONSTRAINT FK_MOV_INV_VARIANTE FOREIGN KEY (variante) REFERENCES producto_variantes(id)');
CALL add_fk_if_missing('movimientos_inventario', 'FK_MOV_INV_INSTITUCION', 'ALTER TABLE movimientos_inventario ADD CONSTRAINT FK_MOV_INV_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('plan_pago_matricula', 'FK_PLAN_PAGO_MATRICULA', 'ALTER TABLE plan_pago_matricula ADD CONSTRAINT FK_PLAN_PAGO_MATRICULA FOREIGN KEY (matricula) REFERENCES matricula(id)');
CALL add_fk_if_missing('plan_pago_matricula', 'FK_PLAN_PAGO_PERIODICIDAD', 'ALTER TABLE plan_pago_matricula ADD CONSTRAINT FK_PLAN_PAGO_PERIODICIDAD FOREIGN KEY (periodicidad) REFERENCES periodicidad_pago(id)');
CALL add_fk_if_missing('plan_pago_matricula', 'FK_PLAN_PAGO_ESTADO', 'ALTER TABLE plan_pago_matricula ADD CONSTRAINT FK_PLAN_PAGO_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('cuotas_matricula', 'FK_CUOTAS_PLAN_PAGO', 'ALTER TABLE cuotas_matricula ADD CONSTRAINT FK_CUOTAS_PLAN_PAGO FOREIGN KEY (plan_pago) REFERENCES plan_pago_matricula(id)');
CALL add_fk_if_missing('cuotas_matricula', 'FK_CUOTAS_ESTADO', 'ALTER TABLE cuotas_matricula ADD CONSTRAINT FK_CUOTAS_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('ventas', 'FK_VENTAS_PERSONA', 'ALTER TABLE ventas ADD CONSTRAINT FK_VENTAS_PERSONA FOREIGN KEY (persona) REFERENCES personas(id)');
CALL add_fk_if_missing('ventas', 'FK_VENTAS_MATRICULA', 'ALTER TABLE ventas ADD CONSTRAINT FK_VENTAS_MATRICULA FOREIGN KEY (matricula) REFERENCES matricula(id)');
CALL add_fk_if_missing('ventas', 'FK_VENTAS_INSTITUCION', 'ALTER TABLE ventas ADD CONSTRAINT FK_VENTAS_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('ventas', 'FK_VENTAS_ESTADO', 'ALTER TABLE ventas ADD CONSTRAINT FK_VENTAS_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('venta_detalle', 'FK_VENTA_DETALLE_VENTA', 'ALTER TABLE venta_detalle ADD CONSTRAINT FK_VENTA_DETALLE_VENTA FOREIGN KEY (venta) REFERENCES ventas(id)');
CALL add_fk_if_missing('venta_detalle', 'FK_VENTA_DETALLE_PRODUCTO', 'ALTER TABLE venta_detalle ADD CONSTRAINT FK_VENTA_DETALLE_PRODUCTO FOREIGN KEY (producto) REFERENCES productos(id)');
CALL add_fk_if_missing('venta_detalle', 'FK_VENTA_DETALLE_VARIANTE', 'ALTER TABLE venta_detalle ADD CONSTRAINT FK_VENTA_DETALLE_VARIANTE FOREIGN KEY (variante) REFERENCES producto_variantes(id)');
CALL add_fk_if_missing('pagos', 'FK_PAGOS_PERSONA', 'ALTER TABLE pagos ADD CONSTRAINT FK_PAGOS_PERSONA FOREIGN KEY (persona) REFERENCES personas(id)');
CALL add_fk_if_missing('pagos', 'FK_PAGOS_MATRICULA', 'ALTER TABLE pagos ADD CONSTRAINT FK_PAGOS_MATRICULA FOREIGN KEY (matricula) REFERENCES matricula(id)');
CALL add_fk_if_missing('pagos', 'FK_PAGOS_VENTA', 'ALTER TABLE pagos ADD CONSTRAINT FK_PAGOS_VENTA FOREIGN KEY (venta) REFERENCES ventas(id)');
CALL add_fk_if_missing('pagos', 'FK_PAGOS_FORMA_PAGO', 'ALTER TABLE pagos ADD CONSTRAINT FK_PAGOS_FORMA_PAGO FOREIGN KEY (forma_pago) REFERENCES formas_pago(id)');
CALL add_fk_if_missing('pagos', 'FK_PAGOS_INSTITUCION', 'ALTER TABLE pagos ADD CONSTRAINT FK_PAGOS_INSTITUCION FOREIGN KEY (institucion) REFERENCES instituciones(id)');
CALL add_fk_if_missing('pagos', 'FK_PAGOS_ESTADO', 'ALTER TABLE pagos ADD CONSTRAINT FK_PAGOS_ESTADO FOREIGN KEY (estado) REFERENCES estados(id)');
CALL add_fk_if_missing('pago_detalle', 'FK_PAGO_DETALLE_PAGO', 'ALTER TABLE pago_detalle ADD CONSTRAINT FK_PAGO_DETALLE_PAGO FOREIGN KEY (pago) REFERENCES pagos(id)');
CALL add_fk_if_missing('pago_detalle', 'FK_PAGO_DETALLE_CUOTA', 'ALTER TABLE pago_detalle ADD CONSTRAINT FK_PAGO_DETALLE_CUOTA FOREIGN KEY (cuota_matricula) REFERENCES cuotas_matricula(id)');
CALL add_fk_if_missing('pago_detalle', 'FK_PAGO_DETALLE_VENTA', 'ALTER TABLE pago_detalle ADD CONSTRAINT FK_PAGO_DETALLE_VENTA FOREIGN KEY (venta) REFERENCES ventas(id)');

DROP PROCEDURE add_fk_if_missing;

SET FOREIGN_KEY_CHECKS = 1;
SET SESSION sql_mode = @OLD_SQL_MODE;
