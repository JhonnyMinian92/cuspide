-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.17.0.7270
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla cuspide.actividades
CREATE TABLE IF NOT EXISTS `actividades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `periodo_academico` int(11) NOT NULL,
  `categoria_deportiva` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `nivel` int(11) NOT NULL,
  `cupo_maximo` int(11) NOT NULL DEFAULT 0,
  `observacion` text DEFAULT NULL,
  `docente` int(11) NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `institucion` (`institucion`),
  KEY `2` (`categoria_deportiva`),
  KEY `3` (`nivel`),
  KEY `4` (`periodo_academico`),
  KEY `5` (`docente`),
  CONSTRAINT `1` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  CONSTRAINT `2` FOREIGN KEY (`categoria_deportiva`) REFERENCES `categorias_deportivas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `3` FOREIGN KEY (`nivel`) REFERENCES `nivel_asignatura` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `4` FOREIGN KEY (`periodo_academico`) REFERENCES `periodos_academicos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `5` FOREIGN KEY (`docente`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.actividades: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.actividades_matricula
CREATE TABLE IF NOT EXISTS `actividades_matricula` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actividad` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `modalidad` int(11) NOT NULL,
  `horario` int(11) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IDX_ACT_MATRICULA_ACTIVIDAD` (`actividad`),
  KEY `IDX_ACT_MATRICULA_MATRICULA` (`matricula`),
  KEY `IDX_ACT_MATRICULA_MODALIDAD` (`modalidad`),
  KEY `IDX_ACT_MATRICULA_HORARIO` (`horario`),
  KEY `IDX_ACT_MATRICULA_ESTADO` (`estado`),
  CONSTRAINT `FK_ACT_MAT_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  CONSTRAINT `FK_ACT_MAT_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_ACT_MAT_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`),
  CONSTRAINT `FK_ACT_MAT_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  CONSTRAINT `FK_ACT_MAT_MODALIDAD` FOREIGN KEY (`modalidad`) REFERENCES `modalidades` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.actividades_matricula: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.alergias
CREATE TABLE IF NOT EXISTS `alergias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `nivel` int(11) NOT NULL,
  `ficha_medica` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_ALERGIAS_TIPO_ALERGIAS` (`tipo`),
  KEY `FK2_ALERGIAS_NIVEL` (`nivel`),
  KEY `FK3_ALERGIAS_FICHA_MEDICA` (`ficha_medica`),
  CONSTRAINT `FK1_ALERGIAS_TIPO_ALERGIAS` FOREIGN KEY (`tipo`) REFERENCES `tipo_alergias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_ALERGIAS_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_ALERGIAS_FICHA_MEDICA` FOREIGN KEY (`ficha_medica`) REFERENCES `fichas_medicas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.alergias: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.asignatura_clase
CREATE TABLE IF NOT EXISTS `asignatura_clase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asignatura` int(11) NOT NULL,
  `clase` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_ASIGNATURA_CLASE` (`asignatura`,`clase`),
  KEY `IDX_ASIGNATURA_CLASE_ASIGNATURA` (`asignatura`),
  KEY `IDX_ASIGNATURA_CLASE_CLASE` (`clase`),
  KEY `IDX_ASIGNATURA_CLASE_ESTADO` (`estado`),
  CONSTRAINT `FK_ASIG_CLASE_ASIGNATURA` FOREIGN KEY (`asignatura`) REFERENCES `asignaturas` (`id`),
  CONSTRAINT `FK_ASIG_CLASE_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  CONSTRAINT `FK_ASIG_CLASE_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.asignatura_clase: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.asignaturas
CREATE TABLE IF NOT EXISTS `asignaturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK2_ASIGNATURA_INSTITUCION` (`institucion`),
  KEY `FK3_ASIGNATURA_ESTADO` (`estado`),
  CONSTRAINT `FK2_ASIGNATURA_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_ASIGNATURA_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.asignaturas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.asistencias
CREATE TABLE IF NOT EXISTS `asistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `matricula` int(11) NOT NULL,
  `clase` int(11) DEFAULT NULL,
  `actividad` int(11) DEFAULT NULL,
  `calendario` int(11) DEFAULT NULL,
  `horario` int(11) DEFAULT NULL,
  `plan_entrenamiento_sesion` int(11) DEFAULT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IDX_ASISTENCIAS_CALENDARIO` (`calendario`),
  KEY `IDX_ASISTENCIAS_HORARIO` (`horario`),
  KEY `IDX_ASISTENCIAS_PLAN_SESION` (`plan_entrenamiento_sesion`),
  KEY `FK_ASISTENCIAS_MATRICULA` (`matricula`),
  KEY `FK_ASISTENCIAS_CLASE` (`clase`),
  KEY `FK_ASISTENCIAS_ACTIVIDAD` (`actividad`),
  KEY `FK_ASISTENCIAS_ESTADO` (`estado`),
  CONSTRAINT `FK_ASISTENCIAS_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  CONSTRAINT `FK_ASISTENCIAS_CALENDARIO` FOREIGN KEY (`calendario`) REFERENCES `calendarios` (`id`),
  CONSTRAINT `FK_ASISTENCIAS_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  CONSTRAINT `FK_ASISTENCIAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_ASISTENCIAS_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`),
  CONSTRAINT `FK_ASISTENCIAS_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  CONSTRAINT `FK_ASISTENCIAS_PLAN_SESION` FOREIGN KEY (`plan_entrenamiento_sesion`) REFERENCES `plan_entrenamiento_sesiones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.asistencias: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.boton_user
