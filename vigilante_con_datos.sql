-- 1. Crear la base de datos
CREATE DATABASE IF NOT EXISTS `vigilante` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `vigilante`;

-- 2. Tablas básicas sin dependencias
CREATE TABLE IF NOT EXISTS `branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tablas de usuarios y permisos
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tablas de personas y accesos
CREATE TABLE IF NOT EXISTS `people` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `people_document_unique` (`document`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accesses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marca_vehiculo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_vehiculo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_vehiculo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `people_count` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entry_at` datetime NOT NULL,
  `entry_note` text COLLATE utf8mb4_unicode_ci,
  `vehicle_exit_at` datetime DEFAULT NULL,
  `exit_at` datetime DEFAULT NULL,
  `exit_note` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vehicle_exit_driver_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accesses_user_id_foreign` (`user_id`),
  CONSTRAINT `accesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `access_occupants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `access_id` bigint unsigned NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_driver` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entry_at` datetime NOT NULL,
  `exit_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `access_occupants_access_id_foreign` (`access_id`),
  CONSTRAINT `access_occupants_access_id_foreign` FOREIGN KEY (`access_id`) REFERENCES `accesses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `access_people` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `access_id` bigint unsigned NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_driver` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entry_at` datetime NOT NULL,
  `exit_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `access_people_access_id_foreign` (`access_id`),
  CONSTRAINT `access_people_access_id_foreign` FOREIGN KEY (`access_id`) REFERENCES `accesses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos en orden correcto

-- 1. Insertar migraciones
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
(20, '2025_08_13_225711_add_branch_id_to_users_table', 13);

-- 2. Insertar usuarios (necesarios para accesos)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_active`, `remember_token`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 'Cristian Dario Rojas Orue', 'graplications@gmail.com', NULL, '$2y$12$EDXy5sO997LTwUEs7tMmnebURggMusGnjKmWE9IrYVKnB.tbP.aXm', '1', NULL, '2025-08-12 00:50:33', '2025-08-12 02:55:10', NULL),
(2, 'Carlos Echague', 'carlos@carlos.com', NULL, '$2y$12$VPWWmHXEniIL/1MHTaDulu90RC7mhuTzbvfRGDrJV9jF6WRAyRqOC', '1', NULL, '2025-08-12 02:05:10', '2025-08-12 10:07:17', NULL),
(3, 'Administrador General', 'admin@admin.com', NULL, '$2y$12$GKu2OITh8fN2fphlN6SSfeAXMoJd5m4vvlCJfcmn93OVRzVxr65BK', '1', 'ePrtYIVoL2X6kcW9OZ0Z0Yicvj1AF6032RTeaBBD8TF393RF2hzupiewup7l', '2025-08-12 10:21:26', '2025-08-12 10:21:26', NULL),
(4, 'guardia 1', 'a@a.com', NULL, '$2y$12$GU81RUfIML2BhP7b68kPKOvzNuLgKto0dRsL6pmBvV22FmYXlKfRa', '1', NULL, '2025-08-12 23:07:35', '2025-08-12 23:07:35', NULL);

-- 3. Insertar permisos y roles
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'access.view', 'web', '2025-08-12 01:36:52', '2025-08-12 01:36:52'),
(2, 'access.view.active', 'web', '2025-08-12 01:36:52', '2025-08-12 01:36:52'),
(3, 'access.enter', 'web', '2025-08-12 01:36:52', '2025-08-12 01:36:52'),
(4, 'access.exit', 'web', '2025-08-12 01:36:52', '2025-08-12 01:36:52'),
(5, 'reports.view', 'web', '2025-08-12 01:36:52', '2025-08-12 01:36:52'),
(6, 'users.manage', 'web', '2025-08-12 01:36:52', '2025-08-12 01:36:52'),
(7, 'roles.manage', 'web', '2025-08-12 01:36:52', '2025-08-12 01:36:52'),
(8, 'access.show', 'web', '2025-08-12 02:04:50', '2025-08-12 02:04:50');

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'guardia', 'web', '2025-08-12 01:36:52', '2025-08-12 01:36:52'),
(3, 'admin', 'web', '2025-08-12 02:04:50', '2025-08-12 02:04:50');

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

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 4),
(3, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3);

-- 4. Insertar cache
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('vigilante-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:8:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"access.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:18:\"access.view.active\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"access.enter\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"access.exit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"reports.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:12:\"users.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:12:\"roles.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:11:\"access.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}}s:5:\"roles\";a:2:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:7:\"guardia\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}}}', 1755224925);

