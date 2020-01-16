-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 12, 2020 at 04:22 PM
-- Server version: 10.3.16-MariaDB
-- PHP Version: 7.1.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ojt`
--

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_map` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_deleted` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `country`, `city`, `address`, `location_map`, `created_at`, `updated_at`, `is_deleted`) VALUES
(2, 'Acadsoft', 'Philippines', 'Makati', 'adfsdf', '', NULL, NULL, '0');

-- --------------------------------------------------------

--
-- Table structure for table `descriptions`
--

CREATE TABLE `descriptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `internship_id` int(11) NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_deleted` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `descriptions`
--

INSERT INTO `descriptions` (`id`, `internship_id`, `description`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 'Coordinate with Senior Software Engineers in analyzing and designing new products.', '2020-01-10 16:37:50', '2020-01-10 16:37:50', '0'),
(2, 1, 'Test and debug codes of every reported bugs on different products', '2020-01-10 16:39:11', '2020-01-10 16:39:11', '0');

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

CREATE TABLE `internships` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `start_date` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `representative` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `student_position` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_approved` int(11) NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_deleted` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`id`, `user_id`, `company_id`, `start_date`, `representative`, `student_position`, `is_approved`, `status`, `comment`, `updated_by`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 2, '2020-01-05', 'Juan Dela Cruz', 'Software Engineer', 0, NULL, NULL, 1, '2020-01-01 21:10:19', '2020-01-09 14:01:02', '0');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_11_02_003610_add_srcode_to_user_table', 2),
(4, '2019_12_04_064649_create_companies_table', 3),
(5, '2019_12_04_133551_delete_main_branch__column', 3),
(6, '2019_12_05_061539_create_internships_table', 4),
(7, '2019_12_05_070020_change_from_to_nullable_column', 5),
(8, '2020_01_01_100531_add_is_deleted_from_company_table', 6),
(9, '2020_01_02_065910_add_is_deleted_column_to_internships_table', 7),
(10, '2020_01_04_134128_create_descriptions_table', 8),
(11, '2020_01_04_140305_add_is_deleted_column_to_description_table', 8),
(12, '2020_01_05_003538_add_profile_to_users', 8),
(13, '2020_01_05_062650_create_requirement_categories_table', 8),
(14, '2020_01_07_065512_create_requirements_table', 9),
(15, '2016_06_01_000001_create_oauth_auth_codes_table', 10),
(16, '2016_06_01_000002_create_oauth_access_tokens_table', 10),
(17, '2016_06_01_000003_create_oauth_refresh_tokens_table', 10),
(18, '2016_06_01_000004_create_oauth_clients_table', 10),
(19, '2016_06_01_000005_create_oauth_personal_access_clients_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Laravel Personal Access Client', 'VBvPpO6oQLuF2UJuIOXCWzlGc0yRFuRPG3x1Sqgs', 'http://localhost', 1, 0, 0, '2020-01-08 22:59:09', '2020-01-08 22:59:09'),
(2, NULL, 'Laravel Password Grant Client', 'V2eIcwmVrVbh2epdKb58mQbZACDXnRM53IZgEujF', 'http://localhost', 0, 1, 0, '2020-01-08 22:59:09', '2020-01-08 22:59:09');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2020-01-08 22:59:09', '2020-01-08 22:59:09');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('test@example.com', '$2y$10$rdfOFES/Soql1SPkLjPtu.5P6tgF/phAG3J0GvcsR5ebBYL7ATujm', '2019-11-01 06:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `requirements`
--

CREATE TABLE `requirements` (
  `id` int(10) UNSIGNED NOT NULL,
  `requirement_category_id` int(11) NOT NULL,
  `internship_id` int(11) NOT NULL,
  `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_approved` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `is_deleted` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requirements`
--

INSERT INTO `requirements` (`id`, `requirement_category_id`, `internship_id`, `file`, `is_approved`, `is_deleted`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 3, 1, NULL, '0', '0', 1, NULL, '2020-01-09 01:28:54'),
(2, 4, 1, NULL, '0', '0', 1, '2020-01-08 23:02:53', '2020-01-09 01:28:56');

-- --------------------------------------------------------

--
-- Table structure for table `requirement_categories`
--

CREATE TABLE `requirement_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_deleted` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requirement_categories`
--

INSERT INTO `requirement_categories` (`id`, `name`, `file`, `is_deleted`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'tes', NULL, '1', 1, '2020-01-06 22:00:12', '2020-01-06 22:00:26'),
(2, 'test', '/uploads/010720200600375e141e8551d2f.png', '1', 1, '2020-01-06 22:00:37', '2020-01-07 04:26:02'),
(3, 'Recomendation Letter', '/uploads/010920202207515e17a43764168.pdf', '0', 1, '2020-01-07 04:26:12', '2020-01-09 14:07:51'),
(4, 'Internship Agreement', '/uploads/010920202208565e17a47859595.pdf', '0', 1, '2020-01-07 04:57:06', '2020-01-09 14:08:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sr_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `sr_code`, `first_name`, `last_name`) VALUES
(1, 'Namikaze', 'superuser@example.com', NULL, '$2y$10$G1RAlsGSxajXovC91PePF.lMIKKKrvNoGDg6v2gIt8gVzI4kxX6HS', '7Y1tRKNFSIpt6S2DTi1ahQJ9TENAC30sanvv4TtBJOsQcF9Em5hsKQw4f03Q', '2019-10-31 05:51:38', '2020-01-07 06:27:55', 'superuser', '19-99999', 'Naruto', 'Uzumaki'),
(3, 'test', 'test@example.com', NULL, '$2y$10$5fc0hSX8MFCx7E4v86Ge0OSpFUSvDE7dO9MOxJsWSwxKJiQUiY7bS', 'XSIqfazk3mOQC6aDsUPMSGxLhPBOYUOSZELDMFNkwz8VjOXPStuLso4yXLhc', '2019-11-01 17:05:25', '2019-11-01 17:05:25', 'student', '18-88888', NULL, NULL),
(4, 'A.', 'coordinator@example.com', NULL, '$2y$10$t48F2r8mM1CF46wKQCkS0eE2CJ2r5G2YUBNtU79bc3ki0t1fyLTPC', NULL, '2020-01-09 13:54:00', '2020-01-09 13:54:00', 'coordinator', '777', 'Jessie', 'Montalbo');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `descriptions`
--
ALTER TABLE `descriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_personal_access_clients_client_id_index` (`client_id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `requirements`
--
ALTER TABLE `requirements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requirement_categories`
--
ALTER TABLE `requirement_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_sr_code_unique` (`sr_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `descriptions`
--
ALTER TABLE `descriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `requirements`
--
ALTER TABLE `requirements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `requirement_categories`
--
ALTER TABLE `requirement_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
