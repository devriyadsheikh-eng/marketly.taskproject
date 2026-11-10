-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 10, 2026 at 10:21 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `taskproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('hrms-cache-30e7d646e73dc8af329a820b6e6b25cc', 'i:1;', 1794305242),
('hrms-cache-30e7d646e73dc8af329a820b6e6b25cc:timer', 'i:1794305242;', 1794305242),
('hrms-cache-3a35cdc8b74ed2979d176bd03f6ab749', 'i:1;', 1794210221),
('hrms-cache-3a35cdc8b74ed2979d176bd03f6ab749:timer', 'i:1794210221;', 1794210221),
('hrms-cache-43369054a429103c06de4cbe7c905189', 'i:1;', 1794210221),
('hrms-cache-43369054a429103c06de4cbe7c905189:timer', 'i:1794210221;', 1794210221),
('hrms-cache-8da3842ddb56df343f737f3d2f60cc17', 'i:1;', 1794210077),
('hrms-cache-8da3842ddb56df343f737f3d2f60cc17:timer', 'i:1794210077;', 1794210077),
('hrms-cache-8dbf94c6b8faa9cae77d69d1fc8a5d56', 'i:1;', 1794305242),
('hrms-cache-8dbf94c6b8faa9cae77d69d1fc8a5d56:timer', 'i:1794305242;', 1794305242),
('hrms-cache-8fc3178070a8032b59b7787e74e042bf', 'i:1;', 1794304132),
('hrms-cache-8fc3178070a8032b59b7787e74e042bf:timer', 'i:1794304132;', 1794304132);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2026_11_08_000001_create_users_table', 1),
(4, '2026_11_08_000002_create_shops_table', 1),
(5, '2026_11_08_000003_create_products_table', 1),
(6, '2026_11_08_000004_create_shop_products_table', 1),
(7, '2026_11_09_050635_create_personal_access_tokens_table', 2),
(8, '2026_11_09_060000_create_sessions_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `created_by`, `name`, `slug`, `description`, `thumbnail`, `created_at`, `updated_at`) VALUES
(1, 1, 'Classic Cotton T-Shirt', 'classic-cotton-t-shirt', 'Soft everyday cotton tee.', 'products/thumbnails/iSxEOLfIcsraCAy64riA44HZKRhbXcTfuImWe4VB.jpg', '2026-11-08 11:20:28', '2026-11-10 04:02:36'),
(2, 1, 'Ceramic Coffee Mug', 'ceramic-coffee-mug', 'Durable glazed ceramic mug.', 'products/thumbnails/St7u0IqDX8FeynXkYSLlO7XnpaZrvULbEXGuyKtd.jpg', '2026-11-08 11:20:28', '2026-11-10 04:02:55'),
(3, 1, 'Wireless Mouse', 'wireless-mouse', 'Compact wireless mouse with USB receiver.', 'products/thumbnails/khQe9t9OVFASIfWXRCbLfiZpJxZXzLHyZMW1YbgL.jpg', '2026-11-08 11:20:28', '2026-11-10 04:03:11'),
(4, 1, 'Canvas Tote Bag', 'canvas-tote-bag', 'Reusable heavyweight canvas tote.', 'products/thumbnails/335SKOEQgH8M1bdBGy2G4itpV7eakWY9M5o5M2lu.jpg', '2026-11-08 11:20:28', '2026-11-10 04:04:40'),
(5, 1, 'Steel Water Bottle', 'steel-water-bottle', 'Insulated stainless steel bottle.', 'products/thumbnails/ztF21NmtAKBiIMI9nVHmPapUL6aoFJaUycfEatU9.jpg', '2026-11-08 11:20:28', '2026-11-10 03:54:44'),
(6, 1, 'Desk Lamp', 'desk-lamp', 'Adjustable LED lamp for a home office.', 'products/thumbnails/DBcFuQin02JFk6nlL02eTpCAyKfwTZ87Sjfq6R85.jpg', '2026-11-08 11:20:28', '2026-11-10 03:49:41'),
(214, 2, 'Hand Watch', 'hand-watch', NULL, 'products/thumbnails/P3FIACcFxJoeth4fKMdZfviq1ZWfiALJkqcDVPAY.jpg', '2026-11-10 04:11:15', '2026-11-10 04:11:15'),
(215, 2, 'Watch', 'watch', NULL, 'products/thumbnails/sluHETGCHMi1oMLnVjGuK58X9v0WoAcx76ALCPD5.jpg', '2026-11-10 04:12:59', '2026-11-10 04:12:59');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shops`
--

