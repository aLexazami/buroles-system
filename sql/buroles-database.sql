-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2025 at 08:15 AM
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
-- Database: `buroles_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback_answers`
--

CREATE TABLE `feedback_answers` (
  `id` int(11) NOT NULL,
  `respondent_id` int(11) NOT NULL,
  `citizen_charter_awareness` varchar(10) DEFAULT NULL,
  `cc1` varchar(255) DEFAULT NULL,
  `cc2` varchar(255) DEFAULT NULL,
  `cc3` varchar(255) DEFAULT NULL,
  `sqd1` varchar(255) NOT NULL,
  `sqd2` varchar(255) NOT NULL,
  `sqd3` varchar(255) NOT NULL,
  `sqd4` varchar(255) NOT NULL,
  `sqd5` varchar(255) NOT NULL,
  `sqd6` varchar(255) NOT NULL,
  `sqd7` varchar(255) NOT NULL,
  `sqd8` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_answers`
--

INSERT INTO `feedback_answers` (`id`, `respondent_id`, `citizen_charter_awareness`, `cc1`, `cc2`, `cc3`, `sqd1`, `sqd2`, `sqd3`, `sqd4`, `sqd5`, `sqd6`, `sqd7`, `sqd8`, `remarks`) VALUES
(22, 22, 'Yes', '4', '3', '2', '1', '2', '3', '4', '5', '4', '3', '2', 'i love banana'),
(23, 23, 'No', NULL, NULL, NULL, '4', '4', '5', '5', '3', '3', '2', '2', 'black bunny'),
(24, 24, 'Yes', '4', '5', '4', 'na', 'na', 'na', 'na', 'na', 'na', 'na', 'na', 'I Love Harith!');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_respondents`
--

CREATE TABLE `feedback_respondents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `age` varchar(50) NOT NULL,
  `sex` varchar(10) NOT NULL,
  `customer_type` varchar(50) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `service_availed_id` int(11) DEFAULT NULL,
  `region_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_respondents`
--

INSERT INTO `feedback_respondents` (`id`, `name`, `date`, `age`, `sex`, `customer_type`, `submitted_at`, `service_availed_id`, `region_id`) VALUES
(22, 'Alex', '2025-08-27', '20-34', 'Male', 'Citizen', '2025-08-27 10:32:08', 2, 4),
(23, 'Bunny', '2025-08-27', 'under-19', 'Female', 'Government', '2025-08-27 11:07:13', 3, 13),
(24, 'Nana', '2025-08-28', 'under-19', 'Female', 'Business', '2025-08-28 09:38:32', 18, 13);

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `code`, `name`, `slug`) VALUES
(1, 'Region I', 'Ilocos Region', 'region_i_ilocos_region'),
(2, 'Region II', 'Cagayan Valley', 'region_ii_cagayan_valley'),
(3, 'Region III', 'Central Luzon', 'region_iii_central_luzon'),
(4, 'Region IV-A', 'Calabarzon', 'region_iv-a_calabarzon'),
(5, 'MIMAROPA', 'Southwestern Tagalog', 'mimaropa_southwestern_tagalog'),
(6, 'Region V', 'Bicol Region', 'region_v_bicol_region'),
(7, 'Region VI', 'Western Visayas', 'region_vi_western_visayas'),
(8, 'Region VII', 'Central Visayas', 'region_vii_central_visayas'),
(9, 'Region VIII', 'Eastern Visayas', 'region_viii_eastern_visayas'),
(10, 'Region IX', 'Zamboanga Peninsula', 'region_ix_zamboanga_peninsula'),
(11, 'Region X', 'Northern Mindanao', 'region_x_northern_mindanao'),
(12, 'Region XI', 'Davao Region', 'region_xi_davao_region'),
(13, 'Region XII', 'SOCCSKSARGEN', 'region_xii_soccsksargen'),
(14, 'Region XIII', 'Caraga', 'region_xiii_caraga'),
(15, 'NCR', 'National Capital Region', 'ncr_national_capital_region'),
(16, 'CAR', 'Cordillera Administrative Region', 'car_cordillera_administrative_region'),
(17, 'BARMM', 'Bangsamoro Autonomous Region', 'barmm_bangsamoro_autonomous_region');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Staff', 'Can view staff dashboard'),
(2, 'Admin', 'Can view Admin Dashboard'),
(99, 'Super Admin', 'Full system access including user management, settings, and audit controls');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `customer_type` enum('Citizen','Government','Business') NOT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `customer_type`, `category_id`) VALUES
(1, 'Enrollment (Online)', 'Citizen', 2),
(2, 'Enrollment (Walk-in)', 'Citizen', 2),
(3, 'Issuance of Special Order for Service Credits and Certification of Compensatory Time Credits', 'Government', 1),
(4, 'Acceptance of Employment Application for Teacher I Position (Walk-in)', 'Citizen', 2),
(5, 'Acceptance of Employment Application for Teacher I Position (Online)', 'Citizen', 2),
(6, 'Borrowing of Learning Materials from the School Library/Learning Resource Center', 'Citizen', 2),
(7, 'Distribution of Printed Self Learning Modules in Distance Learning Modality', 'Citizen', 2),
(8, 'Issuance of Requested Documents in Certified True Copy (CTC) and Photocopy (Walk-in)', 'Citizen', 2),
(9, 'Issuance of Requested Documents in Certified True Copy (CTC) and Photocopy (Online)', 'Citizen', 2),
(10, 'Issuance of School Clearance for different purposes', 'Citizen', 2),
(11, 'Issuance of School Forms, Certifications, and other School Permanent Records', 'Citizen', 2),
(12, 'Public Assistance (walk-in/phone call)', 'Citizen', 2),
(13, 'Public Assistance (email/social media)', 'Citizen', 2),
(14, 'Laboratory and School Inventory', 'Citizen', 1),
(15, 'School Learning and Development', 'Citizen', 1),
(16, 'Receiving and releasing of communications and other documents', 'Government', 2),
(17, 'Reservation Process for the Use of School Facilities', 'Government', 2),
(18, 'Request for Personnel Records for Teaching/Non-Teaching Personnel', 'Business', 2);

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Schools [Internal Services]', 'internal', 'Services provided within the school system, such as enrollment, grading, and internal feedback.'),
(2, 'Schools [External Services]', 'external', 'Services involving external stakeholders, such as community outreach, public reporting, and external evaluations.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(55) NOT NULL,
  `middle_name` varchar(55) DEFAULT NULL,
  `last_name` varchar(55) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role_id` int(11) NOT NULL,
  `must_change_password` tinyint(1) DEFAULT 1,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `username`, `password`, `email`, `role_id`, `must_change_password`, `is_archived`) VALUES