CREATE TABLE IF NOT EXISTS `boton_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boton` int(11) NOT NULL,
  `type_user` varchar(100) NOT NULL,
  `institucion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_BOTON_USER` (`boton`),
  KEY `FK2_TYPE_USER` (`type_user`),
  KEY `IDX_BOTON_USER_INSTITUCION` (`institucion`),
  CONSTRAINT `FK1_BOTON_USER` FOREIGN KEY (`boton`) REFERENCES `botones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_TYPE_USER` FOREIGN KEY (`type_user`) REFERENCES `type_users` (`nemonico`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_BOTON_USER_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.boton_user: ~18 rows (aproximadamente)
INSERT INTO `boton_user` (`id`, `boton`, `type_user`, `institucion`) VALUES
	(1, 13, 'TIPO_DEV', 1),
	(2, 1, 'TIPO_DEV', 1),
	(3, 2, 'TIPO_DEV', 1),
	(4, 24, 'TIPO_DEV', 1),
	(5, 3, 'TIPO_DEV', 1),
	(6, 4, 'TIPO_DEV', 1),
	(7, 6, 'TIPO_DEV', 1),
	(8, 8, 'TIPO_DEV', 1),
	(9, 9, 'TIPO_DEV', 1),
	(10, 10, 'TIPO_DEV', 1),
	(11, 11, 'TIPO_DEV', 1),
	(12, 14, 'TIPO_DEV', 1),
	(13, 16, 'TIPO_DEV', 1),
	(14, 17, 'TIPO_DEV', 1),
	(15, 19, 'TIPO_DEV', 1),
	(16, 22, 'TIPO_DEV', 1),
	(17, 7, 'TIPO_DEV', 1),
	(18, 12, 'TIPO_DEV', 1);

-- Volcando estructura para tabla cuspide.botones
CREATE TABLE IF NOT EXISTS `botones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `icono` varchar(150) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK1_ESTADO_BOTON` (`estado`),
  CONSTRAINT `FK1_ESTADO_BOTON` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.botones: ~18 rows (aproximadamente)
INSERT INTO `botones` (`id`, `orden`, `nombre`, `icono`, `tipo`, `color`, `estado`) VALUES
	(1, 1, 'Dashboard', 'bi-house-check-fill', 'MENU', NULL, 1),
	(2, 2, 'Alumnos', 'bi-person-badge-fill', 'MENU', NULL, 1),
	(3, 3, 'Membresías', 'bi-person-vcard-fill', 'MENU', '', 1),
	(4, 4, 'Pagos', 'bi-cash-coin', 'MENU', '', 1),
	(6, 6, 'Inventario', 'bi-box-seam-fill', 'MENU', '', 1),
	(7, 23, 'Reportes', 'bi-bar-chart-fill', 'MENU', '', 1),
	(8, 8, 'Colaboradores', 'bi-person-badge-fill', 'MENU', '', 1),
	(9, 9, 'Matrículas', 'bi-journal-check', 'MENU', '', 1),
	(10, 10, 'Gastos', 'bi-cash-stack', 'MENU', '', 1),
	(11, 11, 'Contratos', 'bi-file-earmark-text', 'MENU', '', 1),
	(12, 24, 'Configuración', 'bi-gear-fill', 'MENU', '', 1),
	(13, 13, 'Usuarios', 'bi-people-fill', 'MENU', '', 1),
	(14, 14, 'Instituciones', 'bi-building', 'MENU', '', 1),
	(16, 16, 'Asignaturas', 'bi-book-fill', 'MENU', '', 1),
	(17, 17, 'Actividades', 'bi-trophy-fill', 'MENU', '', 1),
	(19, 19, 'Ventas', 'bi-cart-fill', 'MENU', '', 1),
	(22, 22, 'Productos', 'bi-box-seam-fill', 'MENU', '', 1),
	(24, 2, 'Socio', 'bi-person-badge-fill', 'MENU', NULL, 1);

-- Volcando estructura para tabla cuspide.calendarios
CREATE TABLE IF NOT EXISTS `calendarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `institucion` int(11) NOT NULL,
  `horario` int(11) DEFAULT NULL,
  `clase` int(11) DEFAULT NULL,
  `actividad` int(11) DEFAULT NULL,
  `plan_entrenamiento` int(11) DEFAULT NULL,
  `entrenador` int(11) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `ubicacion` varchar(150) NOT NULL DEFAULT '',
  `cupo_maximo` int(11) NOT NULL DEFAULT 0,
  `observacion` text DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IDX_CALENDARIOS_TIPO` (`tipo`),
  KEY `IDX_CALENDARIOS_INSTITUCION` (`institucion`),
  KEY `IDX_CALENDARIOS_HORARIO` (`horario`),
  KEY `IDX_CALENDARIOS_CLASE` (`clase`),
  KEY `IDX_CALENDARIOS_ACTIVIDAD` (`actividad`),
  KEY `IDX_CALENDARIOS_PLAN` (`plan_entrenamiento`),
  KEY `IDX_CALENDARIOS_ENTRENADOR` (`entrenador`),
  KEY `IDX_CALENDARIOS_FECHAS` (`fecha_inicio`,`fecha_fin`),
  KEY `IDX_CALENDARIOS_ESTADO` (`estado`),
  CONSTRAINT `FK_CALENDARIOS_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  CONSTRAINT `FK_CALENDARIOS_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  CONSTRAINT `FK_CALENDARIOS_ENTRENADOR` FOREIGN KEY (`entrenador`) REFERENCES `personas` (`id`),
  CONSTRAINT `FK_CALENDARIOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_CALENDARIOS_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`),
  CONSTRAINT `FK_CALENDARIOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  CONSTRAINT `FK_CALENDARIOS_PLAN` FOREIGN KEY (`plan_entrenamiento`) REFERENCES `planes_entrenamiento` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.calendarios: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.calificaciones
CREATE TABLE IF NOT EXISTS `calificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evaluacion` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `nota` decimal(5,2) NOT NULL,
  `observacion` text DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK_CALIFICACIONES_EVALUACION` (`evaluacion`),
  KEY `FK_CALIFICACIONES_MATRICULA` (`matricula`),
  KEY `FK_CALIFICACIONES_ESTADO` (`estado`),
  CONSTRAINT `FK_CALIFICACIONES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_CALIFICACIONES_EVALUACION` FOREIGN KEY (`evaluacion`) REFERENCES `evaluaciones` (`id`),
  CONSTRAINT `FK_CALIFICACIONES_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.calificaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.categorias_deportivas
CREATE TABLE IF NOT EXISTS `categorias_deportivas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `deporte` int(11) NOT NULL,
  `edad_minima` int(11) DEFAULT NULL,
  `edad_maxima` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_CATEGORIAS_DEPORTIVAS_NEMONICO` (`nemonico`),
  KEY `IDX_CATEGORIAS_DEPORTIVAS_DEPORTE` (`deporte`),
  KEY `IDX_CATEGORIAS_DEPORTIVAS_INSTITUCION` (`institucion`),
  KEY `IDX_CATEGORIAS_DEPORTIVAS_ESTADO` (`estado`),
  CONSTRAINT `FK_CATEGORIAS_DEPORTE` FOREIGN KEY (`deporte`) REFERENCES `deportes` (`id`),
  CONSTRAINT `FK_CATEGORIAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_CATEGORIAS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.categorias_deportivas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.ciudades
CREATE TABLE IF NOT EXISTS `ciudades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.ciudades: ~6 rows (aproximadamente)
INSERT INTO `ciudades` (`id`, `nemonico`, `nombre`, `provincia`) VALUES
	(1, 'MACH', 'MACHALA', 'EL ORO'),
	(101, 'HUAQ', 'HUAQUILLAS', 'EL ORO'),
	(102, 'STRO', 'SANTA ROSA', 'EL ORO'),
	(103, 'GYE', 'GUAYAQUIL', 'GUAYAS'),
	(104, 'UIO', 'QUITO', 'PICHINCHA'),
	(105, 'AREN', 'ARENILLAS', 'EL ORO');

-- Volcando estructura para tabla cuspide.clases
CREATE TABLE IF NOT EXISTS `clases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) DEFAULT NULL,
  `periodo_academico` int(11) DEFAULT NULL,
  `asignatura` int(11) DEFAULT NULL,
  `precio` double DEFAULT NULL,
  `nivel` int(11) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `docente` int(11) DEFAULT NULL,
  `cupo_maximo` int(11) NOT NULL DEFAULT 0,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK1_NIVEL_CLASES` (`nivel`),
  KEY `FK2_ASIGNATURA_CLASES` (`asignatura`),
  KEY `FK3_INSTITUCION_CLASES` (`institucion`),
  KEY `IDX_CLASES_PERIODO` (`periodo_academico`),
  KEY `IDX_CLASES_DOCENTE` (`docente`),
  KEY `IDX_CLASES_ESTADO` (`estado`),
  CONSTRAINT `FK1_NIVEL_CLASES` FOREIGN KEY (`nivel`) REFERENCES `nivel_asignatura` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_ASIGNATURA_CLASES` FOREIGN KEY (`asignatura`) REFERENCES `asignaturas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_INSTITUCION_CLASES` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_CLASES_DOCENTE` FOREIGN KEY (`docente`) REFERENCES `personas` (`id`),
  CONSTRAINT `FK_CLASES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_CLASES_PERIODO` FOREIGN KEY (`periodo_academico`) REFERENCES `periodos_academicos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.clases: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.clases_matricula
