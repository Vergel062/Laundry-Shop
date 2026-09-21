-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2026 at 08:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laundry_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archive_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `location`, `created_at`, `archive_date`) VALUES
(1, 'Carmona Branch', '9300 Jm Loyola St. Maduya Carmona, Cavite', '2026-03-18 11:53:48', NULL),
(2, 'Biñan Branch', 'Biñan, Laguna', '2026-03-18 11:53:48', NULL),
(3, 'Cabuyao Branch 1', 'Cabuyao, Laguna', '2026-03-18 11:53:48', NULL),
(4, 'Cabuyao Branch 2', 'Cabuyao, Laguna', '2026-03-18 11:53:48', NULL),
(5, 'Cabuyao Branch 3', 'Cabuyao, Laguna', '2026-03-18 11:53:48', NULL),
(6, 'Cabuyao Branch 4', 'Cabuyao, Laguna', '2026-03-18 11:53:48', NULL),
(7, 'Cabuyao Branch 5', 'Cabuyao, Laguna', '2026-03-18 11:53:48', NULL),
(13, 'Makati Branch', 'Makati City', '2026-09-21 07:36:55', '2026-09-21 15:38:09');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `expense_type` enum('Electricity','Water','Supplies','Rent','Others') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `date_logged` timestamp NOT NULL DEFAULT current_timestamp(),
  `date` date DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `branch_id`, `staff_id`, `expense_type`, `amount`, `remarks`, `date_logged`, `date`) VALUES
(26, 1, 14, 'Water', 300.00, '', '2026-03-23 15:12:06', '2026-03-23'),
(36, 1, 1, 'Water', 600.00, 'bill', '2026-08-14 10:04:16', '2026-08-14'),
(37, 2, 1, 'Supplies', 60.00, 'kung ano lang', '2026-08-14 10:05:19', '2026-08-14'),
(38, 1, 1, 'Rent', 4500.00, 'bayad renta', '2026-08-28 03:54:47', '2026-08-28');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `stock_level` int(11) NOT NULL DEFAULT 0,
  `alert_level` decimal(10,2) DEFAULT 0.00,
  `unit` enum('Sachets','Gallons','Tanks','Pieces') NOT NULL,
  `min_threshold` int(11) NOT NULL DEFAULT 2,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `branch_id`, `item_name`, `stock_level`, `alert_level`, `unit`, `min_threshold`, `last_updated`) VALUES
(15, 1, 'Detergent', 202, 0.00, 'Sachets', 2, '2026-09-21 07:01:52'),
(16, 1, 'Fabric Spray', 6, 0.00, 'Gallons', 2, '2026-09-21 06:54:30'),
(17, 1, 'LPG', 5, 0.00, 'Tanks', 5, '2026-09-21 06:53:57'),
(18, 1, 'Downy / Softener', 102, 0.00, 'Sachets', 5, '2026-09-21 06:53:57'),
(21, 3, 'Detergent', 80, 0.00, 'Sachets', 2, '2026-09-17 05:30:32'),
(22, 4, 'Detergent', 100, 0.00, 'Sachets', 2, '2026-09-21 07:33:11'),
(23, 4, 'Fabric Spray', 41, 0.00, 'Gallons', 2, '2026-05-09 07:05:14'),
(24, 4, 'LPG', 10, 0.00, 'Tanks', 5, '2026-09-08 17:05:40'),
(25, 4, 'Downy / Softener', 100, 0.00, 'Sachets', 3, '2026-09-21 07:33:21'),
(26, 2, 'Detergent', 195, 0.00, 'Sachets', 2, '2026-09-21 07:32:25'),
(27, 2, 'Fabric Spray', 15, 0.00, 'Gallons', 2, '2026-08-09 21:19:06'),
(28, 2, 'LPG', 10, 0.00, 'Tanks', 5, '2026-08-09 21:19:40'),
(29, 2, 'Downy / Softener', 90, 0.00, 'Sachets', 5, '2026-09-17 05:29:58'),
(31, 3, 'Downy / Softener', 100, 0.00, 'Sachets', 5, '2026-09-08 17:06:49'),
(32, 3, 'Fabric Spray', 15, 0.00, 'Gallons', 2, '2026-09-08 17:12:36'),
(33, 3, 'LPG', 15, 0.00, 'Tanks', 5, '2026-09-08 17:11:58'),
(34, 5, 'Detergent', 100, 0.00, 'Sachets', 2, '2026-09-08 17:10:06'),
(35, 5, 'Downy / Softener', 100, 0.00, 'Sachets', 5, '2026-09-08 17:10:27'),
(36, 5, 'Fabric Spray', 15, 0.00, 'Gallons', 2, '2026-09-08 17:10:22'),
(37, 5, 'LPG', 15, 0.00, 'Tanks', 5, '2026-09-08 17:10:14'),
(38, 6, 'Detergent', 100, 0.00, 'Sachets', 2, '2026-09-08 17:10:57'),
(39, 6, 'Downy / Softener', 100, 0.00, 'Sachets', 5, '2026-09-08 17:11:15'),
(40, 6, 'Fabric Spray', 15, 0.00, 'Gallons', 2, '2026-09-08 17:11:10'),
(41, 6, 'LPG', 15, 0.00, 'Tanks', 5, '2026-09-08 17:11:05'),
(42, 7, 'Detergent', 95, 0.00, 'Sachets', 2, '2026-09-17 05:31:16'),
(43, 7, 'Downy / Softener', 95, 0.00, 'Sachets', 5, '2026-09-17 05:31:23'),
(44, 7, 'Fabric Spray', 14, 0.00, 'Gallons', 2, '2026-09-17 05:31:41'),
(45, 7, 'LPG', 15, 0.00, 'Tanks', 5, '2026-09-08 17:11:29'),
(50, 13, 'Detergent', 0, 0.00, 'Sachets', 2, '2026-09-21 07:36:55'),
(51, 13, 'Fabric Spray', 0, 0.00, 'Gallons', 2, '2026-09-21 07:36:55'),
(52, 13, 'LPG', 0, 0.00, 'Tanks', 5, '2026-09-21 07:36:55'),
(53, 13, 'Downy / Softener', 0, 0.00, 'Sachets', 5, '2026-09-21 07:36:55');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_history`
--

CREATE TABLE `inventory_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `movement_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `stock_before` int(11) NOT NULL,
  `stock_after` int(11) NOT NULL,
  `source` varchar(100) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_history`
