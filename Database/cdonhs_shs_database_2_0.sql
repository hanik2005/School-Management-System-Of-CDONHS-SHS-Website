-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2026 at 04:05 PM
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
-- Database: `cdonhs_shs_database_2.0`
--

-- --------------------------------------------------------

--
-- Table structure for table `activation_settings`
--

CREATE TABLE `activation_settings` (
  `id` int(11) NOT NULL,
  `activation_name` varchar(100) NOT NULL,
  `activation_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Disabled, 1=Enabled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activation_settings`
--

INSERT INTO `activation_settings` (`id`, `activation_name`, `activation_status`) VALUES
(1, 'Student Enrollment', 1),
(2, 'Form 137 and 138 Page', 1),
(3, 'Student Progress Page', 1),
(4, 'Teacher Registration', 1);

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

--
-- Dumping data for table `archived_student_strand`
--

INSERT INTO `archived_student_strand` (`archive_id`, `student_id`, `strand_id`, `grade_level`, `section_id`, `date_archived`, `reason`) VALUES
(19, 8, 5, 11, 33, '2026-02-27 05:42:48', 'PROMOTION'),
(20, 10, 5, 11, 33, '2026-02-27 13:07:49', 'MANUAL');

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
  `grade_status` enum('Draft','Rejected','Approved') NOT NULL DEFAULT 'Draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade_entry`
--

INSERT INTO `grade_entry` (`entry_id`, `student_id`, `subject_id`, `section_id`, `quarter`, `grade`, `grade_status`) VALUES
(233, 8, 33, 33, 1, 90.00, 'Approved'),
(234, 8, 34, 33, 1, 90.00, 'Approved'),
(235, 8, 35, 33, 1, 90.00, 'Approved'),
(236, 8, 36, 33, 1, 90.00, 'Approved'),
(237, 8, 37, 33, 1, 90.00, 'Approved'),
(238, 8, 38, 33, 1, 90.00, 'Approved'),
(239, 8, 39, 33, 1, 90.00, 'Approved'),
(240, 8, 40, 33, 1, 90.00, 'Approved'),
(241, 8, 33, 33, 2, 90.00, 'Approved'),
(242, 8, 34, 33, 2, 100.00, 'Approved'),
(243, 8, 35, 33, 2, 99.00, 'Approved'),
(244, 8, 36, 33, 2, 98.00, 'Approved'),
(245, 8, 37, 33, 2, 94.00, 'Approved'),
(246, 8, 38, 33, 2, 96.00, 'Approved'),
(247, 8, 39, 33, 2, 98.00, 'Approved'),
(248, 8, 40, 33, 2, 91.00, 'Approved'),
(249, 8, 40, 33, 3, 90.00, 'Approved'),
(250, 8, 33, 33, 3, 91.00, 'Approved'),
(251, 8, 34, 33, 3, 92.00, 'Approved'),
(252, 8, 35, 33, 3, 93.00, 'Approved'),
(253, 8, 36, 33, 3, 98.00, 'Approved'),
(254, 8, 37, 33, 3, 91.00, 'Approved'),
(255, 8, 38, 33, 3, 92.00, 'Approved'),
(256, 8, 39, 33, 3, 93.00, 'Approved'),
(257, 8, 40, 33, 4, 91.00, 'Approved'),
(258, 8, 39, 33, 4, 91.00, 'Approved'),
(259, 8, 38, 33, 4, 90.00, 'Approved'),
(260, 8, 37, 33, 4, 93.00, 'Approved'),
(261, 8, 36, 33, 4, 94.00, 'Approved'),
(262, 8, 35, 33, 4, 95.00, 'Approved'),
(263, 8, 34, 33, 4, 96.00, 'Approved'),
(264, 8, 33, 33, 4, 91.00, 'Approved'),
(265, 9, 33, 33, 1, 90.00, 'Rejected'),
(266, 13, 33, 33, 1, 90.00, 'Rejected'),
(267, 11, 33, 33, 1, 100.00, 'Rejected'),
(268, 10, 33, 33, 1, 90.00, 'Approved'),
(269, 9, 34, 33, 1, 90.00, 'Rejected'),
(270, 13, 34, 33, 1, 100.00, 'Rejected'),
(271, 12, 34, 33, 1, 90.00, 'Rejected'),
(272, 11, 34, 33, 1, 90.00, 'Rejected'),
(273, 10, 34, 33, 1, 100.00, 'Approved'),
(274, 9, 35, 33, 1, 90.00, 'Rejected'),
(275, 13, 35, 33, 1, 65.00, 'Rejected'),
(276, 12, 35, 33, 1, 65.00, 'Rejected'),
(277, 11, 35, 33, 1, 65.00, 'Rejected'),
(278, 10, 35, 33, 1, 65.00, 'Approved'),
(279, 9, 36, 33, 1, 60.00, 'Rejected'),
(280, 13, 36, 33, 1, 60.00, 'Rejected'),
(281, 12, 36, 33, 1, 60.00, 'Rejected'),
(282, 11, 36, 33, 1, 60.00, 'Rejected'),
(283, 10, 36, 33, 1, 60.00, 'Approved'),
(284, 9, 37, 33, 1, 90.00, 'Rejected'),
(285, 13, 37, 33, 1, 60.00, 'Rejected'),
(286, 12, 37, 33, 1, 60.00, 'Rejected'),
(287, 11, 37, 33, 1, 60.00, 'Rejected'),
(288, 10, 37, 33, 1, 60.00, 'Approved'),
(289, 9, 38, 33, 1, 95.00, 'Rejected'),
(290, 13, 38, 33, 1, 60.00, 'Rejected'),
(291, 12, 38, 33, 1, 60.00, 'Rejected'),
(292, 11, 38, 33, 1, 60.00, 'Rejected'),
(293, 10, 38, 33, 1, 60.00, 'Approved'),
(294, 9, 39, 33, 1, 100.00, 'Rejected'),
(295, 13, 39, 33, 1, 60.00, 'Rejected'),
(296, 12, 39, 33, 1, 60.00, 'Rejected'),
(297, 11, 39, 33, 1, 60.00, 'Rejected'),
(298, 10, 39, 33, 1, 60.00, 'Approved'),
(299, 9, 40, 33, 1, 90.00, 'Rejected'),
(300, 13, 40, 33, 1, 60.00, 'Rejected'),
(301, 12, 40, 33, 1, 60.00, 'Rejected'),
(302, 11, 40, 33, 1, 60.00, 'Rejected'),
(303, 10, 40, 33, 1, 60.00, 'Approved'),
(304, 9, 40, 33, 2, 90.00, 'Approved'),
(305, 13, 40, 33, 2, 60.00, 'Approved'),
(306, 12, 40, 33, 2, 60.00, 'Approved'),
(307, 11, 40, 33, 2, 60.00, 'Approved'),
(308, 10, 40, 33, 2, 60.00, 'Approved'),
(309, 9, 39, 33, 2, 90.00, 'Approved'),
(310, 13, 39, 33, 2, 60.00, 'Approved'),
(311, 12, 39, 33, 2, 60.00, 'Approved'),
(312, 11, 39, 33, 2, 60.00, 'Approved'),
(313, 10, 39, 33, 2, 60.00, 'Approved'),
(314, 9, 38, 33, 2, 90.00, 'Approved'),
(315, 13, 38, 33, 2, 60.00, 'Approved'),
(316, 12, 38, 33, 2, 60.00, 'Approved'),
(317, 11, 38, 33, 2, 60.00, 'Approved'),
(318, 10, 38, 33, 2, 60.00, 'Approved'),
(319, 9, 37, 33, 2, 90.00, 'Approved'),
(320, 13, 37, 33, 2, 60.00, 'Approved'),
(321, 12, 37, 33, 2, 60.00, 'Approved'),
(322, 11, 37, 33, 2, 60.00, 'Approved'),
(323, 10, 37, 33, 2, 60.00, 'Approved'),
(324, 9, 36, 33, 2, 90.00, 'Approved'),
(325, 13, 36, 33, 2, 60.00, 'Approved'),
(326, 12, 36, 33, 2, 60.00, 'Approved'),
(327, 11, 36, 33, 2, 60.00, 'Approved'),
(328, 10, 36, 33, 2, 60.00, 'Approved'),
(329, 9, 35, 33, 2, 90.00, 'Approved'),
(330, 13, 35, 33, 2, 60.00, 'Approved'),
(331, 12, 35, 33, 2, 60.00, 'Approved'),
(332, 11, 35, 33, 2, 60.00, 'Approved'),
(333, 10, 35, 33, 2, 60.00, 'Approved'),
(334, 9, 34, 33, 2, 100.00, 'Approved'),
(335, 13, 34, 33, 2, 60.00, 'Approved'),
(336, 12, 34, 33, 2, 60.00, 'Approved'),
(337, 11, 34, 33, 2, 60.00, 'Approved'),
(338, 10, 34, 33, 2, 60.00, 'Approved'),
(339, 9, 33, 33, 2, 90.00, 'Approved'),
(340, 13, 33, 33, 2, 60.00, 'Approved'),
(341, 11, 33, 33, 2, 60.00, 'Approved'),
(342, 10, 33, 33, 2, 60.00, 'Approved'),
(343, 9, 33, 33, 3, 100.00, 'Approved'),
(344, 13, 33, 33, 3, 60.00, 'Approved'),
(345, 11, 33, 33, 3, 60.00, 'Approved'),
(346, 10, 33, 33, 3, 60.00, 'Approved'),
(347, 9, 34, 33, 3, 100.00, 'Approved'),
(348, 13, 34, 33, 3, 60.00, 'Approved'),
(349, 12, 34, 33, 3, 60.00, 'Approved'),
(350, 11, 34, 33, 3, 60.00, 'Approved'),
(351, 10, 34, 33, 3, 60.00, 'Approved'),
(352, 9, 35, 33, 3, 100.00, 'Approved'),
(353, 13, 35, 33, 3, 60.00, 'Approved'),
(354, 12, 35, 33, 3, 60.00, 'Approved'),
(355, 11, 35, 33, 3, 60.00, 'Approved'),
(356, 10, 35, 33, 3, 60.00, 'Approved'),
(357, 9, 36, 33, 3, 100.00, 'Approved'),
(358, 13, 36, 33, 3, 60.00, 'Approved'),
(359, 12, 36, 33, 3, 60.00, 'Approved'),
(360, 11, 36, 33, 3, 60.00, 'Approved'),
(361, 10, 36, 33, 3, 60.00, 'Approved'),
(362, 9, 37, 33, 3, 100.00, 'Approved'),
(363, 13, 37, 33, 3, 60.00, 'Approved'),
(364, 12, 37, 33, 3, 60.00, 'Approved'),
(365, 11, 37, 33, 3, 60.00, 'Approved'),
(366, 10, 37, 33, 3, 60.00, 'Approved'),
(367, 9, 38, 33, 3, 90.00, 'Approved'),
(368, 13, 38, 33, 3, 60.00, 'Approved'),
(369, 12, 38, 33, 3, 60.00, 'Approved'),
(370, 11, 38, 33, 3, 60.00, 'Approved'),
(371, 10, 38, 33, 3, 60.00, 'Approved'),
(372, 9, 39, 33, 3, 100.00, 'Approved'),
(373, 13, 39, 33, 3, 60.00, 'Approved'),
(374, 12, 39, 33, 3, 60.00, 'Approved'),
(375, 11, 39, 33, 3, 60.00, 'Approved'),
(376, 10, 39, 33, 3, 60.00, 'Approved'),
(377, 9, 40, 33, 3, 100.00, 'Approved'),
(378, 13, 40, 33, 3, 60.00, 'Approved'),
(379, 12, 40, 33, 3, 60.00, 'Approved'),
(380, 11, 40, 33, 3, 60.00, 'Approved'),
(381, 10, 40, 33, 3, 60.00, 'Approved'),
(382, 9, 40, 33, 4, 100.00, 'Approved'),
(383, 13, 40, 33, 4, 60.00, 'Approved'),
(384, 12, 40, 33, 4, 60.00, 'Approved'),
(385, 11, 40, 33, 4, 60.00, 'Approved'),
(386, 10, 40, 33, 4, 60.00, 'Approved'),
(387, 9, 39, 33, 4, 100.00, 'Approved'),
(388, 13, 39, 33, 4, 60.00, 'Approved'),
(389, 12, 39, 33, 4, 60.00, 'Approved'),
(390, 11, 39, 33, 4, 60.00, 'Approved'),
(391, 10, 39, 33, 4, 60.00, 'Approved'),
(392, 9, 38, 33, 4, 90.00, 'Approved'),
(393, 13, 38, 33, 4, 60.00, 'Approved'),
(394, 12, 38, 33, 4, 60.00, 'Approved'),
(395, 11, 38, 33, 4, 60.00, 'Approved'),
(396, 10, 38, 33, 4, 60.00, 'Approved'),
(397, 9, 37, 33, 4, 90.00, 'Approved'),
(398, 13, 37, 33, 4, 60.00, 'Approved'),
(399, 12, 37, 33, 4, 60.00, 'Approved'),
(400, 11, 37, 33, 4, 60.00, 'Approved'),
(401, 10, 37, 33, 4, 60.00, 'Approved'),
(402, 9, 36, 33, 4, 90.00, 'Approved'),
(403, 13, 36, 33, 4, 60.00, 'Approved'),
(404, 12, 36, 33, 4, 60.00, 'Approved'),
(405, 11, 36, 33, 4, 60.00, 'Approved'),
(406, 10, 36, 33, 4, 60.00, 'Approved'),
(407, 9, 35, 33, 4, 90.00, 'Approved'),
(408, 13, 35, 33, 4, 60.00, 'Approved'),
(409, 12, 35, 33, 4, 60.00, 'Approved'),
(410, 11, 35, 33, 4, 60.00, 'Approved'),
(411, 10, 35, 33, 4, 60.00, 'Approved'),
(412, 9, 34, 33, 4, 90.00, 'Approved'),
(413, 13, 34, 33, 4, 60.00, 'Approved'),
(414, 12, 34, 33, 4, 60.00, 'Approved'),
(415, 11, 34, 33, 4, 60.00, 'Approved'),
(416, 10, 34, 33, 4, 60.00, 'Approved'),
(417, 9, 33, 33, 4, 90.00, 'Approved'),
(418, 13, 33, 33, 4, 60.00, 'Approved'),
(419, 11, 33, 33, 4, 60.00, 'Approved'),
(420, 10, 33, 33, 4, 60.00, 'Approved');

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
  `student_number` int(11) NOT NULL,
  `enrollment_status` enum('Active','Inactive','Graduated','Transferred') DEFAULT 'Active',
  `date_enrolled` date NOT NULL DEFAULT curdate(),
  `enlistment_status` enum('Not Enlisted','Pending','Enlisted','Rejected','Promoted') DEFAULT 'Not Enlisted',
  `school_year` varchar(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `application_id`, `student_number`, `enrollment_status`, `date_enrolled`, `enlistment_status`, `school_year`) VALUES
(8, 20, 27, 304112, 'Active', '2026-02-26', 'Enlisted', '2025-2026'),
(9, 22, 29, 304113, 'Active', '2026-02-27', 'Enlisted', '2025-2026'),
(10, 23, 30, 304114, 'Active', '2026-02-27', 'Enlisted', '2025-2026'),
(11, 24, 31, 304115, 'Active', '2026-02-27', 'Enlisted', '2025-2026'),
(12, 25, 32, 304116, 'Active', '2026-02-27', 'Enlisted', '2025-2026'),
(13, 26, 33, 304117, 'Active', '2026-02-27', 'Enlisted', '2025-2026'),
(14, 34, 34, 304118, 'Active', '2026-02-28', 'Not Enlisted', '2025-2026'),
(15, 35, 35, 304119, 'Active', '2026-02-28', 'Not Enlisted', '2025-2026');

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
  `sex` enum('male','female') NOT NULL,
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
  `application_status` enum('Pending','Approved','Rejected','Conditionally Approved') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_applications`
--

INSERT INTO `student_applications` (`application_id`, `first_name`, `last_name`, `middle_name`, `extension_name`, `lrn`, `date_of_birth`, `sex`, `civil_status`, `house_number_street`, `barangay`, `city_municipality`, `province`, `contact_number`, `email`, `facebook_profile`, `current_school`, `school_classification`, `enrollment_type`, `year_graduated`, `father_guardian_name`, `father_guardian_contact`, `mother_guardian_name`, `mother_guardian_contact`, `psa_birth_certificate`, `form_138`, `student_id_copy`, `application_status`, `remarks`, `date_submitted`, `profile_image`) VALUES
(27, 'Nick Charles', 'Clarito', 'Durangparang', '', '405220150089', '1995-06-15', 'male', 'single', 'Blk4 Lot 3', 'Macasandig', 'Cagayan De Oro City', 'Misamis Oriental', '09944719534', 'nickcharlesclarito@gmail.com', NULL, 'Cagayan De Oro National High School', 'public', 'New', '2020', 'papa', '09977436346', 'mama', '09843924244', NULL, NULL, NULL, 'Approved', '', '2026-02-26 11:44:13', NULL),
(29, 'Maria', 'Clarito', 'Durangparang', '', '105330140076', '1995-11-27', 'male', 'single', 'Blk4 Lot 3', 'Macasandig', 'Cagayan De Oro City', 'Misamis Oriental', '09312314523', 'nickcharlesclaritomicrosoft@gmail.com', NULL, 'Cagayan De Oro National High School', 'public', 'Balik-Eskwela', '2025', 'papa', '09284728472', 'mama', '09748324724', NULL, NULL, NULL, 'Approved', '', '2026-02-27 06:29:36', NULL),
(30, 'Charlie Nathaniel', 'Viador', 'Barero', '', '123456789123', '1998-06-30', 'male', 'single', 'Blk7 Lot 3', 'Barangay 5', 'Cagayan De Oro City', 'Misamis Oriental', '09782739197', 'nickhoyo2005@gmail.com', NULL, 'Cagayan De Oro National High School', 'public', 'Transferee', '2021', 'papa', '09284728472', 'mama', '09748324724', NULL, NULL, NULL, 'Approved', '', '2026-02-27 06:34:31', NULL),
(31, 'Catherine', 'Timbang', 'Lotots', '', '183581758295', '1986-03-27', 'male', 'single', 'Blk4 Lot 3', 'Macasandig', 'Cagayan De Oro City', 'Misamis Oriental', '09855615712', 'clarito.nickcharles@gmail.com', NULL, 'Cagayan De Oro National High School', 'public', 'New', '2021', 'papa', '09798373621', 'mama', '09748324724', NULL, NULL, NULL, 'Approved', '', '2026-02-27 07:19:14', NULL),
(32, 'Alix', 'Garcia', 'Abecia', '', '132940224144', '1998-10-27', 'male', 'single', 'Blk 4 Lot 3 Buena Oro', 'Barangay 5', 'Cagayan De Oro City', 'Misamis Oriental', '09782581568', 'nick@gmail.com', NULL, 'Cagayan De Oro National High School', 'public', 'New', '2023', 'papa', '09284728472', 'mama', '09843924244', NULL, NULL, NULL, 'Approved', '', '2026-02-27 07:23:33', NULL),
(33, 'Billy', 'Durangparang', 'Abecia', 'Jr', '187472842759', '1978-07-27', 'male', 'single', 'Blk7 Lot 3', 'Barangay 11', 'Cagayan De Oro City', 'Misamis Oriental', '09862413525', 'billy@gmail.com', NULL, 'Cagayan De Oro National High School', 'public', 'New', '2023', 'papa', '09977436346', 'mama', '09748324724', NULL, NULL, NULL, 'Approved', '', '2026-02-27 07:28:53', NULL),
(34, 'Domino', 'Bagtong', 'Dumang', '', '108942385023', '1996-07-18', 'male', 'single', '11th Street', 'Bulua', 'Cagayan De Oro City', 'Misamis Oriental', '09785237656', 'Domino@gmail.com', NULL, 'Cagayan De Oro National High School', 'public', 'New', '2020', 'papa', '09284728472', 'mama', '09748324724', NULL, NULL, NULL, 'Approved', '', '2026-02-28 08:33:37', NULL),
(35, 'Jacob', 'Serquina', 'busta', '', '109482982957', '2001-11-28', 'male', 'single', 'Blk9', 'Macasandig', 'Cagayan De Oro City', 'Misamis Oriental', '09732658256', 'Jacob@gmail.com', '', 'Cagayan De Oro National High School', 'public', 'Transferee', '2020', 'papa', '09735681276', 'mama', '09141634637', '1772281145_PSA_Group 1 - Client Interview Submission (1).pdf', '1772281145_FORM138_student.pdf', '1772281145_STUDENTID_Registration_Slip (1).pdf', 'Approved', 'goods', '2026-02-28 12:19:05', '1772290398_PROFILE_35.jpg');

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
(30, 8, 5, 12, 37),
(31, 9, 5, 11, 33),
(32, 10, 5, 11, 33),
(33, 11, 5, 11, 33),
(34, 12, 5, 11, 33),
(35, 13, 5, 11, 33);

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `status` enum('Enrolled','Dropped','Pending','Withdrawn with Grades','Withdrawn','Completed') DEFAULT 'Pending',
  `requested` tinyint(1) DEFAULT 1 COMMENT '1=student requested, 0=student did not request',
  `school_year` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subjects`
--

INSERT INTO `student_subjects` (`enrollment_id`, `student_id`, `subject_id`, `status`, `requested`, `school_year`) VALUES
(219, 8, 38, 'Completed', 1, '2025-2026'),
(220, 8, 39, 'Completed', 1, '2025-2026'),
(221, 8, 40, 'Completed', 1, '2025-2026'),
(222, 8, 35, 'Completed', 1, '2025-2026'),
(223, 8, 34, 'Completed', 1, '2025-2026'),
(224, 8, 33, 'Completed', 1, '2025-2026'),
(225, 8, 36, 'Completed', 1, '2025-2026'),
(226, 8, 37, 'Completed', 1, '2025-2026'),
(251, 8, 89, 'Enrolled', 1, '2025-2026'),
(252, 8, 90, 'Enrolled', 1, '2025-2026'),
(253, 8, 91, 'Enrolled', 1, '2025-2026'),
(254, 8, 92, 'Dropped', 1, '2025-2026'),
(255, 8, 93, 'Dropped', 1, '2025-2026'),
(256, 8, 94, 'Enrolled', 1, '2025-2026'),
(257, 8, 95, 'Enrolled', 1, '2025-2026'),
(258, 8, 96, 'Enrolled', 1, '2025-2026'),
(259, 9, 38, 'Enrolled', 1, '2025-2026'),
(260, 9, 39, 'Enrolled', 1, '2025-2026'),
(261, 9, 40, 'Enrolled', 1, '2025-2026'),
(262, 9, 35, 'Enrolled', 1, '2025-2026'),
(263, 9, 34, 'Enrolled', 1, '2025-2026'),
(264, 9, 33, 'Enrolled', 1, '2025-2026'),
(265, 9, 36, 'Enrolled', 1, '2025-2026'),
(266, 9, 37, 'Enrolled', 1, '2025-2026'),
(267, 10, 38, 'Enrolled', 1, '2025-2026'),
(268, 10, 39, 'Enrolled', 1, '2025-2026'),
(269, 10, 40, 'Enrolled', 1, '2025-2026'),
(270, 10, 35, 'Enrolled', 1, '2025-2026'),
(271, 10, 34, 'Enrolled', 1, '2025-2026'),
(272, 10, 33, 'Enrolled', 1, '2025-2026'),
(273, 10, 36, 'Enrolled', 1, '2025-2026'),
(274, 10, 37, 'Enrolled', 1, '2025-2026'),
(275, 11, 38, 'Enrolled', 1, '2025-2026'),
(276, 11, 39, 'Enrolled', 1, '2025-2026'),
(277, 11, 40, 'Enrolled', 1, '2025-2026'),
(278, 11, 35, 'Enrolled', 1, '2025-2026'),
(279, 11, 34, 'Enrolled', 1, '2025-2026'),
(280, 11, 33, 'Enrolled', 1, '2025-2026'),
(281, 11, 36, 'Enrolled', 1, '2025-2026'),
(282, 11, 37, 'Enrolled', 1, '2025-2026'),
(283, 12, 38, 'Enrolled', 1, '2025-2026'),
(284, 12, 39, 'Enrolled', 1, '2025-2026'),
(285, 12, 40, 'Enrolled', 1, '2025-2026'),
(286, 12, 35, 'Enrolled', 1, '2025-2026'),
(287, 12, 34, 'Enrolled', 1, '2025-2026'),
(288, 12, 33, 'Dropped', 0, '2025-2026'),
(289, 12, 36, 'Enrolled', 1, '2025-2026'),
(290, 12, 37, 'Enrolled', 1, '2025-2026'),
(291, 13, 38, 'Enrolled', 1, '2025-2026'),
(292, 13, 39, 'Enrolled', 1, '2025-2026'),
(293, 13, 40, 'Enrolled', 1, '2025-2026'),
(294, 13, 35, 'Enrolled', 1, '2025-2026'),
(295, 13, 34, 'Enrolled', 1, '2025-2026'),
(296, 13, 33, 'Enrolled', 1, '2025-2026'),
(297, 13, 36, 'Enrolled', 1, '2025-2026'),
(298, 13, 37, 'Enrolled', 1, '2025-2026');

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
  `teacher_number` int(11) NOT NULL,
  `employment_status` enum('Active','Inactive','Resigned','Retired') DEFAULT 'Active',
  `date_hired` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `user_id`, `application_id`, `teacher_number`, `employment_status`, `date_hired`) VALUES