CREATE TABLE IF NOT EXISTS `clases_matricula` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clase` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `contrato` int(11) DEFAULT NULL,
  `modalidad` int(11) NOT NULL,
  `horario` int(11) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK1_CLASE_CLASE_MATRICULA` (`clase`),
  KEY `FK2_MATRICULA_CLASE_MATRICULA` (`matricula`),
  KEY `FK3_ESTADO_CLASE_MATRICULA` (`estado`),
  KEY `FK4_MODALIDAD_CLASES_MATRICULA` (`modalidad`),
  KEY `FK5_CONTRATO_CLASES_MATRICULA` (`contrato`),
  KEY `IDX_CLASE_MATRICULA_HORARIO` (`horario`),
  CONSTRAINT `FK1_CLASE_CLASE_MATRICULA` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_MATRICULA_CLASE_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_ESTADO_CLASE_MATRICULA` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK4_MODALIDAD_CLASES_MATRICULA` FOREIGN KEY (`modalidad`) REFERENCES `modalidades` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK5_CONTRATO_CLASES_MATRICULA` FOREIGN KEY (`contrato`) REFERENCES `contratacion` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_CLASE_MATRICULA_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.clases_matricula: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.contratacion
CREATE TABLE IF NOT EXISTS `contratacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_inscripcion` datetime NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `nivel` int(11) NOT NULL,
  `institucion` int(11) NOT NULL,
  `persona` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_PERSONA_CONTRATO` (`persona`),
  KEY `FK2_PERSONA_NIVEL` (`nivel`),
  KEY `FK3_PERSONA_INSTITUCION` (`institucion`),
  CONSTRAINT `FK1_PERSONA_CONTRATO` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_PERSONA_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel_academico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_PERSONA_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.contratacion: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.correos
CREATE TABLE IF NOT EXISTS `correos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `tipo` int(11) NOT NULL,
  `persona` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_ESTADO_MAIL` (`estado`),
  KEY `FK2_PERSONA` (`persona`),
  KEY `FK3_TIPO_CONTACT` (`tipo`),
  CONSTRAINT `FK1_ESTADO_MAIL` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_TIPO_CONTACT` FOREIGN KEY (`tipo`) REFERENCES `tipos_contacto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.correos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.cuotas_matricula
CREATE TABLE IF NOT EXISTS `cuotas_matricula` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_pago` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `valor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IDX_CUOTAS_PLAN_PAGO` (`plan_pago`),
  KEY `IDX_CUOTAS_ESTADO` (`estado`),
  CONSTRAINT `FK_CUOTAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_CUOTAS_PLAN_PAGO` FOREIGN KEY (`plan_pago`) REFERENCES `plan_pago_matricula` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.cuotas_matricula: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.deportes
CREATE TABLE IF NOT EXISTS `deportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK1_INSTITUCION_DEPORTE` (`institucion`),
  KEY `FK2_ESTADO_DEPORTE` (`estado`),
  CONSTRAINT `FK1_INSTITUCION_DEPORTE` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_ESTADO_DEPORTE` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.deportes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.enfermedad_persona
CREATE TABLE IF NOT EXISTS `enfermedad_persona` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enfermedad` int(11) NOT NULL,
  `medicacion` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_ENFERMEDAD_PERSONA` (`enfermedad`),
  CONSTRAINT `FK1_ENFERMEDAD_PERSONA` FOREIGN KEY (`enfermedad`) REFERENCES `enfermedades` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.enfermedad_persona: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.enfermedades
CREATE TABLE IF NOT EXISTS `enfermedades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `nemonico` varchar(150) NOT NULL,
  `nivel` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`),
  KEY `FK1_ENFERMEDAD_TIPO_ENFERMEDAD` (`tipo`),
  KEY `FK2_ENFERMEDAD_NIVEL` (`nivel`),
  CONSTRAINT `FK1_ENFERMEDAD_TIPO_ENFERMEDAD` FOREIGN KEY (`tipo`) REFERENCES `tipo_enfermedad` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_ENFERMEDAD_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.enfermedades: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.estado_fisico
CREATE TABLE IF NOT EXISTS `estado_fisico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perfil_medico` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_ESTADO_FISICO_PERFIL_MEDICO` (`perfil_medico`),
  KEY `FK2_ESTADO_FISICO_TIPO_ESTADOS` (`tipo`),
  CONSTRAINT `FK1_ESTADO_FISICO_PERFIL_MEDICO` FOREIGN KEY (`perfil_medico`) REFERENCES `perfil_medico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_ESTADO_FISICO_TIPO_ESTADOS` FOREIGN KEY (`tipo`) REFERENCES `tipo_estado_fisico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.estado_fisico: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.estado_permiso
CREATE TABLE IF NOT EXISTS `estado_permiso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.estado_permiso: ~4 rows (aproximadamente)
INSERT INTO `estado_permiso` (`id`, `nemonico`, `nombre`) VALUES
	(0, 'CANCELADO', 'PERMISO CANCELADO'),
	(1, 'PENDIENTE', 'PENDIENTE DE APROBACIÓN'),
	(2, 'APROBADO', 'PERMISO APROBADO'),
	(3, 'RECHAZADO', 'PERMISO RECHAZADO');

-- Volcando estructura para tabla cuspide.estados
CREATE TABLE IF NOT EXISTS `estados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.estados: ~5 rows (aproximadamente)
INSERT INTO `estados` (`id`, `nombre`) VALUES
	(0, 'INACTIVO'),
	(1, 'ACTIVO'),
	(2, 'SUSPENDIDO'),
	(3, 'PENDIENTE'),
	(4, 'ANULADO');

-- Volcando estructura para tabla cuspide.etnias
CREATE TABLE IF NOT EXISTS `etnias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.etnias: ~4 rows (aproximadamente)
INSERT INTO `etnias` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'MEZT', 'MESTIZO'),
	(101, 'AFRO', 'AFROECUATORIANO'),
	(102, 'MONT', 'MONTUBIO'),
	(103, 'IND', 'INDIGENA');

-- Volcando estructura para tabla cuspide.evaluaciones
CREATE TABLE IF NOT EXISTS `evaluaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `clase` int(11) DEFAULT NULL,
  `actividad` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `peso_porcentaje` decimal(5,2) NOT NULL DEFAULT 0.00,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK_EVALUACIONES_CLASE` (`clase`),
  KEY `FK_EVALUACIONES_ACTIVIDAD` (`actividad`),
  KEY `FK_EVALUACIONES_ESTADO` (`estado`),
  CONSTRAINT `FK_EVALUACIONES_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  CONSTRAINT `FK_EVALUACIONES_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  CONSTRAINT `FK_EVALUACIONES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.evaluaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.fichas_medicas
CREATE TABLE IF NOT EXISTS `fichas_medicas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `persona` int(11) DEFAULT NULL,
  `grupo_sanguineo` varchar(10) DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `enfermedades_preexistentes` text DEFAULT NULL,
  `medicamentos_actuales` text DEFAULT NULL,
  `contacto_emergencia_nombre` varchar(150) DEFAULT NULL,
  `contacto_emergencia_telefono` varchar(20) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `perfil_medico` int(11) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK1_FICHA_MEDICA_PERFIL_MEDICO` (`perfil_medico`),
  KEY `IDX_FICHAS_PERSONA` (`persona`),
  CONSTRAINT `FK1_FICHA_MEDICA_PERFIL_MEDICO` FOREIGN KEY (`perfil_medico`) REFERENCES `perfil_medico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_FICHAS_MEDICAS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.fichas_medicas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.formas_pago
CREATE TABLE IF NOT EXISTS `formas_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_FORMAS_PAGO_NEMONICO` (`nemonico`),
  KEY `IDX_FORMAS_PAGO_ESTADO` (`estado`),
  CONSTRAINT `FK_FORMAS_PAGO_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.formas_pago: ~3 rows (aproximadamente)