-- 5. Insertar personas
INSERT INTO `people` (`id`, `full_name`, `document`, `gender`, `created_at`, `updated_at`) VALUES
(1, 'cristian rojas', '4801734', 'masculino', '2025-08-12 01:37:18', '2025-08-12 01:37:18'),
(2, 'Cristian Rojajs', '4802734', 'masculino', '2025-08-12 02:11:42', '2025-08-12 02:11:42'),
(3, 'juan', '123456', 'masculino', '2025-08-12 12:11:00', '2025-08-12 12:33:43'),
(4, 'acompanhante', '1234567', 'masculino', '2025-08-12 12:11:00', '2025-08-12 12:11:00'),
(5, 'ffffff', '333444', 'masculino', '2025-08-12 12:33:43', '2025-08-12 12:33:43'),
(6, 'ffff', '1234', 'masculino', '2025-08-12 17:05:29', '2025-08-12 17:05:29');

-- 6. Insertar accesos (depende de usuarios)
INSERT INTO `accesses` (`id`, `type`, `plate`, `marca_vehiculo`, `color_vehiculo`, `tipo_vehiculo`, `people_count`, `full_name`, `document`, `entry_at`, `entry_note`, `vehicle_exit_at`, `exit_at`, `exit_note`, `user_id`, `created_at`, `updated_at`, `vehicle_exit_driver_id`) VALUES
(1, 'vehicle', 'AAEX-621', NULL, NULL, NULL, NULL, 'roseli bohado', '4801734', '2025-08-12 01:18:21', NULL, NULL, '2025-08-12 01:19:07', NULL, 1, '2025-08-12 01:18:21', '2025-08-12 01:19:07', NULL),
(2, 'vehicle', 'AAEX-621', NULL, NULL, NULL, NULL, 'roseli bohado', '4801734', '2025-08-12 02:59:10', NULL, NULL, '2025-08-12 02:59:33', NULL, 1, '2025-08-12 02:59:10', '2025-08-12 02:59:33', NULL),
(3, 'vehicle', 'AAEX-621', NULL, NULL, NULL, NULL, 'fulana', '12344556', '2025-08-12 03:01:10', NULL, NULL, '2025-08-12 03:01:56', NULL, 1, '2025-08-12 03:01:10', '2025-08-12 03:01:56', NULL),
(4, 'pedestrian', 'AAEX-621', NULL, NULL, NULL, '2', 'roseli bohado', '4801734', '2025-08-12 03:03:09', NULL, NULL, '2025-08-12 03:05:02', NULL, 1, '2025-08-12 03:03:09', '2025-08-12 03:05:02', NULL),
(5, 'pedestrian', NULL, NULL, NULL, NULL, NULL, 'fffff', '123456', '2025-08-12 01:06:19', NULL, NULL, '2025-08-12 01:06:49', NULL, 1, '2025-08-12 01:06:19', '2025-08-12 01:06:49', NULL),
(6, 'pedestrian', NULL, NULL, NULL, NULL, NULL, 'sdsdsd', '123456', '2025-08-12 01:09:29', NULL, NULL, '2025-08-12 01:09:55', NULL, 1, '2025-08-12 01:09:29', '2025-08-12 01:09:56', NULL),
(7, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'dffd', '123455', '2025-08-12 01:10:18', NULL, '2025-08-12 01:11:03', '2025-08-12 01:11:03', NULL, 1, '2025-08-12 01:10:18', '2025-08-12 01:11:03', NULL),
(8, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'chofer', '1234', '2025-08-12 01:11:27', NULL, '2025-08-12 01:12:10', '2025-08-12 01:12:10', NULL, 1, '2025-08-12 01:11:27', '2025-08-12 01:12:10', NULL),
(9, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'chofer', '123456', '2025-08-12 01:14:13', NULL, '2025-08-12 01:35:57', '2025-08-12 01:36:39', NULL, 1, '2025-08-12 01:14:13', '2025-08-12 01:36:39', NULL),
(10, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'cristian rojas', '4801734', '2025-08-12 01:37:18', NULL, '2025-08-12 01:40:05', '2025-08-12 01:40:05', NULL, 1, '2025-08-12 01:37:18', '2025-08-12 01:40:05', NULL),
(11, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'cristian rojas', '4801734', '2025-08-12 01:37:42', NULL, '2025-08-12 01:40:14', '2025-08-12 01:40:14', NULL, 1, '2025-08-12 01:37:42', '2025-08-12 01:40:14', NULL),
(12, 'pedestrian', NULL, NULL, NULL, NULL, NULL, 'Cristian Rojajs', '4802734', '2025-08-12 02:11:42', NULL, NULL, '2025-08-12 02:11:51', NULL, 1, '2025-08-12 02:11:42', '2025-08-12 02:11:51', NULL),
(13, 'vehicle', '123456', NULL, NULL, NULL, NULL, 'cristian rojas', '123456', '2025-08-12 12:11:00', NULL, '2025-08-12 12:11:18', '2025-08-12 12:11:18', NULL, 1, '2025-08-12 12:11:00', '2025-08-12 12:11:18', 13),
(14, 'vehicle', 'QQQQQQ', NULL, NULL, NULL, NULL, 'juan', '123456', '2025-08-12 12:33:43', NULL, '2025-08-12 17:05:02', '2025-08-12 17:05:02', NULL, 1, '2025-08-12 12:33:43', '2025-08-12 17:05:03', 14),
(15, 'vehicle', '1234', 'rrr', 'rrr', 'moto', NULL, 'ffff', '1234', '2025-08-12 17:05:29', NULL, '2025-08-12 23:08:18', '2025-08-12 23:08:18', 'es plataforma bolt', 1, '2025-08-12 17:05:29', '2025-08-12 23:08:18', 16),
(16, 'vehicle', 'ABC1234', 'toyota', 'balnco', 'auto', NULL, 'cristian rojas', '4801734', '2025-08-12 23:05:45', NULL, '2025-08-12 23:06:38', '2025-08-12 23:06:38', NULL, 3, '2025-08-12 23:05:45', '2025-08-12 23:06:38', 17),
(17, 'vehicle', '123123', 'go', 'ha', 'auto', NULL, 'juan', '123456', '2025-08-13 10:16:01', 'bolt', NULL, NULL, NULL, 1, '2025-08-13 10:16:01', '2025-08-13 10:16:01', NULL);