(14, 21, 7, 502301, 'Active', '2026-02-26'),
(22, 36, 12, 585004, 'Active', '2026-02-28');

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
(4, 14, 5, 11, 33),
(10, 22, 5, 12, 37);

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
  `sex` enum('male','female') NOT NULL,
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
  `application_status` enum('Pending','Approved','Rejected','Conditionally Approved') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp(),
  `resume_cv` varchar(255) DEFAULT NULL,
  `prc_id_copy` varchar(255) DEFAULT NULL,
  `certificates` varchar(255) DEFAULT NULL,
  `other_documents` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_applications`
--

INSERT INTO `teacher_applications` (`teacher_application_id`, `first_name`, `last_name`, `middle_name`, `extension_name`, `date_of_birth`, `sex`, `civil_status`, `contact_number`, `email`, `facebook_profile`, `house_number_street`, `barangay`, `city_municipality`, `province`, `current_school`, `highest_education`, `specialization`, `application_status`, `remarks`, `date_submitted`, `resume_cv`, `prc_id_copy`, `certificates`, `other_documents`, `profile_image`) VALUES
(7, 'Jason Jay', 'Japlag', 'Dumang', 'Jr', '1991-07-25', 'male', 'single', '09643632141', 'nidu.clarito.coc@phinmaed.com', 'https://www.hostitsmart.com/manage/knowledgebase/388/How-to-Change-Table-Name-in-phpMyAdmin.html', 'Blk4 Lot 3', 'Barangay 11', 'Cagayan De Oro City', 'Misamis Oriental', 'Cagayan De Oro National High School', '', 'ESP', 'Approved', '', '2026-02-26 12:45:57', NULL, NULL, NULL, NULL, NULL),
(12, 'Justin', 'Bagiuo', 'Bolol', '', '2009-07-23', 'male', 'single', '09674236582', 'Justin@gmail.com', 'https://www.hostitsmart.com/manage/knowledgebase/388/How-to-Change-Table-Name-in-phpMyAdmin.html', 'Blk7 Lot 3', 'Macasandig', 'Cagayan De Oro City', 'Misamis Oriental', 'Cagayan De Oro National High School', 'Bachelors', 'CSS', 'Approved', 'ffasfafsafaf', '2026-02-28 12:29:23', '1772281763_RESUME_GradeSectioning.pdf', '1772281763_PRC_student.pdf', '1772281763_CERT_Registration_Slip (1).pdf', '1772281763_OTHER_Form_Clarito.pdf', '1772290746_PROFILE_TEACHER_12.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `first_login` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role_id`, `status`, `first_login`) VALUES
(19, 'admin', '$2y$10$MdaNNF77fXzwj8JVpli8U.ime3KC7mrjRWYI7VGYXPi3/bZBwBd6u', 2, 'Active', 0),
(20, '405220150089', '$2y$10$lqLMtmDg6ouv1LymcbGi6eV8L76wAaF61pg1v4w1fFu12zWhu4Wku', 1, 'Active', 0),
(21, 'T_502301', '$2y$10$R7Y8Sg.RCIr7Ju/q7G7hfe1x8UIEo7EG715Ikqpv/K3N3tdDYiV66', 3, 'Active', 0),
(22, '105330140076', '$2y$10$ucuNM.OqHWLqGOhAXpTSx.YilERaM4FHvqULE.DOigssgE3yjIkku', 1, 'Active', 0),
(23, '123456789123', '$2y$10$LKgZpJZSNS1CNunpIb4Wlu8m5ECS3O2o6GrGVGkrIPBS.UUVja4BW', 1, 'Active', 0),
(24, '183581758295', '$2y$10$OXAK4OirdZHZaPLcqO6sJ.MFzNZ/hGaSg/ahPiA1fMJKbj05XWLoO', 1, 'Active', 0),
(25, '132940224144', '$2y$10$qma9csYwj09jlsrTw9M1Buw9SzCYXPL8Zq4i9lY1xjbz6G21YFjDi', 1, 'Active', 0),
(26, '187472842759', '$2y$10$xE0CJpNfvHcoOlGVOZVXc.4l2m.bnRyKLQIyQGRAWl7zmFCsY4OBG', 1, 'Active', 0),
(34, '108942385023', '$2y$10$VzBXrjSNa6i8zc0pviBLuuZOPFqd..98CusqgI9uCmqajRGCEN5ZC', 1, 'Active', 1),
(35, '109482982957', '$2y$10$kfRPiZEkqKDOiQiBKys1Be0jR5IaR6D8kSsWLQaqgFRRTEPPrmBrC', 1, 'Active', 0),
(36, 'T_585004', '$2y$10$eU3iB8UNmyDBpLgjPdba3OoouYU5HqpeQ15HGzNYCzwu9/NabtQeS', 3, 'Active', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activation_settings`
--
ALTER TABLE `activation_settings`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `school_id` (`student_number`),
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
  ADD UNIQUE KEY `school_id` (`teacher_number`),
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
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activation_settings`
--
ALTER TABLE `activation_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `archived_student_strand`
--
ALTER TABLE `archived_student_strand`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `grade_entry`
--
ALTER TABLE `grade_entry`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=421;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `student_applications`
--
ALTER TABLE `student_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `student_strand`
--
ALTER TABLE `student_strand`
  MODIFY `student_strand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `teacher_advisory`
--
ALTER TABLE `teacher_advisory`
  MODIFY `teacher_advisory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  MODIFY `teacher_application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