INSERT INTO `formas_pago` (`id`, `nombre`, `nemonico`, `descripcion`, `estado`) VALUES
	(101, 'Efectivo', 'EFECTIVO_DEMO', 'Pago en caja', 1),
	(102, 'Transferencia', 'TRANSFERENCIA_DEMO', 'Transferencia bancaria', 1),
	(103, 'Tarjeta', 'TARJETA_DEMO', 'Pago con tarjeta', 1);

-- Volcando estructura para tabla cuspide.gastos
CREATE TABLE IF NOT EXISTS `gastos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `tipo_gasto` varchar(80) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `institucion` int(11) NOT NULL,
  `persona` int(11) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK_GASTOS_INSTITUCION` (`institucion`),
  KEY `FK_GASTOS_PERSONA` (`persona`),
  KEY `FK_GASTOS_ESTADO` (`estado`),
  CONSTRAINT `FK_GASTOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_GASTOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  CONSTRAINT `FK_GASTOS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.gastos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.generos
CREATE TABLE IF NOT EXISTS `generos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.generos: ~2 rows (aproximadamente)
INSERT INTO `generos` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'TIPO_M', 'MASCULINO'),
	(2, 'TIPO_F', 'FEMENINO');

-- Volcando estructura para tabla cuspide.horarios
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `institucion` int(11) NOT NULL,
  `clase` int(11) DEFAULT NULL,
  `actividad` int(11) DEFAULT NULL,
  `categoria_deportiva` int(11) DEFAULT NULL,
  `entrenador` int(11) DEFAULT NULL,
  `dia_semana` tinyint(1) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `cupo_maximo` int(11) NOT NULL DEFAULT 0,
  `ubicacion` varchar(150) NOT NULL DEFAULT '',
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IDX_HORARIOS_INSTITUCION` (`institucion`),
  KEY `IDX_HORARIOS_CLASE` (`clase`),
  KEY `IDX_HORARIOS_ACTIVIDAD` (`actividad`),
  KEY `IDX_HORARIOS_CATEGORIA` (`categoria_deportiva`),
  KEY `IDX_HORARIOS_ENTRENADOR` (`entrenador`),
  KEY `IDX_HORARIOS_DIA_HORA` (`dia_semana`,`hora_inicio`,`hora_fin`),
  KEY `IDX_HORARIOS_ESTADO` (`estado`),
  CONSTRAINT `FK_HORARIOS_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  CONSTRAINT `FK_HORARIOS_CATEGORIA` FOREIGN KEY (`categoria_deportiva`) REFERENCES `categorias_deportivas` (`id`),
  CONSTRAINT `FK_HORARIOS_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  CONSTRAINT `FK_HORARIOS_ENTRENADOR` FOREIGN KEY (`entrenador`) REFERENCES `personas` (`id`),
  CONSTRAINT `FK_HORARIOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_HORARIOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.horarios: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.incidencia
CREATE TABLE IF NOT EXISTS `incidencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL,
  `persona` int(11) NOT NULL,
  `nivel` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_INCIDENCIA_PERSONA` (`persona`),
  KEY `FK2_INCIDENCIA_NIVEL` (`nivel`),
  CONSTRAINT `FK1_INCIDENCIA_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_INCIDENCIA_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.incidencia: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.instituciones
CREATE TABLE IF NOT EXISTS `instituciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ruc` varchar(13) DEFAULT NULL,
  `razon_social` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `logotipo` varchar(255) NOT NULL,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruc` (`ruc`),
  KEY `FK1_ESTADO_INSTITUCION` (`estado`),
  CONSTRAINT `FK1_ESTADO_INSTITUCION` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.instituciones: ~1 rows (aproximadamente)
INSERT INTO `instituciones` (`id`, `ruc`, `razon_social`, `nombre`, `logotipo`, `estado`) VALUES
	(1, '0791842247001', 'Huaquillas-sporting-club S.a.s.', 'Huaquillas Futbol Club', '/assets/logo/huaquillasfc.jpg', 1);

-- Volcando estructura para tabla cuspide.inventario
CREATE TABLE IF NOT EXISTS `inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto` int(11) NOT NULL,
  `variante` int(11) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `stock_actual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_minimo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `IDX_INVENTARIO_PRODUCTO` (`producto`),
  KEY `IDX_INVENTARIO_VARIANTE` (`variante`),
  KEY `IDX_INVENTARIO_INSTITUCION` (`institucion`),
  CONSTRAINT `FK_INVENTARIO_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  CONSTRAINT `FK_INVENTARIO_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`),
  CONSTRAINT `FK_INVENTARIO_VARIANTE` FOREIGN KEY (`variante`) REFERENCES `producto_variantes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.inventario: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.matricula
CREATE TABLE IF NOT EXISTS `matricula` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` datetime NOT NULL,
  `persona` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `membresia` int(11) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_MATRICULA_PERSONA` (`persona`),
  KEY `FK2_MATRICULA_ESTADO` (`estado`),
  KEY `FK3_MATRICULA_INSTITUCION` (`institucion`),
  KEY `FK4_MATRICULA_MEMBRESIA` (`membresia`),
  CONSTRAINT `FK1_MATRICULA_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_MATRICULA_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_MATRICULA_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK4_MATRICULA_MEMBRESIA` FOREIGN KEY (`membresia`) REFERENCES `membresias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.matricula: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.membresias
CREATE TABLE IF NOT EXISTS `membresias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `precio` varchar(20) NOT NULL,
  `porcentaje` double NOT NULL,
  `periodo` varchar(80) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `url_boton` varchar(255) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` int(11) NOT NULL,
  `institucion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_ESTADO_MEMBRESIA` (`estado`),
  KEY `FK_MEMBRESIAS_INSTITUCION` (`institucion`),
  CONSTRAINT `FK1_ESTADO_MEMBRESIA` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_MEMBRESIAS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.membresias: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.modalidades
CREATE TABLE IF NOT EXISTS `modalidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.modalidades: ~3 rows (aproximadamente)
INSERT INTO `modalidades` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'TIPO_PRESENCIAL', 'PRESENCIAL'),
	(101, 'TIPO_VIRTUAL', 'VIRTUAL'),
	(102, 'TIPO_HIBRIDA', 'HIBRIDA');

-- Volcando estructura para tabla cuspide.movimientos_inventario
CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto` int(11) NOT NULL,
  `variante` int(11) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `cantidad` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_anterior` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_nuevo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `IDX_MOV_INV_PRODUCTO` (`producto`),
  KEY `IDX_MOV_INV_VARIANTE` (`variante`),
  KEY `IDX_MOV_INV_INSTITUCION` (`institucion`),
  CONSTRAINT `FK_MOV_INV_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  CONSTRAINT `FK_MOV_INV_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`),
  CONSTRAINT `FK_MOV_INV_VARIANTE` FOREIGN KEY (`variante`) REFERENCES `producto_variantes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.movimientos_inventario: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.nacionalidades
