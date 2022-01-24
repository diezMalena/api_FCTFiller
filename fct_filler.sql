-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 20-01-2022 a las 11:19:34
-- Versión del servidor: 8.0.27-0ubuntu0.20.04.1
-- Versión de PHP: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fct_filler`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `localidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provincia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `horario` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_horas` int NOT NULL,
  `fecha_ini` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `cif` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_curso` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno_curso`
--

CREATE TABLE `alumno_curso` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_curso` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `matricula` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno_materia`
--

CREATE TABLE `alumno_materia` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_materia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aprobado` tinyint(1) NOT NULL,
  `curso_academico` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_ciclo`
--

CREATE TABLE `centro_ciclo` (
  `cod_ciclo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_centro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_estudios`
--

CREATE TABLE `centro_estudios` (
  `cod_centro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provincia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_postal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cif` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni_director` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_jefe_estudios`
--

CREATE TABLE `centro_jefe_estudios` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_centro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_trabajo`
--

CREATE TABLE `centro_trabajo` (
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provincia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `localidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cif_empresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciclo`
--

CREATE TABLE `ciclo` (
  `cod_ciclo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso`
--

CREATE TABLE `curso` (
  `cod_curso` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estudio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni_tutor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `cif` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provincia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `localidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa_centro_estudios`
--

CREATE TABLE `empresa_centro_estudios` (
  `convenio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `cod_centro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cif_empresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia`
--

CREATE TABLE `materia` (
  `cod_materia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abreviatura` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia_curso`
--

CREATE TABLE `materia_curso` (
  `cod_curso` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_materia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2022_01_19_083043_roles_empresa', 1),
(2, '2022_01_19_083053_roles_estudio', 2),
(3, '2022_01_19_082914_empresa', 3),
(4, '2022_01_19_083033_profesor', 4),
(6, '2022_01_19_082859_ciclo', 6),
(7, '2022_01_19_083105_rol_profesor_asignado', 7),
(8, '2022_01_19_082907_curso', 8),
(9, '2022_01_19_083015_materia', 9),
(10, '2022_01_19_083025_materia_curso', 10),
(11, '2022_01_19_082850_centro_trabajo', 11),
(12, '2022_01_19_083145_trabajador', 12),
(13, '2022_01_19_083135_rol_trabajador_asignado', 13),
(14, '2022_01_19_082942_centro_estudios', 14),
(15, '2022_01_19_082839_centro_ciclo', 15),
(16, '2022_01_19_083005_empresa_centro_estudios', 16),
(17, '2022_01_19_082623_alumno', 17),
(18, '2022_01_19_082806_alumno_curso', 18),
(19, '2022_01_19_082818_alumno_materia', 19),
(20, '2022_01_19_212801_centro_jefe_estudios', 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor`
--

CREATE TABLE `profesor` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_empresa`
--

CREATE TABLE `roles_empresa` (
  `id` bigint UNSIGNED NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_estudio`
--

CREATE TABLE `roles_estudio` (
  `id` bigint UNSIGNED NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_profesor_asignado`
--

CREATE TABLE `rol_profesor_asignado` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_rol` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_trabajador_asignado`
--

CREATE TABLE `rol_trabajador_asignado` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_rol` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajador`
--

CREATE TABLE `trabajador` (
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cif_empresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_centro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`dni`),
  ADD KEY `alumno_cif_foreign` (`cif`),
  ADD KEY `alumno_cod_curso_foreign` (`cod_curso`);

--
-- Indices de la tabla `alumno_curso`
--
ALTER TABLE `alumno_curso`
  ADD PRIMARY KEY (`dni`),
  ADD KEY `alumno_curso_cod_curso_foreign` (`cod_curso`);

--
-- Indices de la tabla `alumno_materia`
--
ALTER TABLE `alumno_materia`
  ADD PRIMARY KEY (`dni`),
  ADD KEY `alumno_materia_cod_materia_foreign` (`cod_materia`);

--
-- Indices de la tabla `centro_ciclo`
--
ALTER TABLE `centro_ciclo`
  ADD PRIMARY KEY (`cod_centro`,`cod_ciclo`),
  ADD KEY `centro_ciclo_cod_ciclo_foreign` (`cod_ciclo`);

--
-- Indices de la tabla `centro_estudios`
--
ALTER TABLE `centro_estudios`
  ADD PRIMARY KEY (`cod_centro`),
  ADD KEY `centro_estudios_dni_director_foreign` (`dni_director`);

--
-- Indices de la tabla `centro_jefe_estudios`
--
ALTER TABLE `centro_jefe_estudios`
  ADD PRIMARY KEY (`dni`,`cod_centro`),
  ADD KEY `centro_jefe_estudios_cod_centro_foreign` (`cod_centro`);

--
-- Indices de la tabla `centro_trabajo`
--
ALTER TABLE `centro_trabajo`
  ADD PRIMARY KEY (`cif_empresa`,`nombre`),
  ADD KEY `centro_trabajo_nombre_index` (`nombre`);

--
-- Indices de la tabla `ciclo`
--
ALTER TABLE `ciclo`
  ADD PRIMARY KEY (`cod_ciclo`);

--
-- Indices de la tabla `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`cod_curso`),
  ADD KEY `curso_dni_tutor_foreign` (`dni_tutor`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`cif`);

--
-- Indices de la tabla `empresa_centro_estudios`
--
ALTER TABLE `empresa_centro_estudios`
  ADD PRIMARY KEY (`cod_centro`,`cif_empresa`),
  ADD KEY `empresa_centro_estudios_cif_empresa_foreign` (`cif_empresa`);

--
-- Indices de la tabla `materia`
--
ALTER TABLE `materia`
  ADD PRIMARY KEY (`cod_materia`);

--
-- Indices de la tabla `materia_curso`
--
ALTER TABLE `materia_curso`
  ADD PRIMARY KEY (`cod_curso`,`cod_materia`),
  ADD KEY `materia_curso_cod_materia_foreign` (`cod_materia`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `profesor`
--
ALTER TABLE `profesor`
  ADD PRIMARY KEY (`dni`);

--
-- Indices de la tabla `roles_empresa`
--
ALTER TABLE `roles_empresa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles_estudio`
--
ALTER TABLE `roles_estudio`
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
  ADD KEY `trabajador_cif_empresa_foreign` (`cif_empresa`),
  ADD KEY `trabajador_nombre_centro_foreign` (`nombre_centro`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `roles_empresa`
--
ALTER TABLE `roles_empresa`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles_estudio`
--
ALTER TABLE `roles_estudio`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD CONSTRAINT `alumno_cif_foreign` FOREIGN KEY (`cif`) REFERENCES `empresa` (`cif`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `alumno_cod_curso_foreign` FOREIGN KEY (`cod_curso`) REFERENCES `curso` (`cod_curso`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `alumno_curso`
--
ALTER TABLE `alumno_curso`
  ADD CONSTRAINT `alumno_curso_cod_curso_foreign` FOREIGN KEY (`cod_curso`) REFERENCES `curso` (`cod_curso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `alumno_curso_dni_foreign` FOREIGN KEY (`dni`) REFERENCES `alumno` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `alumno_materia`
--
ALTER TABLE `alumno_materia`
  ADD CONSTRAINT `alumno_materia_cod_materia_foreign` FOREIGN KEY (`cod_materia`) REFERENCES `materia` (`cod_materia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `alumno_materia_dni_foreign` FOREIGN KEY (`dni`) REFERENCES `alumno` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `centro_ciclo`
--
ALTER TABLE `centro_ciclo`
  ADD CONSTRAINT `centro_ciclo_cod_centro_foreign` FOREIGN KEY (`cod_centro`) REFERENCES `centro_estudios` (`cod_centro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `centro_ciclo_cod_ciclo_foreign` FOREIGN KEY (`cod_ciclo`) REFERENCES `ciclo` (`cod_ciclo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `centro_estudios`
--
ALTER TABLE `centro_estudios`
  ADD CONSTRAINT `centro_estudios_dni_director_foreign` FOREIGN KEY (`dni_director`) REFERENCES `profesor` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `centro_jefe_estudios`
--
ALTER TABLE `centro_jefe_estudios`
  ADD CONSTRAINT `centro_jefe_estudios_cod_centro_foreign` FOREIGN KEY (`cod_centro`) REFERENCES `centro_estudios` (`cod_centro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `centro_jefe_estudios_dni_foreign` FOREIGN KEY (`dni`) REFERENCES `profesor` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `centro_trabajo`
--
ALTER TABLE `centro_trabajo`
  ADD CONSTRAINT `centro_trabajo_cif_empresa_foreign` FOREIGN KEY (`cif_empresa`) REFERENCES `empresa` (`cif`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `curso`
--
ALTER TABLE `curso`
  ADD CONSTRAINT `curso_dni_tutor_foreign` FOREIGN KEY (`dni_tutor`) REFERENCES `profesor` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `empresa_centro_estudios`
--
ALTER TABLE `empresa_centro_estudios`
  ADD CONSTRAINT `empresa_centro_estudios_cif_empresa_foreign` FOREIGN KEY (`cif_empresa`) REFERENCES `empresa` (`cif`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `empresa_centro_estudios_cod_centro_foreign` FOREIGN KEY (`cod_centro`) REFERENCES `centro_estudios` (`cod_centro`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `materia_curso`
--
ALTER TABLE `materia_curso`
  ADD CONSTRAINT `materia_curso_cod_curso_foreign` FOREIGN KEY (`cod_curso`) REFERENCES `curso` (`cod_curso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `materia_curso_cod_materia_foreign` FOREIGN KEY (`cod_materia`) REFERENCES `materia` (`cod_materia`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_profesor_asignado`
--
ALTER TABLE `rol_profesor_asignado`
  ADD CONSTRAINT `rol_profesor_asignado_dni_foreign` FOREIGN KEY (`dni`) REFERENCES `profesor` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rol_profesor_asignado_id_rol_foreign` FOREIGN KEY (`id_rol`) REFERENCES `roles_estudio` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_trabajador_asignado`
--
ALTER TABLE `rol_trabajador_asignado`
  ADD CONSTRAINT `rol_trabajador_asignado_dni_foreign` FOREIGN KEY (`dni`) REFERENCES `trabajador` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rol_trabajador_asignado_id_rol_foreign` FOREIGN KEY (`id_rol`) REFERENCES `roles_empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD CONSTRAINT `trabajador_cif_empresa_foreign` FOREIGN KEY (`cif_empresa`) REFERENCES `empresa` (`cif`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `trabajador_nombre_centro_foreign` FOREIGN KEY (`nombre_centro`) REFERENCES `centro_trabajo` (`nombre`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
