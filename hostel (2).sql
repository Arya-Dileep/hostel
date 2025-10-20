-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2025 at 03:37 PM
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
-- Database: `hostel`
--

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `user_id`, `room_id`, `amount`, `status`, `created_at`) VALUES
(1, 7, 4, 2000.00, 'pending', '2025-10-20 11:30:18'),
(2, 7, 2, 3500.00, 'pending', '2025-10-20 11:30:21'),
(3, 7, 2, 3500.00, 'paid', '2025-10-20 11:30:30'),
(4, 7, 1, 5000.00, 'paid', '2025-10-20 11:46:11'),
(5, 5, 8, 5000.00, 'paid', '2025-10-20 14:59:02'),
(6, 5, 7, 2000.00, 'paid', '2025-10-20 15:35:20'),
(7, 10, 10, 1500.00, 'paid', '2025-10-20 16:38:30'),
(8, 9, 2, 3500.00, 'pending', '2025-10-20 17:11:06'),
(9, 9, 5, 2000.00, 'paid', '2025-10-20 17:11:47'),
(10, 9, 2, 3500.00, 'pending', '2025-10-20 17:13:05'),
(11, 9, 6, 2000.00, 'pending', '2025-10-20 17:13:28'),
(12, 9, 8, 5000.00, 'pending', '2025-10-20 17:14:08'),
(13, 9, 8, 5000.00, 'pending', '2025-10-20 17:19:28'),
(14, 9, 8, 5000.00, 'pending', '2025-10-20 17:49:15');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `razorpay_payment_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `bill_id`, `razorpay_payment_id`, `amount`, `paid_at`) VALUES
(1, 3, 'pay_RVcVwKIRXrtPFO', 3500.00, '2025-10-20 11:31:45'),
(2, 4, 'pay_RVclmzSNdXIeJa', 5000.00, '2025-10-20 11:46:45'),
(3, 5, 'pay_RVg3g234DN0MY0', 5000.00, '2025-10-20 14:59:46'),
(4, 6, 'pay_RVgfsHhMgruIfB', 2000.00, '2025-10-20 15:35:56'),
(5, 7, 'pay_RVhlB7xAjvdx2N', 1500.00, '2025-10-20 16:39:39'),
(6, 9, 'pay_RViJgq1UZkd3hy', 2000.00, '2025-10-20 17:12:19');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `type` enum('Single','Double','Dormitary') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `type`, `price`, `is_available`, `status`, `image`) VALUES
(1, 'S101', 'Single', 5000.00, 0, 'active', 'uploads/rooms/1760951912_article-31.jpg'),
(2, 'D201', 'Double', 3500.00, 1, 'active', 'uploads/rooms/1760951979_images.jpg'),
(3, 'D202', 'Double', 3500.00, 1, 'active', 'uploads/rooms/1760951987_images.jpg'),
(4, 'DM301', 'Dormitary', 2000.00, 1, 'active', 'uploads/rooms/1760952079_images (1).jpg'),
(5, 'DM302', 'Dormitary', 2000.00, 1, 'active', 'uploads/rooms/1760952090_images (1).jpg'),
(6, 'DM303', 'Dormitary', 2000.00, 1, 'active', 'uploads/rooms/1760952100_images (1).jpg'),
(7, 'DM304', 'Dormitary', 2000.00, 0, 'active', 'uploads/rooms/1760952109_images (1).jpg'),
(8, 'S102', 'Single', 5000.00, 1, 'active', 'uploads/PRB_5855-scaled.jpg'),
(10, 'S103', 'Single', 1500.00, 0, 'active', 'uploads/rooms/1760958468_i.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `role` enum('admin','student') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `room_id`, `role`, `status`, `created_at`) VALUES
(2, 'Admin', 'admin@gmail.com', '9999999999', '$2y$10$eSv/SpMelKTXMJ.dnY0lR.BVw1ns61ShJmtBIrL7U6cK0YxQD.O0i', NULL, 'admin', 'active', '2025-10-19 18:36:01'),
(5, 'Akshara Gopan', 'akshara@gmail.com', '7890678905', '$2y$10$9qQ2SBFLu9bG/ZUz/r4KSu8DRyUAh1tTGmtS2MmYUSfvePdRBijgq', 7, 'student', 'active', '2025-10-19 18:55:00'),
(7, 'Arjun Raj', 'arjun02@gmail.com', '9967976898', '$2y$10$AI4KiXUkEKicbFQWgTrjrOoZOosXemOmnhghNzAuHMLaS3uWqkNOi', 1, 'student', 'active', '2025-10-19 18:58:28'),
(8, 'Arya Dileep', 'ayoradileep@gmail.com', '9495171014', '$2y$10$OYaCC2akGKv.euuZh6s1NetfmQcxUtWPoyhYwKTctIkRTeouLYhia', NULL, 'student', 'active', '2025-10-20 13:35:08'),
(9, 'Adwaid D', 'adw@gmail.com', '7561812057', '$2y$10$EAd/188uSW9KqnXhvfOe0OBZGE0vvFEkLWXCsT6dY4j8TINBk8rw2', NULL, 'student', 'active', '2025-10-20 15:57:56'),
(10, 'Sulthana Fathima', 'sulu@gmail.com', '6789568908', '$2y$10$SFNNmJOZIW1Fy41pTApA3.Bt2UtVwmfWqJQ29DYZEAWPllrxWaxWO', 10, 'student', 'active', '2025-10-20 16:34:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