CREATE TABLE IF NOT EXISTS `nacionalidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.nacionalidades: ~4 rows (aproximadamente)
INSERT INTO `nacionalidades` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'EC', 'ECUATORIANA'),
	(101, 'CO', 'COLOMBIANA'),
	(102, 'PE', 'PERUANA'),
	(103, 'VE', 'VENEZOLANA');

-- Volcando estructura para tabla cuspide.nivel
CREATE TABLE IF NOT EXISTS `nivel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(150) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.nivel: ~3 rows (aproximadamente)
INSERT INTO `nivel` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'LEVE', 'SUTIL'),
	(2, 'MEDIO', 'FUERTE'),
	(3, 'GRAVE', 'CRITICO');

-- Volcando estructura para tabla cuspide.nivel_academico
CREATE TABLE IF NOT EXISTS `nivel_academico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.nivel_academico: ~4 rows (aproximadamente)
INSERT INTO `nivel_academico` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'BASICO', 'PRIMARIA'),
	(2, 'MEDIO', 'SECUNDARIA'),
	(3, 'ALTO', 'TERCER NIVEL'),
	(4, 'ULTRA', 'CUARTO NIVEL');

-- Volcando estructura para tabla cuspide.nivel_asignatura
CREATE TABLE IF NOT EXISTS `nivel_asignatura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.nivel_asignatura: ~4 rows (aproximadamente)
INSERT INTO `nivel_asignatura` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'NIVEL_BASICO', 'BASICO'),
	(101, 'NIVEL_INTERMEDIO', 'INTERMEDIO'),
	(102, 'NIVEL_AVANZADO', 'AVANZADO'),
	(103, 'NIVEL_COMPETITIVO', 'COMPETITIVO');

-- Volcando estructura para tabla cuspide.operadoras
CREATE TABLE IF NOT EXISTS `operadoras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.operadoras: ~4 rows (aproximadamente)
INSERT INTO `operadoras` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'OPE_CLARO', 'CLARO'),
	(101, 'OPE_MOVISTAR', 'MOVISTAR'),
	(102, 'OPE_CNT', 'CNT'),
	(103, 'OPE_TUENTI', 'TUENTI');

-- Volcando estructura para tabla cuspide.pago_detalle
CREATE TABLE IF NOT EXISTS `pago_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pago` int(11) NOT NULL,
  `cuota_matricula` int(11) DEFAULT NULL,
  `venta` int(11) DEFAULT NULL,
  `valor` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `IDX_PAGO_DETALLE_PAGO` (`pago`),
  KEY `IDX_PAGO_DETALLE_CUOTA` (`cuota_matricula`),
  KEY `IDX_PAGO_DETALLE_VENTA` (`venta`),
  CONSTRAINT `FK_PAGO_DETALLE_CUOTA` FOREIGN KEY (`cuota_matricula`) REFERENCES `cuotas_matricula` (`id`),
  CONSTRAINT `FK_PAGO_DETALLE_PAGO` FOREIGN KEY (`pago`) REFERENCES `pagos` (`id`),
  CONSTRAINT `FK_PAGO_DETALLE_VENTA` FOREIGN KEY (`venta`) REFERENCES `ventas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.pago_detalle: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.pagos
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `persona` int(11) NOT NULL,
  `matricula` int(11) DEFAULT NULL,
  `venta` int(11) DEFAULT NULL,
  `forma_pago` int(11) NOT NULL,
  `institucion` int(11) NOT NULL,
  `monto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL,
  `observacion` varchar(255) NOT NULL DEFAULT '',
  `estado` int(11) NOT NULL DEFAULT 1,
  `cobrado_por` int(11) DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_PAGOS_NUMERO` (`numero`),
  KEY `IDX_PAGOS_PERSONA` (`persona`),
  KEY `IDX_PAGOS_MATRICULA` (`matricula`),
  KEY `IDX_PAGOS_VENTA` (`venta`),
  KEY `IDX_PAGOS_FORMA_PAGO` (`forma_pago`),
  KEY `IDX_PAGOS_INSTITUCION` (`institucion`),
  KEY `IDX_PAGOS_ESTADO` (`estado`),
  CONSTRAINT `FK_PAGOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_PAGOS_FORMA_PAGO` FOREIGN KEY (`forma_pago`) REFERENCES `formas_pago` (`id`),
  CONSTRAINT `FK_PAGOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  CONSTRAINT `FK_PAGOS_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  CONSTRAINT `FK_PAGOS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`),
  CONSTRAINT `FK_PAGOS_VENTA` FOREIGN KEY (`venta`) REFERENCES `ventas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.pagos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.perfil_medico
CREATE TABLE IF NOT EXISTS `perfil_medico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL,
  `persona` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK_PERFIL_MEDICO_PERSONA` (`persona`),
  KEY `FK_PERFIL_MEDICO_ESTADO` (`estado`),
  CONSTRAINT `FK_PERFIL_MEDICO_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_PERFIL_MEDICO_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.perfil_medico: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.periodicidad_pago
CREATE TABLE IF NOT EXISTS `periodicidad_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `meses` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_PERIODICIDAD_PAGO_NEMONICO` (`nemonico`),
  KEY `IDX_PERIODICIDAD_PAGO_ESTADO` (`estado`),
  CONSTRAINT `FK_PERIODICIDAD_PAGO_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.periodicidad_pago: ~3 rows (aproximadamente)
INSERT INTO `periodicidad_pago` (`id`, `nombre`, `nemonico`, `meses`, `estado`) VALUES
	(101, 'Mensual', 'MENSUAL_DEMO', 1, 1),
	(102, 'Trimestral', 'TRIMESTRAL_DEMO', 3, 1),
	(103, 'Anual', 'ANUAL_DEMO', 12, 1);

-- Volcando estructura para tabla cuspide.periodos_academicos
CREATE TABLE IF NOT EXISTS `periodos_academicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK_PERIODOS_INSTITUCION` (`institucion`),
  KEY `FK_PERIODOS_ESTADO` (`estado`),
  CONSTRAINT `FK_PERIODOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_PERIODOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.periodos_academicos: ~4 rows (aproximadamente)
INSERT INTO `periodos_academicos` (`id`, `nombre`, `fecha_inicio`, `fecha_fin`, `institucion`, `estado`) VALUES
	(1, 'Periodo Lectivo 2026', '2026-05-28', '2026-05-28', 1, 1),
	(2, 'Periodo Lectivo 2027', '2027-01-01', '2027-12-31', 1, 1),
	(3, 'Periodo Lectivo 2028', '2028-01-01', '2028-12-31', 1, 1),
	(4, 'Periodo Lectivo 2029', '2029-01-01', '2029-12-31', 1, 1);