-- 7. Insertar personas de acceso (depende de accesos)
INSERT INTO `access_people` (`id`, `access_id`, `full_name`, `document`, `role`, `is_driver`, `gender`, `entry_at`, `exit_at`, `created_at`, `updated_at`) VALUES
(1, 5, 'fffff', '123456', 'pedestrian', '0', NULL, '2025-08-12 01:06:19', '2025-08-12 01:06:49', '2025-08-12 01:06:19', '2025-08-12 01:06:49'),
(2, 6, 'sdsdsd', '123456', 'pedestrian', '0', NULL, '2025-08-12 01:09:29', '2025-08-12 01:09:55', '2025-08-12 01:09:29', '2025-08-12 01:09:55'),
(3, 7, 'dffd', '123455', 'driver', '1', NULL, '2025-08-12 01:10:18', '2025-08-12 01:11:03', '2025-08-12 01:10:18', '2025-08-12 01:11:03'),
(4, 7, 'sqsqsqsq', '123456', 'passenger', '0', NULL, '2025-08-12 01:10:18', '2025-08-12 01:10:47', '2025-08-12 01:10:18', '2025-08-12 01:10:47'),
(5, 8, 'chofer', '1234', 'driver', '1', NULL, '2025-08-12 01:11:27', '2025-08-12 01:12:10', '2025-08-12 01:11:27', '2025-08-12 01:12:10'),
(6, 8, 'acompanha', '12345', 'passenger', '0', NULL, '2025-08-12 01:11:27', '2025-08-12 01:11:41', '2025-08-12 01:11:27', '2025-08-12 01:11:41'),
(7, 9, 'chofer', '123456', 'driver', '1', NULL, '2025-08-12 01:14:13', '2025-08-12 01:36:39', '2025-08-12 01:14:13', '2025-08-12 01:36:39'),
(8, 9, 'companha', '23456', 'passenger', '0', NULL, '2025-08-12 01:14:13', '2025-08-12 01:35:57', '2025-08-12 01:14:13', '2025-08-12 01:35:57'),
(9, 10, 'cristian rojas', '4801734', 'driver', '1', NULL, '2025-08-12 01:37:18', '2025-08-12 01:40:05', '2025-08-12 01:37:18', '2025-08-12 01:40:05'),
(10, 11, 'cristian rojas', '4801734', 'driver', '1', NULL, '2025-08-12 01:37:42', '2025-08-12 01:40:14', '2025-08-12 01:37:42', '2025-08-12 01:40:14'),
(11, 12, 'Cristian Rojajs', '4802734', 'pedestrian', '0', NULL, '2025-08-12 02:11:42', '2025-08-12 02:11:51', '2025-08-12 02:11:42', '2025-08-12 02:11:51'),
(12, 13, 'cristian rojas', '123456', 'driver', '1', NULL, '2025-08-12 12:11:00', '2025-08-12 12:11:18', '2025-08-12 12:11:00', '2025-08-12 12:11:18'),
(13, 13, 'acompanhante', '1234567', 'passenger', '0', NULL, '2025-08-12 12:11:00', '2025-08-12 12:11:18', '2025-08-12 12:11:00', '2025-08-12 12:11:18'),
(14, 14, 'juan', '123456', 'driver', '1', NULL, '2025-08-12 12:33:43', '2025-08-12 17:05:02', '2025-08-12 12:33:43', '2025-08-12 17:05:02'),
(15, 14, 'ffffff', '333444', 'passenger', '0', NULL, '2025-08-12 12:33:43', '2025-08-12 17:05:02', '2025-08-12 12:33:43', '2025-08-12 17:05:02'),
(16, 15, 'ffff', '1234', 'driver', '1', 'masculino', '2025-08-12 17:05:29', '2025-08-12 23:08:18', '2025-08-12 17:05:29', '2025-08-12 23:08:18'),
(17, 16, 'cristian rojas', '4801734', 'driver', '1', 'masculino', '2025-08-12 23:05:45', '2025-08-12 23:06:38', '2025-08-12 23:05:45', '2025-08-12 23:06:38'),
(18, 16, 'juan', '123456', 'passenger', '0', 'masculino', '2025-08-12 23:05:45', '2025-08-12 23:06:38', '2025-08-12 23:05:45', '2025-08-12 23:06:38'),
(19, 17, 'juan', '123456', 'driver', '1', 'masculino', '2025-08-13 10:16:01', NULL, '2025-08-13 10:16:01', '2025-08-13 10:16:01'),
(20, 17, 'cristian rojas', '4801734', 'passenger', '0', 'masculino', '2025-08-13 10:16:01', NULL, '2025-08-13 10:16:01', '2025-08-13 10:16:01');

