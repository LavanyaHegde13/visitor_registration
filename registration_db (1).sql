-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2025 at 12:13 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `registration_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `guards`
--

CREATE TABLE `guards` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('receptionist','super_admin') NOT NULL DEFAULT 'receptionist'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users1`
--

CREATE TABLE `users1` (
  `id` int(6) UNSIGNED NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('receptionist','super_admin') NOT NULL DEFAULT 'receptionist',
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users1`
--

INSERT INTO `users1` (`id`, `mobile`, `password`, `name`, `role`, `reg_date`) VALUES
(1, '94637362736', '$2y$10$voGEFGStZYZMliisMj7BCOHaL9bZnz7TeG4cFqW86DQTj8xm8SgJ2', 'khushi', 'receptionist', '2025-05-28 05:04:14'),
(2, '9463736273', '$2y$10$UyKjsDRAit00m0gLbHKVnen5oMHqidFUArNtfE..yx7tpGSsfX0ey', 'moni', 'receptionist', '2025-05-28 05:04:41'),
(3, '9463736272', '$2y$10$De.qVmVN5xXCFtMoVLjIZ.B8E/LsrQbJbckJIJfOHIgf/sDnPT.6G', 'naveen', 'receptionist', '2025-05-28 08:47:47'),
(4, '9463736271', '$2y$10$BRtmAbQUQoxXJhoVpiCAK.h9/VHGyHIZ3mhJSJit5Mc13K4pMG3cG', 'pankaj', 'super_admin', '2025-05-28 08:51:08'),
(5, '9481156584', '$2y$10$g/4185hjrZX9vPQMV0sFkurLb/P4aKXw.iCBjhxIozbY6QCclk1KK', 'Chillins', 'receptionist', '2025-05-29 11:13:11'),
(6, '9481124256', '$2y$10$j5YQCYp256L90rAcsHrFaesMAQCl//1kpQYb7lAy7wQhn424Vb4qe', 'lavanya', 'receptionist', '2025-07-03 10:05:46');

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(20) NOT NULL,
  `coming_from` varchar(255) NOT NULL,
  `meeting_to` varchar(255) NOT NULL,
  `selfie_path` varchar(255) NOT NULL,
  `receptionist_id` int(11) UNSIGNED DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `name`, `email`, `whatsapp`, `coming_from`, `meeting_to`, `selfie_path`, `receptionist_id`, `otp`) VALUES
(1, 'ramya', 'raju@gmail.com', '9481535463', 'udupi', 'pankaj ', 'uploads/selfies/selfie_1748417867.jpeg', 2, '931705'),
(2, 'ramya', 'raju@gmail.com', '9481535463', 'udupi', 'pankaj ', 'uploads/selfies/selfie_1748417875.jpeg', 2, '156987'),
(3, 'ramya', 'raju@gmail.com', '9481535463', 'udupi', 'pankaj ', 'uploads/selfies/selfie_1748417884.jpeg', 2, '715920'),
(4, 'Chilins', 'raju@gmail.com', '9481535464', 'udupi', 'pankaj ', 'uploads/selfies/selfie_1748422105.jpeg', 3, '123456'),
(5, 'ysdfghj', 'raju@gmail.com', '9481535465', 'udupi', 'pankaj ', 'uploads/selfies/selfie_1748422183.jpeg', 3, '123456');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guards`
--
ALTER TABLE `guards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- Indexes for table `users1`
--
ALTER TABLE `users1`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receptionist_id` (`receptionist_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guards`
--
ALTER TABLE `guards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users1`
--
ALTER TABLE `users1`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visitors`
--
ALTER TABLE `visitors`
  ADD CONSTRAINT `visitors_ibfk_1` FOREIGN KEY (`receptionist_id`) REFERENCES `users1` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