-- Volcando estructura para tabla cuspide.permisos
CREATE TABLE IF NOT EXISTS `permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) DEFAULT NULL,
  `persona` int(11) NOT NULL,
  `matricula` int(11) DEFAULT NULL,
  `clase` int(11) DEFAULT NULL,
  `actividad` int(11) DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `horas_por_dia` decimal(5,2) DEFAULT NULL,
  `motivo` varchar(255) NOT NULL DEFAULT '',
  `observacion` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) NOT NULL,
  `fechaInicio` datetime DEFAULT NULL,
  `fechaFin` datetime DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK1_PERMISO_PERSONA` (`persona`),
  KEY `FK2_PERMISO_TIPO_PERMISO` (`tipo`),
  KEY `FK3_PERMISO_ESTADO_PERMISO` (`estado`),
  KEY `IDX_PERMISOS_MATRICULA` (`matricula`),
  KEY `IDX_PERMISOS_CLASE` (`clase`),
  KEY `IDX_PERMISOS_ACTIVIDAD` (`actividad`),
  KEY `IDX_PERMISOS_FECHAS` (`fecha_inicio`,`fecha_fin`),
  CONSTRAINT `FK1_PERMISO_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_PERMISO_TIPO_PERMISO` FOREIGN KEY (`tipo`) REFERENCES `tipo_permiso` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_PERMISO_ESTADO_PERMISO` FOREIGN KEY (`estado`) REFERENCES `estado_permiso` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_PERMISOS_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  CONSTRAINT `FK_PERMISOS_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  CONSTRAINT `FK_PERMISOS_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.permisos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.persona_institucion
CREATE TABLE IF NOT EXISTS `persona_institucion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_persona` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK1_ESTADO` (`estado`),
  KEY `FK2_TIPO_PERSONA` (`tipo`),
  KEY `FK3_PERSONA` (`id_persona`),
  KEY `FK4_INSTITUCION` (`id_institucion`),
  CONSTRAINT `FK1_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_TIPO_PERSONA` FOREIGN KEY (`tipo`) REFERENCES `tipo_persona` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_PERSONA` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK4_INSTITUCION` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.persona_institucion: ~1 rows (aproximadamente)
INSERT INTO `persona_institucion` (`id`, `id_persona`, `id_institucion`, `tipo`, `estado`) VALUES
	(1, 1, 1, 1, 1);

-- Volcando estructura para tabla cuspide.personas
CREATE TABLE IF NOT EXISTS `personas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identificacion` varchar(10) NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `nacionalidad` int(11) NOT NULL,
  `genero` int(11) NOT NULL,
  `etnia` int(11) NOT NULL,
  `fecha_nacimiento` datetime NOT NULL,
  `ciudad` int(11) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identificacion` (`identificacion`),
  KEY `FK1_NACIONALIDAD` (`nacionalidad`),
  KEY `FK2_USER` (`user`),
  KEY `FK3_GENERO` (`genero`),
  KEY `FK4_ETNIA` (`etnia`),
  KEY `FK5_CIUDAD` (`ciudad`),
  CONSTRAINT `FK1_NACIONALIDAD` FOREIGN KEY (`nacionalidad`) REFERENCES `nacionalidades` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_USER` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_GENERO` FOREIGN KEY (`genero`) REFERENCES `generos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK4_ETNIA` FOREIGN KEY (`etnia`) REFERENCES `etnias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK5_CIUDAD` FOREIGN KEY (`ciudad`) REFERENCES `ciudades` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.personas: ~1 rows (aproximadamente)
INSERT INTO `personas` (`id`, `identificacion`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `nacionalidad`, `genero`, `etnia`, `fecha_nacimiento`, `ciudad`, `direccion`, `user`) VALUES
	(1, '0706323193', 'Jhonny', 'Darwin', 'Miñan', 'Girón', 1, 1, 1, '1992-03-02 00:00:00', 1, '25 de Junio y Callejon Babahoyo', 1);

-- Volcando estructura para tabla cuspide.plan_entrenamiento_ejercicios
CREATE TABLE IF NOT EXISTS `plan_entrenamiento_ejercicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sesion` int(11) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 1,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `series` int(11) NOT NULL DEFAULT 0,
  `repeticiones` varchar(50) NOT NULL DEFAULT '',
  `duracion_segundos` int(11) NOT NULL DEFAULT 0,
  `descanso_segundos` int(11) NOT NULL DEFAULT 0,
  `intensidad` varchar(50) NOT NULL DEFAULT '',
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IDX_PLAN_EJERCICIOS_SESION` (`sesion`),
  KEY `IDX_PLAN_EJERCICIOS_ORDEN` (`sesion`,`orden`),
  KEY `IDX_PLAN_EJERCICIOS_ESTADO` (`estado`),
  CONSTRAINT `FK_PLAN_EJERCICIOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_PLAN_EJERCICIOS_SESION` FOREIGN KEY (`sesion`) REFERENCES `plan_entrenamiento_sesiones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.plan_entrenamiento_ejercicios: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.plan_entrenamiento_sesiones
CREATE TABLE IF NOT EXISTS `plan_entrenamiento_sesiones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_entrenamiento` int(11) NOT NULL,
  `calendario` int(11) DEFAULT NULL,
  `horario` int(11) DEFAULT NULL,
  `numero_semana` int(11) NOT NULL DEFAULT 1,
  `dia_semana` tinyint(1) DEFAULT NULL,
  `fecha_programada` date DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `objetivo` varchar(255) NOT NULL DEFAULT '',
  `duracion_minutos` int(11) NOT NULL DEFAULT 0,
  `intensidad` varchar(50) NOT NULL DEFAULT '',
  `contenido` text NOT NULL,
  `observacion` text DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IDX_PLAN_SESIONES_PLAN` (`plan_entrenamiento`),
  KEY `IDX_PLAN_SESIONES_CALENDARIO` (`calendario`),
  KEY `IDX_PLAN_SESIONES_HORARIO` (`horario`),
  KEY `IDX_PLAN_SESIONES_ORDEN` (`plan_entrenamiento`,`numero_semana`,`dia_semana`),
  KEY `IDX_PLAN_SESIONES_ESTADO` (`estado`),
  CONSTRAINT `FK_PLAN_SESIONES_CALENDARIO` FOREIGN KEY (`calendario`) REFERENCES `calendarios` (`id`),
  CONSTRAINT `FK_PLAN_SESIONES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_PLAN_SESIONES_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`),
  CONSTRAINT `FK_PLAN_SESIONES_PLAN` FOREIGN KEY (`plan_entrenamiento`) REFERENCES `planes_entrenamiento` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.plan_entrenamiento_sesiones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.plan_pago_matricula
CREATE TABLE IF NOT EXISTS `plan_pago_matricula` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matricula` int(11) NOT NULL,
  `periodicidad` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_generacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IDX_PLAN_PAGO_MATRICULA` (`matricula`),
  KEY `IDX_PLAN_PAGO_PERIODICIDAD` (`periodicidad`),
  KEY `IDX_PLAN_PAGO_ESTADO` (`estado`),
  CONSTRAINT `FK_PLAN_PAGO_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_PLAN_PAGO_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  CONSTRAINT `FK_PLAN_PAGO_PERIODICIDAD` FOREIGN KEY (`periodicidad`) REFERENCES `periodicidad_pago` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.plan_pago_matricula: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.planes_entrenamiento
CREATE TABLE IF NOT EXISTS `planes_entrenamiento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `objetivo` varchar(255) NOT NULL DEFAULT '',
  `descripcion` text DEFAULT NULL,
  `matricula` int(11) DEFAULT NULL,
  `clase` int(11) DEFAULT NULL,
  `actividad` int(11) DEFAULT NULL,
  `categoria_deportiva` int(11) DEFAULT NULL,
  `entrenador` int(11) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `nivel` int(11) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IDX_PLAN_ENT_MATRICULA` (`matricula`),
  KEY `IDX_PLAN_ENT_CLASE` (`clase`),
  KEY `IDX_PLAN_ENT_ACTIVIDAD` (`actividad`),
  KEY `IDX_PLAN_ENT_CATEGORIA` (`categoria_deportiva`),
  KEY `IDX_PLAN_ENT_ENTRENADOR` (`entrenador`),
  KEY `IDX_PLAN_ENT_INSTITUCION` (`institucion`),
  KEY `IDX_PLAN_ENT_NIVEL` (`nivel`),
  KEY `IDX_PLAN_ENT_ESTADO` (`estado`),
  CONSTRAINT `FK_PLAN_ENT_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  CONSTRAINT `FK_PLAN_ENT_CATEGORIA` FOREIGN KEY (`categoria_deportiva`) REFERENCES `categorias_deportivas` (`id`),
  CONSTRAINT `FK_PLAN_ENT_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  CONSTRAINT `FK_PLAN_ENT_ENTRENADOR` FOREIGN KEY (`entrenador`) REFERENCES `personas` (`id`),
  CONSTRAINT `FK_PLAN_ENT_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_PLAN_ENT_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  CONSTRAINT `FK_PLAN_ENT_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  CONSTRAINT `FK_PLAN_ENT_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel_asignatura` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.planes_entrenamiento: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.producto_categorias
CREATE TABLE IF NOT EXISTS `producto_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_PRODUCTO_CATEGORIAS_NEMONICO` (`nemonico`),
  KEY `IDX_PRODUCTO_CATEGORIAS_INSTITUCION` (`institucion`),
  KEY `IDX_PRODUCTO_CATEGORIAS_ESTADO` (`estado`),
  CONSTRAINT `FK_PRODUCTO_CATEGORIAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_PRODUCTO_CATEGORIAS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.producto_categorias: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.producto_variantes
