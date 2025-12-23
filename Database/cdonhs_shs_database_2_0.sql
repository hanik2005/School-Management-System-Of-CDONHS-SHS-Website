-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 04:19 AM
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
-- Table structure for table `enrollment_applications`
--

CREATE TABLE `enrollment_applications` (
  `application_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `extension_name` varchar(20) DEFAULT NULL,
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
-- Dumping data for table `enrollment_applications`
--

INSERT INTO `enrollment_applications` (`application_id`, `first_name`, `last_name`, `middle_name`, `extension_name`, `date_of_birth`, `gender`, `civil_status`, `house_number_street`, `barangay`, `city_municipality`, `province`, `contact_number`, `email`, `facebook_profile`, `current_school`, `school_classification`, `year_graduated`, `father_guardian_name`, `father_guardian_contact`, `mother_guardian_name`, `mother_guardian_contact`, `psa_birth_certificate`, `form_138`, `student_id_copy`, `application_status`, `remarks`, `date_submitted`) VALUES
(3, 'Nick', 'Clarito', 'Durangparang', '', '2005-07-26', 'male', 'single', 'Blk7/lot 3', 'Carmen', 'Cagayan De Oro City', 'Misamis Oriental', '09988716534', 'nickcharlesclarito@gmail.com', 'https://www.facebook.com/niko.clarito.2025', 'CDONHS-SHS', 'public', '2020', 'papa', '09876562321', 'mama', '0987532732', NULL, NULL, NULL, 'Pending', NULL, '2025-12-22 02:54:19'),
(5, 'Harvey', 'Clarito', 'Durangparang', '', '2005-07-26', 'male', 'single', 'Blk7/lot 3', 'Carmen', 'Cagayan De Oro City', 'Misamis Oriental', '09988716534', 'nickcharlesclarito@gmail.com', 'https://www.facebook.com/niko.clarito.2025', 'CDONHS-SHS', 'public', '2020', 'papa', '09876562321', 'mama', '0987532732', NULL, NULL, NULL, 'Pending', NULL, '2025-12-22 03:08:01'),
(6, 'Harvey', 'Clarito', 'Durangparang', '', '2005-07-26', 'male', 'single', 'Blk7/lot 3', 'Carmen', 'Cagayan De Oro City', 'Misamis Oriental', '09988716534', 'nickcharlesclarito@gmail.com', 'https://www.facebook.com/niko.clarito.2025', 'CDONHS-SHS', 'public', '2020', 'papa', '09876562321', 'mama', '0987532732', NULL, NULL, NULL, 'Pending', NULL, '2025-12-22 03:12:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `enrollment_applications`
--
ALTER TABLE `enrollment_applications`
  ADD PRIMARY KEY (`application_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `enrollment_applications`
--
ALTER TABLE `enrollment_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