(1, 'Alex', 'Carbellido', 'Flores', 'flores123', '$2y$10$nUYddWgnw5jl7Fv6K/nJguvqmwQIJPVCJIHkEgwwz/PW9RT678qbq', 'alexazami08@gmail.com', 2, 0, 0),
(2, 'Loren', 'Strawberry', 'Delejero', 'delejero123', '$2y$10$WHdiGHz3EY1fqQ9nuu8B5.xB3Pn4Cr6UyiMsUEpJn8jVD.1YxrYz6', 'adasda@gmail.com', 1, 0, 0),
(4, 'Abigail', 'Blueberry', 'Dimapilis', 'dimapilis123', '$2y$10$saDzKGX6KLbLxifgNUFL4ewRCiWC5YAICzylAMBG0H0tX5SaKg.ai', 'dimapilis@gmail.com', 1, 0, 0),
(5, 'Admin', '', 'Bot', 'admin123', '$2y$10$1vMrrwYEWCU5upAXCtl.ROUPvPwRhxQSTBw9T1It/P4YzRP/E9uaa', 'admin123@gmail.com', 2, 0, 0),
(6, 'Nana', '', 'Batumbakal', 'nana123', '$2y$10$iX8.LpjNTfNLrdSB53f/FetWqPOlkECmUWy5N34ylYXg5/aSApjg.', 'nana123@gmail.com', 1, 1, 0),
(7, 'Harith', '', 'Batumbakal', 'harith123', '$2y$10$.ET51OS9fRZdP.ZbqIXDBu46mlAGE1wEgii6SDT7w2pUuu8iL4rQi', 'harith123@gmail.com', 2, 1, 0),
(8, 'David', NULL, 'Garcia', 'garcia123', '$2y$10$7Hp3GguxUshW.zmXEnOHtuA.3xIOSxABYudsDNc5ebAT7ZVk11j0e', 'garcia123@gmail.com', 99, 0, 0),
(9, 'Balmond', '', 'Cutie', 'balmond123', '$2y$10$hDnMYz8ZN8daWUXmddnpa.Bta2XBajYcQmk0/TaN9Yq.9dH1RUnx2', 'balmond123@gmail.com', 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 99),
(8, 1),
(8, 2),
(8, 99);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedback_answers`
--
ALTER TABLE `feedback_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `respondent_id` (`respondent_id`);

--
-- Indexes for table `feedback_respondents`
--
ALTER TABLE `feedback_respondents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_respondents_ibfk_1` (`service_availed_id`),
  ADD KEY `feedback_respondents_ibfk_2` (`region_id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `services_ibfk_1` (`category_id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedback_answers`
--
ALTER TABLE `feedback_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `feedback_respondents`
--
ALTER TABLE `feedback_respondents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback_answers`
--
ALTER TABLE `feedback_answers`
  ADD CONSTRAINT `feedback_answers_ibfk_1` FOREIGN KEY (`respondent_id`) REFERENCES `feedback_respondents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback_respondents`
--
ALTER TABLE `feedback_respondents`
  ADD CONSTRAINT `feedback_respondents_ibfk_1` FOREIGN KEY (`service_availed_id`) REFERENCES `services` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_respondents_ibfk_2` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