CREATE TABLE IF NOT EXISTS `producto_variantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto` int(11) NOT NULL,
  `sku` varchar(80) NOT NULL,
  `talla` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `genero` varchar(50) DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_PRODUCTO_VARIANTES_SKU` (`sku`),
  KEY `IDX_PRODUCTO_VARIANTES_PRODUCTO` (`producto`),
  KEY `IDX_PRODUCTO_VARIANTES_ESTADO` (`estado`),
  CONSTRAINT `FK_PRODUCTO_VARIANTES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_PRODUCTO_VARIANTES_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.producto_variantes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `categoria` int(11) NOT NULL,
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `costo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `controla_stock` tinyint(1) NOT NULL DEFAULT 1,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_PRODUCTOS_CODIGO` (`codigo`),
  KEY `IDX_PRODUCTOS_CATEGORIA` (`categoria`),
  KEY `IDX_PRODUCTOS_INSTITUCION` (`institucion`),
  KEY `IDX_PRODUCTOS_ESTADO` (`estado`),
  CONSTRAINT `FK_PRODUCTOS_CATEGORIA` FOREIGN KEY (`categoria`) REFERENCES `producto_categorias` (`id`),
  CONSTRAINT `FK_PRODUCTOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_PRODUCTOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.productos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.socios
CREATE TABLE IF NOT EXISTS `socios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `persona_id` int(11) NOT NULL,
  `membresia_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `institucion_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_socio_persona` (`persona_id`),
  KEY `idx_socio_membresia` (`membresia_id`),
  KEY `idx_socio_institucion` (`institucion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.socios: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.tallas
CREATE TABLE IF NOT EXISTS `tallas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `persona` int(11) NOT NULL,
  `tipo_prenda` varchar(50) NOT NULL,
  `talla` varchar(20) NOT NULL,
  `medida_cm` varchar(50) DEFAULT NULL,
  `fecha_medicion` date NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK_TALLAS_PERSONA` (`persona`),
  KEY `FK_TALLAS_ESTADO` (`estado`),
  CONSTRAINT `FK_TALLAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_TALLAS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.tallas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.telefonos
CREATE TABLE IF NOT EXISTS `telefonos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) NOT NULL,
  `operadora` int(11) NOT NULL,
  `persona` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK1_PERSONA_TELEFONO` (`persona`),
  KEY `FK2_ESTADO` (`estado`),
  KEY `FK3_TIPO_CONTACTO` (`tipo`),
  KEY `FK4_OPERADORA` (`operadora`),
  CONSTRAINT `FK1_PERSONA_TELEFONO` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK2_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK3_TIPO_CONTACTO` FOREIGN KEY (`tipo`) REFERENCES `tipos_contacto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK4_OPERADORA` FOREIGN KEY (`operadora`) REFERENCES `operadoras` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.telefonos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.tipo_alergias
CREATE TABLE IF NOT EXISTS `tipo_alergias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.tipo_alergias: ~4 rows (aproximadamente)
INSERT INTO `tipo_alergias` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'RESP_ASMA', 'ASMA'),
	(101, 'ALIMENTOS', 'ALIMENTOS'),
	(102, 'MEDICAMENTOS', 'MEDICAMENTOS'),
	(103, 'AMBIENTAL', 'AMBIENTAL');

-- Volcando estructura para tabla cuspide.tipo_enfermedad
CREATE TABLE IF NOT EXISTS `tipo_enfermedad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(150) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.tipo_enfermedad: ~4 rows (aproximadamente)
INSERT INTO `tipo_enfermedad` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'RESP', 'RESPIRATORIA'),
	(101, 'CARDIO', 'CARDIOVASCULAR'),
	(102, 'DIGESTIVA', 'DIGESTIVA'),
	(103, 'LESION', 'LESION DEPORTIVA');

-- Volcando estructura para tabla cuspide.tipo_estado_fisico
CREATE TABLE IF NOT EXISTS `tipo_estado_fisico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.tipo_estado_fisico: ~6 rows (aproximadamente)
INSERT INTO `tipo_estado_fisico` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'ALTURA', 'ALTURA'),
	(2, 'PESO', 'PESO'),
	(3, 'TIPO_SANGRE', 'TIPO DE SANGRE'),
	(4, 'ESTADO_VISUAL', 'VISION/LENTES'),
	(5, 'TALLA_ROPA', 'TALLA DE ROPA'),
	(6, 'TALLA_CALZADO', 'TALLA DE ZAPATOS');

-- Volcando estructura para tabla cuspide.tipo_permiso
CREATE TABLE IF NOT EXISTS `tipo_permiso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.tipo_permiso: ~3 rows (aproximadamente)
INSERT INTO `tipo_permiso` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'FAMILIAR', 'INCIDENTE FAMILIAR'),
	(101, 'MEDICO', 'PERMISO MEDICO'),
	(102, 'ACADEMICO', 'PERMISO ACADEMICO');

-- Volcando estructura para tabla cuspide.tipo_persona
CREATE TABLE IF NOT EXISTS `tipo_persona` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.tipo_persona: ~7 rows (aproximadamente)
INSERT INTO `tipo_persona` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'TIPO_DEV', 'DESARROLLADOR'),
	(2, 'TIPO_ADMIN', 'ADMININISTRADOR'),
	(3, 'TIPO_EST', 'ESTUDIANTE'),
	(4, 'TIPO_PROF', 'DOCENTE'),
	(5, 'TIPO_MED', 'MEDICO'),
	(6, 'TIPO_ADM_OPER', 'ADMINISTRATIVO'),
	(7, 'TIPO_SOCIO', 'SOCIO');