-- 8. Insertar sesiones (depende de usuarios)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0oldAPXt3ViyQzdFxK1awRSIU7s48oTqUgIbWj5D', NULL, '3.130.96.91', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT2U1dHZXRE5HZTJOcldkRndveXhDbU9PaUJYZzFKejdKbWVoekZ1NiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755111860),
('1FzfJrv3AhswqTLwkMkGMoZJpztdBljcm3V8imJR', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiY0JwMnBDekk2ZHR2QjFjVnNxaTh2Rk1lOTg3bEcyMHR1VUNTZGQxZCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755129146),
('29V18R8ZOHZkFwPsYLZlHe9UcsCsMBZPV2j3bKtO', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoid0ZZRUFRdjhLRHBwVWFQS0tWd3FrYWJ2bmhZUFFZU1hEa0NVZ2xIOCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755129956),
('4ZjEaMw11wSSqiNTV4jZWsC0ExxoKUD160uPte3N', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUncybHcyM2pZSHNzSXlySU5PR2VMU0hXdnRpejdyUmFnQ3YzanludCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755106304),
('6gqOYM0QlfJG8HYzQEIvmmJqH9D3l4NY36u8g8uh', NULL, '3.134.148.59', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiME13bjlvb0xFUDdtSWdaYm56TDFzbVNoTDNwWGtoUVNvQW13RERzRSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755109390),
('7aWvvoAPHtqeOBeJVUUWqFesrJyBANAAtyzkYp5B', NULL, '3.130.96.91', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieWoxb0ZadHBaNjJVZGtZdnp4anRlU2U3dVM5YVpWUElxNXE3UkdzeCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755111952),
('7ECuRwfUNh32SK3tVYLkmoDVJOnot4NbPJy1bwLV', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRnJDVkc4aHpCV3V5NXFJQjNkVXgzbWJiS01ad2lJUmE4QTFUNWdQSyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755131108),
('9kExmAwzQpfflhTzcF6SpxsSQcIPy5vnBm4PFZ0c', NULL, '3.137.73.221', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVWNvcktwdkEydjlLTmFMcDBjMmhVN2ZvYTB6d3BGVVNuSnM1bkdsbCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755115068),
('9yIfvZfHUei3TO8hqXoQ0AreDSa0LD0kw9WU2WUj', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOFl1ZFpjenc1aHRkZUI3Q1ZuaGVYY0xQODJyaUlWN25aOTZSanM5TSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755095743),
('bBTolIu97FU0WjQkQ1RWT5EVWqvmrzMrkM11CwVq', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiN2xSaEQ3cHFUWGVGd2ZMREFMcEFVVUxrSHN3VUs3NW1pak9HalpGSCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755119458),
('bCdv7vajXq3LLa0Slalxf8RWQPlW3GneQMjGNKlw', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSDd4NVM0ZFJ2anBzZHpGOThsMzlkdnhCRTBCSDNtaVJ3TW1BM0lWMyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755124078),
('C8mlhFnEtmYYolkYb9g7HbehDycf4xWXJunw5JSw', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiejhmVGVVb1hIY0lORnl5QUNCN3ZaU0VPNm1GeDl4Q0gxYWh5SXM5QyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755101500),
('cZziVm2gFkWBFFDBobSzdQZ2sWkwuzlti6ehIBsh', NULL, '138.36.152.214', 'ivre-masscan/1.3 https://github.com/robertdavidgraham/', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidjNDZm5zSWMyNnA0V2Zoc0xxeWpiWlZoTm50VmFmT3Naa0p6QVlwbCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoxOToiaHR0cDovLzAuMC4wLjA6ODAwMCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjE5OiJodHRwOi8vMC4wLjAuMDo4MDAwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1755102951),
('EBWfSbJbd14ckZgjJ2i6VPPlfFaGLjgKHlqCCDFO', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoid2hmUFpxYm5yaVY2Q0VnSEV3Sld0SWZxRzZsemhhUHgwWjZCQnlGUiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755131260),
('EMxJNd1Lf0wKgFmzDzEMK91brzoAlE4v0LBLBdwN', NULL, '104.248.53.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUGxMYzZpZDk5cTlaSmdrYnR1T2FwNHpuZ283ZlNQU0dXeERMUktiZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755097497),
('EWgMgHDWEKUQyLQXWRV8Pym6BQiDp4om0c1AVSbi', 3, '190.104.185.249', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoicnp6STVaSWFZYzFuZllyaFRPZmRXZlgzNWpnT1JqSVAyNjE3bFJmMyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQxOiJodHRwOi8vMTkwLjEwNC4xODUuMjQ5OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==', 1755102168),
('fIsTKg3ReVXPwALNIA9xCIiHuN33lutX1vNleBKZ', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiY1NJN2dJeWJhcElLNzBSeklKSHpheHh5VTliTVdqc09KeW5sSlN4QSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755129844),
('fpPo7vji2uS47AXFDWAGeIZAFR43vP54mvtmxX82', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQnJnZW8zc1ZHNDdZVTJLbGdMY0xkdUQ5b25VUWhZNXV0cUtlVGVSUyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755109785),
('FqPAsnkOIqeMGkpCIaakZpIzcxzjA6J0lo5lyisZ', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWjNpZVhHRHdXWklBRGFpMzVNdFJoQTJnaHBSUWJWVVgzbk1mcmtuciI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvc2FsaWRhIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1755135211),
('G93ZRXllDBX8KYKQZ5bY19yahfzELAEHMzHTscYp', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNHA1b0FkeGxrUmM3eTBjYXRyUjB0OGFQYXhuY2s1R2wwV1F0R2VVbyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755130859),
('H3yxitpsvppIO8DKNXREIpxhO4MTjYWTFiPZNJsF', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiaXdjYXZqaktzZkNaWnJvTnRoSkNkWXYwS2VsOHNYM1JNa3lBU2tZUyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMC4wLjAuMDo4MDAwL3Jlc3VtZW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1755137326),
('IX4nPln9iv4MpdpdBfAzyTBHLAMxcqienFbu2SRF', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiU0xjclpTZ2M4NzhxNGNzM0JwaGVLU0VZdnJZOVQ2UHAxZTRhY0VZMyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vMC4wLjAuMDo4MDAwL2JyYW5jaGVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1755137684),
('iXIjiIv8d4T1MOpyqXH7qfFoUzQteuRcEuthaRrD', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoielUyVTRBQ0plZloxdm5EZkFidUtCMzVod2FjTHUxcGtDa05pZmE1UCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755131599),
('jFF9LXNyZ6S9s3w53LXVMOMi2w1RYOINcRaCXwqA', NULL, '3.134.148.59', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmRkaW91V2FWV0hJbmRSTTM1UzBHMWhNSkdMSGNFOWx4SEpEQTVqRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755109391),
('jtrRE0rRWvbZro6JfM0pVkOLB6oi1mrvNc8cX1LC', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiekE3ZmpvSW5XRVc2Y3BNUzlkS3BSQzI4Vm5WM2xtcWJ3aE95Wm9KUiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vMC4wLjAuMDo4MDAwL2JyYW5jaGVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2A6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1755138528),
('KnvEmJaVFRe6c0o2OHBN0NxGRzAlI1dBw4nRxk9Y', NULL, '205.210.31.44', 'Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibnRWRHV1Z1FoaHlFTFZyZkZiTm1qQmlHRUszdlhVbzMxeUhzVXF0byI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755116377),
('KZatMknjbyffo18ZjMEkW2vsr8tLp7fVdvKz9ovV', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidzJ1b2xYVUlVckk3Q3VWeVJLc09hZmtXQWttMUVkeXVnVTFxVkZMRCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQ4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvc2FsaWRhL2J1c2Nhcj9wbGF0ZT0xMjMxMjMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1755101331),
('lXOF0VPY2p4f6guMuDLTSP8bpoQIvWygWZ0p2qTf', NULL, '3.137.73.221', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidmFOMmlxalRCQm1ndldOT2RmVjQzbFVTT3Nhd1UwYzZEWU5mU2liQSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755115167),
('MHTqpFzNCtkT1nwXeFhAR9B41gnQa29wBXgdZxZD', NULL, '3.130.96.91', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMUpmZjA0OHhETjlUNVVmS1lUSzdMenVIY3R4ZmtKTTZRV24wcVlpMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755111860),
('n7yjXWYFboNx4lGGjLKQqBbxirWovYXdSUws2aG3', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWnBkRWpLdlZtNEJNNjd3WW1oZGRycFE0em9GOGMwREJHUWNGYllaUyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvcmVzdW1lbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755134879),
('NepXZcEV4H7Cm8YeIYAO8W775JgtWFISNjwiRRes', NULL, '205.210.31.44', 'Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUVRpNkJXN1NNNU1ocXBxRUt6N0loelRmNnVGY1N2TEVOUGpYeTNVRiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755116378),
('NvS8tiySU3n1uhDnJAIB4Ta9uAJpCawcS738DVza', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicmJsNU5ZcHdJVW9WMDJZMEdnNG5BSnVJbGNVeGg5dmE2dGdWdWlnYiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755107096),
('o8KFZD8hlNZzcjLhD7oFr9DoHygWFN1o6PNU6Qai', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNFBJeEthTUx2M04zeFB2MTUyelRHdUVucEZXd2N0dVcyajVWNHFIcyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vMC4wLjAuMDo4MDAwL2JyYW5jaGVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1755137788),
('Pj5zLNbWwUTS54n56xJAMgRUerQZKfOfPKEtlIh7', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZFIya1J1dDlmaGhLOFpJdmtDTEZINHdid3ZLRW90cmhESjAyWGZnMiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755135950),
('qvtiMCHVJylY4bUSQopPYrqMYw4M4wshHBOQQXsT', NULL, '44.220.188.154', 'Mozilla/5.0 (Windows NT 6.2;en-US) AppleWebKit/537.32.36 (KHTML, live Gecko) Chrome/58.0.3004.92 Safari/537.32', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiN1hDWFFkSzllUGRPdlVaSlBVam1vcllLR1dnWkI3NXpiMWNxWnhhYyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755137571),
('qvUVavoxqZUWyTsTObxgJoGhUSs2wfjkr4bzY8PS', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZHFZT1h3elRjSXVvYkpCUm5SWmFmU0IweU9GM1RzZks1eFFVZTR5bCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755117994),
('RDUTMgpyjGBcH57yQVQjfIc8BPgFj4jocIRtGUjv', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieDBRem5KYXRLRXhoSHNyWkthbFlmcEE1cWZsYTF1R0NVbmROQVdDQiI7czozOiJ1cmwiO2A6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755123790),
('RTrf4jck3tn0FQ87DhiHxz9KqPqJ4zrKiL2Gx5sA', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRTRZRm5ubm1ZTVB5N2paQ0QwZmhRNmFuTElPVGxKMzNKMDdDSkRDTSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvc2FsaWRhIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1755134983),
('RXUfITr8HXTDeuukesX6DPnkCTKTkpjaOzeEsxCl', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUDJYTVZrTlZheGE3eXc0UGlHTDZ5SlM5YVFtalV6MmVDRExWd2VLQyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755127556),
('rzlzycAaM6NZxlLSlG5QmCSgkjwYBZO2s6zLeUaK', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQVhFRTN4RndzMVBVY040ODRMWVVXSG1UalpOZVVxdWtycTZhSGZjRiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755097976),
('tp1yPsUqYXuS5X58m8nDxbaw26FH7yBGES3bVmR6', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZHp3cmhTY2hzdEZlUUpnbmV2NkpzTVVPYnZQNzczV1dsSlFhN1BHOCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755114277),
('u9Z9xKL0IAKeC2ROre58vzAESk98lBAun0J2Tcq3', NULL, '104.248.53.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWEl3aDQycnJ6Z29CTktDN0xoOHVadFZkV0F1QmhpVXpBcjNIMGZSRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755097496),
('UXPIb6l4FgDypTRREluPSuQhqaX2nCsC50pOqNv5', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZUxOektsOWlHUnZHSlJvaVNNOFVxWG4wZUY3eE91cEJQTWZoOXF1YiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMwOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvcmVwb3J0ZXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1755135413),
('VaGwjYUQQB2c1udYVj4N7dwgAdgyD7YFXw7Lenfe', NULL, '3.137.73.221', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU2FxMjJXbW0xbTNLRkZyR1N0MDh5b2xKYWVXZUlVaVVBUVdXR1BmayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755115068),
('vloJc63SEfDZJbfv0NXS2Jkyv4tVHbOVreBQ5lkI', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTlNxRFVnbVpXeVZXVDNHaGRaZlVFV2Vkajg1VTRBRHRyV2JpcFR5MSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755134795),
('vRO9NU9PBR2M1ZKfqMhRukQ5ZIJXsZVFCqPtZKx9', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoibFVWTG44VWJXUWNYOWswejJWekUwY3pPMFJQeW15UmFpZG1OZkp2VSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvcmVzdW1lbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755099045),
('wEHCi0p15vWjgfu92AJizT9W3Lht48kaZPky6emG', NULL, '3.134.148.59', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRmU1Y29NT3BNMlpsajFMRUwxM01yY2xrcnNGZmZiRTdEZ0lFZG5ocyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755109478),
('wPuAm5wIJdS57be2xoR0FlsrkJxcP5rwzYfWtRin', NULL, '104.248.53.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWk12d0NVNzBvVWVEdVFzUTJ2Uk9yYjhscW5xdEo0SU5KMVU0UTREOCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755097496),
('YFSEU05gj2EaPvzT7uVfYJU3JxxHnThTlwBoIRdv', NULL, '149.86.227.49', 'Hello World/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVXpvMnhCa2RtQWJCdnpPM0dGN0gxZU05QlJuODdzRUg2WG1KekRhMyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzE5MC4xMDQuMTg1LjI0OTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTAuMTA0LjE4NS4yNDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1755122169),
('YK2sEj2ff7JwYVYhBchAhtoxhpZlZo5sTwYQwN9D', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoieWladjREQXh5OVlIQXI3Y1hHbVN6dWRzWG9jZGVtMmpFWGJVbUlkYSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755130829),
('yPRULiyZHwqyQgOm6zzBE2XuKji515PQdcbUZfBn', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiT3A2a21HS3BxdjMzb29qWE5IRWdtcVlNZExiaFp6M2F1MlU3NDhseSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755130241),
('yZ2mf60FhwbBqwiZSOlwESOyAKc8mqtGIol7P3eh', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiaUpicGJGeWI0RXE5Q3ZPbmo4dkZaOE8zaW5lWWRxdk9GUjB4aEl1RyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755130496),
('zxP8LGrUfIWrLAruvTk7GVoqy7VPzdCXYOMh7MJy', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWHlaMFNPamVrN1AyUWRMZ0h4S0dMb2xBZWpRRENhRVF4Y244U3YzbSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWNjZXNvcy9jcmVhciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1755130590);