CREATE TABLE `shops` (
  `id` bigint UNSIGNED NOT NULL,
  `owner_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shops`
--

INSERT INTO `shops` (`id`, `owner_id`, `name`, `slug`, `description`, `logo`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Amina Home & Living', 'amina-home-living', 'Demo storefront for Amina Home & Living.', NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(2, 2, 'Amina Tech Corner', 'amina-tech-corner', 'Demo storefront for Amina Tech Corner.', NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(3, 3, 'Karim Essentials', 'karim-essentials', 'Demo storefront for Karim Essentials.', NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28');

-- --------------------------------------------------------

--
-- Table structure for table `shop_products`
--

CREATE TABLE `shop_products` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(12,2) NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shop_products`
--

INSERT INTO `shop_products` (`id`, `shop_id`, `product_id`, `name`, `description`, `price`, `thumbnail`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Amina Select: Cotton Tee', NULL, 12.50, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(2, 1, 2, NULL, NULL, 16.75, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(3, 1, 3, NULL, NULL, 21.00, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(4, 1, 4, NULL, NULL, 25.25, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(5, 2, 1, NULL, NULL, 14.00, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(6, 2, 2, NULL, NULL, 18.25, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(7, 2, 3, NULL, NULL, 22.50, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(8, 2, 4, NULL, NULL, 26.75, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(9, 2, 5, NULL, NULL, 31.00, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(10, 3, 1, NULL, NULL, 15.50, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(11, 3, 2, NULL, NULL, 19.75, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(12, 3, 3, NULL, NULL, 24.00, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(13, 3, 4, NULL, NULL, 28.25, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(14, 3, 5, NULL, NULL, 32.50, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(15, 3, 6, NULL, NULL, 36.75, NULL, 'active', '2026-11-08 11:20:28', '2026-11-08 11:20:28'),
(216, 2, 214, NULL, NULL, 21.00, NULL, 'active', '2026-11-10 04:12:05', '2026-11-10 04:12:05'),
(217, 2, 215, NULL, NULL, 22.00, NULL, 'active', '2026-11-10 04:13:20', '2026-11-10 04:13:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','shop_owner') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'shop_owner',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'System Admin', 'admin@example.com', NULL, '$2y$12$idi6PiASWfo3iyw9M/Nvp.UwSfC/QuyaxR4W5zzO9eKaACGEZz3FK', 'admin', 1, NULL, '2026-11-08 11:20:27', '2026-11-08 11:23:26'),
(2, 'Amina Rahman', 'owner1@example.com', NULL, '$2y$12$LMvct93T4RS/vYU2sgPRqe90CeH0/E5DRAqJGbRaAkb0cjef.uDr6', 'shop_owner', 1, NULL, '2026-11-08 11:20:28', '2026-11-08 11:23:27'),
(3, 'Karim Ahmed', 'owner2@example.com', NULL, '$2y$12$TDAhG5nZOmkHEvoZwddWDOLqPYcEqVWfgpD7Sf7txYTsSiaVZEYXi', 'shop_owner', 1, NULL, '2026-11-08 11:20:28', '2026-11-10 03:48:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_created_by_created_at_index` (`created_by`,`created_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shops_slug_unique` (`slug`),
  ADD KEY `shops_owner_id_status_index` (`owner_id`,`status`),
  ADD KEY `shops_status_index` (`status`);

--
-- Indexes for table `shop_products`
--
ALTER TABLE `shop_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_products_shop_id_product_id_unique` (`shop_id`,`product_id`),
  ADD KEY `shop_products_shop_id_status_index` (`shop_id`,`status`),
  ADD KEY `shop_products_product_id_status_index` (`product_id`,`status`),
  ADD KEY `shop_products_status_index` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_is_active_index` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- AUTO_INCREMENT for table `shops`
--
ALTER TABLE `shops`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT for table `shop_products`
--
ALTER TABLE `shop_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=492;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `shops`
--
ALTER TABLE `shops`
  ADD CONSTRAINT `shops_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shop_products`
--
ALTER TABLE `shop_products`
  ADD CONSTRAINT `shop_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_products_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