-- Volcando estructura para tabla cuspide.tipos_contacto
CREATE TABLE IF NOT EXISTS `tipos_contacto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.tipos_contacto: ~4 rows (aproximadamente)
INSERT INTO `tipos_contacto` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'CONT_PROPIO', 'PROPIO'),
	(101, 'CONT_FAMILIAR', 'FAMILIAR'),
	(102, 'CONT_EMERGENCIA', 'EMERGENCIA'),
	(103, 'CONT_TRABAJO', 'TRABAJO');

-- Volcando estructura para tabla cuspide.torneo_participantes
CREATE TABLE IF NOT EXISTS `torneo_participantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torneo` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK_TORNEO_PART_TORNEO` (`torneo`),
  KEY `FK_TORNEO_PART_MATRICULA` (`matricula`),
  KEY `FK_TORNEO_PART_ESTADO` (`estado`),
  CONSTRAINT `FK_TORNEO_PART_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_TORNEO_PART_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  CONSTRAINT `FK_TORNEO_PART_TORNEO` FOREIGN KEY (`torneo`) REFERENCES `torneos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.torneo_participantes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.torneos
CREATE TABLE IF NOT EXISTS `torneos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `deporte` int(11) NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK_TORNEOS_DEPORTE` (`deporte`),
  KEY `FK_TORNEOS_INSTITUCION` (`institucion`),
  KEY `FK_TORNEOS_ESTADO` (`estado`),
  CONSTRAINT `FK_TORNEOS_DEPORTE` FOREIGN KEY (`deporte`) REFERENCES `deportes` (`id`),
  CONSTRAINT `FK_TORNEOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_TORNEOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.torneos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.type_users
CREATE TABLE IF NOT EXISTS `type_users` (
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  PRIMARY KEY (`nemonico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.type_users: ~7 rows (aproximadamente)
INSERT INTO `type_users` (`nemonico`, `nombre`, `descripcion`, `imagen`) VALUES
	('TIPO_ADMIN', 'Administrador', 'Usuario con privilegios administrativos', 'bi-person-gear'),
	('TIPO_ADM_OPER', 'Administrativo', 'Usuario administrativo para caja y secretaria', 'bi-briefcase-fill'),
	('TIPO_DEV', 'Desarrollador', 'Usuario con privilegios totales de desarrollador', 'bi-code-slash'),
	('TIPO_EST', 'Estudiante', 'Usuario estudiante para pruebas', 'bi-mortarboard-fill'),
	('TIPO_MED', 'Medico', 'Usuario medico para pruebas', 'bi-heart-pulse-fill'),
	('TIPO_PROF', 'Profesor', 'Entrenador o docente para pruebas', 'bi-person-badge-fill'),
	('TIPO_SOCIO', 'Socio', 'Socio del club deportivo', 'bi-briefcase-fill');

-- Volcando estructura para tabla cuspide.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` varchar(30) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_TYPE_USERS` (`type`),
  CONSTRAINT `FK1_TYPE_USERS` FOREIGN KEY (`type`) REFERENCES `type_users` (`nemonico`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.users: ~1 rows (aproximadamente)
INSERT INTO `users` (`id`, `username`, `password`, `type`, `image`) VALUES
	(1, 'jminan', '$2y$10$3nV1BdEGxt1BHuvwtsB24uT.4e8KE70ZteyDb6Qjo60VMUAfuSTpW', 'TIPO_DEV', 'icons/dev.jpg');

-- Volcando estructura para tabla cuspide.venta_detalle
CREATE TABLE IF NOT EXISTS `venta_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `variante` int(11) DEFAULT NULL,
  `cantidad` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `IDX_VENTA_DETALLE_VENTA` (`venta`),
  KEY `IDX_VENTA_DETALLE_PRODUCTO` (`producto`),
  KEY `IDX_VENTA_DETALLE_VARIANTE` (`variante`),
  CONSTRAINT `FK_VENTA_DETALLE_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`),
  CONSTRAINT `FK_VENTA_DETALLE_VARIANTE` FOREIGN KEY (`variante`) REFERENCES `producto_variantes` (`id`),
  CONSTRAINT `FK_VENTA_DETALLE_VENTA` FOREIGN KEY (`venta`) REFERENCES `ventas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.venta_detalle: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.ventas
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `persona` int(11) NOT NULL,
  `matricula` int(11) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `impuesto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_VENTAS_NUMERO` (`numero`),
  KEY `IDX_VENTAS_PERSONA` (`persona`),
  KEY `IDX_VENTAS_MATRICULA` (`matricula`),
  KEY `IDX_VENTAS_INSTITUCION` (`institucion`),
  KEY `IDX_VENTAS_ESTADO` (`estado`),
  CONSTRAINT `FK_VENTAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_VENTAS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  CONSTRAINT `FK_VENTAS_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  CONSTRAINT `FK_VENTAS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.ventas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.ventas_externas
CREATE TABLE IF NOT EXISTS `ventas_externas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `cliente_nombre` varchar(150) NOT NULL DEFAULT 'Anónimo',
  `cliente_identificacion` varchar(20) DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `impuesto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `forma_pago` int(11) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_VENTAS_EXTERNAS_NUMERO` (`numero`),
  KEY `IDX_VE_INSTITUCION` (`institucion`),
  KEY `IDX_VE_FORMA_PAGO` (`forma_pago`),
  KEY `IDX_VE_ESTADO` (`estado`),
  CONSTRAINT `FK_VE_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  CONSTRAINT `FK_VE_FORMA_PAGO` FOREIGN KEY (`forma_pago`) REFERENCES `formas_pago` (`id`),
  CONSTRAINT `FK_VE_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.ventas_externas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla cuspide.ventas_externas_detalle
CREATE TABLE IF NOT EXISTS `ventas_externas_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta_externa` int(11) NOT NULL,
  `producto` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `cantidad` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `IDX_VED_VENTA_EXTERNA` (`venta_externa`),
  KEY `IDX_VED_PRODUCTO` (`producto`),
  CONSTRAINT `FK_VED_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`),
  CONSTRAINT `FK_VED_VENTA_EXTERNA` FOREIGN KEY (`venta_externa`) REFERENCES `ventas_externas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.ventas_externas_detalle: ~0 rows (aproximadamente)

-- ============================================================
-- DATOS DE PRUEBA: Institución 2 con docentes propios
-- Sirve para validar que el filtro por institución funciona:
-- usuario de inst. 1 NO debe ver estos docentes en el select.
-- ============================================================

-- Segunda institución
INSERT INTO `instituciones` (`id`, `ruc`, `razon_social`, `nombre`, `logotipo`, `estado`) VALUES
  (2, '1791234560001', 'Academia Deportiva Sur S.A.', 'Academia Deportiva Sur', 'logo/default.jpg', 1);

-- Usuarios para institución 2
INSERT INTO `users` (`id`, `username`, `password`, `type`, `image`) VALUES
  (201, 'coach.inst2a', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'PROFESOR', 'icons/coach.jpg'),
  (202, 'coach.inst2b', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'PROFESOR', 'icons/coach.jpg');

-- Personas (docentes) de institución 2
INSERT INTO `personas` (`id`, `identificacion`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `nacionalidad`, `genero`, `etnia`, `fecha_nacimiento`, `ciudad`, `direccion`, `user`) VALUES
  (201, '1000000201', 'Marco', 'Antonio', 'Espinoza', 'Cruz',   1, 1, 1, '1987-03-15 00:00:00', 101, 'Av. Universidad s/n, Huaquillas', 201),
  (202, '1000000202', 'Sofia', 'Elena',   'Ramirez',  'Vega',   1, 2, 1, '1990-07-20 00:00:00', 101, 'Barrio Central s/n, Huaquillas', 202);

-- Vincular personas como DOCENTE (tipo=4) a institución 2
INSERT INTO `persona_institucion` (`id`, `id_persona`, `id_institucion`, `tipo`, `estado`) VALUES
  (201, 201, 2, 4, 1),
  (202, 202, 2, 4, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
