-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 03, 2025 at 09:35 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `focus_timer`
--

-- --------------------------------------------------------

--
-- Table structure for table `focus_history`
--

CREATE TABLE `focus_history` (
  `id` int NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `keterangan` text,
  `duration_seconds` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `focus_history`
--

INSERT INTO `focus_history` (`id`, `activity_name`, `keterangan`, `duration_seconds`, `created_at`) VALUES
(4, 'Testing', NULL, 49, '2025-07-31 07:47:19'),
(5, 'Benerin Web TimerFocus', NULL, 131, '2025-08-01 08:00:42'),
(6, 'Main Game', NULL, 962, '2025-07-31 09:06:24'),
(7, 'Benerin Web TimerFocus', NULL, 1959, '2025-07-31 09:45:04'),
(8, 'Tidur', NULL, 5400, '2025-07-31 14:06:58'),
(9, 'Benerin Web TimerFocus', NULL, 3142, '2025-07-31 18:52:03'),
(10, 'Benerin Web TimerFocus', NULL, 3022, '2025-07-31 19:47:59'),
(11, 'Main Game', NULL, 2600, '2025-08-01 16:49:55'),
(12, 'Main Game', NULL, 6220, '2025-08-02 10:58:55'),
(13, 'Drakoran', NULL, 4215, '2025-08-02 13:19:25'),
(14, 'Main Game', NULL, 11436, '2025-08-02 16:30:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `focus_history`
--
ALTER TABLE `focus_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_activity_name` (`activity_name`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
