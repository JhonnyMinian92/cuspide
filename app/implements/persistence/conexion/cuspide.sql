-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 10-06-2026 a las 10:15:51
-- Versión del servidor: 10.11.16-MariaDB-cll-lve-log
-- Versión de PHP: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `operaci1_operacionadmin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `periodo_academico` int(11) NOT NULL,
  `categoria_deportiva` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `nivel` int(11) NOT NULL,
  `cupo_maximo` int(11) NOT NULL DEFAULT 0,
  `observacion` text DEFAULT NULL,
  `docente` int(11) NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades`
--

INSERT INTO `actividades` (`id`, `nombre`, `periodo_academico`, `categoria_deportiva`, `precio`, `nivel`, `cupo_maximo`, `observacion`, `docente`, `institucion`, `estado`) VALUES
(1, 'Futbol Sub 9', 1, 1, 25.00, 1, 24, '', 125, 1, 1),
(2, 'Futbol Sub 6', 1, 2, 25.00, 1, 20, '', 125, 1, 1),
(3, 'Futbol Sub 7', 1, 3, 25.00, 1, 20, '', 125, 1, 1),
(4, 'Futbol Sub 8', 1, 4, 25.00, 1, 20, '', 125, 1, 1),
(5, 'Futbol Sub 10', 1, 5, 25.00, 1, 20, '', 125, 1, 1),
(6, 'Futbol Sub 11', 1, 6, 25.00, 1, 20, '', 125, 1, 1),
(7, 'Futbol Sub 12', 1, 7, 25.00, 1, 20, '', 125, 1, 1),
(8, 'Futbol Sub 13', 1, 8, 25.00, 1, 20, '', 125, 1, 1),
(9, 'Futbol Sub 14', 1, 9, 25.00, 1, 20, '', 125, 1, 1),
(10, 'Sub 15', 9, 18, 25.00, 1, 20, '', 134, 10, 1),
(11, 'Sub18', 12, 18, 10.00, 102, 25, 'prueba mia', 135, 10, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades_matricula`
--

CREATE TABLE `actividades_matricula` (
  `id` int(11) NOT NULL,
  `actividad` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `modalidad` int(11) NOT NULL,
  `horario` int(11) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades_matricula`
--

INSERT INTO `actividades_matricula` (`id`, `actividad`, `matricula`, `modalidad`, `horario`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(1, 1, 1, 1, NULL, '2026-06-01 00:00:00', '2026-12-31 00:00:00', 1),
(2, 10, 4, 1, NULL, '2026-04-01 00:00:00', '2026-12-31 00:00:00', 1),
(3, 10, 5, 1, NULL, '2026-04-15 00:00:00', '2026-12-31 00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alergias`
--

CREATE TABLE `alergias` (
  `id` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `nivel` int(11) NOT NULL,
  `ficha_medica` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas`
--

CREATE TABLE `asignaturas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignaturas`
--

INSERT INTO `asignaturas` (`id`, `nombre`, `descripcion`, `institucion`, `estado`) VALUES
(1, 'Pintado de Uñas', 'Belleza Matutina', 9, 1),
(2, 'Manicure y Pedicure', 'Uñas Artificiale, Manicura y Pedicura Spa', 1, 1),
(3, 'Cosmetología y Estética Facial', 'Cuidado de la Piel, Maquillaje Profesional, Depilación', 1, 1),
(4, 'Cosmetologia y Estetica Facial/Corporal', 'Cuidado de la Piel, Maquillaje Profesional, Depilación', 9, 1),
(5, 'Cosmetología y Estética Facial', 'Cuidado de la Piel, Maquillaje Profesional, Depilación', 10, 1),
(6, 'Belleza Region Costa', 'Belleza', 9, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignatura_clase`
--

CREATE TABLE `asignatura_clase` (
  `id` int(11) NOT NULL,
  `asignatura` int(11) NOT NULL,
  `clase` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL,
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
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `botones`
--

CREATE TABLE `botones` (
  `id` int(11) NOT NULL,
  `orden` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `icono` varchar(150) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `botones`
--

INSERT INTO `botones` (`id`, `orden`, `nombre`, `icono`, `tipo`, `color`, `estado`) VALUES
(1, 1, 'Dashboard', 'bi-house-check-fill', 'MENU', NULL, 1),
(2, 10, 'Alumnos', 'bi-person-badge-fill', 'MENU', NULL, 1),
(3, 6, 'Membresías', 'bi-person-vcard-fill', 'MENU', '', 1),
(4, 12, 'Pagos', 'bi-cash-coin', 'MENU', '', 1),
(6, 15, 'Inventario', 'bi-box-seam-fill', 'MENU', '', 1),
(7, 17, 'Reportes', 'bi-bar-chart-fill', 'MENU', '', 1),
(8, 3, 'Colaboradores', 'bi-person-badge-fill', 'MENU', '', 1),
(9, 11, 'Matrículas', 'bi-journal-check', 'MENU', '', 1),
(10, 13, 'Gastos', 'bi-cash-stack', 'MENU', '', 1),
(11, 4, 'Contratos', 'bi-file-earmark-text', 'MENU', '', 1),
(12, 18, 'Configuración', 'bi-gear-fill', 'MENU', '', 1),
(13, 5, 'Usuarios', 'bi-people-fill', 'MENU', '', 1),
(14, 2, 'Instituciones', 'bi-building', 'MENU', '', 1),
(16, 8, 'Asignaturas', 'bi-book-fill', 'MENU', '', 1),
(17, 9, 'Actividades', 'bi-trophy-fill', 'MENU', '', 1),
(19, 16, 'Ventas', 'bi-cart-fill', 'MENU', '', 1),
(22, 14, 'Productos', 'bi-box-seam-fill', 'MENU', '', 1),
(24, 7, 'Socio', 'bi-person-badge-fill', 'MENU', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boton_user`
--

CREATE TABLE `boton_user` (
  `id` int(11) NOT NULL,
  `boton` int(11) NOT NULL,
  `type_user` varchar(100) NOT NULL,
  `institucion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `boton_user`
--

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
(18, 12, 'TIPO_DEV', 1),
(55, 1, 'TIPO_ADMIN', 1),
(56, 2, 'TIPO_ADMIN', 1),
(57, 24, 'TIPO_ADMIN', 1),
(58, 3, 'TIPO_ADMIN', 1),
(59, 4, 'TIPO_ADMIN', 1),
(60, 6, 'TIPO_ADMIN', 1),
(61, 8, 'TIPO_ADMIN', 1),
(62, 9, 'TIPO_ADMIN', 1),
(63, 10, 'TIPO_ADMIN', 1),
(64, 11, 'TIPO_ADMIN', 1),
(65, 13, 'TIPO_ADMIN', 1),
(66, 14, 'TIPO_ADMIN', 1),
(67, 16, 'TIPO_ADMIN', 1),
(68, 17, 'TIPO_ADMIN', 1),
(69, 19, 'TIPO_ADMIN', 1),
(70, 22, 'TIPO_ADMIN', 1),
(71, 7, 'TIPO_ADMIN', 1),
(72, 12, 'TIPO_ADMIN', 1),
(88, 1, 'TIPO_ADMIN', 9),
(89, 2, 'TIPO_ADMIN', 9),
(90, 3, 'TIPO_ADMIN', 9),
(91, 4, 'TIPO_ADMIN', 9),
(92, 6, 'TIPO_ADMIN', 9),
(93, 7, 'TIPO_ADMIN', 9),
(94, 8, 'TIPO_ADMIN', 9),
(95, 9, 'TIPO_ADMIN', 9),
(96, 10, 'TIPO_ADMIN', 9),
(97, 11, 'TIPO_ADMIN', 9),
(98, 12, 'TIPO_ADMIN', 9),
(99, 13, 'TIPO_ADMIN', 9),
(100, 14, 'TIPO_ADMIN', 9),
(101, 16, 'TIPO_ADMIN', 9),
(102, 17, 'TIPO_ADMIN', 9),
(103, 19, 'TIPO_ADMIN', 9),
(104, 22, 'TIPO_ADMIN', 9),
(105, 24, 'TIPO_ADMIN', 9),
(107, 1, 'TIPO_ADM_OPER', 1),
(108, 8, 'TIPO_ADM_OPER', 1),
(109, 11, 'TIPO_ADM_OPER', 1),
(110, 3, 'TIPO_ADM_OPER', 1),
(112, 2, 'TIPO_ADM_OPER', 1),
(113, 9, 'TIPO_ADM_OPER', 1),
(114, 4, 'TIPO_ADM_OPER', 1),
(115, 10, 'TIPO_ADM_OPER', 1),
(116, 22, 'TIPO_ADM_OPER', 1),
(117, 6, 'TIPO_ADM_OPER', 1),
(118, 19, 'TIPO_ADM_OPER', 1),
(119, 7, 'TIPO_ADM_OPER', 1),
(120, 12, 'TIPO_ADM_OPER', 1),
(121, 1, 'TIPO_ADM_OPER', 9),
(122, 8, 'TIPO_ADM_OPER', 9),
(123, 11, 'TIPO_ADM_OPER', 9),
(124, 3, 'TIPO_ADM_OPER', 9),
(125, 16, 'TIPO_ADM_OPER', 9),
(126, 2, 'TIPO_ADM_OPER', 9),
(127, 9, 'TIPO_ADM_OPER', 9),
(128, 4, 'TIPO_ADM_OPER', 9),
(129, 10, 'TIPO_ADM_OPER', 9),
(130, 22, 'TIPO_ADM_OPER', 9),
(131, 6, 'TIPO_ADM_OPER', 9),
(132, 19, 'TIPO_ADM_OPER', 9),
(133, 7, 'TIPO_ADM_OPER', 9),
(134, 12, 'TIPO_ADM_OPER', 9),
(135, 24, 'TIPO_ADM_OPER', 1),
(136, 17, 'TIPO_ADM_OPER', 1),
(137, 1, 'TIPO_ADMIN', 10),
(138, 2, 'TIPO_ADMIN', 10),
(139, 3, 'TIPO_ADMIN', 10),
(140, 4, 'TIPO_ADMIN', 10),
(141, 6, 'TIPO_ADMIN', 10),
(142, 7, 'TIPO_ADMIN', 10),
(143, 8, 'TIPO_ADMIN', 10),
(144, 9, 'TIPO_ADMIN', 10),
(145, 10, 'TIPO_ADMIN', 10),
(146, 11, 'TIPO_ADMIN', 10),
(147, 12, 'TIPO_ADMIN', 10),
(148, 13, 'TIPO_ADMIN', 10),
(149, 14, 'TIPO_ADMIN', 10),
(150, 16, 'TIPO_ADMIN', 10),
(151, 17, 'TIPO_ADMIN', 10),
(152, 19, 'TIPO_ADMIN', 10),
(153, 22, 'TIPO_ADMIN', 10),
(154, 24, 'TIPO_ADMIN', 10),
(157, 1, 'TIPO_ADM_OPER', 10),
(158, 8, 'TIPO_ADM_OPER', 10),
(160, 3, 'TIPO_ADM_OPER', 10),
(161, 16, 'TIPO_ADM_OPER', 10),
(162, 17, 'TIPO_ADM_OPER', 10),
(163, 2, 'TIPO_ADM_OPER', 10),
(164, 9, 'TIPO_ADM_OPER', 10),
(165, 4, 'TIPO_ADM_OPER', 10),
(166, 10, 'TIPO_ADM_OPER', 10),
(167, 22, 'TIPO_ADM_OPER', 10),
(169, 6, 'TIPO_ADM_OPER', 10),
(170, 19, 'TIPO_ADM_OPER', 10),
(171, 7, 'TIPO_ADM_OPER', 10),
(172, 12, 'TIPO_ADM_OPER', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendarios`
--

CREATE TABLE `calendarios` (
  `id` int(11) NOT NULL,
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
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `calendarios`
--

INSERT INTO `calendarios` (`id`, `titulo`, `tipo`, `institucion`, `horario`, `clase`, `actividad`, `plan_entrenamiento`, `entrenador`, `fecha_inicio`, `fecha_fin`, `ubicacion`, `cupo_maximo`, `observacion`, `estado`) VALUES
(1, 'Actividad: Fútbol Sub 9', 'ACTIVIDAD', 1, NULL, NULL, 1, NULL, NULL, '2026-06-01 00:00:00', '2026-12-31 00:00:00', '', 0, 'Matrícula #1', 1),
(2, 'Clase: Cosmetología Diurna', 'CLASE', 10, NULL, 4, NULL, NULL, NULL, '2026-05-01 00:00:00', '2026-07-31 00:00:00', '', 25, 'Matrícula #2', 1),
(3, 'Clase: Cosmetología Diurna', 'CLASE', 10, NULL, 4, NULL, NULL, NULL, '2026-05-01 00:00:00', '2026-12-31 00:00:00', '', 25, 'Matrícula #3', 1),
(4, 'Actividad: Sub 15', 'ACTIVIDAD', 10, NULL, NULL, 10, NULL, NULL, '2026-04-01 00:00:00', '2026-12-31 00:00:00', '', 0, 'Matrícula #4', 1),
(5, 'Actividad: Sub 15', 'ACTIVIDAD', 10, NULL, NULL, 10, NULL, NULL, '2026-04-15 00:00:00', '2026-12-31 00:00:00', '', 0, 'Matrícula #5', 1),
(6, 'Clase: Cosmetología Diurna', 'CLASE', 10, NULL, 4, NULL, NULL, NULL, '2026-03-01 00:00:00', '2026-12-31 00:00:00', '', 25, 'Matrícula #6', 1),
(7, 'Clase: Cosmetología Diurna', 'CLASE', 10, NULL, 4, NULL, NULL, NULL, '2026-05-01 00:00:00', '2027-05-01 00:00:00', '', 25, 'Matrícula #7', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

CREATE TABLE `calificaciones` (
  `id` int(11) NOT NULL,
  `evaluacion` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `nota` decimal(5,2) NOT NULL,
  `observacion` text DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_deportivas`
--

CREATE TABLE `categorias_deportivas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `deporte` int(11) NOT NULL,
  `edad_minima` int(11) DEFAULT NULL,
  `edad_maxima` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_deportivas`
--

INSERT INTO `categorias_deportivas` (`id`, `nombre`, `nemonico`, `deporte`, `edad_minima`, `edad_maxima`, `descripcion`, `institucion`, `estado`) VALUES
(1, 'Sub-9', 'S9', 1, 8, 9, 'Deporte', 1, 1),
(2, 'Sub-6', 'S6', 1, 4, 6, 'Sub 6', 1, 1),
(3, 'Sub-7', 'S7', 1, 5, 7, 'Sub 7', 1, 1),
(4, 'Sub-8', 'S8', 1, 7, 8, 'Sub 8', 1, 1),
(5, 'Sub-10', 'S10', 1, 9, 10, 'Sub 10', 1, 1),
(6, 'Sub-11', 'S11', 1, 10, 11, 'Sub 11', 1, 1),
(7, 'Sub-12', 'S12', 1, 11, 12, 'Sub 12', 1, 1),
(8, 'Sub-13', 'S13', 1, 12, 13, 'Sub 13', 1, 1),
(9, 'Sub-14', 'S14', 1, 13, 14, 'Sub 14', 1, 1),
(10, 'Sub-15', 'S15', 1, 14, 15, 'Sub 15', 1, 1),
(11, 'Sub-16', 'S16', 1, 15, 16, 'Sub 16', 1, 1),
(12, 'Sub-17', 'S17', 1, 16, 17, 'Sub 17', 1, 1),
(13, 'Sub-18', 'S18', 1, 17, 18, 'Sub 18', 1, 1),
(18, 'Sub-15', 'Prueba15', 1, 14, 15, 'deporte', 10, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudades`
--

CREATE TABLE `ciudades` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `provincia` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ciudades`
--

INSERT INTO `ciudades` (`id`, `nemonico`, `nombre`, `provincia`) VALUES
(1, 'MACH', 'MACHALA', 'EL ORO'),
(101, 'HUAQ', 'HUAQUILLAS', 'EL ORO'),
(102, 'STRO', 'SANTA ROSA', 'EL ORO'),
(103, 'GYE', 'GUAYAQUIL', 'GUAYAS'),
(104, 'UIO', 'QUITO', 'PICHINCHA'),
(105, 'AREN', 'ARENILLAS', 'EL ORO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clases`
--

CREATE TABLE `clases` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `periodo_academico` int(11) DEFAULT NULL,
  `asignatura` int(11) DEFAULT NULL,
  `precio` double DEFAULT NULL,
  `nivel` int(11) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `docente` int(11) DEFAULT NULL,
  `cupo_maximo` int(11) NOT NULL DEFAULT 0,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clases`
--

INSERT INTO `clases` (`id`, `nombre`, `periodo_academico`, `asignatura`, `precio`, `nivel`, `observacion`, `institucion`, `docente`, `cupo_maximo`, `estado`) VALUES
(1, 'Cosmetología', 5, 3, 50, 1, NULL, 1, NULL, 20, 0),
(2, 'Cosmetología', 5, 3, 50, 1, NULL, 1, NULL, 20, 0),
(3, 'Cosmetología Diurna', 5, 4, 50, 1, NULL, 9, 131, 25, 1),
(4, 'Cosmetología Diurna', 9, 5, 50, 1, NULL, 10, 135, 25, 1),
(5, 'Belleza Region Costa Paraleo B', 9, 6, 165, 101, NULL, 9, 131, 25, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clases_matricula`
--

CREATE TABLE `clases_matricula` (
  `id` int(11) NOT NULL,
  `clase` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `contrato` int(11) DEFAULT NULL,
  `modalidad` int(11) NOT NULL,
  `horario` int(11) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clases_matricula`
--

INSERT INTO `clases_matricula` (`id`, `clase`, `matricula`, `contrato`, `modalidad`, `horario`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(1, 4, 2, NULL, 1, NULL, '2026-05-01 00:00:00', '2026-07-31 00:00:00', 2),
(2, 4, 3, NULL, 1, NULL, '2026-05-01 00:00:00', '2026-12-31 00:00:00', 1),
(3, 4, 6, NULL, 1, NULL, '2026-03-01 00:00:00', '2026-12-31 00:00:00', 1),
(4, 4, 7, NULL, 1, NULL, '2026-05-01 00:00:00', '2027-05-01 00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratacion`
--

CREATE TABLE `contratacion` (
  `id` int(11) NOT NULL,
  `fecha_inscripcion` datetime NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `nivel` int(11) NOT NULL,
  `institucion` int(11) NOT NULL,
  `persona` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correos`
--

CREATE TABLE `correos` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tipo` int(11) NOT NULL,
  `persona` int(11) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `correos`
--

INSERT INTO `correos` (`id`, `email`, `tipo`, `persona`, `estado`) VALUES
(1, 'jhinson.moreno@gmail.com', 1, 121, 1),
(2, 'allisonviera22@gmail.com', 1, 122, 2),
(3, 'allisonviera22@gmail.com', 1, 122, 1),
(4, 'comercialcorreacc@hotmail.com', 1, 124, 2),
(5, 'comercialcorreacc@hotmail.com', 1, 124, 1),
(6, 'albanariasj@gmail.com', 1, 126, 1),
(7, 'lcalva1991@gmail.com', 1, 127, 1),
(8, 'isabelsunari94@gmail.com', 1, 128, 2),
(9, 'messi@gmail.com', 1, 134, 1),
(10, 'carolinaherrera@gmail.com', 1, 135, 1),
(11, 'pardomelissa92@gmail.com', 1, 141, 1),
(12, 'babaludiquito@gmail.com', 1, 143, 1),
(13, 'isabelsunari94@gmail.com', 1, 144, 1),
(14, 'silvanaelizabeth17@hotmail.com', 1, 128, 1),
(15, 'tortovanessa24@gmail.com', 1, 146, 1),
(16, 'juanmaria2517@hotmail.com', 1, 149, 1),
(17, 'nayelyapontecis@gmail.com', 1, 150, 1),
(18, 'cabadayuliana6@gmail.com', 1, 152, 1),
(19, 'antoniocuenta16.8@gmail.com', 1, 153, 1),
(20, 'kathalexx96@gmail.com', 1, 154, 1),
(21, 'xraponte@hotmail.com', 1, 156, 1),
(22, 'jonathan.cuero@itsf.edu.ec', 1, 157, 1),
(23, 'dianamendoza232420@gmail.com', 1, 158, 1),
(24, 'nenita_tatys@hotmail.com', 1, 139, 1),
(25, 'elizabeth201996@hotmail.com', 1, 159, 1),
(26, 'danita_janner@hotmail.com', 1, 160, 1),
(27, 'cguaychaa@unemi.edu.ec', 1, 161, 1),
(28, 'edtorresolivo61@gmail.com', 1, 163, 1),
(29, 'manuelcamacho2305@hotmail.com', 1, 164, 1),
(30, 'manuelcamacho2305@hotmail.com', 1, 165, 1),
(31, 'alissysebas6@gmail.com', 1, 166, 1),
(32, 'yaribel_arias@hotmail.com', 1, 167, 1),
(33, 'marialviarez1995@gmail.com', 1, 170, 1),
(34, 'soniazapata@gmail.com', 1, 171, 1),
(35, 'alexpardo771@gmail.com', 1, 172, 1),
(36, 'emmakatherinedelacruzarmijos@gmail.com', 1, 174, 1),
(37, 'josselynlimaa@gmail.com', 1, 175, 1),
(38, 'emmakatherinedelacruzarmijos@gmail.com', 1, 178, 1),
(39, 'richardkun814@gmail.com', 1, 180, 1),
(40, 'karen_margot@hotmail.com', 1, 181, 1),
(41, 'novedadesmafer1974@hotmail.com', 1, 182, 1),
(42, 'alexasdrubal18@outlook.com', 1, 183, 1),
(43, 'mercyeras9@gmail.com', 1, 184, 1),
(44, 'axelb7436@gmail.com', 1, 184, 1),
(45, 'egarbisbernales@gmail.com', 1, 185, 1),
(46, 'vivancofanny59@gmail.com', 1, 186, 1),
(47, 'samuiphone2024@gmail.com', 1, 193, 1),
(48, 'candelaavila39@gmail.com', 1, 194, 1),
(49, 'joralejohn_13@hotmail.com', 1, 197, 1),
(50, 'gina08528@gmail.com', 1, 198, 1),
(51, 'tatianaeras92@gmail.com', 1, 199, 1),
(52, 'espindxn1973@gmail.com', 1, 202, 2),
(53, 'espindxn1973@gmail.com', 1, 202, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuotas_matricula`
--

CREATE TABLE `cuotas_matricula` (
  `id` int(11) NOT NULL,
  `plan_pago` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `valor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuotas_matricula`
--

INSERT INTO `cuotas_matricula` (`id`, `plan_pago`, `numero`, `fecha_vencimiento`, `valor`, `saldo`, `estado`) VALUES
(1, 1, 1, '2026-07-01', 20.00, 20.00, 1),
(2, 1, 2, '2026-08-01', 20.00, 20.00, 1),
(3, 1, 3, '2026-09-01', 20.00, 20.00, 1),
(4, 1, 4, '2026-10-01', 20.00, 20.00, 1),
(5, 1, 5, '2026-11-01', 20.00, 20.00, 1),
(6, 1, 6, '2026-12-01', 20.00, 20.00, 1),
(7, 1, 7, '2027-01-01', 20.00, 20.00, 1),
(8, 2, 1, '2027-05-01', 50.00, 0.00, 2),
(9, 3, 1, '2026-06-01', 50.00, 0.00, 1),
(10, 3, 2, '2026-07-01', 50.00, 0.00, 1),
(11, 3, 3, '2026-08-01', 50.00, 50.00, 1),
(12, 3, 4, '2026-09-01', 50.00, 50.00, 1),
(13, 3, 5, '2026-10-01', 50.00, 50.00, 1),
(14, 3, 6, '2026-11-01', 50.00, 50.00, 1),
(15, 3, 7, '2026-12-01', 50.00, 50.00, 1),
(16, 3, 8, '2027-01-01', 50.00, 50.00, 1),
(17, 4, 1, '2026-05-01', 25.00, 0.00, 1),
(18, 4, 2, '2026-06-01', 25.00, 25.00, 1),
(19, 4, 3, '2026-07-01', 25.00, 25.00, 1),
(20, 4, 4, '2026-08-01', 25.00, 25.00, 1),
(21, 4, 5, '2026-09-01', 25.00, 25.00, 1),
(22, 4, 6, '2026-10-01', 25.00, 25.00, 1),
(23, 4, 7, '2026-11-01', 25.00, 25.00, 1),
(24, 4, 8, '2026-12-01', 25.00, 25.00, 1),
(25, 4, 9, '2027-01-01', 25.00, 25.00, 1),
(26, 5, 1, '2026-05-15', 20.00, 0.00, 1),
(27, 5, 2, '2026-06-15', 20.00, 20.00, 1),
(28, 5, 3, '2026-07-15', 20.00, 20.00, 1),
(29, 5, 4, '2026-08-15', 20.00, 20.00, 1),
(30, 5, 5, '2026-09-15', 20.00, 20.00, 1),
(31, 5, 6, '2026-10-15', 20.00, 20.00, 1),
(32, 5, 7, '2026-11-15', 20.00, 20.00, 1),
(33, 5, 8, '2026-12-15', 20.00, 20.00, 1),
(34, 5, 9, '2027-01-15', 20.00, 20.00, 1),
(35, 6, 1, '2026-04-01', 45.00, 0.00, 1),
(36, 6, 2, '2026-05-01', 45.00, 0.00, 1),
(37, 6, 3, '2026-06-01', 45.00, 45.00, 1),
(38, 6, 4, '2026-07-01', 45.00, 45.00, 1),
(39, 6, 5, '2026-08-01', 45.00, 45.00, 1),
(40, 6, 6, '2026-09-01', 45.00, 45.00, 1),
(41, 6, 7, '2026-10-01', 45.00, 45.00, 1),
(42, 6, 8, '2026-11-01', 45.00, 45.00, 1),
(43, 6, 9, '2026-12-01', 45.00, 45.00, 1),
(44, 6, 10, '2027-01-01', 45.00, 45.00, 1),
(45, 7, 1, '2026-06-01', 50.00, 0.00, 1),
(46, 7, 2, '2026-07-01', 50.00, 50.00, 1),
(47, 7, 3, '2026-08-01', 50.00, 50.00, 1),
(48, 7, 4, '2026-09-01', 50.00, 50.00, 1),
(49, 7, 5, '2026-10-01', 50.00, 50.00, 1),
(50, 7, 6, '2026-11-01', 50.00, 50.00, 1),
(51, 7, 7, '2026-12-01', 50.00, 50.00, 1),
(52, 7, 8, '2027-01-01', 50.00, 50.00, 1),
(53, 7, 9, '2027-02-01', 50.00, 50.00, 1),
(54, 7, 10, '2027-03-01', 50.00, 50.00, 1),
(55, 7, 11, '2027-04-01', 50.00, 50.00, 1),
(56, 7, 12, '2027-05-01', 50.00, 50.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deportes`
--

CREATE TABLE `deportes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deportes`
--

INSERT INTO `deportes` (`id`, `nombre`, `descripcion`, `institucion`, `estado`) VALUES
(1, 'Futbol', 'Deporte', 10, 1),
(2, 'Belleza', 'belleza', 9, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enfermedades`
--

CREATE TABLE `enfermedades` (
  `id` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `nemonico` varchar(150) NOT NULL,
  `nivel` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enfermedad_persona`
--

CREATE TABLE `enfermedad_persona` (
  `id` int(11) NOT NULL,
  `enfermedad` int(11) NOT NULL,
  `medicacion` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados`
--

INSERT INTO `estados` (`id`, `nombre`) VALUES
(0, 'INACTIVO'),
(1, 'ACTIVO'),
(2, 'SUSPENDIDO'),
(3, 'PENDIENTE'),
(4, 'ANULADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_fisico`
--

CREATE TABLE `estado_fisico` (
  `id` int(11) NOT NULL,
  `perfil_medico` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_permiso`
--

CREATE TABLE `estado_permiso` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_permiso`
--

INSERT INTO `estado_permiso` (`id`, `nemonico`, `nombre`) VALUES
(0, 'CANCELADO', 'PERMISO CANCELADO'),
(1, 'PENDIENTE', 'PENDIENTE DE APROBACIÓN'),
(2, 'APROBADO', 'PERMISO APROBADO'),
(3, 'RECHAZADO', 'PERMISO RECHAZADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etnias`
--

CREATE TABLE `etnias` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `etnias`
--

INSERT INTO `etnias` (`id`, `nemonico`, `nombre`) VALUES
(1, 'MEZT', 'MESTIZO'),
(101, 'AFRO', 'AFROECUATORIANO'),
(102, 'MONT', 'MONTUBIO'),
(103, 'IND', 'INDIGENA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluaciones`
--

CREATE TABLE `evaluaciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `clase` int(11) DEFAULT NULL,
  `actividad` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `peso_porcentaje` decimal(5,2) NOT NULL DEFAULT 0.00,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fichas_medicas`
--

CREATE TABLE `fichas_medicas` (
  `id` int(11) NOT NULL,
  `persona` int(11) DEFAULT NULL,
  `grupo_sanguineo` varchar(10) DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `enfermedades_preexistentes` text DEFAULT NULL,
  `medicamentos_actuales` text DEFAULT NULL,
  `contacto_emergencia_nombre` varchar(150) DEFAULT NULL,
  `contacto_emergencia_telefono` varchar(20) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `perfil_medico` int(11) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fichas_medicas`
--

INSERT INTO `fichas_medicas` (`id`, `persona`, `grupo_sanguineo`, `alergias`, `enfermedades_preexistentes`, `medicamentos_actuales`, `contacto_emergencia_nombre`, `contacto_emergencia_telefono`, `fecha`, `perfil_medico`, `estado`) VALUES
(1, 121, NULL, '', '', '', '', '', '2026-06-05 17:18:59', NULL, 1),
(2, 122, NULL, '', '', '', '', '', '2026-06-05 21:03:44', NULL, 1),
(3, 124, 'O+', NULL, NULL, NULL, 'Yoder Correa', '0991003021', '2026-06-05 21:25:58', NULL, 1),
(4, 125, NULL, '', '', '', '', '', '2026-06-05 21:32:28', NULL, 1),
(5, 130, NULL, '', '', '', '', '', '2026-06-06 00:34:57', NULL, 1),
(6, 131, NULL, '', '', '', '', '', '2026-06-06 01:12:30', NULL, 1),
(7, 133, NULL, '', '', '', '', '', '2026-06-07 21:55:58', NULL, 1),
(8, 134, NULL, '', '', '', '', '', '2026-06-07 22:01:25', NULL, 1),
(9, 135, NULL, '', '', '', '', '', '2026-06-07 22:03:22', NULL, 1),
(10, 157, NULL, '', '', '', '', '', '2026-06-08 17:28:25', NULL, 1),
(11, 189, NULL, '', '', '', '', '', '2026-06-09 17:11:25', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formas_pago`
--

CREATE TABLE `formas_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `formas_pago`
--

INSERT INTO `formas_pago` (`id`, `nombre`, `nemonico`, `descripcion`, `estado`) VALUES
(101, 'Efectivo', 'EFECTIVO_DEMO', 'Pago en caja', 1),
(102, 'Transferencia', 'TRANSFERENCIA_DEMO', 'Transferencia bancaria', 1),
(103, 'Tarjeta', 'TARJETA_DEMO', 'Pago con tarjeta', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `tipo_gasto` varchar(80) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `institucion` int(11) NOT NULL,
  `persona` int(11) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id`, `numero`, `fecha`, `descripcion`, `tipo_gasto`, `monto`, `institucion`, `persona`, `referencia`, `observacion`, `estado`) VALUES
(1, 'G-20260607235620', '2026-06-05 00:00:00', 'Abono de Docente Carolina Herrera', 'NOMINA', 100.00, 10, 133, '8792', 'Abono de sueldo del mes de Junio', 1),
(2, 'G-20260609113557', '2026-06-09 00:00:00', 'Pago de Honorarios de mayo', 'NOMINA', 470.00, 10, 133, '12548', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `generos`
--

CREATE TABLE `generos` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `generos`
--

INSERT INTO `generos` (`id`, `nemonico`, `nombre`) VALUES
(1, 'TIPO_M', 'MASCULINO'),
(2, 'TIPO_F', 'FEMENINO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
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
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencia`
--

CREATE TABLE `incidencia` (
  `id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `persona` int(11) NOT NULL,
  `nivel` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `id` int(11) NOT NULL,
  `ruc` varchar(13) DEFAULT NULL,
  `razon_social` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `logotipo` varchar(255) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instituciones`
--

INSERT INTO `instituciones` (`id`, `ruc`, `razon_social`, `nombre`, `logotipo`, `estado`) VALUES
(1, '0791842247001', 'Huaquillas-sporting-club S.a.s.', 'Huaquillas Futbol Club', '/assets/logo/huaquillasfc.jpg', 1),
(9, '0704486018001', 'CORDOVA VARGAS ASTRID YISELA', 'CENTRO DE FORMACION ARTESANAL MAGDALENA', '/assets/logo/inst_6a28367e8014a8.87890129.jpg', 1),
(10, '9999999999999', 'Prueba', 'Prueba de Gestion', '/assets/logo/inst_6a262089010295.69616644.jpg', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `variante` int(11) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `stock_actual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_minimo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id`, `producto`, `variante`, `institucion`, `stock_actual`, `stock_minimo`, `fecha_actualizacion`) VALUES
(1, 1, NULL, 10, 24.00, 0.00, '2026-06-07 23:35:49'),
(2, 2, NULL, 10, 24.00, 0.00, '2026-06-07 23:35:49'),
(3, 3, NULL, 9, 49.00, 0.00, '2026-06-09 11:41:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matricula`
--

CREATE TABLE `matricula` (
  `id` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `persona` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `membresia` int(11) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `matricula`
--

INSERT INTO `matricula` (`id`, `fecha_registro`, `persona`, `fecha_inicio`, `fecha_fin`, `membresia`, `institucion`, `estado`) VALUES
(1, '2026-06-05 21:36:26', 124, '2026-06-01 00:00:00', '2026-12-31 00:00:00', 4, 1, 1),
(2, '2026-06-07 22:21:11', 136, '2026-05-01 00:00:00', '2026-07-31 00:00:00', NULL, 10, 2),
(3, '2026-06-07 22:26:14', 136, '2026-05-01 00:00:00', '2026-12-31 00:00:00', NULL, 10, 1),
(4, '2026-06-07 23:01:12', 137, '2026-04-01 00:00:00', '2026-12-31 00:00:00', NULL, 10, 1),
(5, '2026-06-08 22:04:29', 168, '2026-04-15 00:00:00', '2026-12-31 00:00:00', 7, 10, 1),
(6, '2026-06-08 22:25:47', 169, '2026-03-01 00:00:00', '2026-12-31 00:00:00', 6, 10, 1),
(7, '2026-06-09 11:31:28', 173, '2026-05-01 00:00:00', '2027-05-01 00:00:00', NULL, 10, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membresias`
--

CREATE TABLE `membresias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `precio` varchar(20) NOT NULL,
  `porcentaje` double NOT NULL,
  `periodo` varchar(80) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `url_boton` varchar(255) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` int(11) NOT NULL,
  `institucion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `membresias`
--

INSERT INTO `membresias` (`id`, `titulo`, `precio`, `porcentaje`, `periodo`, `descripcion`, `url_boton`, `fecha_inicio`, `fecha_fin`, `estado`, `institucion`) VALUES
(1, 'Socio Gold', '100.00', 0, 'mensual', '', '', '2026-06-05 00:00:00', '2026-12-31 00:00:00', 1, 1),
(2, 'Socio Platinum', '30.00', 0, 'mensual', '', '', '2026-06-05 00:00:00', '2026-12-31 00:00:00', 1, 1),
(3, 'Socio Honorarios', '0.00', 0, 'mensual', '', '', '2026-06-05 00:00:00', '2026-12-31 00:00:00', 1, 1),
(4, 'Mensulidad Socio', '25.00', 20, 'mensual', '', '', '2026-06-05 00:00:00', '2026-12-31 00:00:00', 1, 1),
(5, 'Promoción Julio', '50.00', 10, 'mensual', '', '', '2026-06-07 00:00:00', '2026-07-20 00:00:00', 1, 9),
(6, 'Promoción Mensualidad Hasta 10 Julio', '50.00', 10, 'mensual', 'Promoción de matricula hasta el 10 de junio', '', '2026-06-07 00:00:00', '2026-12-31 00:00:00', 1, 10),
(7, 'Mensualidad Socio', '25.00', 20, 'mensual', '', '', '2026-06-07 00:00:00', '2026-12-31 00:00:00', 1, 10),
(8, 'Matricula Promoción', '40.00', 20, 'anual', 'Promoción por matricula hasta el 10 de Julio', '', '2026-06-07 00:00:00', '2026-12-31 00:00:00', 1, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modalidades`
--

CREATE TABLE `modalidades` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modalidades`
--

INSERT INTO `modalidades` (`id`, `nemonico`, `nombre`) VALUES
(1, 'TIPO_PRESENCIAL', 'PRESENCIAL'),
(101, 'TIPO_VIRTUAL', 'VIRTUAL'),
(102, 'TIPO_HIBRIDA', 'HIBRIDA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `variante` int(11) DEFAULT NULL,
  `institucion` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `cantidad` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_anterior` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock_nuevo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `referencia` varchar(100) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `producto`, `variante`, `institucion`, `tipo`, `cantidad`, `stock_anterior`, `stock_nuevo`, `referencia`, `fecha`, `observacion`) VALUES
(1, 1, NULL, 10, 'ENTRADA', 25.00, 0.00, 25.00, 'Cupos', '2026-06-07 22:35:50', ''),
(2, 2, NULL, 10, 'ENTRADA', 25.00, 0.00, 25.00, 'Compra', '2026-06-07 22:42:44', 'entregaron el 30 mayo'),
(3, 1, NULL, 10, 'SALIDA', 1.00, 25.00, 24.00, 'VTA-20260607-6a263895dceab', '2026-06-07 23:35:49', 'Venta VTA-20260607-6a263895dceab'),
(4, 2, NULL, 10, 'SALIDA', 1.00, 25.00, 24.00, 'VTA-20260607-6a263895dceab', '2026-06-07 23:35:49', 'Venta VTA-20260607-6a263895dceab'),
(5, 3, NULL, 9, 'ENTRADA', 50.00, 0.00, 50.00, 'Matricula', '2026-06-09 11:40:40', 'Cupos'),
(6, 3, NULL, 9, 'SALIDA', 1.00, 50.00, 49.00, 'VTA-20260609-6a283431cec76', '2026-06-09 11:41:37', 'Venta VTA-20260609-6a283431cec76');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nacionalidades`
--

CREATE TABLE `nacionalidades` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nacionalidades`
--

INSERT INTO `nacionalidades` (`id`, `nemonico`, `nombre`) VALUES
(1, 'EC', 'ECUATORIANA'),
(101, 'CO', 'COLOMBIANA'),
(102, 'PE', 'PERUANA'),
(103, 'VE', 'VENEZOLANA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivel`
--

CREATE TABLE `nivel` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(150) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nivel`
--

INSERT INTO `nivel` (`id`, `nemonico`, `nombre`) VALUES
(1, 'LEVE', 'SUTIL'),
(2, 'MEDIO', 'FUERTE'),
(3, 'GRAVE', 'CRITICO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivel_academico`
--

CREATE TABLE `nivel_academico` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nivel_academico`
--

INSERT INTO `nivel_academico` (`id`, `nemonico`, `nombre`) VALUES
(1, 'BASICO', 'PRIMARIA'),
(2, 'MEDIO', 'SECUNDARIA'),
(3, 'ALTO', 'TERCER NIVEL'),
(4, 'ULTRA', 'CUARTO NIVEL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivel_asignatura`
--

CREATE TABLE `nivel_asignatura` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nivel_asignatura`
--

INSERT INTO `nivel_asignatura` (`id`, `nemonico`, `nombre`) VALUES
(1, 'NIVEL_BASICO', 'BASICO'),
(101, 'NIVEL_INTERMEDIO', 'INTERMEDIO'),
(102, 'NIVEL_AVANZADO', 'AVANZADO'),
(103, 'NIVEL_COMPETITIVO', 'COMPETITIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `operadoras`
--

CREATE TABLE `operadoras` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `operadoras`
--

INSERT INTO `operadoras` (`id`, `nemonico`, `nombre`) VALUES
(1, 'OPE_CLARO', 'CLARO'),
(101, 'OPE_MOVISTAR', 'MOVISTAR'),
(102, 'OPE_CNT', 'CNT'),
(103, 'OPE_TUENTI', 'TUENTI');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
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
  `motivo_anulacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `numero`, `fecha`, `persona`, `matricula`, `venta`, `forma_pago`, `institucion`, `monto`, `referencia`, `observacion`, `estado`, `cobrado_por`, `motivo_anulacion`) VALUES
(1, 'PAG-20260607-6a2630f36da1a', '2026-06-07 23:03:15', 136, NULL, 24, 101, 10, 50.00, '45698', 'Pago Mayo', 1, NULL, NULL),
(2, 'PAG-20260607-6a26348964d9c', '2026-06-07 23:18:33', 137, NULL, 25, 101, 10, 25.00, '15648', 'Pago de Mayo', 4, NULL, 'Se ha anulado el pago debido a un error en el registro de la transacción.'),
(3, 'PAG-20260607-6a263526ad285', '2026-06-07 23:21:10', 137, NULL, 25, 101, 10, 25.00, '24667', 'Pago del mes de Abril', 1, NULL, NULL),
(4, 'PAG-20260607-6a263895df433', '2026-06-07 23:35:49', 136, NULL, 26, 101, 10, 95.00, '45698', 'Pago de venta VTA-20260607-6a263895dceab', 1, NULL, NULL),
(5, 'PAG-20260608-6a277313f15c8', '2026-06-08 21:57:39', 136, NULL, 24, 101, 10, 50.00, 'Pago mes de Junio', '', 1, NULL, NULL),
(6, 'PAG-20260608-6a277587dd77a', '2026-06-08 22:08:07', 168, NULL, 27, 101, 10, 20.00, 'Pago de abril', 'futbol', 1, NULL, NULL),
(7, 'PAG-20260608-6a277c4267c2c', '2026-06-08 22:36:50', 169, NULL, 28, 102, 10, 45.00, '4587', 'Pago de marzo', 1, NULL, NULL),
(8, 'PAG-20260609-6a27933258210', '2026-06-09 00:14:42', 169, NULL, 28, 101, 10, 45.00, '75486', 'Pago Abril', 1, NULL, NULL),
(9, 'PAG-20260609-6a2832032d6a3', '2026-06-09 11:32:19', 173, NULL, 29, 102, 10, 50.00, '125468', 'Pago del mes de Mayo', 1, NULL, NULL),
(10, 'PAG-20260609-6a2834e1cae20', '2026-06-09 11:44:33', 173, NULL, 30, 101, 10, 185.00, '15648', 'pago de matricula', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_detalle`
--

CREATE TABLE `pago_detalle` (
  `id` int(11) NOT NULL,
  `pago` int(11) NOT NULL,
  `cuota_matricula` int(11) DEFAULT NULL,
  `venta` int(11) DEFAULT NULL,
  `valor` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pago_detalle`
--

INSERT INTO `pago_detalle` (`id`, `pago`, `cuota_matricula`, `venta`, `valor`) VALUES
(1, 1, NULL, 24, 50.00),
(2, 2, NULL, 25, 25.00),
(3, 3, NULL, 25, 25.00),
(4, 5, NULL, 24, 50.00),
(5, 6, NULL, 27, 20.00),
(6, 7, NULL, 28, 45.00),
(7, 8, NULL, 28, 45.00),
(8, 9, NULL, 29, 50.00),
(9, 10, NULL, 30, 185.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_medico`
--

CREATE TABLE `perfil_medico` (
  `id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `persona` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodicidad_pago`
--

CREATE TABLE `periodicidad_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `meses` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `periodicidad_pago`
--

INSERT INTO `periodicidad_pago` (`id`, `nombre`, `nemonico`, `meses`, `estado`) VALUES
(101, 'Mensual', 'MENSUAL_DEMO', 1, 1),
(102, 'Trimestral', 'TRIMESTRAL_DEMO', 3, 1),
(103, 'Anual', 'ANUAL_DEMO', 12, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_academicos`
--

CREATE TABLE `periodos_academicos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `periodos_academicos`
--

INSERT INTO `periodos_academicos` (`id`, `nombre`, `fecha_inicio`, `fecha_fin`, `institucion`, `estado`) VALUES
(1, 'Periodo Lectivo 2026', '2026-05-28', '2026-05-28', 1, 1),
(2, 'Periodo Lectivo 2027', '2027-01-01', '2027-12-31', 1, 1),
(3, 'Periodo Lectivo 2028', '2028-01-01', '2028-12-31', 1, 1),
(4, 'Periodo Lectivo 2029', '2029-01-01', '2029-12-31', 1, 1),
(5, 'Periodo Lectivo 2026', '2026-05-28', '2026-05-28', 9, 1),
(6, 'Periodo Lectivo 2027', '2027-01-01', '2027-12-31', 9, 1),
(7, 'Periodo Lectivo 2028', '2028-01-01', '2028-12-31', 9, 1),
(8, 'Periodo Lectivo 2029', '2029-01-01', '2029-12-31', 9, 1),
(9, 'Periodo Lectivo 2026', '2026-01-01', '2026-12-31', 10, 1),
(10, 'Periodo Lectivo 2027', '2027-01-01', '2027-12-31', 10, 1),
(11, 'Periodo Lectivo 2028', '2028-01-01', '2028-12-31', 10, 1),
(12, 'Periodo Lectivo 2029', '2029-01-01', '2029-12-31', 10, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
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
  `descripcion` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` int(11) NOT NULL,
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
  `user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `identificacion`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `nacionalidad`, `genero`, `etnia`, `fecha_nacimiento`, `ciudad`, `direccion`, `user`) VALUES
(1, '0706323193', 'Jhonny', 'Darwin', 'Miñan', 'Girón', 1, 1, 1, '1992-03-02 00:00:00', 1, '25 de Junio y Callejon Babahoyo', 1),
(121, '0701111111', 'Jhinson', 'Gabriel', 'Moreno', 'Ochoa', 1, 1, 1, '1992-06-01 00:00:00', 101, 'Avenida la republica', 113),
(122, '0706187051', 'Allison', 'Claribel', 'Viera', 'Seminario', 1, 2, 1, '2003-04-22 00:00:00', 101, 'Machala y Corina Parrales', 114),
(123, '0704624550', 'Yoder', 'Fernando', 'Correa', 'Llanos', 1, 1, 1, '1991-05-18 00:00:00', 101, 'Arenillas y Tnte Cordovez', NULL),
(124, '0751556051', 'Yoder', 'Raul', 'Correa', 'Granda', 1, 1, 1, '2017-11-15 00:00:00', 101, 'Arenillas y Tnte Cordovez', NULL),
(125, '0705570158', 'BRYAN', 'DANIEL', 'SALAZAR', 'PELAEZ', 1, 1, 1, '1990-01-01 00:00:00', 101, 'Huaquillas', NULL),
(126, '0704335322', 'Jose', 'Antonio', 'Alban', 'Arias', 1, 1, 1, '1990-03-20 00:00:00', 101, 'Costa Rica y Machala', NULL),
(127, '0705961746', 'Lester', 'Hernán', 'Calva', 'Calva', 1, 1, 1, '1991-10-02 00:00:00', 101, 'Bolivar y Huancavilca', NULL),
(128, '0751618208', 'Elian', 'Alejandro', 'Farfan', 'Vinces', 1, 1, 1, '2018-05-05 00:00:00', 101, 'Huaquillas', NULL),
(129, '0704486018', 'Administrador', NULL, 'CENTRO DE FORMACION ARTESANAL MAGDALENA', NULL, 1, 1, 1, '2026-06-06 00:00:00', 1, 'N/A', 115),
(130, '0705404390', 'Mayra', 'Alejandra', 'Labanda', 'Ramirez', 1, 2, 1, '1989-09-18 00:00:00', 101, 'Huaquillas', 116),
(131, '0702381138', 'Mercy', '', 'Narvaez', '', 1, 2, 1, '1988-06-20 00:00:00', 101, 'Huaquillas', NULL),
(132, '9999999999', 'Administrador', NULL, 'Prueba de Gestion', NULL, 1, 1, 1, '2026-06-07 00:00:00', 1, 'N/A', 117),
(133, '999999999', 'Juan', '', 'Perez', '', 1, 1, 1, '2000-06-30 00:00:00', 101, 'Huaquillas', 118),
(134, '8888888888', 'Leonel', '', 'Messi', '', 1, 1, 1, '1989-06-20 00:00:00', 101, 'Huaquillas', NULL),
(135, '77777777', 'Carolina', '', 'Herrera', '', 103, 2, 1, '1980-06-28 00:00:00', 101, 'Huaquillas', NULL),
(136, '1804555306', 'Gabriela', '', 'Ortiz', '', 1, 2, 1, '1991-06-14 00:00:00', 101, 'Hualtaco', NULL),
(137, '070666666', 'Luis', '', 'Diaz', '', 101, 1, 1, '2012-06-29 00:00:00', 101, 'Huaquillas', NULL),
(138, '0751665639', 'Santiago', 'Gael', 'Ayala', 'Becerra', 1, 1, 1, '2018-09-03 00:00:00', 101, 'Huaquillas', NULL),
(139, '0751675588', 'Richard', 'Jared', 'Gracia', 'Rueda', 1, 1, 1, '2018-09-30 00:00:00', 101, 'Huaquillas', NULL),
(140, '0751932187', 'Yeison', 'Noe', 'Robles', 'Flores', 1, 1, 1, '2020-04-04 00:00:00', 101, 'Huaquillas', NULL),
(141, '0751608118', 'Santiago', 'Jonas', 'Rojas', 'Pardo', 1, 1, 1, '2018-04-09 00:00:00', 101, 'Huaquillas', NULL),
(142, '0751634577', 'Iker', 'Bismark', 'Aguilar', 'Pardo', 1, 1, 1, '2018-06-15 00:00:00', 101, 'Huaquillas', NULL),
(143, '0751635020', 'Geovanny', 'Mathias', 'Agurto', 'Bustamante', 1, 1, 1, '2018-06-15 00:00:00', 1, 'Huaquillas', NULL),
(144, '0751587965', 'Julio', 'Abraham', 'Dioses', 'Villegas', 1, 1, 1, '2018-02-16 00:00:00', 101, 'Huaquillas', NULL),
(145, '0751605817', 'Yuliano', 'Valentino', 'Moncada', 'Robles', 1, 1, 1, '2018-04-04 00:00:00', 101, 'Huaquillas', NULL),
(146, '3050699788', 'Cristopher', 'Ariel', 'Ochoa', 'Muñoz', 103, 1, 1, '2018-05-06 00:00:00', 101, 'Huaquillas', NULL),
(147, '24291833', 'Dylan', 'Emanuel', 'Rivas', 'Roman', 103, 1, 1, '2018-04-03 00:00:00', 101, 'Huaquillas', NULL),
(148, '0751634429', 'Kael', 'Anthony', 'Saavedra', 'Criollo', 1, 1, 1, '2018-06-12 00:00:00', 101, 'Huaquillas', NULL),
(149, '0751651928', 'Liam', 'Isaac', 'Saldarriaga', 'Chuchuca', 1, 1, 1, '2018-07-29 00:00:00', 102, 'Huaquillas', NULL),
(150, '0751655200', 'Juan', 'Carlos', 'Serrano', 'Aponte', 1, 1, 1, '2018-08-06 00:00:00', 1, 'Huaquillas', NULL),
(151, '0751505744', 'Daniel', 'Ezequiel', 'Campoverde', 'Sanchez', 1, 1, 1, '2017-07-03 00:00:00', 101, 'Huaquillas', NULL),
(152, '0751486747', 'Allan', 'Razek', 'Chalan', 'Cabada', 1, 1, 1, '2017-05-17 00:00:00', 102, 'Huaquillas', NULL),
(153, '0751478777', 'Iker', 'Antonio', 'Cuenca', 'Moreno', 1, 1, 1, '2017-04-27 00:00:00', 101, 'Huaquillas', NULL),
(154, '0751465931', 'Danny', 'Jahir', 'Herrera', 'Yaguachi', 1, 1, 1, '2017-03-27 00:00:00', 101, 'Huaquillas', NULL),
(155, '0751516154', 'Elian', 'Leonel', 'Medina', 'Mayon', 1, 1, 1, '2017-07-29 00:00:00', 101, 'Huaquillas', NULL),
(156, '0751438185', 'Arick', 'Sebastian', 'Ortega', 'Aponte', 1, 1, 1, '2017-01-06 00:00:00', 101, 'Huaquillas', NULL),
(157, '0802668020', 'Jonathan', 'Sergio', 'Gonzalez', 'Cuero', 1, 1, 101, '1985-12-18 00:00:00', 101, 'Huaquillas', NULL),
(158, '0751443078', 'Cristopher', 'Enrique', 'Ortiz', 'Mendoza', 1, 1, 1, '2017-01-20 00:00:00', 101, 'Huaquillas', NULL),
(159, '0751473208', 'Mathias', 'Xavier', 'Rodriguez', 'Miranda', 1, 1, 1, '2017-04-13 00:00:00', 101, 'Huaquillas', NULL),
(160, '0751474693', 'Jeremy', 'Daniel', 'Yance', 'Jaramillo', 1, 1, 1, '2017-04-18 00:00:00', 101, 'Huaquillas', NULL),
(161, '0751499344', 'Juan', 'Adriel', 'Yungan', 'Guaycha', 1, 1, 1, '2017-06-16 00:00:00', 101, 'Huaquillas', NULL),
(162, '0751488164', 'Francisco', 'Mateo', 'Faican', 'Ontaneda', 1, 1, 1, '2017-05-20 00:00:00', 101, 'Huaquillas', NULL),
(163, '0750927444', 'Erick', 'Jair', 'Arias', 'Torres', 1, 1, 1, '2013-05-21 00:00:00', 101, 'Huaquillas', NULL),
(164, '0751306630', 'Iker', 'Manuel', 'Camacho', 'Valdiviezo', 1, 1, 1, '2015-07-17 00:00:00', 101, 'Huaquillas', NULL),
(165, '0707212312', 'Thiago', 'Vicente', 'Camacho', 'Valdiviezo', 1, 1, 1, '2013-01-11 00:00:00', 101, 'Huaquillas', NULL),
(166, '0751046525', 'Ian', 'Sebastian', 'Celi', 'Vega', 1, 1, 1, '2013-08-24 00:00:00', 101, 'Huaquillas', NULL),
(167, '0751174277', 'Luis', 'Edu', 'Cuenca', 'Arias', 1, 1, 1, '2013-04-21 00:00:00', 101, 'Huaquillas', NULL),
(168, '0705555555', 'Juan', '', 'Riquelme', '', 1, 1, 1, '2010-07-08 00:00:00', 101, 'Huaquillas', NULL),
(169, '0704444444', 'Maria', 'Gabriela', 'Jimenez', '', 1, 2, 1, '2002-06-30 00:00:00', 101, 'Huaquillas', NULL),
(170, '23558153', 'Jesús', 'Daniel', 'Carrillo', 'Colmenarez', 103, 1, 1, '2013-03-18 00:00:00', 101, 'Huaquillas', NULL),
(171, '0707210118', 'Domenica', 'Julieth', 'Espinoza', 'Zapata', 1, 2, 1, '2013-08-14 00:00:00', 101, 'Huaquillas', NULL),
(172, '0707201265', 'Thiago', 'Alexander', 'Pardo', 'Campoverde', 1, 1, 1, '2013-06-10 00:00:00', 101, 'Huaquillas', NULL),
(173, '0202610176', 'Sandra', 'Lucia', 'Azogue', 'Cashabamba', 1, 2, 1, '1997-06-28 00:00:00', 101, 'Huaquillas', NULL),
(174, '0750932386', 'Said', 'Oswaldo', 'Rodriguez', 'De la Cruz', 1, 1, 1, '2013-09-06 00:00:00', 101, 'Huaquillas', NULL),
(175, '0750932345', 'Jordy', 'Andrés', 'Ruiz', 'Lima', 1, 1, 1, '2013-09-28 00:00:00', 101, 'Huaquillas', NULL),
(176, '0751039975', 'Iker', 'Aron', 'Zuñiga', 'Tandazo', 1, 1, 1, '2014-04-24 00:00:00', 101, 'Huaquillas', NULL),
(177, '0750850505', 'Thiago', 'Ezequiel', 'Ramirez', 'Solis', 1, 1, 1, '2013-12-05 00:00:00', 101, 'Huaquillas', NULL),
(178, '0751234022', 'Luis', 'Dereck', 'Prieto', 'De la Cruz', 1, 1, 1, '2014-12-27 00:00:00', 101, 'Huaquillas', NULL),
(179, '0751100629', 'George', 'Joel', 'Muñoz', 'Oviedo', 1, 1, 1, '2014-07-11 00:00:00', 101, 'Huaquillas', NULL),
(180, '0750962755', 'Santiago', 'Matias', 'Kun', 'Toledo', 1, 1, 1, '2014-03-04 00:00:00', 101, 'Huaquillas', NULL),
(181, '0751046376', 'Andy', 'Sebastián', 'Palacios', 'Pérez', 1, 1, 1, '2013-10-08 00:00:00', 101, 'Huaquillas', NULL),
(182, '0755986147', 'Steven', 'Ariel', 'Curay', 'Jordan', 1, 1, 1, '2013-06-17 00:00:00', 101, 'Huaquillas', NULL),
(183, '0954241519', 'Alexander', 'Didier', 'Aquino', 'Maza', 1, 1, 1, '2012-06-18 00:00:00', 101, 'Huaquillas', NULL),
(184, '0707153250', 'Axel', 'Jahir', 'Bautista', 'Eras', 1, 1, 1, '2012-07-04 00:00:00', 101, 'Huaquillas', NULL),
(185, '07072012', 'Enyelber', 'Gabriel', 'Bernales', 'Rodriguez', 103, 1, 1, '2012-07-09 00:00:00', 101, 'Huaquillas', NULL),
(186, '0707140422', 'Cristhian', 'Nicolas', 'Solano', 'Vivanco', 1, 1, 1, '2012-07-17 00:00:00', 101, 'Huaquillas', NULL),
(187, '0707187381', 'Victor', 'Santiago', 'Toledo', 'Hurtado', 1, 1, 1, '2012-07-13 00:00:00', 101, 'Huaquillas', NULL),
(188, '0707163952', 'Derlis', 'Jhair', 'Armijos', 'Oviedo', 1, 1, 1, '2012-10-29 00:00:00', 101, 'Huaquillas', NULL),
(189, '0706412038', 'Jennifer', 'Mercedes', 'Sanchez', 'Paladines', 1, 2, 1, '1993-09-25 00:00:00', 101, 'Huaquillas', NULL),
(190, '733090518', 'Anghy', 'Lucia', 'Huaman', 'Jimenez', 102, 2, 1, '1996-07-25 00:00:00', 101, 'Huaquillas', NULL),
(191, '0750445322', 'Josias', 'Alexander', 'Alvarado', 'Oviedo', 1, 1, 1, '2011-09-24 00:00:00', 101, 'Huaquillas', NULL),
(192, '0707124178', 'Didy', 'Snayder', 'Balcazar', 'Campoverde', 1, 1, 1, '2011-11-02 00:00:00', 101, 'Huaquillas', NULL),
(193, '0750222202', 'Samuel', 'Isaí', 'Campoverde', 'Avila', 1, 1, 1, '2011-12-29 00:00:00', 101, 'Huaquillas', NULL),
(194, '0750343311', 'Maykel', 'Josue', 'Candela', 'Mendoza', 1, 1, 1, '2011-04-22 00:00:00', 101, 'Huaquillas', NULL),
(195, '0750326589', 'Matias', 'Gabriel', 'Correa', 'Hurtado', 1, 1, 1, '2011-05-12 00:00:00', 101, 'Huaquillas', NULL),
(196, '0707181152', 'Jostyn', 'Matias', 'Hurtado', 'Vaca', 1, 1, 1, '2011-09-24 00:00:00', 101, 'Huaquillas', NULL),
(197, '0750350654', 'John', 'Jairo', 'Ontaneda', 'Villalta', 1, 1, 1, '2011-09-15 00:00:00', 101, 'Huaquillas', NULL),
(198, '0750344772', 'Mateo', 'Nicolas', 'Pereira', 'Navarro', 1, 1, 1, '2011-10-25 00:00:00', 101, 'Huaquillas', NULL),
(199, '0707309852', 'Yehssy', 'Leandro', 'Pinargote', 'Eras', 1, 1, 1, '2011-08-09 00:00:00', 101, 'Huaquillas', NULL),
(200, '0750939571', 'Maykel', 'Daniel', 'Riofrio', 'García', 1, 1, 1, '2011-09-15 00:00:00', 101, 'Huaquillas', NULL),
(201, '0707137626', 'Luis', 'Ezequiel', 'Robles', 'Flores', 1, 1, 1, '2011-03-04 00:00:00', 101, 'Huaquillas', NULL),
(202, '0750024135', 'Sebastian', 'Wilbert', 'Rodríguez', 'Ledesma', 1, 1, 1, '2011-05-20 00:00:00', 101, 'Huaquillas', NULL),
(203, '0707123717', 'Josue', 'Snaider', 'Sanchez', 'Villegas', 1, 1, 1, '2011-06-14 00:00:00', 101, 'Huaquillas', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona_institucion`
--

CREATE TABLE `persona_institucion` (
  `id` int(11) NOT NULL,
  `id_persona` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `persona_institucion`
--

INSERT INTO `persona_institucion` (`id`, `id_persona`, `id_institucion`, `tipo`, `estado`) VALUES
(1, 1, 1, 1, 1),
(115, 121, 1, 2, 1),
(116, 122, 1, 6, 1),
(117, 123, 1, 7, 1),
(118, 124, 1, 3, 1),
(119, 125, 1, 4, 1),
(120, 126, 1, 7, 1),
(121, 127, 1, 7, 1),
(122, 128, 1, 3, 1),
(123, 129, 9, 2, 1),
(124, 130, 9, 6, 1),
(125, 131, 9, 4, 1),
(126, 132, 10, 2, 1),
(127, 133, 10, 6, 1),
(128, 134, 10, 4, 1),
(129, 135, 10, 4, 1),
(130, 136, 10, 3, 1),
(131, 137, 10, 3, 1),
(132, 138, 1, 3, 1),
(133, 139, 1, 3, 1),
(134, 140, 1, 3, 1),
(135, 141, 1, 3, 1),
(136, 142, 1, 3, 1),
(137, 143, 1, 3, 1),
(138, 144, 1, 3, 1),
(139, 145, 1, 3, 1),
(140, 146, 1, 3, 1),
(141, 147, 1, 3, 1),
(142, 148, 1, 3, 1),
(143, 149, 1, 3, 1),
(144, 150, 1, 3, 1),
(145, 151, 1, 3, 1),
(146, 152, 1, 3, 1),
(147, 153, 1, 3, 1),
(148, 154, 1, 3, 1),
(149, 155, 1, 3, 1),
(150, 156, 1, 3, 1),
(151, 157, 1, 4, 1),
(152, 158, 1, 3, 1),
(153, 159, 1, 3, 1),
(154, 160, 1, 3, 1),
(155, 161, 1, 3, 1),
(156, 162, 1, 3, 1),
(157, 163, 1, 3, 1),
(158, 164, 1, 3, 1),
(159, 165, 1, 3, 1),
(160, 166, 1, 3, 1),
(161, 167, 1, 3, 1),
(162, 168, 10, 3, 1),
(163, 169, 10, 3, 1),
(164, 170, 1, 3, 1),
(165, 171, 1, 3, 1),
(166, 172, 1, 3, 1),
(167, 173, 10, 3, 1),
(168, 174, 1, 3, 1),
(169, 175, 1, 3, 1),
(170, 176, 1, 3, 1),
(171, 177, 1, 3, 1),
(172, 178, 1, 3, 1),
(173, 179, 1, 3, 1),
(174, 180, 1, 3, 1),
(175, 181, 1, 3, 1),
(176, 182, 1, 3, 1),
(177, 183, 1, 3, 1),
(178, 184, 1, 3, 1),
(179, 185, 1, 3, 1),
(180, 186, 1, 3, 1),
(181, 187, 1, 3, 1),
(182, 188, 1, 3, 1),
(183, 189, 10, 4, 1),
(184, 190, 10, 3, 1),
(185, 191, 1, 3, 1),
(186, 192, 1, 3, 1),
(187, 193, 1, 3, 1),
(188, 194, 1, 3, 1),
(189, 195, 1, 3, 1),
(190, 196, 1, 3, 1),
(191, 197, 1, 3, 1),
(192, 198, 1, 3, 1),
(193, 199, 1, 3, 1),
(194, 200, 1, 3, 1),
(195, 201, 1, 3, 1),
(196, 202, 1, 3, 1),
(197, 203, 1, 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes_entrenamiento`
--

CREATE TABLE `planes_entrenamiento` (
  `id` int(11) NOT NULL,
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
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_entrenamiento_ejercicios`
--

CREATE TABLE `plan_entrenamiento_ejercicios` (
  `id` int(11) NOT NULL,
  `sesion` int(11) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 1,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `series` int(11) NOT NULL DEFAULT 0,
  `repeticiones` varchar(50) NOT NULL DEFAULT '',
  `duracion_segundos` int(11) NOT NULL DEFAULT 0,
  `descanso_segundos` int(11) NOT NULL DEFAULT 0,
  `intensidad` varchar(50) NOT NULL DEFAULT '',
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_entrenamiento_sesiones`
--

CREATE TABLE `plan_entrenamiento_sesiones` (
  `id` int(11) NOT NULL,
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
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_pago_matricula`
--

CREATE TABLE `plan_pago_matricula` (
  `id` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `periodicidad` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_generacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plan_pago_matricula`
--

INSERT INTO `plan_pago_matricula` (`id`, `matricula`, `periodicidad`, `subtotal`, `descuento`, `total`, `saldo`, `fecha_generacion`, `estado`) VALUES
(1, 1, 101, 25.00, 5.00, 20.00, 20.00, '2026-06-05 00:00:00', 1),
(2, 2, 103, 50.00, 0.00, 50.00, 0.00, '2026-06-07 00:00:00', 1),
(3, 3, 101, 50.00, 0.00, 50.00, 50.00, '2026-06-07 00:00:00', 1),
(4, 4, 101, 25.00, 0.00, 25.00, 25.00, '2026-06-07 00:00:00', 1),
(5, 5, 101, 25.00, 5.00, 20.00, 20.00, '2026-06-08 00:00:00', 1),
(6, 6, 101, 50.00, 5.00, 45.00, 45.00, '2026-06-08 00:00:00', 1),
(7, 7, 101, 50.00, 0.00, 50.00, 50.00, '2026-06-09 00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `categoria` int(11) NOT NULL,
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `costo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `controla_stock` tinyint(1) NOT NULL DEFAULT 1,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `categoria`, `precio`, `costo`, `controla_stock`, `institucion`, `estado`) VALUES
(1, 'Cosmetodologia', 'Matricula Cosmetologia', 'Matricula Inicial del Curso', 1, 40.00, 0.00, 1, 10, 1),
(2, 'Uniforme01', 'Uniforme Completo', 'Blusa, Chaleco, pantalon', 2, 55.00, 40.00, 1, 10, 1),
(3, 'Matri', 'Matricula de Incripcion', 'Matricula de Incripcion', 3, 185.00, 100.00, 1, 9, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_categorias`
--

CREATE TABLE `producto_categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_categorias`
--

INSERT INTO `producto_categorias` (`id`, `nombre`, `nemonico`, `descripcion`, `institucion`, `estado`) VALUES
(1, 'Matricula', 'Matri', 'Matricula Cosmetologia', 10, 1),
(2, 'Uniforme', 'UniPru', 'Uniforme de Academia', 10, 1),
(3, 'Matricula', 'MatriMag', 'Matricula de Inscripcion', 9, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_variantes`
--

CREATE TABLE `producto_variantes` (
  `id` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `sku` varchar(80) NOT NULL,
  `talla` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `genero` varchar(50) DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios`
--

CREATE TABLE `socios` (
  `id` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `membresia_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `institucion_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `socios`
--

INSERT INTO `socios` (`id`, `persona_id`, `membresia_id`, `fecha_inicio`, `fecha_fin`, `estado`, `institucion_id`) VALUES
(1, 123, 2, '2026-06-01', '2026-12-31', 1, 1),
(2, 126, 2, '2026-06-01', '2026-12-31', 1, 1),
(3, 127, 2, '2026-06-01', '2026-12-31', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tallas`
--

CREATE TABLE `tallas` (
  `id` int(11) NOT NULL,
  `persona` int(11) NOT NULL,
  `tipo_prenda` varchar(50) NOT NULL,
  `talla` varchar(20) NOT NULL,
  `medida_cm` varchar(50) DEFAULT NULL,
  `fecha_medicion` date NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telefonos`
--

CREATE TABLE `telefonos` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `operadora` int(11) NOT NULL,
  `persona` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `telefonos`
--

INSERT INTO `telefonos` (`id`, `numero`, `operadora`, `persona`, `tipo`, `estado`) VALUES
(1, '0999459672', 1, 121, 1, 1),
(2, '0988951410', 1, 122, 1, 2),
(3, '0988951410', 1, 122, 1, 1),
(4, '0991003021', 1, 123, 1, 1),
(5, '0991003021', 1, 124, 1, 2),
(6, '0991003021', 1, 124, 1, 1),
(7, '0963013515', 1, 125, 1, 1),
(8, '0981624035', 1, 126, 1, 1),
(9, '0992105534', 1, 127, 1, 1),
(10, '0991244747', 1, 128, 1, 2),
(11, '0982741217', 1, 130, 1, 2),
(12, '0982741217', 1, 130, 1, 2),
(13, '0982741217', 1, 130, 1, 1),
(14, '09999999', 1, 131, 1, 1),
(15, '099999999', 1, 134, 1, 1),
(16, '0987777777', 1, 135, 1, 1),
(17, '097777777', 1, 136, 1, 2),
(18, '095555555', 1, 137, 1, 2),
(19, '097777777', 1, 136, 1, 1),
(20, '095555555', 1, 137, 1, 1),
(21, '0993940971', 1, 138, 1, 1),
(22, '0969229395', 1, 139, 1, 2),
(23, '0979611002', 1, 140, 1, 1),
(24, '0995935045', 1, 141, 1, 2),
(25, '0995935045', 1, 141, 1, 1),
(26, '0967601849', 1, 142, 1, 1),
(27, '0980164912', 1, 143, 1, 1),
(28, '0991244747', 1, 128, 1, 1),
(29, '0979611002', 1, 145, 1, 1),
(30, '0988329346', 1, 146, 1, 1),
(31, '0979841876', 1, 147, 1, 1),
(32, '0990643144', 1, 148, 1, 1),
(33, '0994328235', 1, 149, 1, 1),
(34, '0992960908', 1, 150, 1, 1),
(35, '0989524804', 1, 151, 1, 2),
(36, '0985626994', 1, 152, 1, 1),
(37, '0997429233', 1, 152, 1, 1),
(38, '0989524804', 1, 151, 1, 1),
(39, '0994937484', 1, 151, 1, 1),
(40, '0969823680', 1, 153, 1, 1),
(41, '0968687429', 1, 154, 1, 1),
(42, '0925570087', 1, 155, 1, 1),
(43, '0994770292', 1, 156, 1, 1),
(44, '0981094615', 1, 157, 1, 2),
(45, '0981094615', 1, 157, 1, 1),
(46, '0986477477', 1, 158, 1, 1),
(47, '0990398542', 1, 158, 1, 1),
(48, '0969769506', 1, 139, 1, 1),
(49, '0996712316', 1, 139, 1, 1),
(50, '0990135050', 1, 159, 1, 1),
(51, '0962826342', 1, 160, 1, 1),
(52, '0985358241', 1, 161, 1, 1),
(53, '0982837804', 1, 163, 1, 1),
(54, '0997736017', 1, 164, 1, 2),
(55, '0997736017', 1, 164, 1, 1),
(56, '0997736017', 1, 165, 1, 1),
(57, '0993645836', 1, 166, 1, 1),
(58, '0990296896', 1, 167, 1, 1),
(59, '0966666666', 1, 168, 1, 1),
(60, '0944444444', 1, 169, 1, 1),
(61, '0990570707', 1, 170, 1, 1),
(62, '0993280911', 1, 171, 1, 1),
(63, '0969758820', 1, 172, 1, 1),
(64, '0982222222', 1, 173, 1, 1),
(65, '0986511684', 1, 174, 1, 1),
(66, '0986104518', 1, 175, 1, 1),
(67, '0994024284', 1, 176, 1, 1),
(68, '0988726758', 1, 177, 1, 1),
(69, '0986511684', 1, 178, 1, 1),
(70, '0969222880', 1, 179, 1, 1),
(71, '0988194615', 1, 180, 1, 1),
(72, '0988798437', 1, 181, 1, 1),
(73, '0988282402', 1, 182, 1, 1),
(74, '0985586876', 1, 183, 1, 1),
(75, '0994900890', 1, 184, 1, 1),
(76, '0993852029', 1, 185, 1, 1),
(77, '0981434414', 1, 186, 1, 1),
(78, '0959505113', 1, 187, 1, 1),
(79, '0993539826', 1, 188, 1, 1),
(80, '098548759', 1, 189, 1, 1),
(81, '0967601904', 1, 191, 1, 1),
(82, '0983801829', 1, 192, 1, 1),
(83, '0993555837', 1, 193, 1, 1),
(84, '0999900408', 1, 194, 1, 1),
(85, '0979331388', 1, 194, 1, 1),
(86, '0939696213', 1, 195, 1, 1),
(87, '0997475366', 1, 196, 1, 2),
(88, '0997475366', 1, 196, 1, 1),
(89, '0983716927', 1, 197, 1, 1),
(90, '0989881719', 1, 198, 1, 1),
(91, '0988926084', 1, 199, 1, 2),
(92, '0994240944', 1, 199, 1, 2),
(93, '0988926084', 1, 199, 1, 1),
(94, '0994240944', 1, 199, 1, 1),
(95, '0986476005', 1, 200, 1, 1),
(96, '0979611002', 1, 201, 1, 1),
(97, '0992686138', 1, 202, 1, 2),
(98, '0992686138', 1, 202, 1, 1),
(99, '0985131422', 1, 202, 1, 1),
(100, '0989807042', 1, 203, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_contacto`
--

CREATE TABLE `tipos_contacto` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_contacto`
--

INSERT INTO `tipos_contacto` (`id`, `nemonico`, `nombre`) VALUES
(1, 'CONT_PROPIO', 'PROPIO'),
(101, 'CONT_FAMILIAR', 'FAMILIAR'),
(102, 'CONT_EMERGENCIA', 'EMERGENCIA'),
(103, 'CONT_TRABAJO', 'TRABAJO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_alergias`
--

CREATE TABLE `tipo_alergias` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_alergias`
--

INSERT INTO `tipo_alergias` (`id`, `nemonico`, `nombre`) VALUES
(1, 'RESP_ASMA', 'ASMA'),
(101, 'ALIMENTOS', 'ALIMENTOS'),
(102, 'MEDICAMENTOS', 'MEDICAMENTOS'),
(103, 'AMBIENTAL', 'AMBIENTAL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_enfermedad`
--

CREATE TABLE `tipo_enfermedad` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(150) NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_enfermedad`
--

INSERT INTO `tipo_enfermedad` (`id`, `nemonico`, `nombre`) VALUES
(1, 'RESP', 'RESPIRATORIA'),
(101, 'CARDIO', 'CARDIOVASCULAR'),
(102, 'DIGESTIVA', 'DIGESTIVA'),
(103, 'LESION', 'LESION DEPORTIVA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_estado_fisico`
--

CREATE TABLE `tipo_estado_fisico` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_estado_fisico`
--

INSERT INTO `tipo_estado_fisico` (`id`, `nemonico`, `nombre`) VALUES
(1, 'ALTURA', 'ALTURA'),
(2, 'PESO', 'PESO'),
(3, 'TIPO_SANGRE', 'TIPO DE SANGRE'),
(4, 'ESTADO_VISUAL', 'VISION/LENTES'),
(5, 'TALLA_ROPA', 'TALLA DE ROPA'),
(6, 'TALLA_CALZADO', 'TALLA DE ZAPATOS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_permiso`
--

CREATE TABLE `tipo_permiso` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_permiso`
--

INSERT INTO `tipo_permiso` (`id`, `nemonico`, `nombre`) VALUES
(1, 'FAMILIAR', 'INCIDENTE FAMILIAR'),
(101, 'MEDICO', 'PERMISO MEDICO'),
(102, 'ACADEMICO', 'PERMISO ACADEMICO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_persona`
--

CREATE TABLE `tipo_persona` (
  `id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_persona`
--

INSERT INTO `tipo_persona` (`id`, `nemonico`, `nombre`) VALUES
(1, 'TIPO_DEV', 'DESARROLLADOR'),
(2, 'TIPO_ADMIN', 'ADMININISTRADOR'),
(3, 'TIPO_EST', 'ESTUDIANTE'),
(4, 'TIPO_PROF', 'DOCENTE'),
(5, 'TIPO_MED', 'MEDICO'),
(6, 'TIPO_ADM_OPER', 'ADMINISTRATIVO'),
(7, 'TIPO_SOCIO', 'SOCIO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneos`
--

CREATE TABLE `torneos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `deporte` int(11) NOT NULL,
  `institucion` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torneo_participantes`
--

CREATE TABLE `torneo_participantes` (
  `id` int(11) NOT NULL,
  `torneo` int(11) NOT NULL,
  `matricula` int(11) NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `type_users`
--

CREATE TABLE `type_users` (
  `nemonico` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `type_users`
--

INSERT INTO `type_users` (`nemonico`, `nombre`, `descripcion`, `imagen`) VALUES
('TIPO_ADMIN', 'Administrador', 'Usuario con privilegios administrativos', 'bi-person-gear'),
('TIPO_ADM_OPER', 'Administrativo', 'Usuario administrativo para caja y secretaria', 'bi-briefcase-fill'),
('TIPO_DEV', 'Desarrollador', 'Usuario con privilegios totales de desarrollador', 'bi-code-slash'),
('TIPO_EST', 'Estudiante', 'Usuario estudiante para pruebas', 'bi-mortarboard-fill'),
('TIPO_MED', 'Medico', 'Usuario medico para pruebas', 'bi-heart-pulse-fill'),
('TIPO_PROF', 'Profesor', 'Entrenador o docente para pruebas', 'bi-person-badge-fill'),
('TIPO_SOCIO', 'Socio', 'Socio del club deportivo', 'bi-briefcase-fill');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` varchar(30) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `type`, `image`) VALUES
(1, 'jminan', '$2y$10$3nV1BdEGxt1BHuvwtsB24uT.4e8KE70ZteyDb6Qjo60VMUAfuSTpW', 'TIPO_DEV', 'icons/dev.jpg'),
(113, 'jhinson', '$2y$10$x/cG7u/1gqx5bbYQTaktkubrmCRu3AhF3CmSrxUd0uRUE5v.m9luG', 'TIPO_ADMIN', NULL),
(114, 'Llison', '$2y$10$Cp11NrfdoPuytR0JMrB5K.oSlVR3y9CAkQqjlTnvruu6mUhOxmKSC', 'TIPO_ADM_OPER', NULL),
(115, '0704486018001', '$2y$10$canHfjmzTp6APmEv8yPQRufolm/1BLWd.58bZa3MtwacAuLa5ufy.', 'TIPO_ADMIN', NULL),
(116, '0705404390', '$2y$10$a95fXcHE6EuUWCGWDhuG7.K9Nwzxd5QCSRbgTtLl4sykIY9.9GqVS', 'TIPO_ADM_OPER', NULL),
(117, '9999999999999', '$2y$10$FwuVlWSEJjxykL.f7epugOVUYIzeeYSa3b2uW9HvgX2N8iloT27Qe', 'TIPO_ADMIN', NULL),
(118, 'moreno', '$2y$10$YzqcdoABnpA3saCqRM0pZOTzU/gVRcbT8E7.5QjM0//utLhmJRQs6', 'TIPO_ADM_OPER', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
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
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `numero`, `fecha`, `persona`, `matricula`, `institucion`, `tipo`, `subtotal`, `descuento`, `impuesto`, `total`, `saldo`, `estado`) VALUES
(1, 'MENS-202606-123-dabb1', '2026-06-01 00:00:00', 123, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(2, 'MENS-202607-123-dacb7', '2026-07-01 00:00:00', 123, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(3, 'MENS-202608-123-dad38', '2026-08-01 00:00:00', 123, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(4, 'MENS-202609-123-dada5', '2026-09-01 00:00:00', 123, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(5, 'MENS-202610-123-dae21', '2026-10-01 00:00:00', 123, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(6, 'MENS-202611-123-dae8c', '2026-11-01 00:00:00', 123, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(7, 'MENS-202612-123-daef3', '2026-12-01 00:00:00', 123, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(8, 'FAC-20260605-000001', '2026-06-05 21:36:26', 124, 1, 1, 'MATRICULA', 25.00, 5.00, 0.00, 20.00, 20.00, 1),
(9, 'MENS-202606-126-898c8', '2026-06-01 00:00:00', 126, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(10, 'MENS-202607-126-89998', '2026-07-01 00:00:00', 126, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(11, 'MENS-202608-126-89a0c', '2026-08-01 00:00:00', 126, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(12, 'MENS-202609-126-89a74', '2026-09-01 00:00:00', 126, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(13, 'MENS-202610-126-89ad9', '2026-10-01 00:00:00', 126, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(14, 'MENS-202611-126-89b3b', '2026-11-01 00:00:00', 126, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(15, 'MENS-202612-126-89bcd', '2026-12-01 00:00:00', 126, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(16, 'MENS-202606-127-e0301', '2026-06-01 00:00:00', 127, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(17, 'MENS-202607-127-e03e5', '2026-07-01 00:00:00', 127, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(18, 'MENS-202608-127-e045c', '2026-08-01 00:00:00', 127, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(19, 'MENS-202609-127-e04bf', '2026-09-01 00:00:00', 127, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(20, 'MENS-202610-127-e056c', '2026-10-01 00:00:00', 127, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(21, 'MENS-202611-127-e05d5', '2026-11-01 00:00:00', 127, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(22, 'MENS-202612-127-e0646', '2026-12-01 00:00:00', 127, NULL, 1, 'MENSUALIDAD', 30.00, 0.00, 0.00, 30.00, 30.00, 1),
(23, 'FAC-20260607-000002', '2026-06-07 22:21:11', 136, 2, 10, 'MATRICULA', 50.00, 0.00, 0.00, 50.00, 0.00, 1),
(24, 'FAC-20260607-000003', '2026-06-07 22:26:14', 136, 3, 10, 'MATRICULA', 50.00, 0.00, 0.00, 50.00, 0.00, 2),
(25, 'FAC-20260607-000004', '2026-06-07 23:01:12', 137, 4, 10, 'MATRICULA', 25.00, 0.00, 0.00, 25.00, 0.00, 2),
(26, 'VTA-20260607-6a263895dceab', '2026-06-07 23:35:49', 136, NULL, 10, 'VENTA', 95.00, 0.00, 0.00, 95.00, 0.00, 1),
(27, 'FAC-20260608-000005', '2026-06-08 22:04:29', 168, 5, 10, 'MATRICULA', 25.00, 5.00, 0.00, 20.00, 0.00, 2),
(28, 'FAC-20260608-000006', '2026-06-08 22:25:47', 169, 6, 10, 'MATRICULA', 50.00, 5.00, 0.00, 45.00, 0.00, 2),
(29, 'FAC-20260609-000007', '2026-06-09 11:31:28', 173, 7, 10, 'MATRICULA', 50.00, 0.00, 0.00, 50.00, 0.00, 2),
(30, 'VTA-20260609-6a283431cec76', '2026-06-09 11:41:37', 173, NULL, 9, 'VENTA', 185.00, 0.00, 0.00, 185.00, 0.00, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_externas`
--

CREATE TABLE `ventas_externas` (
  `id` int(11) NOT NULL,
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
  `estado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_externas_detalle`
--

CREATE TABLE `ventas_externas_detalle` (
  `id` int(11) NOT NULL,
  `venta_externa` int(11) NOT NULL,
  `producto` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `cantidad` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_detalle`
--

CREATE TABLE `venta_detalle` (
  `id` int(11) NOT NULL,
  `venta` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `variante` int(11) DEFAULT NULL,
  `cantidad` decimal(12,2) NOT NULL DEFAULT 0.00,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `venta_detalle`
--

INSERT INTO `venta_detalle` (`id`, `venta`, `producto`, `variante`, `cantidad`, `precio_unitario`, `descuento`, `total`) VALUES
(1, 26, 1, NULL, 1.00, 40.00, 0.00, 40.00),
(2, 26, 2, NULL, 1.00, 55.00, 0.00, 55.00),
(3, 30, 3, NULL, 1.00, 185.00, 0.00, 185.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `institucion` (`institucion`),
  ADD KEY `2` (`categoria_deportiva`),
  ADD KEY `3` (`nivel`),
  ADD KEY `4` (`periodo_academico`),
  ADD KEY `5` (`docente`);

--
-- Indices de la tabla `actividades_matricula`
--
ALTER TABLE `actividades_matricula`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_ACT_MATRICULA_ACTIVIDAD` (`actividad`),
  ADD KEY `IDX_ACT_MATRICULA_MATRICULA` (`matricula`),
  ADD KEY `IDX_ACT_MATRICULA_MODALIDAD` (`modalidad`),
  ADD KEY `IDX_ACT_MATRICULA_HORARIO` (`horario`),
  ADD KEY `IDX_ACT_MATRICULA_ESTADO` (`estado`);

--
-- Indices de la tabla `alergias`
--
ALTER TABLE `alergias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_ALERGIAS_TIPO_ALERGIAS` (`tipo`),
  ADD KEY `FK2_ALERGIAS_NIVEL` (`nivel`),
  ADD KEY `FK3_ALERGIAS_FICHA_MEDICA` (`ficha_medica`);

--
-- Indices de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK2_ASIGNATURA_INSTITUCION` (`institucion`),
  ADD KEY `FK3_ASIGNATURA_ESTADO` (`estado`);

--
-- Indices de la tabla `asignatura_clase`
--
ALTER TABLE `asignatura_clase`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_ASIGNATURA_CLASE` (`asignatura`,`clase`),
  ADD KEY `IDX_ASIGNATURA_CLASE_ASIGNATURA` (`asignatura`),
  ADD KEY `IDX_ASIGNATURA_CLASE_CLASE` (`clase`),
  ADD KEY `IDX_ASIGNATURA_CLASE_ESTADO` (`estado`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_ASISTENCIAS_CALENDARIO` (`calendario`),
  ADD KEY `IDX_ASISTENCIAS_HORARIO` (`horario`),
  ADD KEY `IDX_ASISTENCIAS_PLAN_SESION` (`plan_entrenamiento_sesion`),
  ADD KEY `FK_ASISTENCIAS_MATRICULA` (`matricula`),
  ADD KEY `FK_ASISTENCIAS_CLASE` (`clase`),
  ADD KEY `FK_ASISTENCIAS_ACTIVIDAD` (`actividad`),
  ADD KEY `FK_ASISTENCIAS_ESTADO` (`estado`);

--
-- Indices de la tabla `botones`
--
ALTER TABLE `botones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_ESTADO_BOTON` (`estado`);

--
-- Indices de la tabla `boton_user`
--
ALTER TABLE `boton_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_BOTON_USER` (`boton`),
  ADD KEY `FK2_TYPE_USER` (`type_user`),
  ADD KEY `IDX_BOTON_USER_INSTITUCION` (`institucion`);

--
-- Indices de la tabla `calendarios`
--
ALTER TABLE `calendarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CALENDARIOS_TIPO` (`tipo`),
  ADD KEY `IDX_CALENDARIOS_INSTITUCION` (`institucion`),
  ADD KEY `IDX_CALENDARIOS_HORARIO` (`horario`),
  ADD KEY `IDX_CALENDARIOS_CLASE` (`clase`),
  ADD KEY `IDX_CALENDARIOS_ACTIVIDAD` (`actividad`),
  ADD KEY `IDX_CALENDARIOS_PLAN` (`plan_entrenamiento`),
  ADD KEY `IDX_CALENDARIOS_ENTRENADOR` (`entrenador`),
  ADD KEY `IDX_CALENDARIOS_FECHAS` (`fecha_inicio`,`fecha_fin`),
  ADD KEY `IDX_CALENDARIOS_ESTADO` (`estado`);

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_CALIFICACIONES_EVALUACION` (`evaluacion`),
  ADD KEY `FK_CALIFICACIONES_MATRICULA` (`matricula`),
  ADD KEY `FK_CALIFICACIONES_ESTADO` (`estado`);

--
-- Indices de la tabla `categorias_deportivas`
--
ALTER TABLE `categorias_deportivas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_CATEGORIAS_DEPORTIVAS_NEMONICO` (`nemonico`),
  ADD KEY `IDX_CATEGORIAS_DEPORTIVAS_DEPORTE` (`deporte`),
  ADD KEY `IDX_CATEGORIAS_DEPORTIVAS_INSTITUCION` (`institucion`),
  ADD KEY `IDX_CATEGORIAS_DEPORTIVAS_ESTADO` (`estado`);

--
-- Indices de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `clases`
--
ALTER TABLE `clases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_NIVEL_CLASES` (`nivel`),
  ADD KEY `FK2_ASIGNATURA_CLASES` (`asignatura`),
  ADD KEY `FK3_INSTITUCION_CLASES` (`institucion`),
  ADD KEY `IDX_CLASES_PERIODO` (`periodo_academico`),
  ADD KEY `IDX_CLASES_DOCENTE` (`docente`),
  ADD KEY `IDX_CLASES_ESTADO` (`estado`);

--
-- Indices de la tabla `clases_matricula`
--
ALTER TABLE `clases_matricula`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_CLASE_CLASE_MATRICULA` (`clase`),
  ADD KEY `FK2_MATRICULA_CLASE_MATRICULA` (`matricula`),
  ADD KEY `FK3_ESTADO_CLASE_MATRICULA` (`estado`),
  ADD KEY `FK4_MODALIDAD_CLASES_MATRICULA` (`modalidad`),
  ADD KEY `FK5_CONTRATO_CLASES_MATRICULA` (`contrato`),
  ADD KEY `IDX_CLASE_MATRICULA_HORARIO` (`horario`);

--
-- Indices de la tabla `contratacion`
--
ALTER TABLE `contratacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_PERSONA_CONTRATO` (`persona`),
  ADD KEY `FK2_PERSONA_NIVEL` (`nivel`),
  ADD KEY `FK3_PERSONA_INSTITUCION` (`institucion`);

--
-- Indices de la tabla `correos`
--
ALTER TABLE `correos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_ESTADO_MAIL` (`estado`),
  ADD KEY `FK2_PERSONA` (`persona`),
  ADD KEY `FK3_TIPO_CONTACT` (`tipo`);

--
-- Indices de la tabla `cuotas_matricula`
--
ALTER TABLE `cuotas_matricula`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CUOTAS_PLAN_PAGO` (`plan_pago`),
  ADD KEY `IDX_CUOTAS_ESTADO` (`estado`);

--
-- Indices de la tabla `deportes`
--
ALTER TABLE `deportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_INSTITUCION_DEPORTE` (`institucion`),
  ADD KEY `FK2_ESTADO_DEPORTE` (`estado`);

--
-- Indices de la tabla `enfermedades`
--
ALTER TABLE `enfermedades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`),
  ADD KEY `FK1_ENFERMEDAD_TIPO_ENFERMEDAD` (`tipo`),
  ADD KEY `FK2_ENFERMEDAD_NIVEL` (`nivel`);

--
-- Indices de la tabla `enfermedad_persona`
--
ALTER TABLE `enfermedad_persona`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_ENFERMEDAD_PERSONA` (`enfermedad`);

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estado_fisico`
--
ALTER TABLE `estado_fisico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_ESTADO_FISICO_PERFIL_MEDICO` (`perfil_medico`),
  ADD KEY `FK2_ESTADO_FISICO_TIPO_ESTADOS` (`tipo`);

--
-- Indices de la tabla `estado_permiso`
--
ALTER TABLE `estado_permiso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `etnias`
--
ALTER TABLE `etnias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_EVALUACIONES_CLASE` (`clase`),
  ADD KEY `FK_EVALUACIONES_ACTIVIDAD` (`actividad`),
  ADD KEY `FK_EVALUACIONES_ESTADO` (`estado`);

--
-- Indices de la tabla `fichas_medicas`
--
ALTER TABLE `fichas_medicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_FICHA_MEDICA_PERFIL_MEDICO` (`perfil_medico`),
  ADD KEY `IDX_FICHAS_PERSONA` (`persona`);

--
-- Indices de la tabla `formas_pago`
--
ALTER TABLE `formas_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_FORMAS_PAGO_NEMONICO` (`nemonico`),
  ADD KEY `IDX_FORMAS_PAGO_ESTADO` (`estado`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_GASTOS_INSTITUCION` (`institucion`),
  ADD KEY `FK_GASTOS_PERSONA` (`persona`),
  ADD KEY `FK_GASTOS_ESTADO` (`estado`);

--
-- Indices de la tabla `generos`
--
ALTER TABLE `generos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_HORARIOS_INSTITUCION` (`institucion`),
  ADD KEY `IDX_HORARIOS_CLASE` (`clase`),
  ADD KEY `IDX_HORARIOS_ACTIVIDAD` (`actividad`),
  ADD KEY `IDX_HORARIOS_CATEGORIA` (`categoria_deportiva`),
  ADD KEY `IDX_HORARIOS_ENTRENADOR` (`entrenador`),
  ADD KEY `IDX_HORARIOS_DIA_HORA` (`dia_semana`,`hora_inicio`,`hora_fin`),
  ADD KEY `IDX_HORARIOS_ESTADO` (`estado`);

--
-- Indices de la tabla `incidencia`
--
ALTER TABLE `incidencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_INCIDENCIA_PERSONA` (`persona`),
  ADD KEY `FK2_INCIDENCIA_NIVEL` (`nivel`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ruc` (`ruc`),
  ADD KEY `FK1_ESTADO_INSTITUCION` (`estado`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_INVENTARIO_PRODUCTO` (`producto`),
  ADD KEY `IDX_INVENTARIO_VARIANTE` (`variante`),
  ADD KEY `IDX_INVENTARIO_INSTITUCION` (`institucion`);

--
-- Indices de la tabla `matricula`
--
ALTER TABLE `matricula`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_MATRICULA_PERSONA` (`persona`),
  ADD KEY `FK2_MATRICULA_ESTADO` (`estado`),
  ADD KEY `FK3_MATRICULA_INSTITUCION` (`institucion`),
  ADD KEY `FK4_MATRICULA_MEMBRESIA` (`membresia`);

--
-- Indices de la tabla `membresias`
--
ALTER TABLE `membresias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_ESTADO_MEMBRESIA` (`estado`),
  ADD KEY `FK_MEMBRESIAS_INSTITUCION` (`institucion`);

--
-- Indices de la tabla `modalidades`
--
ALTER TABLE `modalidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_MOV_INV_PRODUCTO` (`producto`),
  ADD KEY `IDX_MOV_INV_VARIANTE` (`variante`),
  ADD KEY `IDX_MOV_INV_INSTITUCION` (`institucion`);

--
-- Indices de la tabla `nacionalidades`
--
ALTER TABLE `nacionalidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `nivel`
--
ALTER TABLE `nivel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `nivel_academico`
--
ALTER TABLE `nivel_academico`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `nivel_asignatura`
--
ALTER TABLE `nivel_asignatura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `operadoras`
--
ALTER TABLE `operadoras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_PAGOS_NUMERO` (`numero`),
  ADD KEY `IDX_PAGOS_PERSONA` (`persona`),
  ADD KEY `IDX_PAGOS_MATRICULA` (`matricula`),
  ADD KEY `IDX_PAGOS_VENTA` (`venta`),
  ADD KEY `IDX_PAGOS_FORMA_PAGO` (`forma_pago`),
  ADD KEY `IDX_PAGOS_INSTITUCION` (`institucion`),
  ADD KEY `IDX_PAGOS_ESTADO` (`estado`);

--
-- Indices de la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_PAGO_DETALLE_PAGO` (`pago`),
  ADD KEY `IDX_PAGO_DETALLE_CUOTA` (`cuota_matricula`),
  ADD KEY `IDX_PAGO_DETALLE_VENTA` (`venta`);

--
-- Indices de la tabla `perfil_medico`
--
ALTER TABLE `perfil_medico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_PERFIL_MEDICO_PERSONA` (`persona`),
  ADD KEY `FK_PERFIL_MEDICO_ESTADO` (`estado`);

--
-- Indices de la tabla `periodicidad_pago`
--
ALTER TABLE `periodicidad_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_PERIODICIDAD_PAGO_NEMONICO` (`nemonico`),
  ADD KEY `IDX_PERIODICIDAD_PAGO_ESTADO` (`estado`);

--
-- Indices de la tabla `periodos_academicos`
--
ALTER TABLE `periodos_academicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_PERIODOS_INSTITUCION` (`institucion`),
  ADD KEY `FK_PERIODOS_ESTADO` (`estado`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_PERMISO_PERSONA` (`persona`),
  ADD KEY `FK2_PERMISO_TIPO_PERMISO` (`tipo`),
  ADD KEY `FK3_PERMISO_ESTADO_PERMISO` (`estado`),
  ADD KEY `IDX_PERMISOS_MATRICULA` (`matricula`),
  ADD KEY `IDX_PERMISOS_CLASE` (`clase`),
  ADD KEY `IDX_PERMISOS_ACTIVIDAD` (`actividad`),
  ADD KEY `IDX_PERMISOS_FECHAS` (`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identificacion` (`identificacion`),
  ADD KEY `FK1_NACIONALIDAD` (`nacionalidad`),
  ADD KEY `FK2_USER` (`user`),
  ADD KEY `FK3_GENERO` (`genero`),
  ADD KEY `FK4_ETNIA` (`etnia`),
  ADD KEY `FK5_CIUDAD` (`ciudad`);

--
-- Indices de la tabla `persona_institucion`
--
ALTER TABLE `persona_institucion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_ESTADO` (`estado`),
  ADD KEY `FK2_TIPO_PERSONA` (`tipo`),
  ADD KEY `FK3_PERSONA` (`id_persona`),
  ADD KEY `FK4_INSTITUCION` (`id_institucion`);

--
-- Indices de la tabla `planes_entrenamiento`
--
ALTER TABLE `planes_entrenamiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_PLAN_ENT_MATRICULA` (`matricula`),
  ADD KEY `IDX_PLAN_ENT_CLASE` (`clase`),
  ADD KEY `IDX_PLAN_ENT_ACTIVIDAD` (`actividad`),
  ADD KEY `IDX_PLAN_ENT_CATEGORIA` (`categoria_deportiva`),
  ADD KEY `IDX_PLAN_ENT_ENTRENADOR` (`entrenador`),
  ADD KEY `IDX_PLAN_ENT_INSTITUCION` (`institucion`),
  ADD KEY `IDX_PLAN_ENT_NIVEL` (`nivel`),
  ADD KEY `IDX_PLAN_ENT_ESTADO` (`estado`);

--
-- Indices de la tabla `plan_entrenamiento_ejercicios`
--
ALTER TABLE `plan_entrenamiento_ejercicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_PLAN_EJERCICIOS_SESION` (`sesion`),
  ADD KEY `IDX_PLAN_EJERCICIOS_ORDEN` (`sesion`,`orden`),
  ADD KEY `IDX_PLAN_EJERCICIOS_ESTADO` (`estado`);

--
-- Indices de la tabla `plan_entrenamiento_sesiones`
--
ALTER TABLE `plan_entrenamiento_sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_PLAN_SESIONES_PLAN` (`plan_entrenamiento`),
  ADD KEY `IDX_PLAN_SESIONES_CALENDARIO` (`calendario`),
  ADD KEY `IDX_PLAN_SESIONES_HORARIO` (`horario`),
  ADD KEY `IDX_PLAN_SESIONES_ORDEN` (`plan_entrenamiento`,`numero_semana`,`dia_semana`),
  ADD KEY `IDX_PLAN_SESIONES_ESTADO` (`estado`);

--
-- Indices de la tabla `plan_pago_matricula`
--
ALTER TABLE `plan_pago_matricula`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_PLAN_PAGO_MATRICULA` (`matricula`),
  ADD KEY `IDX_PLAN_PAGO_PERIODICIDAD` (`periodicidad`),
  ADD KEY `IDX_PLAN_PAGO_ESTADO` (`estado`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_PRODUCTOS_CODIGO` (`codigo`),
  ADD KEY `IDX_PRODUCTOS_CATEGORIA` (`categoria`),
  ADD KEY `IDX_PRODUCTOS_INSTITUCION` (`institucion`),
  ADD KEY `IDX_PRODUCTOS_ESTADO` (`estado`);

--
-- Indices de la tabla `producto_categorias`
--
ALTER TABLE `producto_categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_PRODUCTO_CATEGORIAS_NEMONICO` (`nemonico`),
  ADD KEY `IDX_PRODUCTO_CATEGORIAS_INSTITUCION` (`institucion`),
  ADD KEY `IDX_PRODUCTO_CATEGORIAS_ESTADO` (`estado`);

--
-- Indices de la tabla `producto_variantes`
--
ALTER TABLE `producto_variantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_PRODUCTO_VARIANTES_SKU` (`sku`),
  ADD KEY `IDX_PRODUCTO_VARIANTES_PRODUCTO` (`producto`),
  ADD KEY `IDX_PRODUCTO_VARIANTES_ESTADO` (`estado`);

--
-- Indices de la tabla `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_socio_persona` (`persona_id`),
  ADD KEY `idx_socio_membresia` (`membresia_id`),
  ADD KEY `idx_socio_institucion` (`institucion_id`);

--
-- Indices de la tabla `tallas`
--
ALTER TABLE `tallas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_TALLAS_PERSONA` (`persona`),
  ADD KEY `FK_TALLAS_ESTADO` (`estado`);

--
-- Indices de la tabla `telefonos`
--
ALTER TABLE `telefonos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_PERSONA_TELEFONO` (`persona`),
  ADD KEY `FK2_ESTADO` (`estado`),
  ADD KEY `FK3_TIPO_CONTACTO` (`tipo`),
  ADD KEY `FK4_OPERADORA` (`operadora`);

--
-- Indices de la tabla `tipos_contacto`
--
ALTER TABLE `tipos_contacto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `tipo_alergias`
--
ALTER TABLE `tipo_alergias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `tipo_enfermedad`
--
ALTER TABLE `tipo_enfermedad`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `tipo_estado_fisico`
--
ALTER TABLE `tipo_estado_fisico`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `tipo_permiso`
--
ALTER TABLE `tipo_permiso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `tipo_persona`
--
ALTER TABLE `tipo_persona`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nemonico` (`nemonico`);

--
-- Indices de la tabla `torneos`
--
ALTER TABLE `torneos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_TORNEOS_DEPORTE` (`deporte`),
  ADD KEY `FK_TORNEOS_INSTITUCION` (`institucion`),
  ADD KEY `FK_TORNEOS_ESTADO` (`estado`);

--
-- Indices de la tabla `torneo_participantes`
--
ALTER TABLE `torneo_participantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_TORNEO_PART_TORNEO` (`torneo`),
  ADD KEY `FK_TORNEO_PART_MATRICULA` (`matricula`),
  ADD KEY `FK_TORNEO_PART_ESTADO` (`estado`);

--
-- Indices de la tabla `type_users`
--
ALTER TABLE `type_users`
  ADD PRIMARY KEY (`nemonico`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_TYPE_USERS` (`type`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_VENTAS_NUMERO` (`numero`),
  ADD KEY `IDX_VENTAS_PERSONA` (`persona`),
  ADD KEY `IDX_VENTAS_MATRICULA` (`matricula`),
  ADD KEY `IDX_VENTAS_INSTITUCION` (`institucion`),
  ADD KEY `IDX_VENTAS_ESTADO` (`estado`);

--
-- Indices de la tabla `ventas_externas`
--
ALTER TABLE `ventas_externas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UK_VENTAS_EXTERNAS_NUMERO` (`numero`),
  ADD KEY `IDX_VE_INSTITUCION` (`institucion`),
  ADD KEY `IDX_VE_FORMA_PAGO` (`forma_pago`),
  ADD KEY `IDX_VE_ESTADO` (`estado`);

--
-- Indices de la tabla `ventas_externas_detalle`
--
ALTER TABLE `ventas_externas_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_VED_VENTA_EXTERNA` (`venta_externa`),
  ADD KEY `IDX_VED_PRODUCTO` (`producto`);

--
-- Indices de la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_VENTA_DETALLE_VENTA` (`venta`),
  ADD KEY `IDX_VENTA_DETALLE_PRODUCTO` (`producto`),
  ADD KEY `IDX_VENTA_DETALLE_VARIANTE` (`variante`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `actividades_matricula`
--
ALTER TABLE `actividades_matricula`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `alergias`
--
ALTER TABLE `alergias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `asignatura_clase`
--
ALTER TABLE `asignatura_clase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `botones`
--
ALTER TABLE `botones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `boton_user`
--
ALTER TABLE `boton_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT de la tabla `calendarios`
--
ALTER TABLE `calendarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias_deportivas`
--
ALTER TABLE `categorias_deportivas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `clases`
--
ALTER TABLE `clases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `clases_matricula`
--
ALTER TABLE `clases_matricula`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `contratacion`
--
ALTER TABLE `contratacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `correos`
--
ALTER TABLE `correos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `cuotas_matricula`
--
ALTER TABLE `cuotas_matricula`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `deportes`
--
ALTER TABLE `deportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `enfermedades`
--
ALTER TABLE `enfermedades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `enfermedad_persona`
--
ALTER TABLE `enfermedad_persona`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados`
--
ALTER TABLE `estados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estado_fisico`
--
ALTER TABLE `estado_fisico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_permiso`
--
ALTER TABLE `estado_permiso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `etnias`
--
ALTER TABLE `etnias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fichas_medicas`
--
ALTER TABLE `fichas_medicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `formas_pago`
--
ALTER TABLE `formas_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `generos`
--
ALTER TABLE `generos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `incidencia`
--
ALTER TABLE `incidencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `matricula`
--
ALTER TABLE `matricula`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `membresias`
--
ALTER TABLE `membresias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `modalidades`
--
ALTER TABLE `modalidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `nacionalidades`
--
ALTER TABLE `nacionalidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `nivel`
--
ALTER TABLE `nivel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `nivel_academico`
--
ALTER TABLE `nivel_academico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `nivel_asignatura`
--
ALTER TABLE `nivel_asignatura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `operadoras`
--
ALTER TABLE `operadoras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `perfil_medico`
--
ALTER TABLE `perfil_medico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `periodicidad_pago`
--
ALTER TABLE `periodicidad_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `periodos_academicos`
--
ALTER TABLE `periodos_academicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT de la tabla `persona_institucion`
--
ALTER TABLE `persona_institucion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;

--
-- AUTO_INCREMENT de la tabla `planes_entrenamiento`
--
ALTER TABLE `planes_entrenamiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plan_entrenamiento_ejercicios`
--
ALTER TABLE `plan_entrenamiento_ejercicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plan_entrenamiento_sesiones`
--
ALTER TABLE `plan_entrenamiento_sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plan_pago_matricula`
--
ALTER TABLE `plan_pago_matricula`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `producto_categorias`
--
ALTER TABLE `producto_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `producto_variantes`
--
ALTER TABLE `producto_variantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `socios`
--
ALTER TABLE `socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tallas`
--
ALTER TABLE `tallas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `telefonos`
--
ALTER TABLE `telefonos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `tipos_contacto`
--
ALTER TABLE `tipos_contacto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `tipo_alergias`
--
ALTER TABLE `tipo_alergias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `tipo_enfermedad`
--
ALTER TABLE `tipo_enfermedad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `tipo_estado_fisico`
--
ALTER TABLE `tipo_estado_fisico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tipo_permiso`
--
ALTER TABLE `tipo_permiso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de la tabla `tipo_persona`
--
ALTER TABLE `tipo_persona`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `torneos`
--
ALTER TABLE `torneos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `torneo_participantes`
--
ALTER TABLE `torneo_participantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `ventas_externas`
--
ALTER TABLE `ventas_externas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas_externas_detalle`
--
ALTER TABLE `ventas_externas_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `1` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  ADD CONSTRAINT `2` FOREIGN KEY (`categoria_deportiva`) REFERENCES `categorias_deportivas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `3` FOREIGN KEY (`nivel`) REFERENCES `nivel_asignatura` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `4` FOREIGN KEY (`periodo_academico`) REFERENCES `periodos_academicos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `5` FOREIGN KEY (`docente`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `actividades_matricula`
--
ALTER TABLE `actividades_matricula`
  ADD CONSTRAINT `FK_ACT_MAT_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  ADD CONSTRAINT `FK_ACT_MAT_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_ACT_MAT_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`),
  ADD CONSTRAINT `FK_ACT_MAT_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  ADD CONSTRAINT `FK_ACT_MAT_MODALIDAD` FOREIGN KEY (`modalidad`) REFERENCES `modalidades` (`id`);

--
-- Filtros para la tabla `alergias`
--
ALTER TABLE `alergias`
  ADD CONSTRAINT `FK1_ALERGIAS_TIPO_ALERGIAS` FOREIGN KEY (`tipo`) REFERENCES `tipo_alergias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_ALERGIAS_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_ALERGIAS_FICHA_MEDICA` FOREIGN KEY (`ficha_medica`) REFERENCES `fichas_medicas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  ADD CONSTRAINT `FK2_ASIGNATURA_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_ASIGNATURA_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `asignatura_clase`
--
ALTER TABLE `asignatura_clase`
  ADD CONSTRAINT `FK_ASIG_CLASE_ASIGNATURA` FOREIGN KEY (`asignatura`) REFERENCES `asignaturas` (`id`),
  ADD CONSTRAINT `FK_ASIG_CLASE_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  ADD CONSTRAINT `FK_ASIG_CLASE_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`);

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `FK_ASISTENCIAS_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  ADD CONSTRAINT `FK_ASISTENCIAS_CALENDARIO` FOREIGN KEY (`calendario`) REFERENCES `calendarios` (`id`),
  ADD CONSTRAINT `FK_ASISTENCIAS_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  ADD CONSTRAINT `FK_ASISTENCIAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_ASISTENCIAS_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`),
  ADD CONSTRAINT `FK_ASISTENCIAS_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  ADD CONSTRAINT `FK_ASISTENCIAS_PLAN_SESION` FOREIGN KEY (`plan_entrenamiento_sesion`) REFERENCES `plan_entrenamiento_sesiones` (`id`);

--
-- Filtros para la tabla `botones`
--
ALTER TABLE `botones`
  ADD CONSTRAINT `FK1_ESTADO_BOTON` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `boton_user`
--
ALTER TABLE `boton_user`
  ADD CONSTRAINT `FK1_BOTON_USER` FOREIGN KEY (`boton`) REFERENCES `botones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_TYPE_USER` FOREIGN KEY (`type_user`) REFERENCES `type_users` (`nemonico`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_BOTON_USER_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `calendarios`
--
ALTER TABLE `calendarios`
  ADD CONSTRAINT `FK_CALENDARIOS_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  ADD CONSTRAINT `FK_CALENDARIOS_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  ADD CONSTRAINT `FK_CALENDARIOS_ENTRENADOR` FOREIGN KEY (`entrenador`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `FK_CALENDARIOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_CALENDARIOS_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`),
  ADD CONSTRAINT `FK_CALENDARIOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  ADD CONSTRAINT `FK_CALENDARIOS_PLAN` FOREIGN KEY (`plan_entrenamiento`) REFERENCES `planes_entrenamiento` (`id`);

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `FK_CALIFICACIONES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_CALIFICACIONES_EVALUACION` FOREIGN KEY (`evaluacion`) REFERENCES `evaluaciones` (`id`),
  ADD CONSTRAINT `FK_CALIFICACIONES_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`);

--
-- Filtros para la tabla `categorias_deportivas`
--
ALTER TABLE `categorias_deportivas`
  ADD CONSTRAINT `FK_CATEGORIAS_DEPORTE` FOREIGN KEY (`deporte`) REFERENCES `deportes` (`id`),
  ADD CONSTRAINT `FK_CATEGORIAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_CATEGORIAS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `clases`
--
ALTER TABLE `clases`
  ADD CONSTRAINT `FK1_NIVEL_CLASES` FOREIGN KEY (`nivel`) REFERENCES `nivel_asignatura` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_ASIGNATURA_CLASES` FOREIGN KEY (`asignatura`) REFERENCES `asignaturas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_INSTITUCION_CLASES` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_CLASES_DOCENTE` FOREIGN KEY (`docente`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `FK_CLASES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_CLASES_PERIODO` FOREIGN KEY (`periodo_academico`) REFERENCES `periodos_academicos` (`id`);

--
-- Filtros para la tabla `clases_matricula`
--
ALTER TABLE `clases_matricula`
  ADD CONSTRAINT `FK1_CLASE_CLASE_MATRICULA` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_MATRICULA_CLASE_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_ESTADO_CLASE_MATRICULA` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK4_MODALIDAD_CLASES_MATRICULA` FOREIGN KEY (`modalidad`) REFERENCES `modalidades` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK5_CONTRATO_CLASES_MATRICULA` FOREIGN KEY (`contrato`) REFERENCES `contratacion` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_CLASE_MATRICULA_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`);

--
-- Filtros para la tabla `contratacion`
--
ALTER TABLE `contratacion`
  ADD CONSTRAINT `FK1_PERSONA_CONTRATO` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_PERSONA_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel_academico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_PERSONA_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `correos`
--
ALTER TABLE `correos`
  ADD CONSTRAINT `FK1_ESTADO_MAIL` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_TIPO_CONTACT` FOREIGN KEY (`tipo`) REFERENCES `tipos_contacto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `cuotas_matricula`
--
ALTER TABLE `cuotas_matricula`
  ADD CONSTRAINT `FK_CUOTAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_CUOTAS_PLAN_PAGO` FOREIGN KEY (`plan_pago`) REFERENCES `plan_pago_matricula` (`id`);

--
-- Filtros para la tabla `deportes`
--
ALTER TABLE `deportes`
  ADD CONSTRAINT `FK1_INSTITUCION_DEPORTE` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_ESTADO_DEPORTE` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `enfermedades`
--
ALTER TABLE `enfermedades`
  ADD CONSTRAINT `FK1_ENFERMEDAD_TIPO_ENFERMEDAD` FOREIGN KEY (`tipo`) REFERENCES `tipo_enfermedad` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_ENFERMEDAD_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `enfermedad_persona`
--
ALTER TABLE `enfermedad_persona`
  ADD CONSTRAINT `FK1_ENFERMEDAD_PERSONA` FOREIGN KEY (`enfermedad`) REFERENCES `enfermedades` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `estado_fisico`
--
ALTER TABLE `estado_fisico`
  ADD CONSTRAINT `FK1_ESTADO_FISICO_PERFIL_MEDICO` FOREIGN KEY (`perfil_medico`) REFERENCES `perfil_medico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_ESTADO_FISICO_TIPO_ESTADOS` FOREIGN KEY (`tipo`) REFERENCES `tipo_estado_fisico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  ADD CONSTRAINT `FK_EVALUACIONES_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  ADD CONSTRAINT `FK_EVALUACIONES_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  ADD CONSTRAINT `FK_EVALUACIONES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`);

--
-- Filtros para la tabla `fichas_medicas`
--
ALTER TABLE `fichas_medicas`
  ADD CONSTRAINT `FK1_FICHA_MEDICA_PERFIL_MEDICO` FOREIGN KEY (`perfil_medico`) REFERENCES `perfil_medico` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_FICHAS_MEDICAS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`);

--
-- Filtros para la tabla `formas_pago`
--
ALTER TABLE `formas_pago`
  ADD CONSTRAINT `FK_FORMAS_PAGO_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`);

--
-- Filtros para la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `FK_GASTOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_GASTOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  ADD CONSTRAINT `FK_GASTOS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`);

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `FK_HORARIOS_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  ADD CONSTRAINT `FK_HORARIOS_CATEGORIA` FOREIGN KEY (`categoria_deportiva`) REFERENCES `categorias_deportivas` (`id`),
  ADD CONSTRAINT `FK_HORARIOS_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  ADD CONSTRAINT `FK_HORARIOS_ENTRENADOR` FOREIGN KEY (`entrenador`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `FK_HORARIOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_HORARIOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `incidencia`
--
ALTER TABLE `incidencia`
  ADD CONSTRAINT `FK1_INCIDENCIA_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_INCIDENCIA_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD CONSTRAINT `FK1_ESTADO_INSTITUCION` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `FK_INVENTARIO_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  ADD CONSTRAINT `FK_INVENTARIO_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `FK_INVENTARIO_VARIANTE` FOREIGN KEY (`variante`) REFERENCES `producto_variantes` (`id`);

--
-- Filtros para la tabla `matricula`
--
ALTER TABLE `matricula`
  ADD CONSTRAINT `FK1_MATRICULA_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_MATRICULA_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_MATRICULA_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK4_MATRICULA_MEMBRESIA` FOREIGN KEY (`membresia`) REFERENCES `membresias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `membresias`
--
ALTER TABLE `membresias`
  ADD CONSTRAINT `FK1_ESTADO_MEMBRESIA` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_MEMBRESIAS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `FK_MOV_INV_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  ADD CONSTRAINT `FK_MOV_INV_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `FK_MOV_INV_VARIANTE` FOREIGN KEY (`variante`) REFERENCES `producto_variantes` (`id`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `FK_PAGOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_PAGOS_FORMA_PAGO` FOREIGN KEY (`forma_pago`) REFERENCES `formas_pago` (`id`),
  ADD CONSTRAINT `FK_PAGOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  ADD CONSTRAINT `FK_PAGOS_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  ADD CONSTRAINT `FK_PAGOS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `FK_PAGOS_VENTA` FOREIGN KEY (`venta`) REFERENCES `ventas` (`id`);

--
-- Filtros para la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  ADD CONSTRAINT `FK_PAGO_DETALLE_CUOTA` FOREIGN KEY (`cuota_matricula`) REFERENCES `cuotas_matricula` (`id`),
  ADD CONSTRAINT `FK_PAGO_DETALLE_PAGO` FOREIGN KEY (`pago`) REFERENCES `pagos` (`id`),
  ADD CONSTRAINT `FK_PAGO_DETALLE_VENTA` FOREIGN KEY (`venta`) REFERENCES `ventas` (`id`);

--
-- Filtros para la tabla `perfil_medico`
--
ALTER TABLE `perfil_medico`
  ADD CONSTRAINT `FK_PERFIL_MEDICO_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_PERFIL_MEDICO_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `periodicidad_pago`
--
ALTER TABLE `periodicidad_pago`
  ADD CONSTRAINT `FK_PERIODICIDAD_PAGO_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`);

--
-- Filtros para la tabla `periodos_academicos`
--
ALTER TABLE `periodos_academicos`
  ADD CONSTRAINT `FK_PERIODOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_PERIODOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `FK1_PERMISO_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_PERMISO_TIPO_PERMISO` FOREIGN KEY (`tipo`) REFERENCES `tipo_permiso` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_PERMISO_ESTADO_PERMISO` FOREIGN KEY (`estado`) REFERENCES `estado_permiso` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_PERMISOS_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  ADD CONSTRAINT `FK_PERMISOS_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  ADD CONSTRAINT `FK_PERMISOS_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`);

--
-- Filtros para la tabla `personas`
--
ALTER TABLE `personas`
  ADD CONSTRAINT `FK1_NACIONALIDAD` FOREIGN KEY (`nacionalidad`) REFERENCES `nacionalidades` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_USER` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_GENERO` FOREIGN KEY (`genero`) REFERENCES `generos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK4_ETNIA` FOREIGN KEY (`etnia`) REFERENCES `etnias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK5_CIUDAD` FOREIGN KEY (`ciudad`) REFERENCES `ciudades` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `persona_institucion`
--
ALTER TABLE `persona_institucion`
  ADD CONSTRAINT `FK1_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_TIPO_PERSONA` FOREIGN KEY (`tipo`) REFERENCES `tipo_persona` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_PERSONA` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK4_INSTITUCION` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `planes_entrenamiento`
--
ALTER TABLE `planes_entrenamiento`
  ADD CONSTRAINT `FK_PLAN_ENT_ACTIVIDAD` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  ADD CONSTRAINT `FK_PLAN_ENT_CATEGORIA` FOREIGN KEY (`categoria_deportiva`) REFERENCES `categorias_deportivas` (`id`),
  ADD CONSTRAINT `FK_PLAN_ENT_CLASE` FOREIGN KEY (`clase`) REFERENCES `clases` (`id`),
  ADD CONSTRAINT `FK_PLAN_ENT_ENTRENADOR` FOREIGN KEY (`entrenador`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `FK_PLAN_ENT_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_PLAN_ENT_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  ADD CONSTRAINT `FK_PLAN_ENT_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  ADD CONSTRAINT `FK_PLAN_ENT_NIVEL` FOREIGN KEY (`nivel`) REFERENCES `nivel_asignatura` (`id`);

--
-- Filtros para la tabla `plan_entrenamiento_ejercicios`
--
ALTER TABLE `plan_entrenamiento_ejercicios`
  ADD CONSTRAINT `FK_PLAN_EJERCICIOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_PLAN_EJERCICIOS_SESION` FOREIGN KEY (`sesion`) REFERENCES `plan_entrenamiento_sesiones` (`id`);

--
-- Filtros para la tabla `plan_entrenamiento_sesiones`
--
ALTER TABLE `plan_entrenamiento_sesiones`
  ADD CONSTRAINT `FK_PLAN_SESIONES_CALENDARIO` FOREIGN KEY (`calendario`) REFERENCES `calendarios` (`id`),
  ADD CONSTRAINT `FK_PLAN_SESIONES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_PLAN_SESIONES_HORARIO` FOREIGN KEY (`horario`) REFERENCES `horarios` (`id`),
  ADD CONSTRAINT `FK_PLAN_SESIONES_PLAN` FOREIGN KEY (`plan_entrenamiento`) REFERENCES `planes_entrenamiento` (`id`);

--
-- Filtros para la tabla `plan_pago_matricula`
--
ALTER TABLE `plan_pago_matricula`
  ADD CONSTRAINT `FK_PLAN_PAGO_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_PLAN_PAGO_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  ADD CONSTRAINT `FK_PLAN_PAGO_PERIODICIDAD` FOREIGN KEY (`periodicidad`) REFERENCES `periodicidad_pago` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `FK_PRODUCTOS_CATEGORIA` FOREIGN KEY (`categoria`) REFERENCES `producto_categorias` (`id`),
  ADD CONSTRAINT `FK_PRODUCTOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_PRODUCTOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `producto_categorias`
--
ALTER TABLE `producto_categorias`
  ADD CONSTRAINT `FK_PRODUCTO_CATEGORIAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_PRODUCTO_CATEGORIAS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `producto_variantes`
--
ALTER TABLE `producto_variantes`
  ADD CONSTRAINT `FK_PRODUCTO_VARIANTES_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_PRODUCTO_VARIANTES_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `tallas`
--
ALTER TABLE `tallas`
  ADD CONSTRAINT `FK_TALLAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_TALLAS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`);

--
-- Filtros para la tabla `telefonos`
--
ALTER TABLE `telefonos`
  ADD CONSTRAINT `FK1_PERSONA_TELEFONO` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK2_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK3_TIPO_CONTACTO` FOREIGN KEY (`tipo`) REFERENCES `tipos_contacto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK4_OPERADORA` FOREIGN KEY (`operadora`) REFERENCES `operadoras` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `torneos`
--
ALTER TABLE `torneos`
  ADD CONSTRAINT `FK_TORNEOS_DEPORTE` FOREIGN KEY (`deporte`) REFERENCES `deportes` (`id`),
  ADD CONSTRAINT `FK_TORNEOS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_TORNEOS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `torneo_participantes`
--
ALTER TABLE `torneo_participantes`
  ADD CONSTRAINT `FK_TORNEO_PART_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_TORNEO_PART_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  ADD CONSTRAINT `FK_TORNEO_PART_TORNEO` FOREIGN KEY (`torneo`) REFERENCES `torneos` (`id`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK1_TYPE_USERS` FOREIGN KEY (`type`) REFERENCES `type_users` (`nemonico`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `FK_VENTAS_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_VENTAS_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`),
  ADD CONSTRAINT `FK_VENTAS_MATRICULA` FOREIGN KEY (`matricula`) REFERENCES `matricula` (`id`),
  ADD CONSTRAINT `FK_VENTAS_PERSONA` FOREIGN KEY (`persona`) REFERENCES `personas` (`id`);

--
-- Filtros para la tabla `ventas_externas`
--
ALTER TABLE `ventas_externas`
  ADD CONSTRAINT `FK_VE_ESTADO` FOREIGN KEY (`estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `FK_VE_FORMA_PAGO` FOREIGN KEY (`forma_pago`) REFERENCES `formas_pago` (`id`),
  ADD CONSTRAINT `FK_VE_INSTITUCION` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`id`);

--
-- Filtros para la tabla `ventas_externas_detalle`
--
ALTER TABLE `ventas_externas_detalle`
  ADD CONSTRAINT `FK_VED_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `FK_VED_VENTA_EXTERNA` FOREIGN KEY (`venta_externa`) REFERENCES `ventas_externas` (`id`);

--
-- Filtros para la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD CONSTRAINT `FK_VENTA_DETALLE_PRODUCTO` FOREIGN KEY (`producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `FK_VENTA_DETALLE_VARIANTE` FOREIGN KEY (`variante`) REFERENCES `producto_variantes` (`id`),
  ADD CONSTRAINT `FK_VENTA_DETALLE_VENTA` FOREIGN KEY (`venta`) REFERENCES `ventas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