--

INSERT INTO `inventory_history` (`id`, `inventory_id`, `branch_id`, `item_name`, `movement_type`, `quantity`, `stock_before`, `stock_after`, `source`, `user_id`, `created_at`) VALUES
(1, 26, 2, 'Detergent', 'OUT', 43, 93, 50, 'Admin inventory update', 1, '2026-09-17 05:00:38'),
(2, 26, 2, 'Detergent', 'IN', 50, 50, 100, 'Admin inventory update', 1, '2026-09-17 05:01:33'),
(3, 15, 1, 'Detergent', 'OUT', 5, 157, 152, 'Manager service order usage', 28, '2026-09-17 05:03:55'),
(4, 18, 1, 'Downy / Softener', 'OUT', 5, 144, 139, 'Manager service order usage', 28, '2026-09-17 05:03:55'),
(5, 26, 2, 'Detergent', 'OUT', 5, 100, 95, 'Admin inventory update', 1, '2026-09-17 05:29:51'),
(6, 29, 2, 'Downy / Softener', 'OUT', 3, 93, 90, 'Admin inventory update', 1, '2026-09-17 05:29:58'),
(7, 15, 1, 'Detergent', 'OUT', 2, 152, 150, 'Admin inventory update', 1, '2026-09-17 05:30:08'),
(8, 18, 1, 'Downy / Softener', 'OUT', 4, 139, 135, 'Admin inventory update', 1, '2026-09-17 05:30:16'),
(9, 21, 3, 'Detergent', 'IN', 60, 20, 80, 'Admin inventory update', 1, '2026-09-17 05:30:32'),
(10, 42, 7, 'Detergent', 'OUT', 5, 100, 95, 'Admin inventory update', 1, '2026-09-17 05:31:16'),
(11, 43, 7, 'Downy / Softener', 'OUT', 5, 100, 95, 'Admin inventory update', 1, '2026-09-17 05:31:23'),
(12, 44, 7, 'Fabric Spray', 'OUT', 14, 15, 1, 'Admin inventory update', 1, '2026-09-17 05:31:30'),
(13, 44, 7, 'Fabric Spray', 'IN', 13, 1, 14, 'Admin inventory update', 1, '2026-09-17 05:31:41'),
(14, 15, 1, 'Detergent', 'OUT', 3, 150, 147, 'Staff service order usage', 1, '2026-09-19 17:37:43'),
(15, 18, 1, 'Downy / Softener', 'OUT', 3, 135, 132, 'Staff service order usage', 1, '2026-09-19 17:37:43'),
(16, 15, 1, 'Detergent', 'OUT', 142, 147, 5, 'Admin inventory update', 1, '2026-09-19 17:38:26'),
(17, 18, 1, 'Downy / Softener', 'OUT', 127, 132, 5, 'Admin inventory update', 1, '2026-09-19 17:38:48'),
(18, 15, 1, 'Detergent', 'OUT', 2, 5, 3, 'Admin inventory update', 1, '2026-09-19 17:38:55'),
(19, 16, 1, 'Fabric Spray', 'OUT', 6, 9, 3, 'Admin inventory update', 1, '2026-09-19 17:39:12'),
(20, 16, 1, 'Fabric Spray', 'OUT', 2, 3, 1, 'Admin inventory update', 1, '2026-09-19 17:39:19'),
(21, 15, 1, 'Detergent', 'OUT', 2, 3, 1, 'Admin inventory update', 1, '2026-09-19 17:39:24'),
(22, 17, 1, 'LPG', 'OUT', 13, 14, 1, 'Admin inventory update', 1, '2026-09-19 17:39:30'),
(23, 15, 1, 'Detergent', 'IN', 14, 1, 15, 'Admin inventory update', 1, '2026-09-19 18:11:02'),
(24, 15, 1, 'Detergent', 'IN', 1, 15, 16, 'Admin inventory update', 1, '2026-09-19 18:11:08'),
(25, 18, 1, 'Downy / Softener', 'IN', 10, 5, 15, 'Admin inventory update', 1, '2026-09-19 18:11:16'),
(26, 18, 1, 'Downy / Softener', 'IN', 1, 15, 16, 'Admin inventory update', 1, '2026-09-19 18:11:24'),
(27, 17, 1, 'LPG', 'IN', 2, 1, 3, 'Admin inventory update', 1, '2026-09-19 18:11:31'),
(28, 17, 1, 'LPG', 'IN', 2, 3, 5, 'Admin inventory update', 1, '2026-09-19 18:11:36'),
(29, 16, 1, 'Fabric Spray', 'IN', 4, 1, 5, 'Admin inventory update', 1, '2026-09-19 18:11:41'),
(30, 16, 1, 'Fabric Spray', 'IN', 4, 5, 9, 'Admin inventory update', 1, '2026-09-19 18:11:47'),
(31, 15, 1, 'Detergent', 'OUT', 3, 16, 13, 'Staff service order usage', 1, '2026-09-20 10:19:06'),
(32, 18, 1, 'Downy / Softener', 'OUT', 3, 16, 13, 'Staff service order usage', 1, '2026-09-20 10:19:06'),
(33, 15, 1, 'Detergent', 'OUT', 2, 13, 11, 'Staff service order usage', 1, '2026-09-20 10:21:03'),
(34, 18, 1, 'Downy / Softener', 'OUT', 2, 13, 11, 'Staff service order usage', 1, '2026-09-20 10:21:03'),
(35, 15, 1, 'Detergent', 'OUT', 2, 11, 9, 'Staff service order usage', 1, '2026-09-20 10:33:24'),
(36, 18, 1, 'Downy / Softener', 'OUT', 2, 11, 9, 'Staff service order usage', 1, '2026-09-20 10:33:24'),
(37, 15, 1, 'Detergent', 'OUT', 2, 9, 7, 'Staff service order usage', 1, '2026-09-20 10:34:55'),
(38, 18, 1, 'Downy / Softener', 'OUT', 2, 9, 7, 'Staff service order usage', 1, '2026-09-20 10:34:55'),
(39, 15, 1, 'Detergent', 'OUT', 1, 7, 6, 'Staff service order usage', 1, '2026-09-20 10:38:57'),
(40, 18, 1, 'Downy / Softener', 'OUT', 1, 7, 6, 'Staff service order usage', 1, '2026-09-20 10:38:57'),
(41, 15, 1, 'Detergent', 'OUT', 2, 6, 4, 'Staff service order usage', 1, '2026-09-20 10:45:46'),
(42, 18, 1, 'Downy / Softener', 'OUT', 2, 6, 4, 'Staff service order usage', 1, '2026-09-20 10:45:46'),
(43, 15, 1, 'Detergent', 'OUT', 2, 4, 2, 'Staff service order usage', 1, '2026-09-21 06:41:50'),
(44, 18, 1, 'Downy / Softener', 'OUT', 2, 4, 2, 'Staff service order usage', 1, '2026-09-21 06:41:50'),
(45, 16, 1, 'Fabric Spray', 'OUT', 1, 9, 8, 'Staff manual deduction', 14, '2026-09-21 06:50:16'),
(46, 17, 1, 'LPG', 'OUT', 1, 5, 4, 'Staff manual deduction', 14, '2026-09-21 06:50:20'),
(47, 16, 1, 'Fabric Spray', 'OUT', 1, 8, 7, 'Staff manual deduction', 14, '2026-09-21 06:50:33'),
(48, 17, 1, 'LPG', 'OUT', 1, 4, 3, 'Staff manual deduction', 14, '2026-09-21 06:50:35'),
(49, 17, 1, 'LPG', 'OUT', 1, 3, 2, 'Staff manual deduction', 14, '2026-09-21 06:50:51'),
(50, 15, 1, 'Detergent', 'IN', 100, 2, 102, 'Manager inventory restock', 28, '2026-09-21 06:53:57'),
(51, 18, 1, 'Downy / Softener', 'IN', 100, 2, 102, 'Manager inventory restock', 28, '2026-09-21 06:53:57'),
(52, 16, 1, 'Fabric Spray', 'OUT', 2, 7, 5, 'Manager inventory adjustment', 28, '2026-09-21 06:53:57'),
(53, 17, 1, 'LPG', 'IN', 3, 2, 5, 'Manager inventory adjustment', 28, '2026-09-21 06:53:57'),
(54, 16, 1, 'Fabric Spray', 'IN', 1, 5, 6, 'Manager inventory adjustment', 28, '2026-09-21 06:54:30'),
(55, 15, 1, 'Detergent', 'IN', 100, 102, 202, 'Admin inventory update', 1, '2026-09-21 07:01:52'),
(56, 26, 2, 'Detergent', 'IN', 100, 95, 195, 'Admin inventory update', 1, '2026-09-21 07:32:25'),
(57, 22, 4, 'Detergent', 'IN', 90, 10, 100, 'Admin inventory update', 1, '2026-09-21 07:33:11'),
(58, 25, 4, 'Downy / Softener', 'IN', 95, 5, 100, 'Admin inventory update', 1, '2026-09-21 07:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `landing_contents`
--

CREATE TABLE `landing_contents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `content_key` varchar(255) NOT NULL,
  `content_value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landing_contents`
--

INSERT INTO `landing_contents` (`id`, `content_key`, `content_value`, `created_at`, `updated_at`) VALUES
(1, 'about_title', 'Modernizing Garment Care Since 2015', '2026-09-21 07:42:30', '2026-09-21 07:42:30'),
(2, 'about_content', 'Established in 2015, Laundry Care and Services started with a mission to provide dependable washing and drying solutions for local communities. Today, our operations span across 7 strategic branches in Carmona, Cavite, Biñan, and Cabuyao, Laguna—handling hundreds of regular transactions monthly.', '2026-09-21 07:42:30', '2026-09-21 07:42:30'),
(3, 'about_secondary', 'To ensure total service transparency, eliminate record errors, and streamline supply inventory, we integrated our Online Transaction and Monitoring System. This digital solution keeps you connected to your garment status every step of the way.', '2026-09-21 07:42:30', '2026-09-21 07:42:30'),
(4, 'contact_description', 'Have questions about our multi-branch services, drop-offs, or payment processing? Send us a message!', '2026-09-21 07:42:30', '2026-09-21 07:42:30'),
(5, 'contact_address', '9300 JM Loyola St., Maduya, Carmona, Cavite', '2026-09-21 07:42:30', '2026-09-21 07:42:30'),
(6, 'contact_email', 'laundrycare4@gmail.com', '2026-09-21 07:42:30', '2026-09-21 07:42:30'),
(7, 'contact_phone', '+63 909-825-6981 / +63 910-910-7296', '2026-09-21 07:42:30', '2026-09-21 07:42:30'),
(8, 'contact_hours', 'Monday – Sunday: 8:00 AM – 7:00 PM', '2026-09-21 07:42:30', '2026-09-21 07:42:30');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_points`
--

CREATE TABLE `loyalty_points` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points_earned` int(11) NOT NULL DEFAULT 0,
  `points_redeemed` int(11) NOT NULL DEFAULT 0,
  `source` varchar(255) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_points`
--

INSERT INTO `loyalty_points` (`id`, `user_id`, `points_earned`, `points_redeemed`, `source`, `transaction_id`, `created_at`) VALUES
(1, 16, 100, 0, 'Admin Reward', NULL, '2026-03-20 15:25:22'),
(2, 16, 100, 0, 'Admin Reward', NULL, '2026-03-20 15:33:52'),
(5, 22, 20, 0, '', NULL, '2026-03-29 09:44:26'),
(7, 24, 10, 0, '', NULL, '2026-04-14 11:03:01'),
(9, 34, 10, 0, 'New Member', NULL, '2026-08-14 00:10:33'),
(10, 35, 10, 0, 'New Member', NULL, '2026-08-14 00:11:51'),
(11, 37, 20, 0, 'Welcome Bonus', NULL, '2026-08-14 10:19:53'),
(12, 16, 0, 50, 'Admin Reward', NULL, '2026-08-30 00:33:09'),
(13, 22, 80, 0, 'Admin Reward', NULL, '2026-08-30 00:33:31');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_09_17_000000_create_landing_contents_table', 1),
(2, '2026_09_17_010000_create_inventory_history_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `ref_number` varchar(50) NOT NULL,
  `weight_kg` decimal(10,2) NOT NULL,
  `service_type` enum('Wash Only','Dry Only','Wash-Dry','Wash-Dry-Fold','Comforter','Comforter (Wash Only)','Comforter (Dry Only)','Comforter (Wash-Dry)','Comforter (Wash-Dry-Fold)','Others') NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `proof_of_payment` varchar(255) DEFAULT NULL,
  `order_status` enum('Pending','Washing','Drying','Ready','Claimed','Cancelled') DEFAULT 'Pending',
  `payment_status` varchar(50) DEFAULT 'Unpaid',
  `reference_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_paid` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `staff_id`, `branch_id`, `ref_number`, `weight_kg`, `service_type`, `total_amount`, `payment_method`, `proof_image`, `payment_reference`, `proof_of_payment`, `order_status`, `payment_status`, `reference_number`, `created_at`, `date_paid`) VALUES
(81, 16, 14, 1, 'LC-421865', 20.00, 'Comforter', 700.00, 'GCash', NULL, '3454353243243', 'uploads/payments/PAY_1777116392_81.jpg', 'Claimed', 'Paid', NULL, '2026-04-25 11:25:56', '2026-04-25 19:32:35'),
(90, 24, 14, 1, 'LC-37E993', 4.00, 'Wash-Dry-Fold', 140.00, 'GCash', NULL, '5244364565465', 'uploads/payments/PAY_1777723242_90.jpg', 'Claimed', 'Paid', NULL, '2026-05-02 12:00:03', '2026-05-02 20:00:42'),
(91, 16, 14, 1, 'LC-07677D', 12.00, 'Wash-Dry-Fold', 420.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-05-08 15:13:52', '2026-05-09 14:04:07'),
(92, 24, 14, 1, 'LC-0EAC2D', 8.00, 'Wash-Dry-Fold', 280.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-05-09 07:31:28', '2026-08-28 00:00:00'),
(93, 24, 14, 1, 'LC-841B2D', 8.00, 'Wash-Dry-Fold', 280.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-05-09 07:31:52', '2026-08-28 00:00:00'),
(94, 24, 1, 1, 'LC-518F73', 1.00, 'Wash-Dry-Fold', 35.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-13 22:10:29', '2026-08-28 00:00:00'),
(95, 16, 14, 1, 'LC-0BC781', 1.00, 'Wash-Dry-Fold', 35.00, 'GCash', NULL, '2156589562626', 'uploads/payments/PAY_1787847080_95.jpg', 'Claimed', 'Paid', NULL, '2026-08-14 06:19:28', '2026-08-27 16:11:42'),
(96, 24, 1, 1, 'LC-5F2A3A', 2.00, 'Wash-Dry-Fold', 70.00, 'GCash', NULL, '2345678765432', 'uploads/payments/PAY_1786692068_96.jpg', 'Claimed', 'Paid', NULL, '2026-08-13 22:26:29', '2026-08-14 07:23:47'),
(97, 16, 1, 1, 'LC-13E9E0', 3.00, 'Wash-Dry-Fold', 105.00, 'GCash', NULL, '5857882727828', 'uploads/payments/PAY_1787847349_97.jpg', 'Claimed', 'Paid', NULL, '2026-08-13 22:27:45', '2026-08-27 16:16:05'),
(98, 35, 1, 2, 'LC-FCA257', 2.00, 'Comforter', 70.00, NULL, NULL, NULL, NULL, 'Pending', 'Unpaid', NULL, '2026-08-14 00:12:31', NULL),
(99, 16, 1, 1, 'LC-C3C5F8', 5.00, 'Comforter', 175.00, 'GCash', NULL, '7522452527754', 'uploads/payments/PAY_1787847690_99.jpg', 'Claimed', 'Paid', NULL, '2026-08-27 08:20:28', '2026-08-27 16:21:44'),
(100, 24, 1, 1, 'LC-F0665B', 6.00, 'Wash-Dry-Fold', 210.00, 'GCash', NULL, '5858254245245', 'uploads/payments/PAY_1787848353_100.jpg', 'Claimed', 'Paid', NULL, '2026-08-27 08:30:55', '2026-08-27 16:32:44'),
(101, 24, 1, 1, 'LC-D8D142', 2.00, 'Wash-Dry-Fold', 70.00, 'GCash', NULL, '6857827852574', 'uploads/payments/PAY_1787848497_101.jpg', 'Claimed', 'Paid', NULL, '2026-08-27 08:34:05', '2026-08-27 16:35:24'),
(102, 16, 1, 1, 'LC-4908D4', 6.00, 'Wash-Dry-Fold', 210.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-27 09:03:00', '2026-08-28 00:00:00'),
(111, 16, 14, 1, 'LC-9DE8E6', 2.00, 'Wash-Dry-Fold', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-27 18:19:37', '2026-08-28 00:00:00'),
(112, 16, 14, 1, 'LC-6A91CB', 5.00, 'Wash-Dry-Fold', 175.00, 'GCash', NULL, '3338524528765', 'uploads/payments/PAY_1787855424_112.jpg', 'Claimed', 'Paid', NULL, '2026-08-27 18:29:26', '2026-08-27 18:31:32'),
(113, 16, 1, 1, 'LC-7A45B6', 2.00, 'Wash-Dry-Fold', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-27 18:41:11', '2026-08-28 00:00:00'),
(114, 16, 1, 1, 'LC-99B2A0', 10.00, 'Wash-Dry-Fold', 350.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-27 18:47:21', '2026-08-28 00:00:00'),
(115, 16, 1, 1, 'LC-6A0DD7', 10.00, 'Wash-Dry-Fold', 350.00, 'GCash', NULL, '2616515616514', 'uploads/payments/PAY_1787856652_115.jpg', 'Claimed', 'Paid', NULL, '2026-08-27 18:48:22', '2026-08-27 18:51:39'),
(116, 16, 1, 1, 'LC-ADAA77', 10.00, 'Wash-Dry-Fold', 350.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-27 18:52:58', '2026-08-28 00:00:00'),
(117, 16, 1, 1, 'LC-C6729F', 10.00, 'Wash-Dry-Fold', 350.00, 'GCash', NULL, '5242452452452', 'uploads/payments/PAY_1787856881_117.jpg', 'Claimed', 'Paid', NULL, '2026-08-27 18:53:32', '2026-08-27 18:55:00'),
(118, 16, 1, 1, 'LC-EA6DEB', 10.00, 'Wash-Dry-Fold', 350.00, 'GCash', NULL, '4528245282452', 'uploads/payments/PAY_1787857421_118.jpg', 'Claimed', 'Paid', NULL, '2026-08-27 19:02:54', '2026-08-28 03:03:55'),
(119, 16, 1, 1, 'LC-F4D3E0', 2.00, 'Wash-Dry-Fold', 70.00, 'GCash', NULL, '5252524285424', 'uploads/payments/PAY_1787893463_119.jpg', 'Claimed', 'Paid', NULL, '2026-08-28 05:03:27', '2026-08-28 13:06:10'),
(120, 16, 1, 1, 'LC-D95387', 3.00, 'Wash-Dry-Fold', 105.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-28 05:10:21', '2026-08-28 00:00:00'),
(121, 38, 1, 1, 'LC-259FF8', 5.00, 'Wash-Dry-Fold', 175.00, 'GCash', NULL, '2798258755875', 'uploads/payments/PAY_1787894509_121.jpg', 'Claimed', 'Paid', NULL, '2026-08-28 05:19:30', '2026-08-28 13:21:56'),
(122, 16, 1, 1, 'LC-A86CF5', 2.00, 'Wash-Dry-Fold', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-28 05:34:50', '2026-08-28 00:00:00'),
(123, 16, 1, 1, 'LC-20FD7B', 2.00, 'Wash-Dry-Fold', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-28 05:40:50', '2026-08-28 00:00:00'),
(124, 16, 1, 1, 'LC-F5F791', 2.00, 'Wash-Dry-Fold', 70.00, 'GCash', NULL, '5872782572752', 'uploads/payments/PAY_1787896068_124.jpg', 'Claimed', 'Paid', NULL, '2026-08-28 05:46:55', '2026-08-28 13:48:00'),
(125, 16, 1, 1, 'LC-934CA7', 2.00, 'Wash-Dry-Fold', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-28 05:48:57', '2026-08-28 00:00:00'),
(126, 37, 1, 1, 'LC-EA31E8', 2.00, 'Wash-Dry-Fold', 70.00, 'GCash', NULL, '6516516516516', 'uploads/payments/PAY_1788072367_126.jpg', 'Claimed', 'Paid', NULL, '2026-08-30 06:44:30', '2026-08-30 14:47:08'),
(127, 16, 1, 1, 'LC-B73EBD', 2.00, 'Wash-Dry-Fold', 70.00, 'GCash', NULL, '2875785287575', 'uploads/payments/PAY_1788078940_127.jpg', 'Claimed', 'Paid', NULL, '2026-08-30 08:34:19', '2026-08-30 16:35:54'),
(128, 16, 1, 1, 'LC-293E0F', 2.00, 'Wash-Dry-Fold', 70.00, 'GCash', NULL, '8282598727982', 'uploads/payments/PAY_1788082810_128.jpg', 'Claimed', 'Paid', NULL, '2026-08-30 09:39:14', '2026-08-30 17:40:33'),
(129, 16, 1, 1, 'LC-C57F5D', 2.00, 'Wash-Dry-Fold', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-30 09:41:16', '2026-08-30 00:00:00'),
(130, 16, 1, 1, 'LC-AAE146', 10.00, 'Wash-Dry-Fold', 350.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-30 09:43:06', '2026-08-30 00:00:00'),
(131, 16, 1, 1, 'LC-44285E', 2.00, 'Wash-Dry-Fold', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-08-30 09:45:24', '2026-08-30 00:00:00'),
(132, 16, 1, 1, 'LC-821EAA', 5.00, 'Wash-Dry-Fold', 175.00, 'GCash', NULL, '5863235426245', 'uploads/payments/PAY_1788232146_132.jpg', 'Claimed', 'Paid', NULL, '2026-09-01 03:07:52', '2026-09-01 11:09:29'),
(133, 16, 1, 1, 'LC-01BA6F', 5.00, 'Wash-Dry-Fold', 175.00, 'GCash', NULL, '6875978278527', 'uploads/payments/PAY_1788232730_133.jpg', 'Claimed', 'Paid', NULL, '2026-09-01 03:17:20', '2026-09-01 11:19:08'),
(134, 16, 1, 1, 'LC-4980C0', 5.00, 'Wash-Dry-Fold', 175.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-01 03:19:48', '2026-09-01 00:00:00'),
(135, 16, 1, 1, 'LC-C5D922', 5.00, 'Wash-Dry-Fold', 175.00, 'GCash', NULL, '2145727852727', 'uploads/payments/PAY_1788243473_135.jpg', 'Claimed', 'Paid', NULL, '2026-09-01 06:15:40', '2026-09-01 14:18:15'),
(136, 16, 1, 1, 'LC-F6D785', 8.00, 'Wash-Dry-Fold', 280.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-02 07:43:11', '2026-09-02 00:00:00'),
(137, 16, 1, 1, 'LC-4CD577', 6.00, 'Wash-Dry-Fold', 210.00, 'GCash', NULL, '7225252782782', 'uploads/payments/PAY_1788840808_137.jpg', 'Claimed', 'Paid', NULL, '2026-09-04 05:23:48', '2026-09-08 12:16:52'),
(138, 16, 1, 1, 'LC-CC304F', 2.00, 'Wash-Dry-Fold', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-08 13:07:40', '2026-09-09 00:00:00'),
(139, 35, 1, 2, 'LC-07D081', 5.00, 'Wash-Dry-Fold', 175.00, 'GCash', NULL, '8752782527252', 'uploads/payments/PAY_1788885701_139.jpg', 'Ready', 'Paid', NULL, '2026-09-08 16:38:40', '2026-09-09 00:42:38'),
(140, 35, 1, 2, 'LC-DB3768', 5.00, 'Wash-Dry-Fold', 175.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-08 16:50:05', NULL),
(141, 16, 28, 1, 'LC-B477D6', 2.00, 'Wash-Dry-Fold', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-09 13:20:09', '2026-09-09 00:00:00'),
(142, 16, 1, 1, 'LC-081723', 5.00, 'Wash-Dry-Fold', 175.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-09 13:22:00', '2026-09-10 00:00:00'),
(143, 16, 28, 1, 'LC-B95D6B', 8.00, 'Comforter (Wash-Dry-Fold)', 240.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-10 09:45:52', '2026-09-15 00:00:00'),
(144, 16, 28, 1, 'LC-C04AD0', 5.00, 'Comforter (Wash-Dry-Fold)', 240.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-15 05:36:21', '2026-09-17 00:00:00'),
(145, 16, 28, 1, 'LC-B550BE', 3.00, 'Comforter (Dry Only)', 130.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-17 05:03:55', '2026-09-20 00:00:00'),
(146, 16, 1, 1, 'LC-743231', 5.00, 'Comforter (Wash-Dry-Fold)', 240.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-19 17:37:43', '2026-09-20 00:00:00'),
(147, 16, 16, 1, 'LC-61CA0C', 0.00, 'Others', 0.00, NULL, NULL, NULL, NULL, 'Cancelled', 'Service Request', NULL, '2026-09-20 09:25:26', NULL),
(148, 16, 1, 1, 'LC-FED23B', 2.00, 'Wash-Dry-Fold', 170.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-20 10:17:33', '2026-09-20 00:00:00'),
(149, 16, 1, 1, 'LC-ACD420', 7.00, 'Wash Only', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-20 10:19:06', '2026-09-20 00:00:00'),
(150, 16, 1, 1, 'LC-F4E35E', 8.00, 'Wash Only', 70.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-20 10:30:38', '2026-09-20 00:00:00'),
(151, 38, 1, 1, 'LC-482D28', 8.00, 'Comforter (Wash Only)', 140.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-20 10:33:24', '2026-09-20 00:00:00'),
(152, 16, 1, 1, 'LC-180166', 2.00, 'Wash Only', 70.00, 'GCash', NULL, '2655156156155', 'uploads/payments/PAY_1789973316_152.jpg', 'Ready', 'Paid', NULL, '2026-09-20 10:38:57', '2026-09-21 14:49:14'),
(153, 16, 16, 1, 'LC-836899', 0.00, 'Others', 0.00, NULL, NULL, NULL, NULL, 'Cancelled', 'Service Request', NULL, '2026-09-20 10:44:24', NULL),
(154, 38, 1, 1, 'LC-A23B45', 8.00, 'Wash Only', 70.00, NULL, NULL, NULL, NULL, 'Pending', 'Unpaid', NULL, '2026-09-20 10:45:46', NULL),
(155, 16, 16, 1, 'LC-CB0F17', 0.00, 'Others', 0.00, NULL, NULL, NULL, NULL, 'Pending', 'Service Request', NULL, '2026-09-21 06:38:52', NULL),
(156, 16, 1, 1, 'LC-EC4692', 8.00, 'Wash-Dry-Fold', 170.00, NULL, NULL, NULL, NULL, 'Claimed', 'Paid', NULL, '2026-09-21 06:41:50', '2026-09-21 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','customer','manager') NOT NULL DEFAULT 'customer',
  `branch_id` int(11) DEFAULT NULL,
  `referral_code` varchar(20) DEFAULT NULL,
  `referred_by` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default_avatar.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0,
  `archive_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `phone`, `email`, `password`, `role`, `branch_id`, `referral_code`, `referred_by`, `profile_pic`, `created_at`, `is_archived`, `archive_date`) VALUES
(1, 'Virgilio Rayos', '09109107296', 'vierghiellrayos06@gmail.com', '$2y$12$DRdhQ/gWtdgNACfmBNE8zuADifSeNIArr.QlF8qemVyuJ5T9lX9K.', 'admin', NULL, 'VIR346', '', 'default_avatar.png', '2026-03-04 12:46:01', 0, NULL),
(14, 'Azlee Estilon', '12345678909', 'azlee@gmail.com', '$2y$10$l00ICCzipJE.tRiLfPIy5O7ZMylE5OAVinkhB0g/W/SNfagFD0Uqa', 'staff', 1, NULL, NULL, 'default_avatar.png', '2026-03-18 15:18:52', 0, NULL),
(15, 'Diego Anover', '09876543212', 'diego@gmail.com', '$2y$10$Rf03bXJ6u1dCZ56z5w6Q9OVPMr0hfBdbp2SdVARZtxdyQ/M5dEJ6y', 'staff', 2, NULL, NULL, 'default_avatar.png', '2026-03-18 15:19:21', 0, NULL),
(16, 'Rayos Vergel', '09081332403', 'rayosvirgilio6@gmail.com', '$2y$12$9SQOB83s6HSMQKWg6vcvm.HwA3m2uDtKXzxaRqvbYE3vD/Nd7ubyS', 'customer', 1, 'MIL344', '', 'profile_pics/gq6U4JSC4cet7l1KD3qtl4gblrJmoGWTJEPTIczK.jpg', '2026-03-18 15:20:01', 0, NULL),
(22, 'jayvin medina', '1234567854', 'jayvin@gmail.com', '$2y$10$U6woEXIecJ4iWIY/fEdkv.VPREl8JVD1qRDTtxD3qF6W6Hc8JCJ5O', 'customer', 4, 'LC-87857', NULL, 'default_avatar.png', '2026-03-29 09:44:26', 0, NULL),
(24, 'Lance Mediona', '9109107296', 'estilonazlee123@gmail.com', '$2y$10$xenBwZVVQyGqe.ajUTbxoeajypAsWeBVw0sYXnHsZ4gTxrT8HimMW', 'customer', 1, 'LAN869', NULL, 'default_avatar.png', '2026-04-14 11:03:01', 0, NULL),
(26, 'KintKint', '12345678901', 'kintkint@gmail.com', '$2y$10$0UOk5BgVGvIa4skD7r.DyOk/HaNkUklYtSggp6zqnBL5EMgI2WiJ6', 'manager', 2, NULL, NULL, 'default_avatar.png', '2026-08-09 20:35:37', 0, NULL),
(28, 'Lara', '123456789098', 'lara@gmail.com', '$2y$10$Evn.SgzFCLbjE9j1V1dqVO4PzVlARiIKh1mHLupqnDcWbfYNZ.OMm', 'manager', 1, NULL, NULL, 'default_avatar.png', '2026-08-09 21:01:45', 0, NULL),
(34, 'insu', '0923536785', 'insu@gmail.com', '$2y$12$vMDOQsAFZnyQoSSX1Qp8YONkL5NIAChDZNby23PoWOdYFN1dKKkQu', 'customer', 1, 'INS646', NULL, 'default_avatar.png', '2026-08-14 08:10:33', 0, NULL),
(35, 'kara', '09563485693', 'kara@gmail.com', '$2y$12$EwsvTsYqRkVWmDstA9M9/u0U0yIVZSUGoa2kauj5GVi18nD2py/fW', 'customer', 2, 'KAR265', NULL, 'default_avatar.png', '2026-08-14 08:11:51', 0, NULL),
(37, 'cuties', '09564368323', 'diegoanovergwapo12345@gmail.com', '$2y$12$ET6iqs6CJoTTqFDdSQBSoO9GD7j9CbiKYhXvQv8S1NAx7MOue5DYe', 'customer', 1, 'LC-BB6E0', NULL, 'profile_pics/35bOaMD6lP7xH8PjppN5coEoaRo5iZKg5IKu5yKf.jpg', '2026-08-14 10:19:53', 0, NULL),
(38, 'Mikaela Maalat', '09098256331', 'maalatmikaela501@gmail.com', '$2y$12$yFE509WmcU2RrktrCtb94un0x4Bp0W1HrCVrN0ER4UZKv46i1tsjO', 'customer', 1, NULL, NULL, 'default_avatar.png', '2026-08-28 05:18:20', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_history_branch_id_created_at_index` (`branch_id`,`created_at`),
  ADD KEY `inventory_history_inventory_id_index` (`inventory_id`);

--
-- Indexes for table `landing_contents`
--
ALTER TABLE `landing_contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `landing_contents_content_key_unique` (`content_key`);

--
-- Indexes for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ref_number` (`ref_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `fk_user_branch` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `landing_contents`
--
ALTER TABLE `landing_contents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_points_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
