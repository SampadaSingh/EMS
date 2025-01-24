-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2024 at 05:20 AM
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
-- Database: `ems`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_venue` varchar(255) NOT NULL,
  `event_location` varchar(255) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `event_description` text NOT NULL,
  `organizer_name` varchar(255) NOT NULL,
  `organizer_contact` varchar(255) NOT NULL,
  `event_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `organizer_id` int(11) DEFAULT NULL,
  `event_fee` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_title`, `event_venue`, `event_location`, `start_time`, `end_time`, `start_date`, `end_date`, `event_description`, `organizer_name`, `organizer_contact`, `event_image`, `created_at`, `organizer_id`, `event_fee`) VALUES
(1, 'Carnival', 'ACHS college', 'Ekantakuna, Lalitpur', '10:00:00', '15:00:00', '2024-11-04', '2024-11-04', 'Band performance, games, food stalls', 'ACHS CLUB', '1111111111', '../assets/uploads/event_1732376031.jpg', '2024-11-05 16:49:41', 15, 0.00),
(2, 'Career Meet', 'Everest Hotel', 'Baneshwor', '10:00:00', '16:00:00', '2024-11-06', '2024-11-08', 'This event features panel discussions, workshops, and Q&A sessions with experts from various fields.', 'ACHS CLUB', '2222222222', '../assets/uploads/event_1732375621.jpg', '2024-11-05 17:02:10', 15, 0.00),
(3, 'Cultural Fiesta', 'Kalimati Banquet', 'Kalimati', '09:00:00', '17:00:00', '2024-11-30', '2024-11-30', 'Musical and dance performances, along with Mr. & Miss ACHS', 'ACHS Student Committee', '3333333333', '../assets/uploads/event_1732375913.jpg', '2024-11-06 16:26:52', 1, 1500.00),
(4, 'Christmas Bash', 'Heritage Garden', 'Sanepa, Lalitpur', '10:00:00', '15:30:00', '2024-12-25', '2024-12-25', 'A day filled with fun performances, delicious food, and surprises', 'ACHS CLUB', '2222222222', '../assets/uploads/event_1732375984.jpg', '2024-11-22 13:30:38', 15, 500.00),
(5, 'Musical Fest', 'ACHS college', 'Ekantakuna, Lalitpur', '14:00:00', '18:00:00', '2024-12-31', '2024-12-31', 'Live performances by talented artists', 'ACHS CLUB', '2222222222', '../assets/uploads/event_1732376003.jpg', '2024-11-22 14:09:19', 15, 0.00),
(7, 'Tech Fest 2024', 'ACHS college', 'Ekantakuna, Lalitpur', '07:00:00', '09:08:00', '2024-11-30', '2024-12-07', 'This event fosters learning, teamwork, and networking among participants.', 'ACHS CLUB', '0123456789', '../assets/uploads/event_1732971524.png', '2024-11-30 11:24:22', NULL, 0.00),
(8, 'Khumai Dada Trek', 'Khumai Dada', 'Meet at college premises', '05:30:00', '22:00:00', '2024-12-10', '2024-12-15', 'Join us for an unforgettable trek to Khumai Dada, a serene hilltop offering breathtaking panoramic views of the Annapurna range. ', 'BCA 4th', '4444444444', 'IMG-674afa0e9d3112.06680251-Khumai Dada Trek.jpg', '2024-11-30 11:42:06', NULL, 7000.00);

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `p_name` varchar(100) NOT NULL,
  `p_email` varchar(100) NOT NULL,
  `p_phone` varchar(15) NOT NULL,
  `event_title` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `p_name`, `p_email`, `p_phone`, `event_title`, `created_at`) VALUES
(1, 'Sampada Singh', 'sampada@gmail.com', '9823725525', 'Carnival', '2024-11-19 15:54:39'),
(2, 'Hisi Maharjan', 'hisimaharjan1@gmail.com', '1212121212', 'Cultural Fiesta', '2024-11-19 15:55:31'),
(6, 'Sampada Singh', 'sampada@gmail.com', '9823725525', 'Christmas Bash', '2024-11-23 14:07:38'),
(10, 'Daisy Suwal', 'daisy@gmail.com', '0000000000', 'Christmas Bash', '2024-11-23 14:26:07'),
(11, 'Sampada Singh', 'sampada@gmail.com', '9823725525', 'Career Meet', '2024-11-30 06:52:35'),
(12, 'hisi maharjan', 'hisi@gmail.com', '4444444444', 'Cultural Fiesta', '2024-11-30 06:55:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','organizer','participant') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `phone`, `created_at`, `role`) VALUES
(1, 'ACHS Student Committee', 'achsstudent@gmail.com', 'achs_student_committee', '$2y$10$m4le7EIyuZVDcqwleqex4evp3oB.JRVMaZU78aYDKkrqbdMxBROCK', '1231231231', '2024-11-27 15:46:40', 'organizer'),
(9, 'hisi maharjan', 'hisi@gmail.com', 'hisi', '$2y$10$.59o.H9Qp3vU/KVPTi0qheGb4DNrpWszZwVTQD5PyLDd54U9.i7x.', '1414141414', '2024-10-26 16:16:32', 'participant'),
(11, 'admin', 'admin@gmail.com', 'admin', '$2y$10$4CMBtx60VRWG0wCLc3oB2eXmbqcGHKTzlp1N.8/OuCX/YkT14TNiC', '0000000000', '2024-10-28 15:07:42', 'admin'),
(14, 'basant karki', 'basantkarki@gmail.com', 'basant', '$2y$10$kbEOeYziHPoeIsJK5Bz7fu1.mUkH9YKoOMzyeb2BJDDY2VddTPTn6', '9876543210', '2024-11-17 06:27:13', 'organizer'),
(15, 'ACHS CLUB', 'achs@gmail.com', 'achs_club', '$2y$10$EyxkvxQVfXwnLfjBcPjvseavwXh/GtRqc.ZIn6ZRom4lTmGdWVF0a', '0123456789', '2024-11-25 15:59:20', 'organizer'),
(16, 'Sampada Singh', 'sampada@gmail.com', 'sampada', '$2y$10$ecYPn0ZW7m6bZ8JG0uvqne7y2ZBRBvs3QxyXGh.rmPGd6Qoc0Ldiu', '9823725525', '2024-11-28 17:14:55', 'participant'),
(17, 'BCA 4th', 'bca_4th@gmail.com', 'bca_4th', '$2y$10$5ulFo.et5/7TDm9I3CxTau4INiMw7IfaHT/XMbyszjN/QtpXH4YQO', '4444444444', '2024-11-30 11:35:30', 'organizer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_title` (`event_title`),
  ADD UNIQUE KEY `event_title_2` (`event_title`),
  ADD KEY `fk_organizer` (`organizer_id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_title` (`event_title`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `username_2` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`event_title`) REFERENCES `events` (`event_title`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
