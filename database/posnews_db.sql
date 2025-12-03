-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 05:59 AM
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
-- Database: `posnews_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `category_name`) VALUES
(5, 'Antispasmodic / Anticholinergic', NULL),
(6, 'Antiviral / Immune Booster', NULL),
(7, 'Cold & Allergy / Decongestant', NULL),
(8, 'Antidiarrheal', NULL),
(9, 'Antispasmodic (Stomach / Abdominal Cramps)', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` varchar(255) DEFAULT NULL,
  `expiration` date DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `mg` varchar(50) DEFAULT NULL,
  `medicine_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `stock`, `expiration`, `barcode`, `batch_number`, `quantity`, `mg`, `medicine_type`) VALUES
(37, 7, 'Symdex-D', 5.00, '89', '2028-09-11', '±4806502850008', NULL, 0, '500', NULL),
(39, 5, 'Dicycloverine Hydrochloride', 10.00, '71', '2025-12-04', '±4806520352898', NULL, 0, '200', NULL),
(40, 8, 'Loperamide', 10.00, '100', '2025-12-04', '±8904182012726', NULL, 0, '100', NULL),
(41, 5, 'Inosine Dimepranol Acedoben', 10.00, '100', '2025-12-06', '±4807788654014', NULL, 0, '500', NULL),
(48, 9, 'Celecoxib', 15.00, '92', '2025-12-11', '±4806503123897', NULL, 0, '200', 'Capsule'),
(49, 5, 'Cefalexin', 10.00, '97', '2027-07-07', '±4806523301893', NULL, 0, '500', 'Capsule'),
(51, 5, 'lozartan', 10.00, '100', '2025-12-04', '±4806524146769', NULL, 0, '50', 'Tablet'),
(52, 8, 'neozip', 20.00, '20', '2025-12-29', '123456789', NULL, 0, '60mg', 'Tablet');

-- --------------------------------------------------------

--
-- Table structure for table `product_details`
--

CREATE TABLE `product_details` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `barcode` varchar(50) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` varchar(255) DEFAULT '0',
  `expiration` date DEFAULT NULL,
  `quantity` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_details`
--

INSERT INTO `product_details` (`id`, `category_id`, `category_name`, `name`, `barcode`, `batch_number`, `price`, `stock`, `expiration`, `quantity`) VALUES
(1, 1, 'Cold & Allergy / Decongestant', 'Symdex-D', '±4806502850008', NULL, 5.99, '100', NULL, '1000'),
(2, 2, 'Antidiarrheal', 'Loperamide', '±8904182012726', NULL, 10.50, '100', NULL, NULL),
(3, 1, 'Antiviral / Immune Booster', 'Inosine Dimepranol Acedoben', '±4807788654014', NULL, 15.50, '100', NULL, NULL),
(4, 3, 'Antispasmodic / Anticholinergic (Stomach & Intestinal Cramps)', 'Dicycloverine Hydrochloride', '±4806520352898', NULL, 10.00, '100', NULL, NULL),
(5, 4, 'Antispasmodic (Stomach / Abdominal Cramps)', 'Hyoscine-N-Butylbromide', '±4806527199823', NULL, 5.75, '100', NULL, NULL),
(7, NULL, 'Antibacterial', 'Vinmox', '±4809015614013', NULL, 75.00, '50', NULL, '100'),
(8, NULL, 'Coffee', 'Nescafe Classic 50g', '4801234560028', NULL, 85.00, '40', NULL, NULL),
(9, NULL, 'Instant Noodles', 'Lucky Me Pancit Canton', '4801234560035', NULL, 17.00, '200', NULL, NULL),
(10, NULL, 'Milk', 'Bear Brand Milk 320g', '4801234560042', NULL, 90.00, '35', NULL, NULL),
(11, NULL, 'Detergent', 'Surf Detergent 350g', '4801234560059', NULL, 25.00, '80', NULL, NULL),
(12, NULL, 'Toiletries', 'Safeguard Soap 90g', '4801234560066', NULL, 32.00, '100', NULL, NULL),
(13, NULL, 'Toiletries', 'Colgate Toothpaste 120ml', '4801234560073', NULL, 75.00, '60', NULL, NULL),
(14, NULL, 'Canned Goods', 'Argentina Corned Beef 150g', '4801234560080', NULL, 38.00, '120', NULL, NULL),
(15, NULL, 'Condiments', 'Datu Puti Soy Sauce 1L', '4801234560097', NULL, 45.00, '40', NULL, NULL),
(16, NULL, 'Alcohol', 'GSM Blue 750ml', '4801234560103', NULL, 135.00, '25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `cash` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL,
  `sale_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `cash_given` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cashier` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `total`, `cash`, `change_amount`, `sale_date`, `total_amount`, `cash_given`, `cashier`) VALUES
(1, 36.00, 40.00, 4.00, '2025-09-04 14:00:35', 0.00, 0.00, 'cashier'),
(2, 36.00, 40.00, 4.00, '2025-09-04 14:00:38', 0.00, 0.00, 'cashier'),
(3, 0.00, 0.00, 0.00, '2025-09-25 10:26:07', 10.00, 0.00, 'cashier@gmail.com'),
(4, 0.00, 0.00, 0.00, '2025-09-25 10:26:08', 10.00, 0.00, 'cashier@gmail.com'),
(5, 0.00, 0.00, 0.00, '2025-09-25 10:43:13', 0.00, 30.00, ''),
(6, 0.00, 0.00, 0.00, '2025-09-25 10:49:52', 10.00, 20.00, 'cashier@gmail.com'),
(7, 0.00, 0.00, 0.00, '2025-09-25 10:54:57', 10.00, 20.00, ''),
(8, 0.00, 0.00, 0.00, '2025-09-25 10:56:02', 20.00, 30.00, ''),
(9, 0.00, 0.00, 0.00, '2025-09-25 10:56:30', 10.00, 20.00, ''),
(10, 0.00, 0.00, 0.00, '2025-09-25 10:56:55', 20.00, 30.00, ''),
(11, 0.00, 0.00, 0.00, '2025-09-25 11:05:11', 20.00, 50.00, ''),
(12, 0.00, 0.00, 0.00, '2025-10-09 11:03:16', 20.00, 30.00, ''),
(13, 0.00, 0.00, 0.00, '2025-10-09 11:05:38', 50.00, 70.00, ''),
(14, 0.00, 0.00, 0.00, '2025-10-09 13:23:25', 10.00, 21.00, ''),
(15, 0.00, 0.00, 0.00, '2025-10-09 14:19:05', 20.00, 21.00, ''),
(16, 0.00, 0.00, 0.00, '2025-10-09 14:30:25', 30.00, 60.00, ''),
(17, 0.00, 0.00, 0.00, '2025-10-16 09:11:50', 30.00, 200.00, ''),
(18, 0.00, 0.00, 0.00, '2025-10-23 13:42:45', 30.00, 31.00, ''),
(19, 0.00, 0.00, 0.00, '2025-10-23 14:14:44', 60.00, 100.00, ''),
(20, 0.00, 0.00, 0.00, '2025-10-23 20:41:30', 12.00, 20.00, ''),
(21, 0.00, 0.00, 0.00, '2025-11-11 15:29:28', 24.00, 30.00, ''),
(22, 0.00, 0.00, 0.00, '2025-11-11 15:29:56', 12.00, 59.00, ''),
(23, 0.00, 0.00, 0.00, '2025-11-11 20:18:13', 12.00, 34.00, ''),
(24, 0.00, 0.00, 0.00, '2025-11-11 20:32:35', 12.00, 21.00, ''),
(25, 0.00, 0.00, 0.00, '2025-11-11 20:44:33', 12.00, 21.00, ''),
(26, 0.00, 0.00, 0.00, '2025-11-11 22:22:09', 204.00, 500.00, ''),
(27, 0.00, 0.00, 0.00, '2025-11-11 23:06:49', 12.00, 20.00, ''),
(28, 0.00, 0.00, 0.00, '2025-11-11 23:09:32', 12.00, 20.00, ''),
(29, 0.00, 0.00, 0.00, '2025-11-11 23:11:29', 12.00, 20.00, ''),
(30, 0.00, 0.00, 0.00, '2025-11-11 23:13:53', 12.00, 21.00, ''),
(31, 0.00, 0.00, 0.00, '2025-11-11 23:14:19', 0.00, 21.00, ''),
(32, 0.00, 0.00, 0.00, '2025-11-11 23:14:38', 12.00, 21.00, ''),
(33, 0.00, 0.00, 0.00, '2025-11-11 23:15:31', 12.00, 21.00, ''),
(34, 0.00, 0.00, 0.00, '2025-11-11 23:17:09', 12.00, 21.00, ''),
(35, 0.00, 0.00, 0.00, '2025-11-19 08:10:15', 36.00, 1000.00, ''),
(36, 0.00, 0.00, 0.00, '2025-11-19 08:20:14', 1056.00, 2000.00, ''),
(37, 0.00, 0.00, 0.00, '2025-11-19 09:11:56', 348.00, 2000.00, ''),
(38, 0.00, 0.00, 0.00, '2025-11-19 09:12:31', 216.00, 2000.00, ''),
(39, 0.00, 0.00, 0.00, '2025-11-19 16:54:07', 45.00, 50.00, ''),
(40, 0.00, 0.00, 0.00, '2025-11-20 11:17:07', 30.00, 100.00, ''),
(41, 0.00, 0.00, 0.00, '2025-11-20 18:30:01', 180.00, 200.00, ''),
(42, 0.00, 0.00, 0.00, '2025-11-22 14:58:02', 15.00, 20.00, ''),
(43, 0.00, 0.00, 0.00, '2025-11-22 14:58:02', 0.00, 20.00, ''),
(44, 0.00, 0.00, 0.00, '2025-11-22 15:17:54', 15.00, 20.00, ''),
(45, 0.00, 0.00, 0.00, '2025-11-22 15:20:58', 19.00, 100.00, ''),
(46, 0.00, 0.00, 0.00, '2025-11-22 15:22:32', 0.00, 100.00, ''),
(47, 0.00, 0.00, 0.00, '2025-11-22 15:24:08', 34.00, 100.00, ''),
(48, 0.00, 0.00, 0.00, '2025-11-22 15:25:07', 0.00, 100.00, ''),
(49, 0.00, 0.00, 0.00, '2025-11-22 15:25:30', 15.00, 100.00, ''),
(50, 0.00, 0.00, 0.00, '2025-11-22 15:26:32', 30.00, 100.00, ''),
(51, 0.00, 0.00, 0.00, '2025-11-22 15:28:46', 49.00, 50.00, ''),
(52, 0.00, 0.00, 0.00, '2025-11-22 15:31:59', 34.00, 50.00, ''),
(53, 0.00, 0.00, 0.00, '2025-11-22 15:33:13', 39.00, 50.00, ''),
(54, 0.00, 0.00, 0.00, '2025-11-22 15:35:47', 19.00, 20.00, ''),
(55, 0.00, 0.00, 0.00, '2025-11-22 15:36:44', 12.00, 13.00, ''),
(56, 0.00, 0.00, 0.00, '2025-11-22 15:37:51', 12.00, 13.00, ''),
(57, 0.00, 0.00, 0.00, '2025-11-22 15:44:21', 34.00, 50.00, ''),
(58, 0.00, 0.00, 0.00, '2025-11-22 15:45:47', 15.00, 100.00, ''),
(59, 0.00, 0.00, 0.00, '2025-11-22 15:46:15', 19.00, 40.00, ''),
(60, 0.00, 0.00, 0.00, '2025-11-22 15:46:59', 19.00, 20.00, ''),
(61, 0.00, 0.00, 0.00, '2025-11-22 15:49:12', 34.00, 100.00, ''),
(62, 0.00, 0.00, 0.00, '2025-11-22 23:39:49', 30.00, 40.00, ''),
(63, 0.00, 0.00, 0.00, '2025-11-22 23:39:54', 30.00, 40.00, ''),
(64, 0.00, 0.00, 0.00, '2025-11-22 23:39:55', 30.00, 40.00, ''),
(65, 0.00, 0.00, 0.00, '2025-11-22 23:39:58', 30.00, 40.00, ''),
(66, 0.00, 0.00, 0.00, '2025-11-22 23:39:58', 30.00, 40.00, ''),
(67, 0.00, 0.00, 0.00, '2025-11-22 23:39:59', 30.00, 40.00, ''),
(68, 0.00, 0.00, 0.00, '2025-11-22 23:39:59', 30.00, 40.00, ''),
(69, 0.00, 0.00, 0.00, '2025-11-22 23:39:59', 30.00, 40.00, ''),
(70, 0.00, 0.00, 0.00, '2025-11-22 23:39:59', 30.00, 40.00, ''),
(71, 0.00, 0.00, 0.00, '2025-11-22 23:40:02', 30.00, 40.00, ''),
(72, 0.00, 0.00, 0.00, '2025-11-22 23:40:02', 30.00, 40.00, ''),
(73, 0.00, 0.00, 0.00, '2025-11-22 23:40:02', 30.00, 40.00, ''),
(74, 0.00, 0.00, 0.00, '2025-11-22 23:40:02', 30.00, 40.00, ''),
(75, 0.00, 0.00, 0.00, '2025-11-22 23:40:02', 30.00, 40.00, ''),
(76, 0.00, 0.00, 0.00, '2025-11-22 23:40:03', 30.00, 40.00, ''),
(77, 0.00, 0.00, 0.00, '2025-11-22 23:40:03', 30.00, 40.00, ''),
(78, 0.00, 0.00, 0.00, '2025-11-22 23:40:03', 30.00, 40.00, ''),
(79, 0.00, 0.00, 0.00, '2025-11-22 23:40:03', 30.00, 40.00, ''),
(80, 0.00, 0.00, 0.00, '2025-11-22 23:40:03', 30.00, 40.00, ''),
(81, 0.00, 0.00, 0.00, '2025-11-22 23:40:03', 30.00, 40.00, ''),
(82, 0.00, 0.00, 0.00, '2025-11-22 23:40:04', 30.00, 40.00, ''),
(83, 0.00, 0.00, 0.00, '2025-11-22 23:40:04', 30.00, 40.00, ''),
(84, 0.00, 0.00, 0.00, '2025-11-22 23:42:00', 11.98, 20.00, ''),
(85, 0.00, 0.00, 0.00, '2025-11-23 00:50:02', 11.98, 20.00, ''),
(86, 0.00, 0.00, 0.00, '2025-11-23 00:50:05', 11.98, 20.00, ''),
(87, 0.00, 0.00, 0.00, '2025-11-23 00:50:14', 11.98, 20.00, ''),
(88, 0.00, 0.00, 0.00, '2025-11-23 00:50:24', 11.98, 20.00, ''),
(89, 0.00, 0.00, 0.00, '2025-11-23 02:03:35', 5.99, 20.00, ''),
(90, 0.00, 0.00, 0.00, '2025-11-23 02:27:07', 11.98, 12.00, ''),
(91, 0.00, 0.00, 0.00, '2025-11-23 02:30:23', 17.97, 20.00, ''),
(92, 0.00, 0.00, 0.00, '2025-11-23 03:09:21', 5.99, 12.00, ''),
(93, 0.00, 0.00, 0.00, '2025-11-23 17:22:25', 11.98, 20.00, ''),
(94, 0.00, 0.00, 0.00, '2025-11-23 17:25:09', 20.00, 30.00, ''),
(95, 0.00, 0.00, 0.00, '2025-11-24 23:34:04', 17.97, 20.00, ''),
(96, 0.00, 0.00, 0.00, '2025-11-24 23:37:12', 17.97, 20.00, ''),
(97, 0.00, 0.00, 0.00, '2025-11-24 23:37:31', 23.96, 50.00, ''),
(98, 0.00, 0.00, 0.00, '2025-11-24 23:56:27', 20.00, 20.00, ''),
(99, 0.00, 0.00, 0.00, '2025-11-24 23:59:56', 30.00, 200.00, ''),
(100, 0.00, 0.00, 0.00, '2025-11-25 00:05:48', 50.00, 100.00, ''),
(101, 0.00, 0.00, 0.00, '2025-11-25 00:06:08', 50.00, 100.00, ''),
(102, 0.00, 0.00, 0.00, '2025-11-25 00:19:20', 100.00, 100.00, ''),
(103, 0.00, 0.00, 0.00, '2025-11-25 00:37:18', 41.99, 50.00, ''),
(104, 0.00, 0.00, 0.00, '2025-11-25 12:12:52', 72.00, 100.00, ''),
(105, 0.00, 0.00, 0.00, '2025-11-25 12:15:20', 26.00, 39.00, ''),
(106, 0.00, 0.00, 0.00, '2025-11-25 12:16:54', 10.50, 20.00, ''),
(107, 0.00, 0.00, 0.00, '2025-11-25 12:17:16', 15.50, 20.00, ''),
(108, 0.00, 0.00, 0.00, '2025-11-25 12:27:39', 15.50, 100.00, ''),
(109, 0.00, 0.00, 0.00, '2025-11-25 12:31:51', 10.00, 20.00, ''),
(110, 0.00, 0.00, 0.00, '2025-11-25 12:33:50', 10.00, 20.00, ''),
(111, 0.00, 0.00, 0.00, '2025-11-25 12:40:57', 20.00, 30.00, ''),
(112, 0.00, 0.00, 0.00, '2025-11-25 12:44:04', 15.50, 30.00, ''),
(113, 0.00, 0.00, 0.00, '2025-11-25 14:27:58', 36.00, 100.00, ''),
(114, 0.00, 0.00, 0.00, '2025-11-25 15:29:23', 10.00, 20.00, ''),
(115, 0.00, 0.00, 0.00, '2025-11-25 15:29:49', 77.50, 100.00, ''),
(116, 0.00, 0.00, 0.00, '2025-11-25 23:27:23', 20.00, 30.00, ''),
(117, 0.00, 0.00, 0.00, '2025-11-26 00:54:48', 20.00, 30.00, ''),
(118, 0.00, 0.00, 0.00, '2025-11-26 09:09:51', 25.00, 30.00, ''),
(119, 0.00, 0.00, 0.00, '2025-12-03 09:14:47', 70.00, 100.00, ''),
(120, 0.00, 0.00, 0.00, '2025-12-03 09:15:04', 0.00, 100.00, ''),
(121, 0.00, 0.00, 0.00, '2025-12-03 09:15:15', 0.00, 100.00, ''),
(122, 0.00, 0.00, 0.00, '2025-12-03 09:19:38', 0.00, 100.00, ''),
(123, 0.00, 0.00, 0.00, '2025-12-03 09:19:48', 15.00, 100.00, ''),
(124, 0.00, 0.00, 0.00, '2025-12-03 09:19:56', 0.00, 100.00, ''),
(125, 0.00, 0.00, 0.00, '2025-12-03 09:27:40', 25.00, 50.00, ''),
(126, 0.00, 0.00, 0.00, '2025-12-03 09:27:50', 20.00, 60.00, ''),
(127, 0.00, 0.00, 0.00, '2025-12-03 09:32:44', 20.00, 20.00, ''),
(128, 0.00, 0.00, 0.00, '2025-12-03 09:35:53', 25.00, 50.00, ''),
(129, 0.00, 0.00, 0.00, '2025-12-03 09:36:32', 25.00, 50.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`) VALUES
(94, 95, 37, 3, 5.99),
(95, 96, 37, 3, 5.99),
(96, 97, 37, 4, 5.99),
(97, 98, 39, 2, 10.00),
(98, 99, 39, 3, 10.00),
(99, 100, 39, 5, 10.00),
(100, 101, 39, 5, 10.00),
(101, 102, 39, 10, 10.00),
(102, 103, 39, 1, 10.00),
(103, 103, 41, 1, 15.50),
(104, 103, 40, 1, 10.50),
(105, 103, 37, 1, 5.99),
(106, 104, 39, 1, 10.00),
(107, 104, 41, 4, 15.50),
(108, 105, 40, 1, 10.50),
(109, 105, 41, 1, 15.50),
(110, 106, 40, 1, 10.50),
(111, 107, 41, 1, 15.50),
(112, 108, 41, 1, 15.50),
(113, 109, 39, 1, 10.00),
(114, 110, 39, 1, 10.00),
(115, 111, 39, 2, 10.00),
(116, 112, 41, 1, 15.50),
(117, 113, 39, 1, 10.00),
(118, 113, 41, 1, 15.50),
(119, 113, 40, 1, 10.50),
(120, 114, 39, 1, 10.00),
(121, 115, 41, 5, 15.50),
(122, 116, 39, 2, 10.00),
(123, 117, 39, 2, 10.00),
(124, 118, 49, 1, 10.00),
(125, 118, 37, 3, 5.00),
(126, 119, 37, 5, 5.00),
(127, 119, 48, 3, 15.00),
(128, 123, 49, 1, 10.00),
(129, 123, 37, 1, 5.00),
(130, 125, 49, 1, 10.00),
(131, 125, 48, 1, 15.00),
(132, 126, 48, 1, 15.00),
(133, 126, 37, 1, 5.00),
(134, 127, 37, 1, 5.00),
(135, 127, 48, 1, 15.00),
(136, 128, 48, 1, 15.00),
(137, 128, 39, 1, 10.00),
(138, 129, 48, 1, 15.00),
(139, 129, 39, 1, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier') NOT NULL,
  `reset_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `reset_code`, `created_at`) VALUES
(1, 'cashier', 'cashier1@gmail.com', '$2y$10$M1q26K5ckVMGGGJv9giq8eSrP95GLr0hTADB5i0mDo9UwtyzisugW', 'cashier', NULL, '2025-09-04 05:37:43'),
(2, 'admin', 'admin1@gmail.com', '$2y$10$bf04hNJOWASHGEb5Mgby5e6FMLy0Lad4gnpkd.yV1hXN7uCAhTeY2', 'admin', NULL, '2025-09-04 05:38:30'),
(3, 'cashier123', 'cashier@gmail.com', '$2y$10$WoBBOfT55pRYPcnlufzgJeDXImK7/GP60CcMs9vxQvzvq8mCXp2Ca', 'cashier', NULL, '2025-09-25 00:44:40'),
(4, 'aldem123', 'aldem@gmail.com', '$2y$10$.azxdHbjCkx.qXa11s6XwuMUaY0DkgbgFUI2Sfo0CagUj7eDzOEHe', 'admin', NULL, '2025-09-25 00:48:49'),
(5, 'adminko', 'admin@example.com', '$2y$10$6qqYTv8y7Vpyi6HB2KYSu.eJllU2zR81VAQdUgjfMNVVp/WNU9wlK', 'admin', NULL, '2025-11-11 11:55:12'),
(6, 'cashierEx', 'takasukunami76@gmail.com', '$2y$10$X7qIJ9iHy6e1XwxrvRVZN.2SRal3NFic2jRSXL8mK/M7JoV.irF.a', 'cashier', NULL, '2025-11-11 12:17:05'),
(8, 'example user', 'example@gmail.com', '$2y$10$TA.0MhQdeWMpAjP6SaQlheOp42US3CqlcBQvIbJacC/zObYhKbkdu', 'admin', NULL, '2025-12-02 21:21:34'),
(9, 'Aldem Gwapo', 'ananavincent8@gmail.com', '$2y$10$u5ewnBxevHTC1lUhmDlhZevqpSp77enKPg1i6jzqqfZW4aSwGQ152', 'cashier', NULL, '2025-12-03 04:53:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_details`
--
ALTER TABLE `product_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `product_details`
--
ALTER TABLE `product_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
