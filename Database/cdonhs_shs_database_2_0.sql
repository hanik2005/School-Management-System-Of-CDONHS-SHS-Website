-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 05:26 AM
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
-- Database: `cdonhs_shs_database_2.0`
--

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(2, 'Admin'),
(1, 'Student'),
(3, 'Teacher');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `enrollment_status` enum('Active','Inactive','Graduated','Transferred') DEFAULT 'Active',
  `date_enrolled` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_applications`
--

CREATE TABLE `student_applications` (
  `application_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `extension_name` varchar(20) DEFAULT NULL,
  `lrn` varchar(12) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `civil_status` enum('single','married','divorced','widowed') NOT NULL,
  `house_number_street` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city_municipality` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `facebook_profile` varchar(255) DEFAULT NULL,
  `current_school` varchar(255) NOT NULL,
  `school_classification` enum('public','private') NOT NULL,
  `year_graduated` year(4) NOT NULL,
  `father_guardian_name` varchar(150) NOT NULL,
  `father_guardian_contact` varchar(20) NOT NULL,
  `mother_guardian_name` varchar(150) NOT NULL,
  `mother_guardian_contact` varchar(20) NOT NULL,
  `psa_birth_certificate` varchar(255) DEFAULT NULL,
  `form_138` varchar(255) DEFAULT NULL,
  `student_id_copy` varchar(255) DEFAULT NULL,
  `application_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_applications`
--

INSERT INTO `student_applications` (`application_id`, `first_name`, `last_name`, `middle_name`, `extension_name`, `lrn`, `date_of_birth`, `gender`, `civil_status`, `house_number_street`, `barangay`, `city_municipality`, `province`, `contact_number`, `email`, `facebook_profile`, `current_school`, `school_classification`, `year_graduated`, `father_guardian_name`, `father_guardian_contact`, `mother_guardian_name`, `mother_guardian_contact`, `psa_birth_certificate`, `form_138`, `student_id_copy`, `application_status`, `remarks`, `date_submitted`) VALUES
(11, 'Nick', 'Clarito', 'Durangparang', '', '405220150089', '2005-08-20', 'male', 'single', 'Blk7/lot 3', 'Carmen', 'Cagayan De Oro City', 'Misamis Oriental', '09988716534', 'nickcharlesclarito@gmail.com', 'https://www.facebook.com/niko.clarito.2025', 'CDONHS-SHS', 'public', '2020', 'papa', '09876562321', 'mama', '0987532732', NULL, NULL, NULL, 'Approved', 'goods', '2025-12-24 05:47:16'),
(12, 'Maria', 'Clarito', 'Durangparang', '', '189979375623', '1983-02-08', 'female', 'married', 'Blk7 Lot 3', 'Barangay 1', 'CDO', 'Misamis Oriental', '09944718764', 'maria@gmail.com', 'https://www.hostitsmart.com/manage/knowledgebase/388/How-to-Change-Table-Name-in-phpMyAdmin.html', 'None', 'public', '2010', 'papa', '09798373621', 'mama', '0974832472482', NULL, NULL, NULL, 'Pending', NULL, '2026-01-15 06:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `employment_status` enum('Active','Inactive','Resigned','Retired') DEFAULT 'Active',
  `date_hired` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `application_id`, `school_id`, `employment_status`, `date_hired`) VALUES
(8, 1, 502301, 'Active', '2026-01-29');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_applications`
--

CREATE TABLE `teacher_applications` (
  `teacher_application_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `extension_name` varchar(20) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `civil_status` enum('single','married','divorced','widowed') NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `facebook_profile` varchar(255) DEFAULT NULL,
  `house_number_street` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city_municipality` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `current_school` varchar(255) NOT NULL,
  `highest_education` enum('Bachelors','Masters','Doctorate','Other') NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `application_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp(),
  `resume_cv` varchar(255) DEFAULT NULL,
  `prc_id_copy` varchar(255) DEFAULT NULL,
  `certificates` varchar(255) DEFAULT NULL,
  `other_documents` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_applications`
--

INSERT INTO `teacher_applications` (`teacher_application_id`, `first_name`, `last_name`, `middle_name`, `extension_name`, `date_of_birth`, `gender`, `civil_status`, `contact_number`, `email`, `facebook_profile`, `house_number_street`, `barangay`, `city_municipality`, `province`, `current_school`, `highest_education`, `specialization`, `application_status`, `remarks`, `date_submitted`, `resume_cv`, `prc_id_copy`, `certificates`, `other_documents`) VALUES
(1, 'Andry', 'Clarito', 'Durangparang', '', '2010-02-05', 'male', 'married', '09944718764', 'clarito.nickcharles@gmail.com', 'https://www.hostitsmart.com/manage/knowledgebase/388/How-to-Change-Table-Name-in-phpMyAdmin.html', 'Blk4 Lot 3', 'Barangay 2', 'CDO', 'Misamis Oriental', 'None', 'Masters', 'Math', 'Pending', 'Your application has been approved today but there is some problems with your documents.', '2026-01-29 00:49:57', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `school_id`, `username`, `password`, `role_id`, `status`) VALUES
(1, 304111, '405220150089', '$2y$10$T1Qkc.zE1PWpRQX4FmIznepx1GJRGUzaVWsVMjdb8hj.Ve2MkQoAu', 1, 'Active'),
(2, 5362, 'admin', '$2y$10$T1Qkc.zE1PWpRQX4FmIznepx1GJRGUzaVWsVMjdb8hj.Ve2MkQoAu', 2, 'Active'),
(5, 7, '7', '$2y$10$10S8meOCGIOPm4LQyd7hAuTOn9GJ.ct8E9M25vuViBhJErW6/XnyO', 3, 'Active'),
(6, 502301, '502301', '$2y$10$QdRpqZx5hEuhezXQOXApperT1sUqPN/VMK7keyEvOHM17jSEPlChy', 3, 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD UNIQUE KEY `school_id` (`school_id`);

--
-- Indexes for table `student_applications`
--
ALTER TABLE `student_applications`
  ADD PRIMARY KEY (`application_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD UNIQUE KEY `school_id` (`school_id`);

--
-- Indexes for table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  ADD PRIMARY KEY (`teacher_application_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `school_id` (`school_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_applications`
--
ALTER TABLE `student_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  MODIFY `teacher_application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_application` FOREIGN KEY (`application_id`) REFERENCES `student_applications` (`application_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
