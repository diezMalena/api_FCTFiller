-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-02-2022 a las 10:30:09
-- Versión del servidor: 10.4.22-MariaDB
-- Versión de PHP: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fct_filler_new`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provincia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `localidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `va_a_fct` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alumno`
--

INSERT INTO `alumno` (`dni`, `email`, `password`, `nombre`, `apellidos`, `provincia`, `localidad`, `va_a_fct`, `created_at`, `updated_at`) VALUES
('10x', 'david@mail.com', '12345', 'David', 'Alumno 1', 'Ciudad Real', 'Puertollano', 0, '2022-02-02 21:42:20', '2022-02-02 21:42:20'),
('11x', 'malena@mail.com', '12345', 'Malena', 'Alumno 2', 'Ciudad Real', 'Puertollano', 0, '2022-02-02 21:42:20', '2022-02-02 21:42:20'),
('12x', 'alvaro@mail.com', '12345', 'Álvaro', 'Alumno 3', 'Ciudad Real', 'Puertollano', 1, '2022-02-02 21:42:20', '2022-02-02 21:42:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aux_convenio`
--

CREATE TABLE `aux_convenio` (
  `id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aux_curso_academico`
--

CREATE TABLE `aux_curso_academico` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cod_curso` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `aux_curso_academico`
--

INSERT INTO `aux_curso_academico` (`id`, `cod_curso`, `fecha_inicio`, `fecha_fin`, `created_at`, `updated_at`) VALUES
(1, '21/22', '2021-09-01', '2022-09-01', '2022-02-03 08:23:59', '2022-02-03 08:23:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_estudios`
--

CREATE TABLE `centro_estudios` (
  `cod` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cif` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_centro_convenio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `localidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provincia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `centro_estudios`
--

INSERT INTO `centro_estudios` (`cod`, `cif`, `cod_centro_convenio`, `nombre`, `localidad`, `provincia`, `direccion`, `cp`, `telefono`, `email`, `created_at`, `updated_at`) VALUES
('1350090894', 'H785B786', 'x', 'CIFP Virgen de Gracia', 'Puertollano', 'Ciudad Real', 'Calle Copa 1', '13500', '625812584', 'cifpvg@mail.com', '2022-02-02 21:34:26', '2022-02-02 21:34:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `convenio`
--

CREATE TABLE `convenio` (
  `cod_convenio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_centro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_empresa` bigint(20) UNSIGNED NOT NULL,
  `curso_academico_inicio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `curso_academico_fin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firmado_director` int(11) NOT NULL,
  `firmado_empresa` int(11) NOT NULL,
  `ruta_anexo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `convenio`
--

INSERT INTO `convenio` (`cod_convenio`, `cod_centro`, `id_empresa`, `curso_academico_inicio`, `curso_academico_fin`, `firmado_director`, `firmado_empresa`, `ruta_anexo`, `created_at`, `updated_at`) VALUES
('AA', '1350090894', 1, '21/22', '24/25', 0, 0, '', '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
('AB', '1350090894', 2, '21/22', '24/25', 0, 0, '', '2022-02-02 21:34:26', '2022-02-02 21:34:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cif` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `localidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provincia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id`, `cif`, `nombre`, `telefono`, `email`, `localidad`, `provincia`, `direccion`, `cp`, `created_at`, `updated_at`) VALUES
(1, '12345', 'Empresa 1', '500900600', 'empresa1@mail.com', 'Puertollano', 'Ciudad Real', 'Calle de la llorería, 1', '13500', '2022-02-02 21:31:27', '2022-02-02 21:31:27'),
(2, '12346', 'Empresa 2', '500900630', 'empresa2@mail.com', 'Almodovar del Campo', 'Ciudad Real', 'Calle de la abundancia, 1', '13280', '2022-02-02 21:31:27', '2022-02-02 21:31:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa_grupo`
--

CREATE TABLE `empresa_grupo` (
  `id_empresa` bigint(20) UNSIGNED NOT NULL,
  `cod_grupo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empresa_grupo`
--

INSERT INTO `empresa_grupo` (`id_empresa`, `cod_grupo`, `created_at`, `updated_at`) VALUES
(1, '2DAM', '2022-02-03 08:23:59', '2022-02-03 08:23:59'),
(1, '2DAW', '2022-02-03 08:23:59', '2022-02-03 08:23:59'),
(2, '2GAC', '2022-02-03 08:23:59', '2022-02-03 08:23:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `familia_profesional`
--

CREATE TABLE `familia_profesional` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `familia_profesional`
--

INSERT INTO `familia_profesional` (`id`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Actividades físicas y deportivas', '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
(2, 'Administración y gestión', '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
(3, 'Comercio y marketing', '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
(4, 'Informática y comunicaciones', '2022-02-02 21:34:26', '2022-02-02 21:34:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fct`
--

CREATE TABLE `fct` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_empresa` bigint(20) UNSIGNED NOT NULL,
  `dni_alumno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni_tutor_empresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `curso_academico` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `horario` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_horas` int(11) NOT NULL,
  `fecha_ini` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `firmado_director` int(11) NOT NULL,
  `firmado_empresa` int(11) NOT NULL,
  `ruta_anexo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fct`
--

INSERT INTO `fct` (`id`, `id_empresa`, `dni_alumno`, `dni_tutor_empresa`, `curso_academico`, `horario`, `num_horas`, `fecha_ini`, `fecha_fin`, `firmado_director`, `firmado_empresa`, `ruta_anexo`, `created_at`, `updated_at`) VALUES
(1, 1, '10x', '1a', '21/22', '08:00 a 14:00 y 15:00 a 18:00', 400, '2022-02-02', '2022-02-02', 0, 0, '', '2022-02-02 21:48:04', '2022-02-02 21:48:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo`
--

CREATE TABLE `grupo` (
  `cod` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_largo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_ciclo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_familia_profesional` bigint(20) UNSIGNED NOT NULL,
  `cod_nivel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupo`
--

INSERT INTO `grupo` (`cod`, `nombre_largo`, `nombre_ciclo`, `cod_familia_profesional`, `cod_nivel`, `created_at`, `updated_at`) VALUES
('2DAM', '2º Desarrollo de Aplicaciones Multiplataforma (LOE)', 'Desarrollo de Aplicaciones Multiplataforma', 4, 'CFGS', '2022-02-02 21:41:32', '2022-02-02 21:41:32'),
('2DAW', '2º Desarrollo de Aplicaciones Web (LOE)', 'Desarrollo de Aplicaciones Web', 4, 'CFGS', '2022-02-02 21:41:32', '2022-02-02 21:41:32'),
('2GAC', '2º Gestión de Actividades Comerciales (LOE)', 'Gestión de Actividades Comerciales', 3, 'CFGM', '2022-02-02 21:41:32', '2022-02-02 21:41:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matricula`
--

CREATE TABLE `matricula` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cod_centro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni_alumno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_grupo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `curso_academico` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `matricula`
--

INSERT INTO `matricula` (`id`, `cod_centro`, `dni_alumno`, `cod_grupo`, `curso_academico`, `created_at`, `updated_at`) VALUES
(1, '1350090894', '10x', '2DAM', '21/22', '2022-02-02 21:42:53', '2022-02-02 21:42:53'),
(2, '1350090894', '11x', '2DAM', '20/21', '2022-02-02 21:42:53', '2022-02-02 21:42:53'),
(3, '1350090894', '12x', '2DAW', '21/22', '2022-02-02 21:42:53', '2022-02-02 21:42:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2022_01_19_083043_create_rol_empresa', 1),
(2, '2022_01_19_083053_create_rol_profesor', 2),
(3, '2022_01_19_082914_create_empresa', 3),
(4, '2022_01_19_083145_create_trabajador', 4),
(5, '2022_01_19_083135_create_rol_trabajador_asignado', 5),
(6, '2022_01_19_082942_create_centro_estudios', 6),
(7, '2022_01_19_083033_create_profesor', 7),
(8, '2022_01_19_083105_create_rol_profesor_asignado', 8),
(9, '2022_01_25_123542_create_aux_convenio', 9),
(10, '2022_01_19_083005_create_convenio', 10),
(11, '2022_02_01_185127_create_familia_profesional', 11),
(12, '2022_02_01_190818_create_nivel_estudios', 12),
(13, '2022_01_19_082907_create_grupo', 13),
(14, '2022_01_19_082839_create_oferta_grupo', 14),
(15, '2022_01_19_082623_create_alumno', 15),
(16, '2022_01_19_082806_create_matricula', 16),
(17, '2022_02_02_002148_create_tutoria_table', 17),
(19, '2022_01_25_214558_create_empresa_grupo', 18),
(20, '2022_02_03_084828_create_aux_curso_academico', 19);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivel_estudios`
--

CREATE TABLE `nivel_estudios` (
  `cod` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `nivel_estudios`
--

INSERT INTO `nivel_estudios` (`cod`, `descripcion`, `created_at`, `updated_at`) VALUES
('CFGB', 'Ciclo formativo de Grado Básico', '2022-02-02 21:41:32', '2022-02-02 21:41:32'),
('CFGM', 'Ciclo formativo de Grado Medio', '2022-02-02 21:41:32', '2022-02-02 21:41:32'),
('CFGS', 'Ciclo formativo de Grado Superior', '2022-02-02 21:41:32', '2022-02-02 21:41:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oferta_grupo`
--

CREATE TABLE `oferta_grupo` (
  `cod_centro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_grupo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `oferta_grupo`
--

INSERT INTO `oferta_grupo` (`cod_centro`, `cod_grupo`, `created_at`, `updated_at`) VALUES
('1350090894', '2DAM', '2022-02-02 21:42:20', '2022-02-02 21:42:20'),
('1350090894', '2DAW', '2022-02-02 21:42:20', '2022-02-02 21:42:20'),
('1350090894', '2GAC', '2022-02-02 21:42:20', '2022-02-02 21:42:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor`
--

CREATE TABLE `profesor` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_centro_estudios` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `profesor`
--

INSERT INTO `profesor` (`dni`, `email`, `password`, `nombre`, `apellidos`, `cod_centro_estudios`, `created_at`, `updated_at`) VALUES
('3c', 'diego@mail.com', '12345', 'Diego', 'Tutor', '1350090894', '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
('4d', 'irene@mail.com', '12345', 'Irene', 'JefaEstudios', '1350090894', '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
('5e', 'ana@mail.com', '12345', 'Ana Belén', 'Directora', '1350090894', '2022-02-02 21:34:26', '2022-02-02 21:34:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_empresa`
--

CREATE TABLE `rol_empresa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol_empresa`
--

INSERT INTO `rol_empresa` (`id`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'JefazoMaximo', '2022-02-02 21:31:27', '2022-02-02 21:31:27'),
(2, 'ResponsableCentro', '2022-02-02 21:31:27', '2022-02-02 21:31:27'),
(3, 'Tutor', '2022-02-02 21:31:27', '2022-02-02 21:31:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_profesor`
--

CREATE TABLE `rol_profesor` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol_profesor`
--

INSERT INTO `rol_profesor` (`id`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Director', '2022-02-02 21:31:27', '2022-02-02 21:31:27'),
(2, 'JefeEstudios', '2022-02-02 21:31:27', '2022-02-02 21:31:27'),
(3, 'Tutor', '2022-02-02 21:31:27', '2022-02-02 21:31:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_profesor_asignado`
--

CREATE TABLE `rol_profesor_asignado` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_rol` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol_profesor_asignado`
--

INSERT INTO `rol_profesor_asignado` (`dni`, `id_rol`, `created_at`, `updated_at`) VALUES
('3c', 3, '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
('4d', 3, '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
('5e', 3, '2022-02-02 21:34:26', '2022-02-02 21:34:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_trabajador_asignado`
--

CREATE TABLE `rol_trabajador_asignado` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_rol` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol_trabajador_asignado`
--

INSERT INTO `rol_trabajador_asignado` (`dni`, `id_rol`, `created_at`, `updated_at`) VALUES
('1a', 1, '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
('1a', 2, '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
('1a', 3, '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
('2b', 2, '2022-02-02 21:34:26', '2022-02-02 21:34:26'),
('2b', 3, '2022-02-02 21:34:26', '2022-02-02 21:34:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajador`
--

CREATE TABLE `trabajador` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_empresa` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trabajador`
--

INSERT INTO `trabajador` (`dni`, `email`, `password`, `nombre`, `apellidos`, `id_empresa`, `created_at`, `updated_at`) VALUES
('1a', 'trabajador1@mail.com', '12345', 'Juanito', 'Valderrama', 1, '2022-02-02 21:31:27', '2022-02-02 21:31:27'),
('2b', 'trabajador2@mail.com', '12345', 'Pepi', 'Valladares', 2, '2022-02-02 21:34:26', '2022-02-02 21:34:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tutoria`
--

CREATE TABLE `tutoria` (
  `dni_profesor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_grupo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `curso_academico` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tutoria`
--

INSERT INTO `tutoria` (`dni_profesor`, `cod_grupo`, `curso_academico`, `created_at`, `updated_at`) VALUES
('3c', '2DAW', '21/22', '2022-02-02 21:47:41', '2022-02-02 21:47:41'),
('4d', '2DAM', '21/22', '2022-02-02 21:48:04', '2022-02-02 21:48:04'),
('5e', '2GAC', '21/22', '2022-02-02 21:48:04', '2022-02-02 21:48:04');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`dni`),
  ADD UNIQUE KEY `alumno_email_unique` (`email`);

--
-- Indices de la tabla `aux_convenio`
--
ALTER TABLE `aux_convenio`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `aux_curso_academico`
--
ALTER TABLE `aux_curso_academico`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `centro_estudios`
--
ALTER TABLE `centro_estudios`
  ADD PRIMARY KEY (`cod`),
  ADD UNIQUE KEY `centro_estudios_cif_unique` (`cif`);

--
-- Indices de la tabla `convenio`
--
ALTER TABLE `convenio`
  ADD PRIMARY KEY (`cod_convenio`),
  ADD KEY `convenio_id_empresa_foreign` (`id_empresa`),
  ADD KEY `convenio_cod_centro_foreign` (`cod_centro`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `empresa_cif_unique` (`cif`);

--
-- Indices de la tabla `empresa_grupo`
--
ALTER TABLE `empresa_grupo`
  ADD PRIMARY KEY (`id_empresa`,`cod_grupo`),
  ADD KEY `empresa_grupo_cod_grupo_foreign` (`cod_grupo`);

--
-- Indices de la tabla `familia_profesional`
--
ALTER TABLE `familia_profesional`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `fct`
--
ALTER TABLE `fct`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fct_id_empresa_foreign` (`id_empresa`),
  ADD KEY `fct_dni_alumno_foreign` (`dni_alumno`);

--
-- Indices de la tabla `grupo`
--
ALTER TABLE `grupo`
  ADD PRIMARY KEY (`cod`),
  ADD KEY `grupo_cod_familia_profesional_foreign` (`cod_familia_profesional`),
  ADD KEY `grupo_cod_nivel_foreign` (`cod_nivel`);

--
-- Indices de la tabla `matricula`
--
ALTER TABLE `matricula`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matricula_cod_centro_foreign` (`cod_centro`),
  ADD KEY `matricula_dni_alumno_foreign` (`dni_alumno`),
  ADD KEY `matricula_cod_grupo_foreign` (`cod_grupo`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `nivel_estudios`
--
ALTER TABLE `nivel_estudios`
  ADD PRIMARY KEY (`cod`);

--
-- Indices de la tabla `oferta_grupo`
--
ALTER TABLE `oferta_grupo`
  ADD PRIMARY KEY (`cod_centro`,`cod_grupo`),
  ADD KEY `oferta_grupo_cod_grupo_foreign` (`cod_grupo`);

--
-- Indices de la tabla `profesor`
--
ALTER TABLE `profesor`
  ADD PRIMARY KEY (`dni`),
  ADD UNIQUE KEY `profesor_email_unique` (`email`),
  ADD KEY `profesor_cod_centro_estudios_foreign` (`cod_centro_estudios`);

--
-- Indices de la tabla `rol_empresa`
--
ALTER TABLE `rol_empresa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rol_profesor`
--
ALTER TABLE `rol_profesor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rol_profesor_asignado`
--
ALTER TABLE `rol_profesor_asignado`
  ADD PRIMARY KEY (`dni`,`id_rol`),
  ADD KEY `rol_profesor_asignado_id_rol_foreign` (`id_rol`);

--
-- Indices de la tabla `rol_trabajador_asignado`
--
ALTER TABLE `rol_trabajador_asignado`
  ADD PRIMARY KEY (`dni`,`id_rol`),
  ADD KEY `rol_trabajador_asignado_id_rol_foreign` (`id_rol`);

--
-- Indices de la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD PRIMARY KEY (`dni`),
  ADD UNIQUE KEY `trabajador_email_unique` (`email`),
  ADD KEY `trabajador_id_empresa_foreign` (`id_empresa`);

--
-- Indices de la tabla `tutoria`
--
ALTER TABLE `tutoria`
  ADD PRIMARY KEY (`dni_profesor`,`cod_grupo`,`curso_academico`),
  ADD KEY `tutoria_cod_grupo_foreign` (`cod_grupo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aux_convenio`
--
ALTER TABLE `aux_convenio`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aux_curso_academico`
--
ALTER TABLE `aux_curso_academico`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `familia_profesional`
--
ALTER TABLE `familia_profesional`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `fct`
--
ALTER TABLE `fct`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `matricula`
--
ALTER TABLE `matricula`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `rol_empresa`
--
ALTER TABLE `rol_empresa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rol_profesor`
--
ALTER TABLE `rol_profesor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `convenio`
--
ALTER TABLE `convenio`
  ADD CONSTRAINT `convenio_cod_centro_foreign` FOREIGN KEY (`cod_centro`) REFERENCES `centro_estudios` (`cod`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `convenio_id_empresa_foreign` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `empresa_grupo`
--
ALTER TABLE `empresa_grupo`
  ADD CONSTRAINT `empresa_grupo_cod_grupo_foreign` FOREIGN KEY (`cod_grupo`) REFERENCES `grupo` (`cod`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `empresa_grupo_id_empresa_foreign` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `fct`
--
ALTER TABLE `fct`
  ADD CONSTRAINT `fct_dni_alumno_foreign` FOREIGN KEY (`dni_alumno`) REFERENCES `alumno` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fct_id_empresa_foreign` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `grupo`
--
ALTER TABLE `grupo`
  ADD CONSTRAINT `grupo_cod_familia_profesional_foreign` FOREIGN KEY (`cod_familia_profesional`) REFERENCES `familia_profesional` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `grupo_cod_nivel_foreign` FOREIGN KEY (`cod_nivel`) REFERENCES `nivel_estudios` (`cod`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `matricula`
--
ALTER TABLE `matricula`
  ADD CONSTRAINT `matricula_cod_centro_foreign` FOREIGN KEY (`cod_centro`) REFERENCES `centro_estudios` (`cod`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `matricula_cod_grupo_foreign` FOREIGN KEY (`cod_grupo`) REFERENCES `grupo` (`cod`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `matricula_dni_alumno_foreign` FOREIGN KEY (`dni_alumno`) REFERENCES `alumno` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `oferta_grupo`
--
ALTER TABLE `oferta_grupo`
  ADD CONSTRAINT `oferta_grupo_cod_centro_foreign` FOREIGN KEY (`cod_centro`) REFERENCES `centro_estudios` (`cod`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `oferta_grupo_cod_grupo_foreign` FOREIGN KEY (`cod_grupo`) REFERENCES `grupo` (`cod`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `profesor`
--
ALTER TABLE `profesor`
  ADD CONSTRAINT `profesor_cod_centro_estudios_foreign` FOREIGN KEY (`cod_centro_estudios`) REFERENCES `centro_estudios` (`cod`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_profesor_asignado`
--
ALTER TABLE `rol_profesor_asignado`
  ADD CONSTRAINT `rol_profesor_asignado_dni_foreign` FOREIGN KEY (`dni`) REFERENCES `profesor` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rol_profesor_asignado_id_rol_foreign` FOREIGN KEY (`id_rol`) REFERENCES `rol_profesor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_trabajador_asignado`
--
ALTER TABLE `rol_trabajador_asignado`
  ADD CONSTRAINT `rol_trabajador_asignado_dni_foreign` FOREIGN KEY (`dni`) REFERENCES `trabajador` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rol_trabajador_asignado_id_rol_foreign` FOREIGN KEY (`id_rol`) REFERENCES `rol_empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD CONSTRAINT `trabajador_id_empresa_foreign` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tutoria`
--
ALTER TABLE `tutoria`
  ADD CONSTRAINT `tutoria_cod_grupo_foreign` FOREIGN KEY (`cod_grupo`) REFERENCES `grupo` (`cod`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tutoria_dni_profesor_foreign` FOREIGN KEY (`dni_profesor`) REFERENCES `profesor` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
