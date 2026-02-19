-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2026 at 03:46 AM
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
-- Table structure for table `archived_student_strand`
--

CREATE TABLE `archived_student_strand` (
  `archive_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `strand_id` int(11) NOT NULL,
  `grade_level` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `date_archived` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason` enum('PROMOTION','TRANSFER','MANUAL') DEFAULT 'PROMOTION'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grade_entry`
--

CREATE TABLE `grade_entry` (
  `entry_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `quarter` int(11) NOT NULL,
  `grade` decimal(5,2) NOT NULL,
  `grade_status` enum('Draft','Submitted','Approved') NOT NULL DEFAULT 'Draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade_entry`
--

INSERT INTO `grade_entry` (`entry_id`, `student_id`, `subject_id`, `section_id`, `quarter`, `grade`, `grade_status`) VALUES
(24, 3, 33, 33, 1, 90.00, 'Approved'),
(25, 3, 34, 33, 1, 98.00, 'Approved'),
(26, 3, 35, 33, 1, 97.00, 'Approved'),
(27, 3, 36, 33, 1, 91.00, 'Approved'),
(28, 3, 37, 33, 1, 93.00, 'Approved'),
(29, 3, 38, 33, 1, 88.00, 'Approved'),
(30, 3, 39, 33, 1, 79.00, 'Approved'),
(31, 3, 40, 33, 1, 91.00, 'Approved'),
(32, 3, 33, 33, 2, 90.00, 'Approved'),
(33, 3, 34, 33, 2, 80.00, 'Approved'),
(34, 3, 35, 33, 2, 97.00, 'Approved'),
(35, 3, 36, 33, 2, 91.00, 'Approved'),
(36, 3, 37, 33, 2, 87.00, 'Approved'),
(37, 3, 38, 33, 2, 95.00, 'Approved'),
(38, 3, 39, 33, 2, 100.00, 'Approved'),
(39, 3, 40, 33, 2, 100.00, 'Approved'),
(40, 3, 33, 33, 3, 90.00, 'Approved'),
(41, 3, 34, 33, 3, 100.00, 'Approved'),
(42, 3, 35, 33, 3, 98.00, 'Approved'),
(43, 3, 36, 33, 3, 93.00, 'Approved'),
(44, 3, 37, 33, 3, 89.00, 'Approved'),
(45, 3, 38, 33, 3, 97.00, 'Approved'),
(46, 3, 39, 33, 3, 98.00, 'Approved'),
(47, 3, 40, 33, 3, 93.00, 'Approved'),
(48, 3, 33, 33, 4, 91.00, 'Approved'),
(49, 3, 34, 33, 4, 89.00, 'Approved'),
(50, 3, 35, 33, 4, 67.00, 'Approved'),
(51, 3, 36, 33, 4, 88.00, 'Approved'),
(52, 3, 37, 33, 4, 90.00, 'Approved'),
(53, 3, 38, 33, 4, 90.00, 'Approved'),
(54, 3, 39, 33, 4, 100.00, 'Approved'),
(55, 3, 40, 33, 4, 91.00, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`reset_id`, `user_id`, `email`, `otp_code`, `expires_at`, `is_used`, `created_at`) VALUES
(10, 11, 'nickcharlesclarito@gmail.com', 'D1FA29', '2026-02-19 11:31:47', 1, '2026-02-19 02:31:47'),
(12, 12, 'nidu.clarito.coc@phinmaed.com', 'B89731', '2026-02-19 11:36:35', 1, '2026-02-19 02:36:35'),
(13, 11, 'nickcharlesclarito@gmail.com', 'CDAEFB', '2026-02-19 11:40:31', 1, '2026-02-19 02:40:31');

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
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(30) NOT NULL,
  `grade_level` int(2) NOT NULL,
  `strand_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `section_name`, `grade_level`, `strand_id`) VALUES
(1, 'A', 11, 1),
(2, 'B', 11, 1),
(3, 'C', 11, 1),
(4, 'D', 11, 1),
(5, 'A', 12, 1),
(6, 'B', 12, 1),
(7, 'C', 12, 1),
(8, 'D', 12, 1),
(9, 'A', 11, 2),
(10, 'B', 11, 2),
(11, 'C', 11, 2),
(12, 'D', 11, 2),
(13, 'A', 12, 2),
(14, 'B', 12, 2),
(15, 'C', 12, 2),
(16, 'D', 12, 2),
(17, 'A', 11, 3),
(18, 'B', 11, 3),
(19, 'C', 11, 3),
(20, 'D', 11, 3),
(21, 'A', 12, 3),
(22, 'B', 12, 3),
(23, 'C', 12, 3),
(24, 'D', 12, 3),
(25, 'A', 11, 4),
(26, 'B', 11, 4),
(27, 'C', 11, 4),
(28, 'D', 11, 4),
(29, 'A', 12, 4),
(30, 'B', 12, 4),
(31, 'C', 12, 4),
(32, 'D', 12, 4),
(33, 'A', 11, 5),
(34, 'B', 11, 5),
(35, 'C', 11, 5),
(36, 'D', 11, 5),
(37, 'A', 12, 5),
(38, 'B', 12, 5),
(39, 'C', 12, 5),
(40, 'D', 12, 5),
(41, 'A', 11, 6),
(42, 'B', 11, 6),
(43, 'C', 11, 6),
(44, 'D', 11, 6),
(45, 'A', 12, 6),
(46, 'B', 12, 6),
(47, 'C', 12, 6),
(48, 'D', 12, 6),
(49, 'A', 11, 7),
(50, 'B', 11, 7),
(51, 'C', 11, 7),
(52, 'D', 11, 7),
(53, 'A', 12, 7),
(54, 'B', 12, 7),
(55, 'C', 12, 7),
(56, 'D', 12, 7);

-- --------------------------------------------------------

--
-- Table structure for table `strands`
--

CREATE TABLE `strands` (
  `strand_id` int(11) NOT NULL,
  `strand_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `strands`
--

INSERT INTO `strands` (`strand_id`, `strand_name`) VALUES
(1, 'STEM'),
(2, 'ABM'),
(3, 'HUMSS'),
(4, 'GAS'),
(5, 'TVL-ICT'),
(6, 'TVL-EIM'),
(7, 'TVL-HE');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `enrollment_status` enum('Active','Inactive','Graduated','Transferred') DEFAULT 'Active',
  `date_enrolled` date NOT NULL DEFAULT curdate(),
  `enlistment_status` enum('Not Enlisted','Pending','Enlisted','Rejected','Promoted') DEFAULT 'Not Enlisted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `application_id`, `school_id`, `enrollment_status`, `date_enrolled`, `enlistment_status`) VALUES
(3, 11, 20, 304112, 'Active', '2026-02-17', 'Enlisted');

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
  `enrollment_type` enum('New','Transferee','Balik-Eskwela') NOT NULL DEFAULT 'New',
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

INSERT INTO `student_applications` (`application_id`, `first_name`, `last_name`, `middle_name`, `extension_name`, `lrn`, `date_of_birth`, `gender`, `civil_status`, `house_number_street`, `barangay`, `city_municipality`, `province`, `contact_number`, `email`, `facebook_profile`, `current_school`, `school_classification`, `enrollment_type`, `year_graduated`, `father_guardian_name`, `father_guardian_contact`, `mother_guardian_name`, `mother_guardian_contact`, `psa_birth_certificate`, `form_138`, `student_id_copy`, `application_status`, `remarks`, `date_submitted`) VALUES
(20, 'Nick Charles', 'Clarito', 'Durangparang', '', '405220150089', '2005-08-20', 'male', 'single', 'Blk 4 Lot 3 Buena Oro', 'Macasandig', 'Cagayan De Oro City', 'Misamis Oriental', '09944719534', 'nickcharlesclarito@gmail.com', NULL, 'Cagayan De Oro National High School', 'public', 'New', '2020', 'papa', '09798373621', 'mama', '0974832472482', NULL, NULL, NULL, 'Approved', 'Your done', '2026-02-17 03:08:50');

-- --------------------------------------------------------

--
-- Table structure for table `student_strand`
--

CREATE TABLE `student_strand` (
  `student_strand_id` int(11) NOT NULL,
  `student_id` int(10) NOT NULL,
  `strand_id` int(11) NOT NULL,
  `grade_level` int(2) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_strand`
--

INSERT INTO `student_strand` (`student_strand_id`, `student_id`, `strand_id`, `grade_level`, `section_id`) VALUES
(7, 3, 5, 11, 33);

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `status` enum('Enrolled','Dropped') DEFAULT 'Enrolled',
  `school_year` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subjects`
--

INSERT INTO `student_subjects` (`enrollment_id`, `student_id`, `subject_id`, `status`, `school_year`) VALUES
(47, 3, 38, 'Enrolled', '2026-2027'),
(48, 3, 39, 'Enrolled', '2026-2027'),
(49, 3, 40, 'Enrolled', '2026-2027'),
(50, 3, 35, 'Enrolled', '2026-2027'),
(51, 3, 34, 'Enrolled', '2026-2027'),
(52, 3, 33, 'Enrolled', '2026-2027'),
(53, 3, 36, 'Enrolled', '2026-2027'),
(54, 3, 37, 'Enrolled', '2026-2027');

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `grade_level` int(2) NOT NULL,
  `strand_id` int(11) NOT NULL,
  `subject_order` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`, `grade_level`, `strand_id`, `subject_order`) VALUES
(1, 'Oral Communication', 11, 1, 1),
(2, 'Komunikasyon at Pananaliksik', 11, 1, 2),
(3, 'General Mathematics', 11, 1, 3),
(4, 'Earth and Life Science', 11, 1, 4),
(5, 'Understanding Culture, Society and Politics', 11, 1, 5),
(6, 'Pre-Calculus', 11, 1, 6),
(7, 'Basic Calculus', 11, 1, 7),
(8, 'Chemistry 1', 11, 1, 8),
(9, 'Oral Communication', 11, 2, 1),
(10, 'Komunikasyon at Pananaliksik', 11, 2, 2),
(11, 'General Mathematics', 11, 2, 3),
(12, 'Statistics and Probability', 11, 2, 4),
(13, 'Understanding Culture, Society and Politics', 11, 2, 5),
(14, 'Business Math', 11, 2, 6),
(15, 'Fundamentals of Accountancy 1', 11, 2, 7),
(16, 'Organization and Management', 11, 2, 8),
(17, 'Oral Communication', 11, 3, 1),
(18, 'Komunikasyon at Pananaliksik', 11, 3, 2),
(19, 'General Mathematics', 11, 3, 3),
(20, 'Statistics and Probability', 11, 3, 4),
(21, 'Understanding Culture, Society and Politics', 11, 3, 5),
(22, 'Creative Writing', 11, 3, 6),
(23, 'Disciplines and Ideas in Social Sciences', 11, 3, 7),
(24, 'Introduction to Philosophy', 11, 3, 8),
(25, 'Oral Communication', 11, 4, 1),
(26, 'Komunikasyon at Pananaliksik', 11, 4, 2),
(27, 'General Mathematics', 11, 4, 3),
(28, 'Statistics and Probability', 11, 4, 4),
(29, 'Understanding Culture, Society and Politics', 11, 4, 5),
(30, 'Humanities 1', 11, 4, 6),
(31, 'Applied Economics', 11, 4, 7),
(32, 'Organization and Management', 11, 4, 8),
(33, 'Oral Communication', 11, 5, 1),
(34, 'Komunikasyon at Pananaliksik', 11, 5, 2),
(35, 'General Mathematics', 11, 5, 3),
(36, 'Statistics and Probability', 11, 5, 4),
(37, 'Understanding Culture, Society and Politics', 11, 5, 5),
(38, 'Computer Systems Servicing 1', 11, 5, 6),
(39, 'Computer Systems Servicing 2', 11, 5, 7),
(40, 'Computer Systems Servicing 3', 11, 5, 8),
(41, 'Oral Communication', 11, 6, 1),
(42, 'Komunikasyon at Pananaliksik', 11, 6, 2),
(43, 'General Mathematics', 11, 6, 3),
(44, 'Statistics and Probability', 11, 6, 4),
(45, 'Understanding Culture, Society and Politics', 11, 6, 5),
(46, 'Electrical Installation 1', 11, 6, 6),
(47, 'Electrical Installation 2', 11, 6, 7),
(48, 'Electrical Installation 3', 11, 6, 8),
(49, 'Oral Communication', 11, 7, 1),
(50, 'Komunikasyon at Pananaliksik', 11, 7, 2),
(51, 'General Mathematics', 11, 7, 3),
(52, 'Statistics and Probability', 11, 7, 4),
(53, 'Understanding Culture, Society and Politics', 11, 7, 5),
(54, 'Cookery 1', 11, 7, 6),
(55, 'Cookery 2', 11, 7, 7),
(56, 'Bread and Pastry Production', 11, 7, 8),
(57, 'Reading and Writing', 12, 1, 1),
(58, '21st Century Literature', 12, 1, 2),
(59, 'Contemporary Philippine Arts', 12, 1, 3),
(60, 'Media and Information Literacy', 12, 1, 4),
(61, 'Physical Science', 12, 1, 5),
(62, 'Physics 2', 12, 1, 6),
(63, 'Biology 1', 12, 1, 7),
(64, 'Chemistry 2', 12, 1, 8),
(65, 'Reading and Writing', 12, 2, 1),
(66, '21st Century Literature', 12, 2, 2),
(67, 'Contemporary Philippine Arts', 12, 2, 3),
(68, 'Media and Information Literacy', 12, 2, 4),
(69, 'Business Ethics', 12, 2, 5),
(70, 'Fundamentals of Accountancy 2', 12, 2, 6),
(71, 'Applied Economics', 12, 2, 7),
(72, 'Business Finance', 12, 2, 8),
(73, 'Reading and Writing', 12, 3, 1),
(74, '21st Century Literature', 12, 3, 2),
(75, 'Contemporary Philippine Arts', 12, 3, 3),
(76, 'Media and Information Literacy', 12, 3, 4),
(77, 'Creative Nonfiction', 12, 3, 5),
(78, 'Philippine Politics', 12, 3, 6),
(79, 'Trends and Networks', 12, 3, 7),
(80, 'Disciplines in Social Science', 12, 3, 8),
(81, 'Reading and Writing', 12, 4, 1),
(82, '21st Century Literature', 12, 4, 2),
(83, 'Contemporary Philippine Arts', 12, 4, 3),
(84, 'Media and Information Literacy', 12, 4, 4),
(85, 'Creative Writing', 12, 4, 5),
(86, 'Humanities 2', 12, 4, 6),
(87, 'Disaster Readiness', 12, 4, 7),
(88, 'Applied Economics', 12, 4, 8),
(89, 'Reading and Writing', 12, 5, 1),
(90, '21st Century Literature', 12, 5, 2),
(91, 'Contemporary Philippine Arts', 12, 5, 3),
(92, 'Media and Information Literacy', 12, 5, 4),
(93, 'Computer Systems Servicing 4', 12, 5, 5),
(94, 'CSS Project', 12, 5, 6),
(95, 'Practical Research 1', 12, 5, 7),
(96, 'Empowerment Technologies', 12, 5, 8),
(97, 'Reading and Writing', 12, 6, 1),
(98, '21st Century Literature', 12, 6, 2),
(99, 'Contemporary Philippine Arts', 12, 6, 3),
(100, 'Media and Information Literacy', 12, 6, 4),
(101, 'Electrical Installation 4', 12, 6, 5),
(102, 'EIM Project', 12, 6, 6),
(103, 'Practical Research 1', 12, 6, 7),
(104, 'Empowerment Technologies', 12, 6, 8),
(105, 'Reading and Writing', 12, 7, 1),
(106, '21st Century Literature', 12, 7, 2),
(107, 'Contemporary Philippine Arts', 12, 7, 3),
(108, 'Media and Information Literacy', 12, 7, 4),
(109, 'Cookery 3', 12, 7, 5),
(110, 'Food and Beverage Services', 12, 7, 6),
(111, 'Practical Research 1', 12, 7, 7),
(112, 'Empowerment Technologies', 12, 7, 8);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `employment_status` enum('Active','Inactive','Resigned','Retired') DEFAULT 'Active',
  `date_hired` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `user_id`, `application_id`, `school_id`, `employment_status`, `date_hired`) VALUES
(12, 12, 3, 502301, 'Active', '2026-02-17');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_advisory`
--

CREATE TABLE `teacher_advisory` (
  `teacher_advisory_id` int(11) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `strand_id` int(11) NOT NULL,
  `grade_level` int(2) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_advisory`
--

INSERT INTO `teacher_advisory` (`teacher_advisory_id`, `teacher_id`, `strand_id`, `grade_level`, `section_id`) VALUES
(2, 12, 5, 11, 33);

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
(3, 'Andry', 'Clarito', 'Durangparang', 'Jr', '1995-07-13', 'male', 'single', '09262360968', 'nidu.clarito.coc@phinmaed.com', 'https://www.hostitsmart.com/manage/knowledgebase/388/How-to-Change-Table-Name-in-phpMyAdmin.html', 'Blk7 Lot 3', 'Macasandig', 'Cagayan De Oro City', 'Misamis Oriental', 'Cagayan De Oro National High School', '', 'Math', 'Approved', 'Your ready for deployment', '2026-02-17 03:21:21', NULL, NULL, NULL, NULL);

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
(2, 5362, 'admin', '$2y$10$T1Qkc.zE1PWpRQX4FmIznepx1GJRGUzaVWsVMjdb8hj.Ve2MkQoAu', 2, 'Active'),
(11, 304112, '405220150089', '$2y$10$wYd0hjZGOyUR5paZMqZzQ.2hT4uF3VzQrriaJ2ZugKBjO44tnWloS', 1, 'Active'),
(12, 502301, 'T_502301', '$2y$10$CmbjyzbGpha4yj7y0t6P8.q/Ql1tj99ehHKutXFKk2pcDSrHCMzWy', 3, 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archived_student_strand`
--
ALTER TABLE `archived_student_strand`
  ADD PRIMARY KEY (`archive_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `strand_id` (`strand_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `grade_entry`
--
ALTER TABLE `grade_entry`
  ADD PRIMARY KEY (`entry_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `fk_reset_user` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `section_ibfk_1` (`strand_id`);

--
-- Indexes for table `strands`
--
ALTER TABLE `strands`
  ADD PRIMARY KEY (`strand_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD UNIQUE KEY `school_id` (`school_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `student_applications`
--
ALTER TABLE `student_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD UNIQUE KEY `lrn` (`lrn`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student_strand`
--
ALTER TABLE `student_strand`
  ADD PRIMARY KEY (`student_strand_id`),
  ADD KEY `student_strand_ibfk_1` (`student_id`),
  ADD KEY `student_strand_ibfk_2` (`strand_id`),
  ADD KEY `student_strand_ibfk_3` (`section_id`);

--
-- Indexes for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `uq_student_subject` (`student_id`,`subject_id`,`school_year`),
  ADD KEY `fk_subject` (`subject_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `strand_id` (`strand_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD UNIQUE KEY `school_id` (`school_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `teacher_advisory`
--
ALTER TABLE `teacher_advisory`
  ADD PRIMARY KEY (`teacher_advisory_id`),
  ADD KEY `teacher_advisory_ibfk_1` (`teacher_id`),
  ADD KEY `teacher_advisory_ibfk_2` (`strand_id`),
  ADD KEY `teacher_advisory_ibfk_3` (`section_id`);

--
-- Indexes for table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  ADD PRIMARY KEY (`teacher_application_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `contact_number` (`contact_number`,`email`);

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
-- AUTO_INCREMENT for table `archived_student_strand`
--
ALTER TABLE `archived_student_strand`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `grade_entry`
--
ALTER TABLE `grade_entry`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_applications`
--
ALTER TABLE `student_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `student_strand`
--
ALTER TABLE `student_strand`
  MODIFY `student_strand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `teacher_advisory`
--
ALTER TABLE `teacher_advisory`
  MODIFY `teacher_advisory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  MODIFY `teacher_application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `archived_student_strand`
--
ALTER TABLE `archived_student_strand`
  ADD CONSTRAINT `archived_student_strand_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `archived_student_strand_ibfk_2` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`strand_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `archived_student_strand_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grade_entry`
--
ALTER TABLE `grade_entry`
  ADD CONSTRAINT `grade_entry_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `grade_entry_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`),
  ADD CONSTRAINT `grade_entry_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `section_ibfk_1` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`strand_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_application` FOREIGN KEY (`application_id`) REFERENCES `student_applications` (`application_id`),
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_strand`
--
ALTER TABLE `student_strand`
  ADD CONSTRAINT `student_strand_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_strand_ibfk_2` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`strand_id`),
  ADD CONSTRAINT `student_strand_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`);

--
-- Constraints for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD CONSTRAINT `fk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`),
  ADD CONSTRAINT `student_subjects_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `subject_ibfk_1` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`strand_id`);

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teachers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `teacher_applications` (`teacher_application_id`);

--
-- Constraints for table `teacher_advisory`
--
ALTER TABLE `teacher_advisory`
  ADD CONSTRAINT `teacher_advisory_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`),
  ADD CONSTRAINT `teacher_advisory_ibfk_2` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`strand_id`),
  ADD CONSTRAINT `teacher_advisory_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
