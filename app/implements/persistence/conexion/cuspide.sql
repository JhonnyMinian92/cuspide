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
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.actividades: ~8 rows (aproximadamente)
INSERT INTO `actividades` (`id`, `nombre`, `periodo_academico`, `categoria_deportiva`, `precio`, `nivel`, `cupo_maximo`, `observacion`, `docente`, `institucion`, `estado`) VALUES
	(1, 'Fútbol Infantil Sub-15', 101, 101, 60.00, 101, 0, 'Para jóvenes de 12 a 15 años', 104, 1, 1),
	(2, 'Fútbol Juvenil Sub-18', 101, 101, 70.00, 101, 0, 'Para jóvenes de 15 a 18 años', 104, 1, 1),
	(3, 'Natación Básica', 101, 102, 50.00, 101, 0, 'Nivel básico para principiantes', 104, 1, 1),
	(4, 'Atletismo Pista', 101, 104, 45.00, 101, 10, 'Entrenamiento de pista completo', 104, 1, 1),
	(101, 'Entrenamiento Futbol Sub 12 Demo', 101, 101, 45.00, 101, 0, 'Grupo formativo lunes y miercoles', 104, 1, 1),
	(102, 'Entrenamiento Arqueros Demo', 102, 101, 55.00, 102, 0, 'Tecnica especifica para porteros', 104, 1, 1),
	(103, 'Natacion Inicial Demo', 101, 102, 50.00, 101, 0, 'Clases iniciales de piscina', 104, 1, 1),
	(104, 'Atletismo Velocidad Demo', 101, 103, 40.00, 102, 0, 'Entrenamiento de salida y sprint', 104, 1, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.actividades_matricula: ~4 rows (aproximadamente)
INSERT INTO `actividades_matricula` (`id`, `actividad`, `matricula`, `modalidad`, `horario`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
	(101, 101, 101, 1, 103, '2026-05-01 00:00:00', '2026-06-30 23:59:59', 1),
	(102, 102, 102, 1, 104, '2026-05-02 00:00:00', '2026-08-02 23:59:59', 1),
	(103, 4, 105, 1, NULL, '2026-05-17 00:00:00', '2026-12-31 00:00:00', 1),
	(104, 1, 105, 1, NULL, '2026-05-17 00:00:00', '2026-12-31 00:00:00', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.alergias: ~3 rows (aproximadamente)
INSERT INTO `alergias` (`id`, `tipo`, `nombre`, `descripcion`, `nivel`, `ficha_medica`) VALUES
	(1, 1, 'ASMA SINTOMATICO', 'Le da con exposicion al polvo', 1, 1),
	(101, 103, 'Polvo ambiental', 'Estornudos al entrenar en cancha seca', 1, 102),
	(102, 101, 'Lactosa', 'Intolerancia leve a lacteos', 1, 103);

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.asignatura_clase: ~4 rows (aproximadamente)
INSERT INTO `asignatura_clase` (`id`, `asignatura`, `clase`, `estado`) VALUES
	(1, 1, 1, 1),
	(2, 101, 101, 1),
	(3, 102, 102, 1),
	(4, 103, 103, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.asignaturas: ~4 rows (aproximadamente)
INSERT INTO `asignaturas` (`id`, `nombre`, `descripcion`, `institucion`, `estado`) VALUES
	(1, 'HISTORIA DEL FUTBOL', 'Clases de la trayectoria del deporte', 1, 1),
	(101, 'Matematica Aplicada Demo', 'Apoyo academico para deportistas', 1, 1),
	(102, 'Historia del Deporte Demo', 'Cultura deportiva y valores', 1, 1),
	(103, 'Preparacion Fisica Demo', 'Fundamentos de acondicionamiento', 1, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.asistencias: ~4 rows (aproximadamente)
INSERT INTO `asistencias` (`id`, `fecha`, `matricula`, `clase`, `actividad`, `calendario`, `horario`, `plan_entrenamiento_sesion`, `hora_entrada`, `hora_salida`, `observacion`, `estado`) VALUES
	(101, '2026-05-18', 101, 101, NULL, 101, 101, NULL, '14:58:00', '16:02:00', 'Asistencia a clase demo', 1),
	(102, '2026-05-19', 101, NULL, 101, 102, 103, 101, '16:25:00', '18:03:00', 'Asistencia a entrenamiento demo', 1),
	(103, '2026-05-20', 102, 102, NULL, 103, 102, NULL, NULL, NULL, 'Falta justificada por permiso', 2),
	(104, '2026-05-22', 102, NULL, 102, NULL, 104, 103, '17:01:00', '18:32:00', 'Sesion de arqueros demo', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=309 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.boton_user: ~161 rows (aproximadamente)
INSERT INTO `boton_user` (`id`, `boton`, `type_user`, `institucion`) VALUES
	(31, 1, 'ADMIN_USER', NULL),
	(32, 2, 'ADMIN_USER', NULL),
	(33, 3, 'ADMIN_USER', NULL),
	(34, 4, 'ADMIN_USER', NULL),
	(35, 5, 'ADMIN_USER', NULL),
	(36, 6, 'ADMIN_USER', NULL),
	(37, 7, 'ADMIN_USER', NULL),
	(38, 8, 'ADMIN_USER', NULL),
	(39, 9, 'ADMIN_USER', NULL),
	(40, 10, 'ADMIN_USER', NULL),
	(41, 11, 'ADMIN_USER', NULL),
	(42, 12, 'ADMIN_USER', NULL),
	(43, 13, 'ADMIN_USER', NULL),
	(44, 14, 'ADMIN_USER', NULL),
	(45, 15, 'ADMIN_USER', NULL),
	(46, 16, 'ADMIN_USER', NULL),
	(47, 17, 'ADMIN_USER', NULL),
	(48, 18, 'ADMIN_USER', NULL),
	(49, 19, 'ADMIN_USER', NULL),
	(50, 20, 'ADMIN_USER', NULL),
	(51, 21, 'ADMIN_USER', NULL),
	(52, 22, 'ADMIN_USER', NULL),
	(53, 23, 'ADMIN_USER', NULL),
	(54, 1, 'DEV_USER', 1),
	(55, 2, 'DEV_USER', 1),
	(56, 3, 'DEV_USER', 1),
	(57, 4, 'DEV_USER', 1),
	(58, 5, 'DEV_USER', 1),
	(59, 6, 'DEV_USER', 1),
	(60, 7, 'DEV_USER', 1),
	(61, 8, 'DEV_USER', 1),
	(62, 9, 'DEV_USER', 1),
	(63, 10, 'DEV_USER', 1),
	(64, 11, 'DEV_USER', 1),
	(65, 12, 'DEV_USER', 1),
	(66, 13, 'DEV_USER', 1),
	(67, 14, 'DEV_USER', 1),
	(68, 15, 'DEV_USER', 1),
	(69, 16, 'DEV_USER', 1),
	(70, 17, 'DEV_USER', 1),
	(71, 18, 'DEV_USER', 1),
	(72, 19, 'DEV_USER', 1),
	(73, 20, 'DEV_USER', 1),
	(74, 21, 'DEV_USER', 1),
	(75, 22, 'DEV_USER', 1),
	(76, 23, 'DEV_USER', 1),
	(77, 1, 'ADMIN', 1),
	(78, 2, 'ADMIN', 1),
	(79, 3, 'ADMIN', 1),
	(80, 4, 'ADMIN', 1),
	(81, 5, 'ADMIN', 1),
	(82, 6, 'ADMIN', 1),
	(83, 7, 'ADMIN', 1),
	(84, 8, 'ADMIN', 1),
	(85, 9, 'ADMIN', 1),
	(86, 10, 'ADMIN', 1),
	(87, 11, 'ADMIN', 1),
	(88, 12, 'ADMIN', 1),
	(89, 13, 'ADMIN', 1),
	(90, 14, 'ADMIN', 1),
	(91, 15, 'ADMIN', 1),
	(92, 16, 'ADMIN', 1),
	(93, 17, 'ADMIN', 1),
	(94, 18, 'ADMIN', 1),
	(95, 19, 'ADMIN', 1),
	(96, 20, 'ADMIN', 1),
	(97, 21, 'ADMIN', 1),
	(98, 22, 'ADMIN', 1),
	(99, 23, 'ADMIN', 1),
	(100, 1, 'ADMINISTRATIVO', 1),
	(101, 2, 'ADMINISTRATIVO', 1),
	(102, 3, 'ADMINISTRATIVO', 1),
	(103, 4, 'ADMINISTRATIVO', 1),
	(104, 5, 'ADMINISTRATIVO', 1),
	(105, 6, 'ADMINISTRATIVO', 1),
	(106, 7, 'ADMINISTRATIVO', 1),
	(107, 8, 'ADMINISTRATIVO', 1),
	(108, 9, 'ADMINISTRATIVO', 1),
	(109, 10, 'ADMINISTRATIVO', 1),
	(110, 11, 'ADMINISTRATIVO', 1),
	(111, 12, 'ADMINISTRATIVO', 1),
	(112, 13, 'ADMINISTRATIVO', 1),
	(113, 14, 'ADMINISTRATIVO', 1),
	(114, 15, 'ADMINISTRATIVO', 1),
	(115, 16, 'ADMINISTRATIVO', 1),
	(116, 17, 'ADMINISTRATIVO', 1),
	(117, 18, 'ADMINISTRATIVO', 1),
	(118, 19, 'ADMINISTRATIVO', 1),
	(119, 20, 'ADMINISTRATIVO', 1),
	(120, 21, 'ADMINISTRATIVO', 1),
	(121, 22, 'ADMINISTRATIVO', 1),
	(122, 23, 'ADMINISTRATIVO', 1),
	(123, 1, 'PROFESOR', 1),
	(124, 2, 'PROFESOR', 1),
	(125, 3, 'PROFESOR', 1),
	(126, 4, 'PROFESOR', 1),
	(127, 5, 'PROFESOR', 1),
	(128, 6, 'PROFESOR', 1),
	(129, 7, 'PROFESOR', 1),
	(130, 8, 'PROFESOR', 1),
	(131, 9, 'PROFESOR', 1),
	(132, 10, 'PROFESOR', 1),
	(133, 11, 'PROFESOR', 1),
	(134, 12, 'PROFESOR', 1),
	(135, 13, 'PROFESOR', 1),
	(136, 14, 'PROFESOR', 1),
	(137, 15, 'PROFESOR', 1),
	(138, 16, 'PROFESOR', 1),
	(139, 17, 'PROFESOR', 1),
	(140, 18, 'PROFESOR', 1),
	(141, 19, 'PROFESOR', 1),
	(142, 20, 'PROFESOR', 1),
	(143, 21, 'PROFESOR', 1),
	(144, 22, 'PROFESOR', 1),
	(145, 23, 'PROFESOR', 1),
	(146, 1, 'ESTUDIANTE', 1),
	(147, 2, 'ESTUDIANTE', 1),
	(148, 3, 'ESTUDIANTE', 1),
	(149, 4, 'ESTUDIANTE', 1),
	(150, 5, 'ESTUDIANTE', 1),
	(151, 6, 'ESTUDIANTE', 1),
	(152, 7, 'ESTUDIANTE', 1),
	(153, 8, 'ESTUDIANTE', 1),
	(154, 9, 'ESTUDIANTE', 1),
	(155, 10, 'ESTUDIANTE', 1),
	(156, 11, 'ESTUDIANTE', 1),
	(157, 12, 'ESTUDIANTE', 1),
	(158, 13, 'ESTUDIANTE', 1),
	(159, 14, 'ESTUDIANTE', 1),
	(160, 15, 'ESTUDIANTE', 1),
	(161, 16, 'ESTUDIANTE', 1),
	(162, 17, 'ESTUDIANTE', 1),
	(163, 18, 'ESTUDIANTE', 1),
	(164, 19, 'ESTUDIANTE', 1),
	(165, 20, 'ESTUDIANTE', 1),
	(166, 21, 'ESTUDIANTE', 1),
	(167, 22, 'ESTUDIANTE', 1),
	(168, 23, 'ESTUDIANTE', 1),
	(169, 1, 'MEDICO', 1),
	(170, 2, 'MEDICO', 1),
	(171, 3, 'MEDICO', 1),
	(172, 4, 'MEDICO', 1),
	(173, 5, 'MEDICO', 1),
	(174, 6, 'MEDICO', 1),
	(175, 7, 'MEDICO', 1),
	(176, 8, 'MEDICO', 1),
	(177, 9, 'MEDICO', 1),
	(178, 10, 'MEDICO', 1),
	(179, 11, 'MEDICO', 1),
	(180, 12, 'MEDICO', 1),
	(181, 13, 'MEDICO', 1),
	(182, 14, 'MEDICO', 1),
	(183, 15, 'MEDICO', 1),
	(184, 16, 'MEDICO', 1),
	(185, 17, 'MEDICO', 1),
	(186, 18, 'MEDICO', 1),
	(187, 19, 'MEDICO', 1),
	(188, 20, 'MEDICO', 1),
	(189, 21, 'MEDICO', 1),
	(190, 22, 'MEDICO', 1),
	(191, 23, 'MEDICO', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.botones: ~23 rows (aproximadamente)
INSERT INTO `botones` (`id`, `orden`, `nombre`, `icono`, `tipo`, `color`, `estado`) VALUES
	(1, 1, 'Dashboard', 'bi-house-check-fill', 'MENU', NULL, 1),
	(2, 2, 'Alumnos', 'bi-person-badge-fill', 'MENU', NULL, 0),
	(3, 3, 'Membresías', 'bi-person-vcard-fill', 'MENU', '', 0),
	(4, 4, 'Pagos', 'bi-cash-coin', 'MENU', '', 1),
	(5, 5, 'Asistencia', 'bi-calendar-check-fill', 'MENU', '', 0),
	(6, 6, 'Inventario', 'bi-box-seam-fill', 'MENU', '', 1),
	(7, 7, 'Reportes', 'bi-bar-chart-fill', 'MENU', '', 0),
	(8, 8, 'Empleados', 'bi-person-badge-fill', 'MENU', '', 0),
	(9, 9, 'Matrículas', 'bi-journal-check', 'MENU', '', 1),
	(10, 10, 'Gastos', 'bi-cash-stack', 'MENU', '', 1),
	(11, 11, 'Contratos', 'bi-file-earmark-text', 'MENU', '', 1),
	(12, 12, 'Configuración', 'bi-gear-fill', 'MENU', '', 0),
	(13, 13, 'Usuarios', 'bi-people-fill', 'MENU', '', 1),
	(14, 14, 'Instituciones', 'bi-building', 'MENU', '', 1),
	(15, 15, 'Horarios', 'bi-calendar3', 'MENU', '', 0),
	(16, 16, 'Asignaturas', 'bi-book-fill', 'MENU', '', 0),
	(17, 17, 'Actividades', 'bi-trophy-fill', 'MENU', '', 0),
	(18, 18, 'Categorías', 'bi-tags-fill', 'MENU', '', 0),
	(19, 19, 'Ventas', 'bi-cart-fill', 'MENU', '', 1),
	(20, 20, 'Facturas', 'bi-receipt', 'MENU', '', 0),
	(21, 21, 'Cuentas x Cobrar', 'bi-currency-dollar', 'MENU', '', 0),
	(22, 22, 'Productos', 'bi-box-seam-fill', 'MENU', '', 0),
	(23, 23, 'Horarios', 'bi-calendar3', 'MENU', '', 0);

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
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.calendarios: ~6 rows (aproximadamente)
INSERT INTO `calendarios` (`id`, `titulo`, `tipo`, `institucion`, `horario`, `clase`, `actividad`, `plan_entrenamiento`, `entrenador`, `fecha_inicio`, `fecha_fin`, `ubicacion`, `cupo_maximo`, `observacion`, `estado`) VALUES
	(101, 'Clase matematica demo', 'CLASE', 1, 101, 101, NULL, NULL, 104, '2026-05-18 15:00:00', '2026-05-18 16:00:00', 'Aula 1', 18, 'Evento demo generado manualmente', 1),
	(102, 'Entrenamiento futbol sub 12 demo', 'ACTIVIDAD', 1, 103, NULL, 101, 101, 104, '2026-05-19 16:30:00', '2026-05-19 18:00:00', 'Cancha Principal', 24, 'Evento demo de entrenamiento', 1),
	(103, 'Clase historia demo', 'CLASE', 1, 102, 102, NULL, NULL, 104, '2026-05-20 15:00:00', '2026-05-20 16:00:00', 'Aula 2', 20, 'Evento demo academico', 1),
	(104, 'Clase: Historia del Deporte Demo', 'CLASE', 1, NULL, 102, NULL, NULL, NULL, '2026-05-17 00:00:00', '2026-12-31 00:00:00', '', 20, 'Matrícula #105', 1),
	(105, 'Actividad: Atletismo Pista', 'ACTIVIDAD', 1, NULL, NULL, 4, NULL, NULL, '2026-05-17 00:00:00', '2026-12-31 00:00:00', '', 0, 'Matrícula #105', 1),
	(106, 'Actividad: Fútbol Infantil Sub-15', 'ACTIVIDAD', 1, NULL, NULL, 1, NULL, NULL, '2026-05-17 00:00:00', '2026-12-31 00:00:00', '', 0, 'Matrícula #105', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.calificaciones: ~4 rows (aproximadamente)
INSERT INTO `calificaciones` (`id`, `evaluacion`, `matricula`, `nota`, `observacion`, `estado`) VALUES
	(101, 101, 101, 8.75, 'Buen rendimiento academico', 1),
	(102, 102, 101, 9.10, 'Excelente control de balon', 1),
	(103, 102, 102, 8.40, 'Debe reforzar pase largo', 1),
	(104, 103, 104, 9.35, 'Muy buena condicion fisica', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.categorias_deportivas: ~4 rows (aproximadamente)
INSERT INTO `categorias_deportivas` (`id`, `nombre`, `nemonico`, `deporte`, `edad_minima`, `edad_maxima`, `descripcion`, `institucion`, `estado`) VALUES
	(101, 'Sub 12 Demo', 'DEMO_SUB12', 1, 9, 12, 'Categoria formativa sub 12', 1, 1),
	(102, 'Sub 15 Demo', 'DEMO_SUB15', 1, 13, 15, 'Categoria competitiva sub 15', 1, 1),
	(103, 'Natacion Basica Demo', 'DEMO_NAT_BAS', 2, 8, 14, 'Categoria inicial de natacion', 1, 1),
	(104, 'Atletismo Juvenil Demo', 'DEMO_ATL_JUV', 3, 12, 17, 'Categoria de atletismo juvenil', 1, 1);

-- Volcando estructura para tabla cuspide.ciudades
CREATE TABLE IF NOT EXISTS `ciudades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.ciudades: ~5 rows (aproximadamente)
INSERT INTO `ciudades` (`id`, `nemonico`, `nombre`, `provincia`) VALUES
	(1, 'MACH', 'MACHALA', 'EL ORO'),
	(101, 'HUAQ', 'HUAQUILLAS', 'EL ORO'),
	(102, 'STRO', 'SANTA ROSA', 'EL ORO'),
	(103, 'GYE', 'GUAYAQUIL', 'GUAYAS'),
	(104, 'UIO', 'QUITO', 'PICHINCHA');

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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.clases: ~4 rows (aproximadamente)
INSERT INTO `clases` (`id`, `nombre`, `periodo_academico`, `asignatura`, `precio`, `nivel`, `observacion`, `institucion`, `docente`, `cupo_maximo`, `estado`) VALUES
	(1, 'HISTORIA DEL FUTBOL', 102, 102, 25, 103, '', 1, 104, 10, 1),
	(101, 'Matematica Basica Demo', 101, 101, 25, 101, 'Clase de refuerzo academico', 1, 104, 18, 1),
	(102, 'Historia del Deporte Demo', 101, 102, 20, 101, 'Clase teorica semanal', 1, 104, 20, 1),
	(103, 'Preparacion Fisica Competitiva Demo', 101, 103, 35, 103, 'Acondicionamiento para competencia', 1, 104, 16, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.clases_matricula: ~5 rows (aproximadamente)
INSERT INTO `clases_matricula` (`id`, `clase`, `matricula`, `contrato`, `modalidad`, `horario`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
	(1, 1, 1, 1, 1, NULL, '2026-05-07 14:20:58', '2026-06-07 14:20:58', 1),
	(101, 101, 101, 101, 1, 101, '2026-05-01 00:00:00', '2026-06-30 23:59:59', 1),
	(102, 102, 102, 102, 1, 102, '2026-05-02 00:00:00', '2026-08-02 23:59:59', 1),
	(103, 103, 104, 104, 1, NULL, '2026-05-04 00:00:00', '2026-12-31 23:59:59', 1),
	(104, 102, 105, NULL, 1, NULL, '2026-05-17 00:00:00', '2026-12-31 00:00:00', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.contratacion: ~5 rows (aproximadamente)
INSERT INTO `contratacion` (`id`, `fecha_inscripcion`, `fecha_inicio`, `fecha_fin`, `nivel`, `institucion`, `persona`) VALUES
	(1, '2026-05-07 17:41:02', '2026-05-07 17:41:03', '2026-06-07 17:41:06', 3, 1, 1),
	(101, '2026-05-01 09:00:00', '2026-05-01 00:00:00', '2026-12-31 23:59:59', 2, 1, 106),
	(102, '2026-05-02 09:00:00', '2026-05-02 00:00:00', '2026-12-31 23:59:59', 2, 1, 107),
	(103, '2026-05-03 09:00:00', '2026-05-03 00:00:00', '2026-12-31 23:59:59', 2, 1, 108),
	(104, '2026-05-04 09:00:00', '2026-05-04 00:00:00', '2026-12-31 23:59:59', 3, 1, 109);

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
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.correos: ~11 rows (aproximadamente)
INSERT INTO `correos` (`id`, `email`, `tipo`, `persona`, `estado`) VALUES
	(1, 'jhonnyminian@gmail.com', 1, 1, 1),
	(101, 'admin.demo@cuspide.local', 103, 101, 1),
	(102, 'gerencia.demo@cuspide.local', 103, 102, 1),
	(103, 'secretaria.demo@cuspide.local', 103, 103, 1),
	(104, 'coach.demo@cuspide.local', 103, 104, 1),
	(105, 'medico.demo@cuspide.local', 103, 105, 1),
	(106, 'ana.demo@cuspide.local', 101, 106, 2),
	(107, 'juan.demo@cuspide.local', 101, 107, 1),
	(108, 'maria.demo@cuspide.local', 101, 108, 1),
	(109, 'luis.demo@cuspide.local', 101, 109, 1),
	(110, 'ana.demo@cuspide.local', 1, 106, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.cuotas_matricula: ~15 rows (aproximadamente)
INSERT INTO `cuotas_matricula` (`id`, `plan_pago`, `numero`, `fecha_vencimiento`, `valor`, `saldo`, `estado`) VALUES
	(101, 101, 1, '2026-05-10', 55.00, 0.00, 2),
	(102, 101, 2, '2026-06-10', 55.00, 55.00, 1),
	(103, 102, 1, '2026-05-10', 45.00, 0.00, 2),
	(104, 102, 2, '2026-06-10', 45.00, 0.00, 2),
	(105, 102, 3, '2026-07-10', 45.00, 0.00, 2),
	(106, 103, 1, '2026-05-10', 45.00, 0.00, 2),
	(107, 103, 2, '2026-06-10', 45.00, 45.00, 1),
	(108, 104, 1, '2026-06-16', 15.63, 15.63, 1),
	(109, 104, 2, '2026-07-16', 15.63, 15.63, 1),
	(110, 104, 3, '2026-08-15', 15.63, 15.63, 1),
	(111, 104, 4, '2026-09-14', 15.63, 15.63, 1),
	(112, 104, 5, '2026-10-14', 15.63, 15.63, 1),
	(113, 104, 6, '2026-11-13', 15.63, 15.63, 1),
	(114, 104, 7, '2026-12-13', 15.63, 15.63, 1),
	(115, 104, 8, '2027-01-12', 15.59, 15.59, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.deportes: ~5 rows (aproximadamente)
INSERT INTO `deportes` (`id`, `nombre`, `descripcion`, `institucion`, `estado`) VALUES
	(1, 'FUTBOL', 'Clases  en cacha de futbol sala', 1, 1),
	(2, 'Natacion', 'Entrenamiento acuatico formativo', 1, 1),
	(3, 'Atletismo', 'Entrenamiento de pista y campo', 1, 1),
	(101, 'Baloncesto', 'Fundamentos de baloncesto', 1, 1),
	(102, 'Preparacion Fisica', 'Acondicionamiento general', 1, 1);

-- Volcando estructura para tabla cuspide.enfermedad_persona
CREATE TABLE IF NOT EXISTS `enfermedad_persona` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enfermedad` int(11) NOT NULL,
  `medicacion` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_ENFERMEDAD_PERSONA` (`enfermedad`),
  CONSTRAINT `FK1_ENFERMEDAD_PERSONA` FOREIGN KEY (`enfermedad`) REFERENCES `enfermedades` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.enfermedad_persona: ~3 rows (aproximadamente)
INSERT INTO `enfermedad_persona` (`id`, `enfermedad`, `medicacion`, `descripcion`) VALUES
	(1, 1, 'salbutamol ', 'Usar antes de cualquier actividad fisica'),
	(101, 101, 'Salbutamol SOS', 'Control medico previo a competencias'),
	(102, 102, 'Fisioterapia preventiva', 'Seguimiento por lesion antigua');

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.enfermedades: ~3 rows (aproximadamente)
INSERT INTO `enfermedades` (`id`, `tipo`, `nombre`, `nemonico`, `nivel`) VALUES
	(1, 1, 'ASMA BRONQUIAL', 'ASMA_B', 3),
	(101, 1, 'Asma leve', 'ASMA_LEVE_DEMO', 1),
	(102, 103, 'Tendinitis rotuliana', 'TENDINITIS_DEMO', 2);

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
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.estado_fisico: ~11 rows (aproximadamente)
INSERT INTO `estado_fisico` (`id`, `perfil_medico`, `tipo`, `descripcion`) VALUES
	(1, 1, 1, '180 cm'),
	(101, 101, 1, 'Altura 1.48 m'),
	(102, 101, 2, 'Peso 42 kg'),
	(103, 101, 5, 'Talla camiseta S'),
	(104, 102, 1, 'Altura 1.62 m'),
	(105, 102, 2, 'Peso 50 kg'),
	(106, 102, 6, 'Calzado 39'),
	(107, 103, 1, 'Altura 1.55 m'),
	(108, 103, 2, 'Peso 47 kg'),
	(109, 104, 1, 'Altura 1.70 m'),
	(110, 104, 2, 'Peso 62 kg');

-- Volcando estructura para tabla cuspide.estado_permiso
CREATE TABLE IF NOT EXISTS `estado_permiso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nemonico` (`nemonico`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.estado_permiso: ~3 rows (aproximadamente)
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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.evaluaciones: ~3 rows (aproximadamente)
INSERT INTO `evaluaciones` (`id`, `nombre`, `clase`, `actividad`, `fecha`, `peso_porcentaje`, `estado`) VALUES
	(101, 'Evaluacion matematica demo', 101, NULL, '2026-05-25', 30.00, 1),
	(102, 'Control tecnico futbol demo', NULL, 101, '2026-05-26', 40.00, 1),
	(103, 'Prueba fisica competitiva demo', 103, NULL, '2026-05-27', 30.00, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.fichas_medicas: ~5 rows (aproximadamente)
INSERT INTO `fichas_medicas` (`id`, `persona`, `grupo_sanguineo`, `alergias`, `enfermedades_preexistentes`, `medicamentos_actuales`, `contacto_emergencia_nombre`, `contacto_emergencia_telefono`, `fecha`, `perfil_medico`, `estado`) VALUES
	(1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-07 12:24:48', 1, 1),
	(101, 106, 'O+', 'Ninguna reportada', 'Ninguna', 'Ninguno', 'Maria Lopez', '0987000106', '2026-05-01 08:10:00', 101, 1),
	(102, 107, 'A+', 'Polvo', 'Asma leve', 'Salbutamol SOS', 'Carlos Morales', '0987000107', '2026-05-02 08:10:00', 102, 1),
	(103, 108, 'B+', 'Lactosa', 'Ninguna', 'Ninguno', 'Diana Castro', '0987000108', '2026-05-03 08:10:00', 103, 1),
	(104, 109, 'O-', 'Ninguna reportada', 'Lesion rodilla antigua', 'Ninguno', 'Rosa Salazar', '0987000109', '2026-05-04 08:10:00', 104, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.gastos: ~4 rows (aproximadamente)
INSERT INTO `gastos` (`id`, `numero`, `fecha`, `descripcion`, `tipo_gasto`, `monto`, `institucion`, `persona`, `referencia`, `observacion`, `estado`) VALUES
	(101, 'DEMO-GAS-0001', '2026-05-05 10:00:00', 'Compra de hidratacion para entrenamiento', 'OPERATIVO', 38.50, 1, 103, 'FACT-DEMO-001', 'Agua y bebidas hidratantes', 1),
	(102, 'DEMO-GAS-0002', '2026-05-06 12:00:00', 'Mantenimiento de cancha', 'MANTENIMIENTO', 120.00, 1, 102, 'FACT-DEMO-002', 'Corte de cesped y pintura', 1),
	(103, 'DEMO-GAS-0003', '2026-05-08 14:30:00', 'Transporte a amistoso', 'LOGISTICA', 75.00, 1, 104, 'REC-DEMO-003', 'Movilizacion equipo sub 12', 1),
	(104, 'G-20260518164839', '2026-05-18 00:00:00', 'mantenimiento del sistema', 'MANTENIMIENTO', 100.00, 1, 1, '12555', 'se le pago ha un externo', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.horarios: ~4 rows (aproximadamente)
INSERT INTO `horarios` (`id`, `nombre`, `institucion`, `clase`, `actividad`, `categoria_deportiva`, `entrenador`, `dia_semana`, `hora_inicio`, `hora_fin`, `fecha_inicio`, `fecha_fin`, `cupo_maximo`, `ubicacion`, `estado`) VALUES
	(101, 'Matematica Demo Lunes', 1, 101, NULL, NULL, 104, 1, '15:00:00', '16:00:00', '2026-05-01', '2026-12-15', 18, 'Aula 1', 1),
	(102, 'Historia Demo Miercoles', 1, 102, NULL, NULL, 104, 3, '15:00:00', '16:00:00', '2026-05-01', '2026-12-15', 20, 'Aula 2', 1),
	(103, 'Futbol Sub 12 Demo', 1, NULL, 101, 101, 104, 2, '16:30:00', '18:00:00', '2026-05-01', '2026-12-15', 24, 'Cancha Principal', 1),
	(104, 'Arqueros Demo Viernes', 1, NULL, 102, 102, 104, 5, '17:00:00', '18:30:00', '2026-05-01', '2026-12-15', 12, 'Cancha Alterna', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.incidencia: ~3 rows (aproximadamente)
INSERT INTO `incidencia` (`id`, `fecha`, `persona`, `nivel`, `descripcion`) VALUES
	(1, '2026-05-07 13:14:00', 1, 1, 'Grito en la cancha'),
	(101, '2026-05-19 18:05:00', 106, 1, 'Golpe leve durante entrenamiento, atendido en sitio'),
	(102, '2026-05-22 18:40:00', 107, 2, 'Molestia en tobillo, se recomienda reposo preventivo');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.instituciones: ~0 rows (aproximadamente)
INSERT INTO `instituciones` (`id`, `ruc`, `razon_social`, `nombre`, `logotipo`, `estado`) VALUES
	(1, '0791842247001', 'Huaquillas-sporting-club S.a.s.', 'Huaquillas Futbol Club', 'logo/huaquillasfc.jpg', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.inventario: ~8 rows (aproximadamente)
INSERT INTO `inventario` (`id`, `producto`, `variante`, `institucion`, `stock_actual`, `stock_minimo`, `fecha_actualizacion`) VALUES
	(101, 101, 101, 1, 18.00, 5.00, '2026-05-10 08:00:00'),
	(102, 101, 102, 1, 24.00, 5.00, '2026-05-10 08:00:00'),
	(103, 101, 103, 1, 12.00, 5.00, '2026-05-10 08:00:00'),
	(104, 102, 104, 1, 16.00, 5.00, '2026-05-10 08:00:00'),
	(105, 102, 105, 1, 10.00, 5.00, '2026-05-10 08:00:00'),
	(106, 103, NULL, 1, 8.00, 3.00, '2026-05-10 08:00:00'),
	(107, 104, NULL, 1, 13.00, 4.00, '2026-05-18 18:16:19'),
	(108, 105, NULL, 1, 30.00, 8.00, '2026-05-10 08:00:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.matricula: ~6 rows (aproximadamente)
INSERT INTO `matricula` (`id`, `fecha_registro`, `persona`, `fecha_inicio`, `fecha_fin`, `membresia`, `institucion`, `estado`) VALUES
	(1, '2026-05-07 13:26:19', 1, '2026-05-07 13:26:21', '2027-05-07 13:26:24', NULL, 1, 1),
	(101, '2026-05-01 09:10:00', 106, '2026-05-01 00:00:00', '2026-06-30 23:59:59', 101, 1, 1),
	(102, '2026-05-02 10:15:00', 107, '2026-05-02 00:00:00', '2026-08-02 23:59:59', 102, 1, 1),
	(103, '2026-05-03 11:00:00', 108, '2026-05-03 00:00:00', '2026-06-03 23:59:59', 101, 1, 2),
	(104, '2026-05-04 15:30:00', 109, '2026-05-04 00:00:00', '2026-12-31 23:59:59', 103, 1, 1),
	(105, '2026-05-17 23:00:24', 109, '2026-05-17 00:00:00', '2026-12-31 00:00:00', 101, 1, 1);

-- Volcando estructura para tabla cuspide.membresias
CREATE TABLE IF NOT EXISTS `membresias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `precio` varchar(20) NOT NULL,
  `porcentaje` double NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.membresias: ~4 rows (aproximadamente)
INSERT INTO `membresias` (`id`, `titulo`, `precio`, `porcentaje`, `descripcion`, `url_boton`, `fecha_inicio`, `fecha_fin`, `estado`, `institucion`) VALUES
	(1, 'FUTBOL SUB17', '90', 10, 'Clases para categoria de estudiantes con 17 años', '', '2026-05-07 14:30:47', '2026-06-07 14:30:48', 1, NULL),
	(101, 'Mensual Demo', '55.00', 0, 'Membresia mensual de entrenamiento', '/membresias', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1, 1),
	(102, 'Trimestral Demo', '150.00', 10, 'Plan trimestral con descuento', '/membresias', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1, 1),
	(103, 'Competitivo Demo', '90.00', 5, 'Plan competitivo con seguimiento', '/membresias', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.movimientos_inventario: ~9 rows (aproximadamente)
INSERT INTO `movimientos_inventario` (`id`, `producto`, `variante`, `institucion`, `tipo`, `cantidad`, `stock_anterior`, `stock_nuevo`, `referencia`, `fecha`, `observacion`) VALUES
	(101, 101, 101, 1, 'ENTRADA', 20.00, 0.00, 20.00, 'COMPRA-DEMO-001', '2026-05-01 09:00:00', 'Ingreso inicial camisetas S'),
	(102, 101, 101, 1, 'SALIDA', 2.00, 20.00, 18.00, 'VENTA-DEMO-001', '2026-05-12 11:00:00', 'Venta demo camisetas S'),
	(103, 103, NULL, 1, 'ENTRADA', 10.00, 0.00, 10.00, 'COMPRA-DEMO-002', '2026-05-02 09:00:00', 'Ingreso inicial balones'),
	(104, 103, NULL, 1, 'SALIDA', 2.00, 10.00, 8.00, 'VENTA-DEMO-002', '2026-05-13 12:30:00', 'Venta demo balones'),
	(105, 105, NULL, 1, 'ENTRADA', 30.00, 0.00, 30.00, 'COMPRA-DEMO-003', '2026-05-03 10:15:00', 'Ingreso inicial botellas'),
	(109, 104, NULL, 1, 'SALIDA', 2.00, 15.00, 13.00, 'VTA-20260518-6a0b3b5300256', '2026-05-18 18:16:19', 'Venta VTA-20260518-6a0b3b5300256'),
	(110, 107, NULL, 1, 'SALIDA', 1.00, 0.00, -1.00, 'VTA-20260518-6a0b3da28880c', '2026-05-18 18:26:10', 'Venta VTA-20260518-6a0b3da28880c'),
	(111, 107, NULL, 1, 'SALIDA', 1.00, 0.00, -1.00, 'VTA-20260518-6a0b3dac7bd96', '2026-05-18 18:26:20', 'Venta VTA-20260518-6a0b3dac7bd96'),
	(112, 107, NULL, 1, 'ENTRADA', 1.00, 0.00, 1.00, 'VTA-20260518-6a0b3da28880c', '2026-05-18 19:09:21', 'Anulación de Venta VTA-20260518-6a0b3da28880c');

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.pago_detalle: ~4 rows (aproximadamente)
INSERT INTO `pago_detalle` (`id`, `pago`, `cuota_matricula`, `venta`, `valor`) VALUES
	(101, 101, 101, 101, 56.00),
	(102, 102, 106, 104, 45.00),
	(103, 103, 101, 103, 55.00),
	(104, 106, 108, 105, 15.63);

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
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.pagos: ~5 rows (aproximadamente)
INSERT INTO `pagos` (`id`, `numero`, `fecha`, `persona`, `matricula`, `venta`, `forma_pago`, `institucion`, `monto`, `referencia`, `observacion`, `estado`, `cobrado_por`) VALUES
	(101, 'DEMO-PAG-0001', '2026-05-12 11:05:00', 106, 101, 101, 101, 1, 56.00, 'CAJA-DEMO-001', 'Pago completo venta producto', 1, 103),
	(102, 'DEMO-PAG-0002', '2026-05-04 15:50:00', 109, 104, 104, 102, 1, 45.00, 'TRF-DEMO-002', 'Abono matricula competitiva', 1, 103),
	(103, 'DEMO-PAG-0003', '2026-05-01 09:25:00', 106, 101, 103, 101, 1, 55.00, 'CAJA-DEMO-003', 'Pago primera cuota mensual', 1, 103),
	(106, 'PAG-20260517-000105', '2026-05-17 00:00:00', 109, 105, 105, 101, 1, 0.00, NULL, 'Pendiente cuota 1 — matrícula #105', 1, NULL),
	(110, 'PAG-20260518-6a0b3da28aa2a', '2026-05-18 18:26:10', 101, NULL, 110, 101, 1, 10.00, NULL, 'Pago de venta VTA-20260518-6a0b3da28880c', 2, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.perfil_medico: ~5 rows (aproximadamente)
INSERT INTO `perfil_medico` (`id`, `fecha`, `persona`, `estado`) VALUES
	(1, '2026-05-07 11:58:46', 1, 1),
	(101, '2026-05-01 08:00:00', 106, 1),
	(102, '2026-05-02 08:00:00', 107, 1),
	(103, '2026-05-03 08:00:00', 108, 1),
	(104, '2026-05-04 08:00:00', 109, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.periodos_academicos: ~2 rows (aproximadamente)
INSERT INTO `periodos_academicos` (`id`, `nombre`, `fecha_inicio`, `fecha_fin`, `institucion`, `estado`) VALUES
	(101, 'Periodo Demo 2026', '2026-01-15', '2026-12-15', 1, 1),
	(102, 'Vacacional Demo 2026', '2026-03-01', '2026-04-30', 1, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.permisos: ~3 rows (aproximadamente)
INSERT INTO `permisos` (`id`, `tipo`, `persona`, `matricula`, `clase`, `actividad`, `fecha_inicio`, `fecha_fin`, `horas_por_dia`, `motivo`, `observacion`, `fecha_registro`, `estado`, `fechaInicio`, `fechaFin`, `descripcion`) VALUES
	(2, 1, 1, NULL, NULL, NULL, '2026-05-07 00:00:01', '2026-05-07 23:59:59', NULL, 'Inconveniente Familiar', 'Inconveniente Familiar', '2026-05-17 01:17:09', 1, '2026-05-07 00:00:01', '2026-05-07 23:59:59', 'Inconveniente Familiar'),
	(101, 101, 107, NULL, NULL, NULL, '2026-05-20 00:00:00', '2026-05-20 23:59:59', NULL, 'Control medico programado', 'Control medico programado', '2026-05-17 01:17:09', 2, '2026-05-20 00:00:00', '2026-05-20 23:59:59', 'Control medico programado'),
	(102, 102, 106, NULL, NULL, NULL, '2026-05-28 00:00:00', '2026-05-28 23:59:59', NULL, 'Salida academica pendiente', 'Salida academica pendiente', '2026-05-17 01:17:09', 1, '2026-05-28 00:00:00', '2026-05-28 23:59:59', 'Salida academica pendiente');

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
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.persona_institucion: ~10 rows (aproximadamente)
INSERT INTO `persona_institucion` (`id`, `id_persona`, `id_institucion`, `tipo`, `estado`) VALUES
	(1, 1, 1, 1, 1),
	(101, 101, 1, 1, 1),
	(102, 102, 1, 2, 1),
	(103, 103, 1, 102, 1),
	(104, 104, 1, 4, 1),
	(105, 105, 1, 101, 1),
	(106, 106, 1, 3, 1),
	(107, 107, 1, 3, 1),
	(108, 108, 1, 3, 1),
	(109, 109, 1, 3, 1);

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
  `user` int(11) NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.personas: ~10 rows (aproximadamente)
INSERT INTO `personas` (`id`, `identificacion`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `nacionalidad`, `genero`, `etnia`, `fecha_nacimiento`, `ciudad`, `direccion`, `user`) VALUES
	(1, '0706323193', 'Jhonny', 'Darwin', 'Miñan', 'Girón', 1, 1, 1, '1992-03-02 00:00:00', 1, '25 de Junio y Callejon Babahoyo', 1),
	(101, '0700000101', 'Carlos', 'Andres', 'Soberon', 'Demo', 1, 1, 1, '1985-02-12 00:00:00', 101, 'Av. Republica y Loja, Huaquillas', 101),
	(102, '0700000102', 'Valeria', 'Isabel', 'Torres', 'Mendoza', 1, 2, 1, '1989-08-22 00:00:00', 101, 'Cdla. Las Mercedes, Huaquillas', 102),
	(103, '0700000103', 'Daniela', 'Paola', 'Cabrera', 'Ruiz', 1, 2, 1, '1994-01-18 00:00:00', 102, 'Barrio Central, Santa Rosa', 103),
	(104, '0700000104', 'Pedro', 'Andres', 'Rojas', 'Vera', 1, 1, 1, '1988-04-11 00:00:00', 101, 'Via al estadio, Huaquillas', 104),
	(105, '0700000105', 'Lorena', 'Gabriela', 'Vargas', 'Leon', 1, 2, 1, '1990-09-05 00:00:00', 101, 'Centro medico deportivo, Huaquillas', 105),
	(106, '0700000106', 'Ana', 'Paula', 'Alvarado', 'Lopez', 1, 2, 1, '2026-05-17 00:00:00', 101, 'Barrio El Bosque, Huaquillas', 106),
	(107, '0700000107', 'Juan', 'Diego', 'Morales', 'Perez', 1, 1, 1, '2010-09-23 00:00:00', 101, 'Cdla. Las Mercedes, Huaquillas', 107),
	(108, '0700000108', 'Maria', 'Jose', 'Reyes', 'Castro', 1, 2, 101, '2011-11-02 00:00:00', 102, 'Barrio Nuevo, Santa Rosa', 108),
	(109, '0700000109', 'Luis', 'Fernando', 'Molina', 'Salazar', 1, 1, 102, '2009-06-30 00:00:00', 103, 'Garzota, Guayaquil', 109);

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
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.plan_entrenamiento_ejercicios: ~5 rows (aproximadamente)
INSERT INTO `plan_entrenamiento_ejercicios` (`id`, `sesion`, `orden`, `nombre`, `descripcion`, `series`, `repeticiones`, `duracion_segundos`, `descanso_segundos`, `intensidad`, `estado`) VALUES
	(101, 101, 1, 'Rondos 4x2', 'Trabajo de pase, apoyo y presion', 4, '4 min', 240, 60, 'Media', 1),
	(102, 101, 2, 'Conduccion zigzag', 'Circuito con conos y finalizacion', 5, '6 pasadas', 0, 45, 'Media', 1),
	(103, 102, 1, 'Intervalos cortos', 'Carreras de 20 metros con balon', 6, '20 m', 0, 30, 'Alta', 1),
	(104, 103, 1, 'Reaccion a color', 'Salida a disparo segun senal visual', 5, '8 rep', 0, 45, 'Alta', 1),
	(105, 103, 2, 'Blocaje frontal', 'Recepcion y control de balon frontal', 4, '10 rep', 0, 40, 'Media alta', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.plan_entrenamiento_sesiones: ~3 rows (aproximadamente)
INSERT INTO `plan_entrenamiento_sesiones` (`id`, `plan_entrenamiento`, `calendario`, `horario`, `numero_semana`, `dia_semana`, `fecha_programada`, `titulo`, `objetivo`, `duracion_minutos`, `intensidad`, `contenido`, `observacion`, `estado`) VALUES
	(101, 101, 102, 103, 1, 2, '2026-05-19', 'Tecnica con balon', 'Control orientado y pase corto', 90, 'Media', 'Calentamiento, tecnica individual, rondos y juego reducido', 'Sesion demo', 1),
	(102, 101, NULL, 103, 1, 4, '2026-05-21', 'Resistencia con balon', 'Mejorar cambios de ritmo', 80, 'Media alta', 'Circuito de conduccion y finalizacion', 'Sesion demo', 1),
	(103, 102, NULL, 104, 1, 5, '2026-05-22', 'Reflejos de arquero', 'Mejorar reaccion y blocaje', 90, 'Alta', 'Activacion, reaccion visual, caidas laterales y salida aerea', 'Sesion demo', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.plan_pago_matricula: ~4 rows (aproximadamente)
INSERT INTO `plan_pago_matricula` (`id`, `matricula`, `periodicidad`, `subtotal`, `descuento`, `total`, `saldo`, `fecha_generacion`, `estado`) VALUES
	(101, 101, 101, 110.00, 0.00, 110.00, 55.00, '2026-05-01 09:15:00', 1),
	(102, 102, 102, 150.00, 15.00, 135.00, 0.00, '2026-05-02 10:20:00', 1),
	(103, 104, 101, 90.00, 0.00, 90.00, 45.00, '2026-05-04 15:35:00', 1),
	(104, 105, 101, 125.00, 0.00, 125.00, 125.00, '2026-05-17 00:00:00', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.planes_entrenamiento: ~2 rows (aproximadamente)
INSERT INTO `planes_entrenamiento` (`id`, `nombre`, `objetivo`, `descripcion`, `matricula`, `clase`, `actividad`, `categoria_deportiva`, `entrenador`, `institucion`, `nivel`, `fecha_inicio`, `fecha_fin`, `version`, `estado`) VALUES
	(101, 'Plan Sub 12 Ana Demo', 'Mejorar tecnica y resistencia', 'Plan de entrenamiento de cuatro semanas', 101, NULL, 101, 101, 104, 1, 101, '2026-05-01', '2026-05-31', 1, 1),
	(102, 'Plan Arqueros Juan Demo', 'Reforzar reflejos y posicionamiento', 'Plan especifico para porteros juveniles', 102, NULL, 102, 102, 104, 1, 102, '2026-05-01', '2026-06-15', 1, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.producto_categorias: ~3 rows (aproximadamente)
INSERT INTO `producto_categorias` (`id`, `nombre`, `nemonico`, `descripcion`, `institucion`, `estado`) VALUES
	(101, 'Uniformes Demo', 'UNIFORMES_DEMO', 'Camisetas, shorts y medias', 1, 1),
	(102, 'Equipos Deportivos Demo', 'EQUIPOS_DEMO', 'Balones y materiales de entrenamiento', 1, 1),
	(103, 'Accesorios Demo', 'ACCESORIOS_DEMO', 'Botellas, conos y articulos menores', 1, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.producto_variantes: ~5 rows (aproximadamente)
INSERT INTO `producto_variantes` (`id`, `producto`, `sku`, `talla`, `color`, `genero`, `precio`, `estado`) VALUES
	(101, 101, 'DEMO-CAM-001-S-AZUL', 'S', 'Azul', 'Unisex', 28.00, 1),
	(102, 101, 'DEMO-CAM-001-M-AZUL', 'M', 'Azul', 'Unisex', 28.00, 1),
	(103, 101, 'DEMO-CAM-001-L-AZUL', 'L', 'Azul', 'Unisex', 28.00, 1),
	(104, 102, 'DEMO-SHO-001-M-NEGRO', 'M', 'Negro', 'Unisex', 18.00, 1),
	(105, 102, 'DEMO-SHO-001-L-NEGRO', 'L', 'Negro', 'Unisex', 18.00, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.productos: ~7 rows (aproximadamente)
INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `categoria`, `precio`, `costo`, `controla_stock`, `institucion`, `estado`) VALUES
	(101, 'DEMO-CAM-001', 'Camiseta Oficial Demo', 'Camiseta de entrenamiento color azul', 101, 28.00, 16.00, 1, 1, 1),
	(102, 'DEMO-SHO-001', 'Short Deportivo Demo', 'Short negro de entrenamiento', 101, 18.00, 10.00, 1, 1, 1),
	(103, 'DEMO-BAL-001', 'Balon Futbol #5 Demo', 'Balon de futbol numero cinco', 102, 35.00, 22.00, 1, 1, 1),
	(104, 'DEMO-CON-001', 'Set de Conos Demo', 'Paquete de veinte conos', 102, 22.00, 12.00, 1, 1, 1),
	(105, 'DEMO-BOT-001', 'Botella Deportiva Demo', 'Botella plastica 750 ml', 103, 8.50, 4.00, 1, 1, 1),
	(106, 'DEMO-MAT-001', 'Matricula Deportiva Demo', 'Servicio de inscripcion deportiva', 103, 45.00, 0.00, 0, 1, 1),
	(107, '002335', 'Camiseta HF', 'prueba de almacenar', 101, 10.00, 25.00, 1, 1, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.tallas: ~6 rows (aproximadamente)
INSERT INTO `tallas` (`id`, `persona`, `tipo_prenda`, `talla`, `medida_cm`, `fecha_medicion`, `estado`) VALUES
	(101, 106, 'Camiseta', 'S', 'Pecho 78 cm', '2026-05-01', 1),
	(102, 106, 'Calzado', '36', 'Pie 23 cm', '2026-05-01', 1),
	(103, 107, 'Camiseta', 'M', 'Pecho 86 cm', '2026-05-02', 1),
	(104, 107, 'Calzado', '39', 'Pie 25 cm', '2026-05-02', 1),
	(105, 108, 'Camiseta', 'M', 'Pecho 84 cm', '2026-05-03', 1),
	(106, 109, 'Camiseta', 'L', 'Pecho 92 cm', '2026-05-04', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.telefonos: ~11 rows (aproximadamente)
INSERT INTO `telefonos` (`id`, `numero`, `operadora`, `persona`, `tipo`, `estado`) VALUES
	(1, '0981100645', 1, 1, 1, 1),
	(101, '0981000101', 1, 101, 103, 1),
	(102, '0981000102', 101, 102, 103, 1),
	(103, '0981000103', 1, 103, 103, 1),
	(104, '0981000104', 101, 104, 103, 1),
	(105, '0981000105', 102, 105, 103, 1),
	(106, '0981000106', 1, 106, 101, 2),
	(107, '0981000107', 101, 107, 101, 1),
	(108, '0981000108', 102, 108, 101, 1),
	(109, '0981000109', 103, 109, 101, 1),
	(110, '0981000106', 1, 106, 1, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.tipo_persona: ~6 rows (aproximadamente)
INSERT INTO `tipo_persona` (`id`, `nemonico`, `nombre`) VALUES
	(1, 'TIPO_DEV', 'DESARROLLADOR'),
	(2, 'TIPO_ADMIN', 'ADMIN'),
	(3, 'TIPO_EST', 'ESTUDIANTE'),
	(4, 'TIPO_PROF', 'DOCENTE'),
	(101, 'TIPO_MED', 'MEDICO'),
	(102, 'TIPO_ADM_OPER', 'ADMINISTRATIVO');

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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.torneo_participantes: ~3 rows (aproximadamente)
INSERT INTO `torneo_participantes` (`id`, `torneo`, `matricula`, `fecha_inscripcion`, `estado`) VALUES
	(101, 101, 101, '2026-05-15', 1),
	(102, 101, 102, '2026-05-15', 1),
	(103, 102, 104, '2026-05-16', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.torneos: ~2 rows (aproximadamente)
INSERT INTO `torneos` (`id`, `nombre`, `fecha_inicio`, `fecha_fin`, `deporte`, `institucion`, `estado`) VALUES
	(101, 'Copa Formativa Demo 2026', '2026-06-10', '2026-06-20', 1, 1, 1),
	(102, 'Festival Atletismo Demo 2026', '2026-07-05', '2026-07-06', 3, 1, 1);

-- Volcando estructura para tabla cuspide.type_users
CREATE TABLE IF NOT EXISTS `type_users` (
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  PRIMARY KEY (`nemonico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.type_users: ~8 rows (aproximadamente)
INSERT INTO `type_users` (`nemonico`, `nombre`, `descripcion`, `imagen`) VALUES
	('ADMIN', 'Administrador', 'Usuario administrador para pruebas', 'icons/dev.jpg'),
	('ADMINISTRATIVO', 'Administrativo', 'Usuario administrativo para caja y secretaria', 'icons/admin.jpg'),
	('ADMIN_USER', 'Administrador', 'Usuario con privilegios administrativos', 'icons/dev.jpg'),
	('DEV_USER', 'Desarrollador', 'Usuario con privilegios totales de desarrollador', 'icons/dev.jpg'),
	('ESTUDIANTE', 'Estudiante', 'Usuario estudiante para pruebas', 'icons/student.jpg'),
	('INACTIVO', 'Inactivo', 'Usuario deshabilitado', 'icons/inactive.jpg'),
	('MEDICO', 'Personal Medico', 'Usuario medico para pruebas', 'icons/medical.jpg'),
	('PROFESOR', 'Profesor', 'Entrenador o docente para pruebas', 'icons/coach.jpg');

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
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.users: ~10 rows (aproximadamente)
INSERT INTO `users` (`id`, `username`, `password`, `type`, `image`) VALUES
	(1, 'jminan', '$2y$10$3nV1BdEGxt1BHuvwtsB24uT.4e8KE70ZteyDb6Qjo60VMUAfuSTpW', 'DEV_USER', 'icons/dev.jpg'),
	(101, 'admin.demo', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'DEV_USER', 'icons/dev.jpg'),
	(102, 'gerencia.demo', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'ADMIN', 'icons/admin.jpg'),
	(103, 'secretaria.demo', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'ADMINISTRATIVO', 'icons/admin.jpg'),
	(104, 'coach.demo', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'PROFESOR', 'icons/coach.jpg'),
	(105, 'medico.demo', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'MEDICO', 'icons/medical.jpg'),
	(106, 'ana.demo', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'ESTUDIANTE', 'icons/student.jpg'),
	(107, 'juan.demo', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'ESTUDIANTE', 'icons/student.jpg'),
	(108, 'maria.demo', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'ESTUDIANTE', 'icons/student.jpg'),
	(109, 'luis.demo', '$2y$10$VGinM4DJkVMbMB5OX/kYnuIBW.ihTovJY/njq2FxHjMALvq8IwoAm', 'ESTUDIANTE', 'icons/student.jpg');

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
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.venta_detalle: ~7 rows (aproximadamente)
INSERT INTO `venta_detalle` (`id`, `venta`, `producto`, `variante`, `cantidad`, `precio_unitario`, `descuento`, `total`) VALUES
	(101, 101, 101, 101, 2.00, 28.00, 0.00, 56.00),
	(102, 102, 103, NULL, 2.00, 35.00, 5.00, 65.00),
	(103, 103, 106, NULL, 1.00, 55.00, 0.00, 55.00),
	(104, 104, 106, NULL, 2.00, 45.00, 0.00, 90.00),
	(108, 109, 104, NULL, 2.00, 22.00, 0.00, 44.00),
	(109, 110, 107, NULL, 1.00, 10.00, 0.00, 10.00),
	(110, 111, 107, NULL, 1.00, 10.00, 0.00, 10.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.ventas: ~8 rows (aproximadamente)
INSERT INTO `ventas` (`id`, `numero`, `fecha`, `persona`, `matricula`, `institucion`, `tipo`, `subtotal`, `descuento`, `impuesto`, `total`, `saldo`, `estado`) VALUES
	(101, 'DEMO-VTA-0001', '2026-05-12 11:00:00', 106, 101, 1, 'PRODUCTO', 56.00, 0.00, 0.00, 56.00, 0.00, 2),
	(102, 'DEMO-VTA-0002', '2026-05-13 12:30:00', 107, 102, 1, 'PRODUCTO', 70.00, 5.00, 0.00, 65.00, 65.00, 1),
	(103, 'DEMO-MAT-0001', '2026-05-01 09:20:00', 106, 101, 1, 'MATRICULA', 55.00, 0.00, 0.00, 55.00, 0.00, 2),
	(104, 'DEMO-MAT-0002', '2026-05-04 15:45:00', 109, 104, 1, 'MATRICULA', 90.00, 0.00, 0.00, 90.00, 45.00, 1),
	(105, 'FAC-20260517-000105', '2026-05-17 23:00:24', 109, 105, 1, 'MATRICULA', 125.00, 0.00, 0.00, 125.00, 125.00, 1),
	(109, 'VTA-20260518-6a0b3b5300256', '2026-05-18 18:16:19', 1, NULL, 1, 'VENTA', 44.00, 0.00, 0.00, 44.00, 44.00, 1),
	(110, 'VTA-20260518-6a0b3da28880c', '2026-05-18 18:26:10', 101, NULL, 1, 'VENTA', 10.00, 0.00, 0.00, 10.00, 0.00, 2),
	(111, 'VTA-20260518-6a0b3dac7bd96', '2026-05-18 18:26:20', 1, NULL, 1, 'VENTA', 10.00, 0.00, 0.00, 10.00, 10.00, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla cuspide.ventas_externas_detalle: ~0 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
