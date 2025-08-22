-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 16-08-2025 a las 15:54:41
-- Versión del servidor: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- Versión de PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `vigilante`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accesses`
--

CREATE TABLE `accesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `plate` varchar(255) DEFAULT NULL,
  `marca_vehiculo` varchar(255) DEFAULT NULL,
  `color_vehiculo` varchar(255) DEFAULT NULL,
  `tipo_vehiculo` varchar(255) DEFAULT NULL,
  `people_count` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `document` varchar(255) NOT NULL,
  `entry_at` datetime NOT NULL,
  `entry_note` text DEFAULT NULL,
  `vehicle_exit_at` datetime DEFAULT NULL,
  `exit_at` datetime DEFAULT NULL,
  `exit_note` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vehicle_exit_driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `accesses`
--

INSERT INTO `accesses` (`id`, `type`, `plate`, `marca_vehiculo`, `color_vehiculo`, `tipo_vehiculo`, `people_count`, `full_name`, `document`, `entry_at`, `entry_note`, `vehicle_exit_at`, `exit_at`, `exit_note`, `user_id`, `created_at`, `updated_at`, `vehicle_exit_driver_id`, `branch_id`) VALUES
(1, 'vehicle', 'AAEX-621', NULL, NULL, NULL, NULL, 'roseli bohado', '4801734', '2025-08-12 01:18:21', NULL, NULL, '2025-08-12 01:19:07', NULL, 1, '2025-08-12 04:18:21', '2025-08-12 04:19:07', NULL, 1),
(2, 'vehicle', 'AAEX-621', NULL, NULL, NULL, NULL, 'roseli bohado', '4801734', '2025-08-12 02:59:10', NULL, NULL, '2025-08-12 02:59:33', NULL, 1, '2025-08-12 05:59:10', '2025-08-12 05:59:33', NULL, 1),
(3, 'vehicle', 'AAEX-621', NULL, NULL, NULL, NULL, 'fulana', '12344556', '2025-08-12 03:01:10', NULL, NULL, '2025-08-12 03:01:56', NULL, 1, '2025-08-12 06:01:10', '2025-08-12 06:01:56', NULL, 1),
(4, 'pedestrian', 'AAEX-621', NULL, NULL, NULL, '2', 'roseli bohado', '4801734', '2025-08-12 03:03:09', NULL, NULL, '2025-08-12 03:05:02', NULL, 1, '2025-08-12 06:03:09', '2025-08-12 06:05:02', NULL, 1),
(5, 'pedestrian', NULL, NULL, NULL, NULL, NULL, 'fffff', '123456', '2025-08-12 01:06:19', NULL, NULL, '2025-08-12 01:06:49', NULL, 1, '2025-08-12 04:06:19', '2025-08-12 04:06:49', NULL, 1),
(6, 'pedestrian', NULL, NULL, NULL, NULL, NULL, 'sdsdsd', '123456', '2025-08-12 01:09:29', NULL, NULL, '2025-08-12 01:09:55', NULL, 1, '2025-08-12 04:09:29', '2025-08-12 04:09:56', NULL, 1),
(7, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'dffd', '123455', '2025-08-12 01:10:18', NULL, '2025-08-12 01:11:03', '2025-08-12 01:11:03', NULL, 1, '2025-08-12 04:10:18', '2025-08-12 04:11:03', NULL, 1),
(8, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'chofer', '1234', '2025-08-12 01:11:27', NULL, '2025-08-12 01:12:10', '2025-08-12 01:12:10', NULL, 1, '2025-08-12 04:11:27', '2025-08-12 04:12:10', NULL, 1),
(9, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'chofer', '123456', '2025-08-12 01:14:13', NULL, '2025-08-12 01:35:57', '2025-08-12 01:36:39', NULL, 1, '2025-08-12 04:14:13', '2025-08-12 04:36:39', NULL, 1),
(10, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'cristian rojas', '4801734', '2025-08-12 01:37:18', NULL, '2025-08-12 01:40:05', '2025-08-12 01:40:05', NULL, 1, '2025-08-12 04:37:18', '2025-08-12 04:40:05', NULL, 1),
(11, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'cristian rojas', '4801734', '2025-08-12 01:37:42', NULL, '2025-08-12 01:40:14', '2025-08-12 01:40:14', NULL, 1, '2025-08-12 04:37:42', '2025-08-12 04:40:14', NULL, 1),
(12, 'pedestrian', NULL, NULL, NULL, NULL, NULL, 'Cristian Rojajs', '4802734', '2025-08-12 02:11:42', NULL, NULL, '2025-08-12 02:11:51', NULL, 1, '2025-08-12 05:11:42', '2025-08-12 05:11:51', NULL, 1),
(13, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'cristian rojas', '123456', '2025-08-12 12:11:00', NULL, '2025-08-12 12:11:18', '2025-08-12 12:11:18', NULL, 1, '2025-08-12 15:11:00', '2025-08-12 15:11:18', 13, 1),
(14, 'vehicle', 'QQQQQQ', NULL, NULL, NULL, NULL, 'juan', '123456', '2025-08-12 12:33:43', NULL, '2025-08-12 17:05:02', '2025-08-12 17:05:02', NULL, 1, '2025-08-12 15:33:43', '2025-08-12 20:05:03', 14, 1),
(15, 'vehicle', '1234', 'rrr', 'rrr', 'moto', NULL, 'ffff', '1234', '2025-08-12 17:05:29', NULL, '2025-08-12 23:08:18', '2025-08-12 23:08:18', 'es plataforma bolt', 1, '2025-08-12 20:05:29', '2025-08-13 02:08:18', 16, 1),
(16, 'vehicle', 'ABC1234', 'toyota', 'balnco', 'auto', NULL, 'cristian rojas', '4801734', '2025-08-12 23:05:45', NULL, '2025-08-12 23:06:38', '2025-08-12 23:06:38', NULL, 3, '2025-08-13 02:05:45', '2025-08-13 02:06:38', 17, 1),
(17, 'vehicle', '123123', 'go', 'ha', 'auto', NULL, 'juan', '123456', '2025-08-13 10:16:01', 'bolt', '2025-08-15 16:59:39', '2025-08-15 16:59:39', NULL, 1, '2025-08-13 13:16:01', '2025-08-15 19:59:39', 19, 1),
(18, 'vehicle', '123456', 'df', 'dfdf', 'auto', NULL, 'ffff', '222333', '2025-08-14 17:05:04', NULL, '2025-08-15 16:55:53', '2025-08-15 16:55:53', NULL, 1, '2025-08-14 20:05:04', '2025-08-15 19:55:53', 21, 2),
(19, 'vehicle', '232323', '23', '23', 'auto', NULL, 'juan', '123456', '2025-08-15 19:37:13', NULL, '2025-08-15 19:45:24', '2025-08-15 19:45:24', NULL, 1, '2025-08-15 22:37:13', '2025-08-15 22:45:24', 22, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `access_occupants`
--

CREATE TABLE `access_occupants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `access_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `document` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `is_driver` varchar(255) NOT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `entry_at` datetime NOT NULL,
  `exit_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `access_people`
--

CREATE TABLE `access_people` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `access_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `document` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `is_driver` varchar(255) NOT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `entry_at` datetime NOT NULL,
  `exit_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `access_people`
--

INSERT INTO `access_people` (`id`, `access_id`, `full_name`, `document`, `role`, `is_driver`, `gender`, `entry_at`, `exit_at`, `created_at`, `updated_at`) VALUES
(1, 5, 'fffff', '123456', 'pedestrian', '0', NULL, '2025-08-12 01:06:19', '2025-08-12 01:06:49', '2025-08-12 04:06:19', '2025-08-12 04:06:49'),
(2, 6, 'sdsdsd', '123456', 'pedestrian', '0', NULL, '2025-08-12 01:09:29', '2025-08-12 01:09:55', '2025-08-12 04:09:29', '2025-08-12 04:09:55'),
(3, 7, 'dffd', '123455', 'driver', '1', NULL, '2025-08-12 01:10:18', '2025-08-12 01:11:03', '2025-08-12 04:10:18', '2025-08-12 04:11:03'),
(4, 7, 'sqsqsqsq', '123456', 'passenger', '0', NULL, '2025-08-12 01:10:18', '2025-08-12 01:10:47', '2025-08-12 04:10:18', '2025-08-12 04:10:47'),
(5, 8, 'chofer', '1234', 'driver', '1', NULL, '2025-08-12 01:11:27', '2025-08-12 01:12:10', '2025-08-12 04:11:27', '2025-08-12 04:12:10'),
(6, 8, 'acompanha', '12345', 'passenger', '0', NULL, '2025-08-12 01:11:27', '2025-08-12 01:11:41', '2025-08-12 04:11:27', '2025-08-12 04:11:41'),
(7, 9, 'chofer', '123456', 'driver', '1', NULL, '2025-08-12 01:14:13', '2025-08-12 01:36:39', '2025-08-12 04:14:13', '2025-08-12 04:36:39'),
(8, 9, 'companha', '23456', 'passenger', '0', NULL, '2025-08-12 01:14:13', '2025-08-12 01:35:57', '2025-08-12 04:14:13', '2025-08-12 04:35:57'),
(9, 10, 'cristian rojas', '4801734', 'driver', '1', NULL, '2025-08-12 01:37:18', '2025-08-12 01:40:05', '2025-08-12 04:37:18', '2025-08-12 04:40:05'),
(10, 11, 'cristian rojas', '4801734', 'driver', '1', NULL, '2025-08-12 01:37:42', '2025-08-12 01:40:14', '2025-08-12 04:37:42', '2025-08-12 04:40:14'),
(11, 12, 'Cristian Rojajs', '4802734', 'pedestrian', '0', NULL, '2025-08-12 02:11:42', '2025-08-12 02:11:51', '2025-08-12 05:11:42', '2025-08-12 05:11:51'),
(12, 13, 'cristian rojas', '123456', 'driver', '1', NULL, '2025-08-12 12:11:00', '2025-08-12 12:11:18', '2025-08-12 15:11:00', '2025-08-12 15:11:18'),
(13, 13, 'acompanhante', '1234567', 'passenger', '0', NULL, '2025-08-12 12:11:00', '2025-08-12 12:11:18', '2025-08-12 15:11:00', '2025-08-12 15:11:18'),
(14, 14, 'juan', '123456', 'driver', '1', NULL, '2025-08-12 12:33:43', '2025-08-12 17:05:02', '2025-08-12 15:33:43', '2025-08-12 20:05:02'),
(15, 14, 'ffffff', '333444', 'passenger', '0', NULL, '2025-08-12 12:33:43', '2025-08-12 17:05:02', '2025-08-12 15:33:43', '2025-08-12 20:05:02'),
(16, 15, 'ffff', '1234', 'driver', '1', 'masculino', '2025-08-12 17:05:29', '2025-08-12 23:08:18', '2025-08-12 20:05:29', '2025-08-13 02:08:18'),
(17, 16, 'cristian rojas', '4801734', 'driver', '1', 'masculino', '2025-08-12 23:05:45', '2025-08-12 23:06:38', '2025-08-13 02:05:45', '2025-08-13 02:06:38'),
(18, 16, 'juan', '123456', 'passenger', '0', 'masculino', '2025-08-12 23:05:45', '2025-08-12 23:06:38', '2025-08-13 02:05:45', '2025-08-13 02:06:38'),
(19, 17, 'juan', '123456', 'driver', '1', 'masculino', '2025-08-13 10:16:01', '2025-08-15 16:59:39', '2025-08-13 13:16:01', '2025-08-15 19:59:39'),
(20, 17, 'cristian rojas', '4801734', 'passenger', '0', 'masculino', '2025-08-13 10:16:01', '2025-08-15 16:59:39', '2025-08-13 13:16:01', '2025-08-15 19:59:39'),
(21, 18, 'ffff', '222333', 'driver', '1', 'masculino', '2025-08-14 17:05:04', '2025-08-15 16:55:53', '2025-08-14 20:05:04', '2025-08-15 19:55:53'),
(22, 19, 'juan', '123456', 'driver', '1', 'masculino', '2025-08-15 19:37:14', '2025-08-15 19:45:24', '2025-08-15 22:37:14', '2025-08-15 22:45:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `color` varchar(7) DEFAULT NULL,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `branches`
--

INSERT INTO `branches` (`id`, `name`, `location`, `color`, `manager_id`, `created_at`, `updated_at`) VALUES
(1, 'Sucursal 1', 'Mariano Roque Alonso', '#b94437', NULL, '2025-08-14 18:36:57', '2025-08-16 01:13:58'),
(2, 'Sucursal 2', 'Limpio', '#6c757d', 1, '2025-08-14 18:37:35', '2025-08-16 01:29:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('vigilante-cache-graplication@gmail.com|192.168.100.7', 'i:1;', 1755358056),
('vigilante-cache-graplication@gmail.com|192.168.100.7:timer', 'i:1755358056;', 1755358056),
('vigilante-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:8:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"access.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:18:\"access.view.active\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"access.enter\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"access.exit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"reports.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:12:\"users.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:12:\"roles.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:11:\"access.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}}s:5:\"roles\";a:2:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:7:\"guardia\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}}}', 1755444405);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_12_005254_create_accesses_table', 2),
(5, '2025_08_12_005333_create_access_occupants_table', 2),
(6, '2025_08_12_013623_create_permission_tables', 3),
(7, '2025_08_12_021006_add_is_active_to_users_table', 4),
(8, '2025_08_12_030742_create_access_people_table', 5),
(9, '2025_08_12_030842_add_vehicle_exit_at_to_accesses_table', 5),
(10, '2025_08_12_033258_add_notes_to_accesses_table', 6),
(11, '2025_08_12_000001_create_people_table', 7),
(12, '2025_08_12_000002_add_vehicle_fields_to_accesses_table', 7),
(13, '2025_08_12_000003_update_access_people_add_gender_and_unique', 7),
(14, '2025_08_12_000004_add_vehicle_exit_driver_to_accesses_table', 8),
(15, '2025_08_12_121815_add_vehiculo_and_sexo_to_accesos_table', 9),
(16, '2025_08_13_000001_add_indexes_for_vigilante', 10),
(17, '2025_08_13_000002_unique_people_document', 11),
(18, '2025_08_13_000003_fk_access_people_access', 11),
(19, '2025_08_13_225544_create_branches_table', 12),
(20, '2025_08_13_225711_add_branch_id_to_users_table', 13),
(21, '2025_08_15_000000_add_phone_to_users_table', 14),
(22, '2025_08_15_000001_add_color_to_branches_table', 14),
(23, '2025_01_01_000001_add_color_to_branches_table', 15),
(24, '2025_01_01_000000_add_avatar_to_users_table', 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 2),
(1, 'App\\Models\\User', 4),
(1, 'App\\Models\\User', 5),
(3, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `people`
--

CREATE TABLE `people` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `document` varchar(255) NOT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `people`
--

INSERT INTO `people` (`id`, `full_name`, `document`, `gender`, `created_at`, `updated_at`) VALUES
(1, 'cristian rojas', '4801734', 'masculino', '2025-08-12 04:37:18', '2025-08-12 04:37:18'),
(2, 'Cristian Rojajs', '4802734', 'masculino', '2025-08-12 05:11:42', '2025-08-12 05:11:42'),
(3, 'juan', '123456', 'masculino', '2025-08-12 15:11:00', '2025-08-12 15:33:43'),
(4, 'acompanhante', '1234567', 'masculino', '2025-08-12 15:11:00', '2025-08-12 15:11:00'),
(5, 'ffffff', '333444', 'masculino', '2025-08-12 15:33:43', '2025-08-12 15:33:43'),
(6, 'ffff', '1234', 'masculino', '2025-08-12 20:05:29', '2025-08-12 20:05:29'),
(7, 'ffff', '222333', 'masculino', '2025-08-14 20:05:04', '2025-08-14 20:05:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'access.view', 'web', '2025-08-12 04:36:52', '2025-08-12 04:36:52'),
(2, 'access.view.active', 'web', '2025-08-12 04:36:52', '2025-08-12 04:36:52'),
(3, 'access.enter', 'web', '2025-08-12 04:36:52', '2025-08-12 04:36:52'),
(4, 'access.exit', 'web', '2025-08-12 04:36:52', '2025-08-12 04:36:52'),
(5, 'reports.view', 'web', '2025-08-12 04:36:52', '2025-08-12 04:36:52'),
(6, 'users.manage', 'web', '2025-08-12 04:36:52', '2025-08-12 04:36:52'),
(7, 'roles.manage', 'web', '2025-08-12 04:36:52', '2025-08-12 04:36:52'),
(8, 'access.show', 'web', '2025-08-12 05:04:50', '2025-08-12 05:04:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'guardia', 'web', '2025-08-12 04:36:52', '2025-08-12 04:36:52'),
(3, 'admin', 'web', '2025-08-12 05:04:50', '2025-08-12 05:04:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 3),
(2, 1),
(2, 3),
(3, 1),
(3, 3),
(4, 1),
(4, 3),
(5, 3),
(6, 3),
(7, 3),
(8, 1),
(8, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2z35eWwB1Vy4JZ54n5Oyh4cN6WZSClYc47NtQ6NQ', 1, '192.168.100.7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUVFPdUdYczZXdDRRcUxqUlRvcTF6Tzg3M2d2aVo1aUlWNUV4NHlxMCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vMTkyLjE2OC4xMDAuNzo4MDAwL2FjY2Vzb3MiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1755358276),
('4sJAJYlcZ0fRyFJWEyR4lZDm2QfOwZEY4jPFY4LK', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMlp4anlPQUp5TkRJWG5BVmFQSVEyQjFWRXFqdnp3bFV1YnN5d3BwbyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755359504),
('Oacf68kij1msmyfcfYHrZtOO35YNAIfqGh6A8a3I', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiaWJ0dE1rTlk1ZHJWTUJrUW8zMnhnTnY3TVQ5dFl3SHpsWUl0UE1xRCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQwOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvcmVzdW1lbj9zb3VyY2U9cHdhIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1755359514),
('rEdLVxbOBWosGRtRIcVzcsX3aIS5TL1T3scYAVNh', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSEVGeldPZmxGSEU1a2dpbU1aN09BdTk0ZGZlZEJ0bkZiN255WjJ0bCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755358707),
('uFK6SUHGrZJAIlabfuyshtDGwzC3rnARtTTQc1gs', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoib2xYb3Fxc01UcWtpRXN5OWFEM1ozbVpMeTEydlBZT3BmdWJYaXdDQyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoxOToiaHR0cDovLzAuMC4wLjA6ODAwMCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI1OiJodHRwOi8vMC4wLjAuMDo4MDAwL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1755358536);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `avatar_path`, `email_verified_at`, `password`, `is_active`, `remember_token`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 'Cristian Dario Rojas Orue', 'graplications@gmail.com', '+595983691395', NULL, NULL, '$2y$12$EDXy5sO997LTwUEs7tMmnebURggMusGnjKmWE9IrYVKnB.tbP.aXm', '1', NULL, '2025-08-12 03:50:33', '2025-08-16 03:47:42', NULL),
(2, 'Carlos Echague', 'carlos@carlos.com', '+595983691395', 'avatars/ELP5VFJInIG4mbJouWs2rK6cSGKdb8Y34pWNyDxc.jpg', NULL, '$2y$12$VPWWmHXEniIL/1MHTaDulu90RC7mhuTzbvfRGDrJV9jF6WRAyRqOC', '1', NULL, '2025-08-12 05:05:10', '2025-08-16 02:25:14', 1),
(3, 'Administrador General', 'admin@admin.com', NULL, NULL, NULL, '$2y$12$GKu2OITh8fN2fphlN6SSfeAXMoJd5m4vvlCJfcmn93OVRzVxr65BK', '1', 'ePrtYIVoL2X6kcW9OZ0Z0Yicvj1AF6032RTeaBBD8TF393RF2hzupiewup7l', '2025-08-12 13:21:26', '2025-08-12 13:21:26', 1),
(4, 'guardia 1', 'a@a.com', NULL, NULL, NULL, '$2y$12$GU81RUfIML2BhP7b68kPKOvzNuLgKto0dRsL6pmBvV22FmYXlKfRa', '1', NULL, '2025-08-13 02:07:35', '2025-08-15 20:08:41', 2),
(5, 'Cristian Dario Rojas Orue', 'rosaamarapy@gmail.com', '+595983691395', 'avatars/3zzQjHgQN2iKjvlQ9HsVGK2lOcTZCLJthf6xnX3i.jpg', NULL, '$2y$12$gOj2AFwJPMf7nfRJxjO9ROUoRTUC2WBCAmBTI3gesqVBvAySfjN9q', '1', NULL, '2025-08-16 15:45:46', '2025-08-16 15:45:46', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `accesses`
--
ALTER TABLE `accesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `accesses_user_id_foreign` (`user_id`),
  ADD KEY `accesses_branch_id_foreign` (`branch_id`);

--
-- Indices de la tabla `access_occupants`
--
ALTER TABLE `access_occupants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `access_occupants_access_id_foreign` (`access_id`);

--
-- Indices de la tabla `access_people`
--
ALTER TABLE `access_people`
  ADD PRIMARY KEY (`id`),
  ADD KEY `access_people_access_id_foreign` (`access_id`);

--
-- Indices de la tabla `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indices de la tabla `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `people_document_unique` (`document`);

--
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indices de la tabla `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_phone_index` (`phone`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `accesses`
--
ALTER TABLE `accesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `access_occupants`
--
ALTER TABLE `access_occupants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `access_people`
--
ALTER TABLE `access_people`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `people`
--
ALTER TABLE `people`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `accesses`
--
ALTER TABLE `accesses`
  ADD CONSTRAINT `accesses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `accesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `access_occupants`
--
ALTER TABLE `access_occupants`
  ADD CONSTRAINT `access_occupants_access_id_foreign` FOREIGN KEY (`access_id`) REFERENCES `accesses` (`id`);

--
-- Filtros para la tabla `access_people`
--
ALTER TABLE `access_people`
  ADD CONSTRAINT `access_people_access_id_foreign` FOREIGN KEY (`access_id`) REFERENCES `accesses` (`id`);

--
-- Filtros para la tabla `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
