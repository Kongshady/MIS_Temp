-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2026 at 03:28 AM
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
-- Database: `clinlab`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `activity_log_id` int(10) NOT NULL,
  `employee_id` int(10) NOT NULL,
  `datetime_added` datetime NOT NULL,
  `description` varchar(70) NOT NULL,
  `status_code` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`activity_log_id`, `employee_id`, `datetime_added`, `description`, `status_code`) VALUES
(1, 1, '2026-01-23 11:48:50', 'User logged in', 0),
(2, 1, '2026-01-26 10:46:29', 'Failed login attempt for username: admin', 0),
(3, 1, '2026-01-26 10:46:36', 'Failed login attempt for username: admin', 0),
(4, 1, '2026-01-26 10:46:42', 'Failed login attempt for username: admin', 0),
(5, 1, '2026-01-26 10:48:37', 'Failed login attempt for username: admin', 0),
(6, 6, '2026-01-26 10:52:20', 'Failed login attempt for username: nicole', 0),
(7, 8, '2026-01-26 11:01:10', 'User logged in successfully', 1),
(8, 8, '2026-01-26 11:11:51', 'User logged out', 1),
(9, 1, '2026-01-26 11:12:01', 'Failed login attempt for username: admin', 0),
(10, 1, '2026-01-26 11:12:14', 'Failed login attempt for username: admin', 0),
(11, 1, '2026-01-26 11:13:11', 'Failed login attempt for username: admin', 0),
(12, 1, '2026-01-26 11:13:18', 'Failed login attempt for username: admin', 0),
(13, 8, '2026-01-26 11:13:32', 'User logged in successfully', 1),
(14, 8, '2026-01-26 11:16:20', 'User logged out', 1),
(15, 6, '2026-01-26 11:17:59', 'Failed login attempt for username: nicole', 0),
(16, 6, '2026-01-26 11:18:06', 'Failed login attempt for username: nicole', 0),
(17, 6, '2026-01-26 11:18:16', 'Failed login attempt for username: nicole', 0),
(18, 9, '2026-01-26 11:21:49', 'User logged in successfully', 1),
(19, 9, '2026-01-26 11:23:52', 'User logged out', 1),
(20, 10, '2026-01-26 11:23:56', 'User logged in successfully', 1),
(21, 8, '2026-01-28 16:32:24', 'User logged in successfully', 1),
(22, 8, '2026-01-28 16:32:57', 'User logged out', 1),
(23, 8, '2026-01-28 16:35:14', 'User logged in successfully', 1),
(24, 8, '2026-01-28 17:12:54', 'User logged out', 1),
(25, 8, '2026-01-28 17:13:11', 'User logged in successfully', 1),
(26, 8, '2026-01-28 17:30:04', 'User logged out', 1),
(27, 8, '2026-01-28 17:30:40', 'User logged in successfully', 1),
(28, 8, '2026-01-28 17:37:52', 'User logged out', 1),
(29, 1, '2026-01-28 17:37:55', 'Failed login attempt for username: admin', 0),
(30, 1, '2026-01-28 17:38:14', 'Failed login attempt for username: admin', 0),
(31, 1, '2026-01-28 17:38:22', 'Failed login attempt for username: admin', 0),
(32, 6, '2026-01-28 17:40:25', 'Failed login attempt for username: nicole', 0),
(33, 6, '2026-01-28 17:40:33', 'Failed login attempt for username: nicole', 0),
(34, 6, '2026-01-28 17:40:39', 'Failed login attempt for username: nicole', 0),
(35, 10, '2026-01-28 17:41:15', 'User logged in successfully', 1),
(36, 10, '2026-01-29 07:31:45', 'User logged in successfully', 1),
(37, 10, '2026-01-29 07:46:19', 'User logged out', 1),
(38, 12, '2026-01-29 07:46:24', 'User logged in successfully', 1),
(39, 12, '2026-01-29 07:52:35', 'User logged out', 1),
(40, 9, '2026-01-29 07:52:39', 'User logged in successfully', 1),
(41, 9, '2026-01-29 07:52:46', 'User logged out', 1),
(42, 8, '2026-01-29 07:52:50', 'User logged in successfully', 1),
(43, 8, '2026-01-29 07:53:46', 'User logged out', 1),
(44, 1, '2026-01-29 07:53:49', 'User logged in successfully', 1),
(45, 1, '2026-01-29 07:56:49', 'User logged out', 1),
(46, 8, '2026-01-29 07:57:05', 'User logged in successfully', 1),
(47, 8, '2026-01-29 08:06:51', 'User logged out', 1),
(48, 10, '2026-01-29 08:06:55', 'User logged in successfully', 1),
(49, 10, '2026-01-29 09:33:56', 'Added new patient: Jhon Rulona (ID: 8) - Type: Internal', 0),
(50, 10, '2026-01-29 11:09:17', 'User logged out', 1),
(51, 8, '2026-01-29 11:09:20', 'User logged in successfully', 1),
(52, 8, '2026-01-29 11:09:32', 'User logged out', 1),
(53, 10, '2026-01-29 11:09:36', 'User logged in successfully', 1),
(54, 10, '2026-01-29 11:23:05', 'Added new item: New Name (ID: 57)', 0),
(55, 10, '2026-01-29 11:24:46', 'Added new patient: Test Name (ID: 9) - Type: Internal', 0),
(56, 10, '2026-01-29 11:50:10', 'User logged out', 1),
(57, 8, '2026-01-29 11:50:15', 'User logged in successfully', 1),
(58, 8, '2026-01-29 11:50:38', 'User logged out', 1),
(59, 12, '2026-01-29 11:50:43', 'User logged in successfully', 1),
(60, 12, '2026-01-29 13:33:59', 'Deleted test (ID: 26)', 0),
(61, 12, '2026-01-29 13:35:55', 'Added new test: Urinalysis  (ID: 29)', 0),
(62, 12, '2026-01-29 13:56:14', 'User logged out', 1),
(63, 8, '2026-01-29 13:57:06', 'User logged in successfully', 1),
(64, 8, '2026-01-29 13:57:07', 'User logged out', 1),
(65, 10, '2026-01-29 13:57:13', 'User logged in successfully', 1),
(66, 10, '2026-01-29 14:00:06', 'Added new item: test (ID: 59)', 1),
(67, 10, '2026-01-29 14:00:24', 'Auto-created new item type: ngengek (ID: 16)', 1),
(68, 10, '2026-01-29 14:00:24', 'Added new item: Hematology (ID: 60)', 1),
(69, 10, '2026-01-29 14:00:34', 'Deleted item ID: 60', 1),
(70, 10, '2026-01-29 14:00:37', 'Deleted item ID: 59', 1),
(71, 10, '2026-01-29 14:01:29', 'Added new patient: Test Name (ID: 10)', 1),
(72, 10, '2026-01-29 14:48:50', 'User logged out', 1),
(73, 8, '2026-01-29 14:48:53', 'User logged in successfully', 1),
(74, 8, '2026-01-29 14:49:27', 'User logged out', 1),
(75, 8, '2026-01-29 14:49:31', 'User logged in successfully', 1),
(76, 8, '2026-01-29 14:51:29', 'User logged out', 1),
(77, 8, '2026-01-29 14:51:35', 'User logged in successfully', 1),
(78, 8, '2026-01-29 14:51:54', 'User logged out', 1),
(79, 8, '2026-01-29 14:51:56', 'User logged in successfully', 1),
(80, 8, '2026-01-29 14:51:59', 'User logged out', 1),
(81, 9, '2026-01-29 14:52:03', 'User logged in successfully', 1),
(82, 9, '2026-01-29 15:35:28', 'User logged out', 1),
(83, 8, '2026-01-29 15:37:12', 'User logged in successfully', 1),
(84, 8, '2026-01-29 16:02:38', 'User logged out', 1),
(85, 9, '2026-01-29 16:02:44', 'User logged in successfully', 1),
(86, 9, '2026-01-29 16:21:03', 'User logged out', 1),
(87, 10, '2026-01-29 16:21:07', 'User logged in successfully', 1),
(88, 10, '2026-01-29 16:21:41', 'User logged out', 1),
(89, 8, '2026-01-29 16:21:42', 'User logged in successfully', 1),
(90, 8, '2026-01-29 16:22:04', 'User logged out', 1),
(91, 10, '2026-01-29 16:22:08', 'User logged in successfully', 1),
(92, 10, '2026-01-29 16:24:02', 'User logged out', 1),
(93, 8, '2026-01-29 16:24:03', 'User logged in successfully', 1),
(94, 8, '2026-01-29 16:24:07', 'User logged out', 1),
(95, 10, '2026-01-29 16:24:11', 'User logged in successfully', 1),
(96, 10, '2026-01-29 16:24:27', 'User logged out', 1),
(97, 9, '2026-01-29 16:24:31', 'User logged in successfully', 1),
(98, 9, '2026-01-29 17:04:53', 'User logged out', 1),
(99, 11, '2026-01-29 17:04:57', 'User logged in successfully', 1),
(100, 11, '2026-01-29 17:19:15', 'User logged out', 1),
(101, 10, '2026-01-29 17:19:25', 'User logged in successfully', 1),
(102, 10, '2026-01-29 17:20:35', 'User logged out', 1),
(103, 9, '2026-01-29 17:20:39', 'User logged in successfully', 1),
(104, 9, '2026-01-29 17:24:13', 'User logged out', 1),
(105, 10, '2026-01-29 17:24:19', 'User logged in successfully', 1),
(106, 10, '2026-01-29 17:24:30', 'User logged out', 1),
(107, 9, '2026-01-29 17:24:38', 'User logged in successfully', 1),
(108, 9, '2026-01-29 17:24:52', 'User logged out', 1),
(109, 8, '2026-01-29 17:24:53', 'User logged in successfully', 1),
(110, 8, '2026-01-29 17:29:01', 'Added new employee: Audi Thor (ID: 13)', 1),
(111, 8, '2026-01-29 17:29:16', 'User logged out', 1),
(112, 13, '2026-01-29 17:29:22', 'User logged in successfully', 1),
(113, 13, '2026-01-29 17:29:37', 'User logged out', 1),
(114, 8, '2026-01-29 17:29:39', 'User logged in successfully', 1),
(115, 8, '2026-01-29 17:33:00', 'User logged out', 1),
(116, 12, '2026-01-29 17:33:04', 'User logged in successfully', 1),
(117, 12, '2026-01-29 17:41:35', 'User logged out', 1),
(118, 8, '2026-01-29 17:41:48', 'User logged in successfully', 1),
(119, 10, '2026-01-30 10:09:46', 'User logged in successfully', 1),
(120, 10, '2026-01-30 10:23:45', 'User logged out', 1),
(121, 9, '2026-01-30 10:23:49', 'Failed login attempt - incorrect password', 0),
(122, 9, '2026-01-30 10:23:56', 'User logged in successfully', 1);

-- --------------------------------------------------------

--
-- Table structure for table `blood_chemistry`
--

CREATE TABLE `blood_chemistry` (
  `blood_chemistry_id` int(10) NOT NULL,
  `employee_id` int(10) NOT NULL,
  `transaction_id` int(10) NOT NULL,
  `lab_number` int(10) NOT NULL,
  `urates` float NOT NULL,
  `glucose` float NOT NULL,
  `datetime_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calibration_procedure`
--

CREATE TABLE `calibration_procedure` (
  `procedure_id` int(10) NOT NULL,
  `equipment_id` int(10) NOT NULL,
  `procedure_name` varchar(200) NOT NULL,
  `standard_reference` varchar(200) DEFAULT NULL,
  `frequency` enum('monthly','quarterly','semi-annual','annual') NOT NULL,
  `next_due_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calibration_procedure`
--

INSERT INTO `calibration_procedure` (`procedure_id`, `equipment_id`, `procedure_name`, `standard_reference`, `frequency`, `next_due_date`, `is_active`, `datetime_added`) VALUES
(1, 1, 'Hematology Analyzer Calibration', 'ISO 15189:2012', 'quarterly', '2026-04-01', 1, '2026-01-23 14:34:54'),
(2, 2, 'Chemistry Analyzer Calibration', 'CLSI C24-A3', 'quarterly', '2026-04-15', 1, '2026-01-23 14:34:54'),
(3, 3, 'Microscope Optical Calibration', 'ISO 8039:2014', 'semi-annual', '2026-07-01', 1, '2026-01-23 14:34:54'),
(4, 1, 'Hematology Analyzer Calibration', 'ISO 15189:2012', 'quarterly', '2026-04-01', 1, '2026-01-23 14:39:57'),
(5, 2, 'Chemistry Analyzer Calibration', 'CLSI C24-A3', 'quarterly', '2026-04-15', 1, '2026-01-23 14:39:57'),
(6, 3, 'Microscope Optical Calibration', 'ISO 8039:2014', 'semi-annual', '2026-07-01', 1, '2026-01-23 14:39:57');

-- --------------------------------------------------------

--
-- Table structure for table `calibration_record`
--

CREATE TABLE `calibration_record` (
  `record_id` int(10) NOT NULL,
  `procedure_id` int(10) NOT NULL,
  `equipment_id` int(10) NOT NULL,
  `calibration_date` date NOT NULL,
  `performed_by` int(10) DEFAULT NULL,
  `result_status` enum('pass','fail','conditional') NOT NULL,
  `notes` text DEFAULT NULL,
  `attachments` varchar(255) DEFAULT NULL,
  `next_calibration_date` date DEFAULT NULL,
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calibration_record`
--

INSERT INTO `calibration_record` (`record_id`, `procedure_id`, `equipment_id`, `calibration_date`, `performed_by`, `result_status`, `notes`, `attachments`, `next_calibration_date`, `datetime_added`) VALUES
(1, 0, 1, '2026-01-26', 5, 'pass', '', NULL, '2026-01-26', '2026-01-26 11:11:08'),
(2, 0, 1, '2026-01-26', 8, 'fail', '', NULL, '2026-01-26', '2026-01-26 16:08:06'),
(3, 0, 1, '2026-01-26', 8, 'fail', '', NULL, '2026-01-26', '2026-01-26 16:09:55'),
(4, 0, 3, '2026-01-29', 10, 'conditional', 'Naay buak sa ilalom puryagaba nalang jud kinsa nag hulam ani', NULL, '2026-01-29', '2026-01-29 09:20:20');

-- --------------------------------------------------------

--
-- Table structure for table `certificate`
--

CREATE TABLE `certificate` (
  `certificate_id` int(10) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `template_id` int(10) NOT NULL,
  `certificate_type` enum('lab_result','calibration','compliance','safety','other') NOT NULL,
  `linked_record_id` int(10) DEFAULT NULL,
  `linked_table` varchar(50) DEFAULT NULL COMMENT 'lab_result, calibration_record, etc',
  `patient_id` int(10) DEFAULT NULL,
  `equipment_id` int(10) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `issued_by` int(10) DEFAULT NULL,
  `verified_by` int(10) DEFAULT NULL,
  `status` enum('draft','issued','revoked') DEFAULT 'draft',
  `certificate_data` text DEFAULT NULL COMMENT 'JSON data for certificate fields',
  `pdf_path` varchar(255) DEFAULT NULL,
  `datetime_added` datetime DEFAULT current_timestamp(),
  `datetime_modified` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificate_template`
--

CREATE TABLE `certificate_template` (
  `template_id` int(10) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_type` enum('lab_result','calibration','compliance','safety','other') NOT NULL,
  `html_layout` text NOT NULL,
  `version` varchar(20) DEFAULT '1.0',
  `status` enum('active','inactive') DEFAULT 'active',
  `datetime_added` datetime DEFAULT current_timestamp(),
  `datetime_modified` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_template`
--

INSERT INTO `certificate_template` (`template_id`, `template_name`, `template_type`, `html_layout`, `version`, `status`, `datetime_added`, `datetime_modified`) VALUES
(1, 'Laboratory Test Result Certificate', 'lab_result', '<div style=\"text-align:center; padding:50px; border:2px solid #333;\">\r\n<h1>LABORATORY TEST RESULT CERTIFICATE</h1>\r\n<p>Certificate No: {certificate_number}</p>\r\n<p>Patient: {patient_name}</p>\r\n<p>Test: {test_name}</p>\r\n<p>Result: {result_value}</p>\r\n<p>Date: {issue_date}</p>\r\n<p>Issued by: {issued_by}</p>\r\n</div>', '1.0', 'active', '2026-01-23 14:34:54', NULL),
(2, 'Equipment Calibration Certificate', 'calibration', '<div style=\"text-align:center; padding:50px; border:2px solid #333;\">\r\n<h1>CALIBRATION CERTIFICATE</h1>\r\n<p>Certificate No: {certificate_number}</p>\r\n<p>Equipment: {equipment_name}</p>\r\n<p>Calibration Date: {calibration_date}</p>\r\n<p>Result: {result_status}</p>\r\n<p>Valid Until: {next_calibration_date}</p>\r\n<p>Certified by: {issued_by}</p>\r\n</div>', '1.0', 'active', '2026-01-23 14:34:54', NULL),
(3, 'Laboratory Test Result Certificate', 'lab_result', '<div style=\"text-align:center; padding:50px; border:2px solid #333;\">\r\n<h1>LABORATORY TEST RESULT CERTIFICATE</h1>\r\n<p>Certificate No: {certificate_number}</p>\r\n<p>Patient: {patient_name}</p>\r\n<p>Test: {test_name}</p>\r\n<p>Result: {result_value}</p>\r\n<p>Date: {issue_date}</p>\r\n<p>Issued by: {issued_by}</p>\r\n</div>', '1.0', 'active', '2026-01-23 14:39:57', NULL),
(4, 'Equipment Calibration Certificate', 'calibration', '<div style=\"text-align:center; padding:50px; border:2px solid #333;\">\r\n<h1>CALIBRATION CERTIFICATE</h1>\r\n<p>Certificate No: {certificate_number}</p>\r\n<p>Equipment: {equipment_name}</p>\r\n<p>Calibration Date: {calibration_date}</p>\r\n<p>Result: {result_status}</p>\r\n<p>Valid Until: {next_calibration_date}</p>\r\n<p>Certified by: {issued_by}</p>\r\n</div>', '1.0', 'active', '2026-01-23 14:39:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `client_id` int(10) NOT NULL,
  `client_type_id` int(10) NOT NULL,
  `reference_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_type`
--

CREATE TABLE `client_type` (
  `client_type_id` int(8) NOT NULL,
  `label` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employee_id` int(10) NOT NULL,
  `section_id` int(10) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `middlename` varchar(20) DEFAULT NULL,
  `lastname` varchar(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(100) NOT NULL,
  `position` varchar(20) NOT NULL,
  `role_id` int(10) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'Staff',
  `status_code` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`employee_id`, `section_id`, `firstname`, `middlename`, `lastname`, `username`, `password`, `position`, `role_id`, `password_hash`, `role`, `status_code`) VALUES
(1, 1, 'Admin', '', 'nistrator', 'admin', 'admin', 'MIS Admin', 3, '$2y$10$Udd8WmCcV6t6eQhOsuTZjOv5kG2qJtvDOQ0X1YsUSgOfB9uTQkQN6', 'Admin', 1),
(6, 3, 'Nicole', '', 'Calayo', 'nicole', '$2y$10$lxj/4jDkQAQ3nfCA3L5j4OjYIKDjIwA.hIG6CwJ910xkgCEihMvYy', 'MIT Staff', 3, NULL, 'Staff', 1),
(8, 3, 'Rommel', '', 'Jardenico', 'rjardenico', '', 'MIT Staff', 1, '$2y$10$cm35whJmbb5k1cofEQ2hSO6rPiTnDUyXXVJ3wqZq/vtroqkm3CvUy', 'Staff', 1),
(9, 4, 'Nicole Mije', '', 'Calayo', 'mije', '', 'Missionary', 3, '$2y$10$zaFSGSENL.IN6zPHuZ1gmuWmKsroP3rTkemrqZh5Kxef8qwRdwj0u', 'Staff', 1),
(10, 1, 'Mikko', '', 'Jardenico', 'mikko', '', 'Laboratory Manager', 2, '$2y$10$ueDD1TbVczwBWjMiwVXye.BTznQS2rI/8jNyjMVhA9xEks1k7aXnC', 'Staff', 1),
(11, 2, 'Kyle', '', 'Tan', 'ktan', '', 'Secretary', 4, '$2y$10$YwL0azGZGG0WDRLRNhAxOOUjlPd1Iaxdq.H.6n4ZumN9YrhaiBtFq', 'Staff', 1),
(12, 2, 'Martin ', '', 'Tolang', 'mtolang', '', 'SIC', 3, '$2y$10$NhNJwhZUDpRm63yLCDoAB.N/tmzyjwtkaHF6ANLvjlLv8DmRHsUIS', 'Staff', 1),
(13, 1, 'Audi', '', 'Thor', 'audit', '', 'Auditor', 5, '$2y$10$6iBQqg92XnOGq89nxlek7urPl3KB.RbUAVdfKMyEUAaFl20gD4wZC', 'Staff', 1);

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `section_id` int(10) DEFAULT NULL,
  `status` enum('operational','under_maintenance','decommissioned') DEFAULT 'operational',
  `purchase_date` date DEFAULT NULL,
  `supplier` varchar(200) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `model`, `serial_no`, `section_id`, `status`, `purchase_date`, `supplier`, `remarks`, `datetime_added`) VALUES
(1, 'Hematology Analyzer', 'HA-2000', 'SN-HA-001', 1, 'operational', '2023-01-15', 'MedTech Supplies Inc.', 'Automated blood cell counter', '2026-01-23 14:34:54'),
(2, 'Chemistry Analyzer', 'CA-5000', 'SN-CA-002', 2, 'operational', '2023-03-20', 'LabEquip Corp.', 'Biochemistry analyzer', '2026-01-23 14:34:54'),
(3, 'Microscope - Advanced', 'MS-Pro-100', 'SN-MS-003', 1, 'operational', '2022-11-10', 'OpticLab Solutions', 'High-resolution microscope', '2026-01-23 14:34:54'),
(4, 'Centrifuge Machine', 'CF-3000', 'SN-CF-004', 1, 'operational', '2023-05-05', 'MedTech Supplies Inc.', 'High-speed centrifuge', '2026-01-23 14:34:54'),
(5, 'Hematology Analyzer', 'HA-2000', 'SN-HA-001', 1, 'operational', '2023-01-15', 'MedTech Supplies Inc.', 'Automated blood cell counter', '2026-01-23 14:39:57'),
(6, 'Chemistry Analyzer', 'CA-5000', 'SN-CA-002', 2, 'operational', '2023-03-20', 'LabEquip Corp.', 'Biochemistry analyzer', '2026-01-23 14:39:57'),
(7, 'Microscope - Advanced', 'MS-Pro-100', 'SN-MS-003', 1, 'operational', '2022-11-10', 'OpticLab Solutions', 'High-resolution microscope', '2026-01-23 14:39:57'),
(8, 'Centrifuge Machine', 'CF-3000', 'SN-CF-004', 1, 'operational', '2023-05-05', 'MedTech Supplies Inc.', 'High-speed centrifuge', '2026-01-23 14:39:57');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_usage`
--

CREATE TABLE `equipment_usage` (
  `usage_id` int(10) NOT NULL,
  `equipment_id` int(10) NOT NULL,
  `date_used` date NOT NULL,
  `user_name` varchar(200) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `quantity` int(10) NOT NULL DEFAULT 1,
  `purpose` text NOT NULL,
  `or_number` varchar(50) DEFAULT NULL,
  `status` enum('functional','not_functional') NOT NULL DEFAULT 'functional',
  `remarks` text DEFAULT NULL,
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_usage`
--

INSERT INTO `equipment_usage` (`usage_id`, `equipment_id`, `date_used`, `user_name`, `item_name`, `quantity`, `purpose`, `or_number`, `status`, `remarks`, `datetime_added`) VALUES
(1, 6, '2026-01-27', 'Marting Rey Tolang', 'Hehe', 1, 'For school project', '56575859', 'functional', 'Remarks', '2026-01-27 13:17:56');

-- --------------------------------------------------------

--
-- Table structure for table `fecalysis`
--

CREATE TABLE `fecalysis` (
  `fecalysis_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `lab_number` int(11) NOT NULL,
  `datetime_added` datetime NOT NULL,
  `color` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(10) NOT NULL,
  `section_id` int(10) NOT NULL,
  `item_type_id` int(10) NOT NULL,
  `label` varchar(20) NOT NULL,
  `status_code` int(10) NOT NULL,
  `unit` varchar(20) DEFAULT 'pcs',
  `reorder_level` int(10) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`item_id`, `section_id`, `item_type_id`, `label`, `status_code`, `unit`, `reorder_level`) VALUES
(1, 1, 1, 'Blood Suit', 0, 'pcs', 10),
(2, 2, 9, 'Headgear', 1, 'pcs', 10),
(3, 1, 3, 'Urine Reagent Strip', 1, 'box', 5),
(4, 1, 4, 'Urine Container', 1, 'pcs', 50),
(5, 1, 7, 'Microscope Slide', 1, 'box', 10),
(6, 1, 7, 'Cover Slip', 1, 'box', 10),
(7, 1, 3, 'Fecal Occult Blood', 1, 'kit', 3),
(8, 1, 4, 'Stool Container', 1, 'pcs', 30),
(9, 1, 8, 'Crystal Violet', 1, 'ml', 100),
(10, 1, 3, 'Pregnancy Test Kit', 1, 'box', 5),
(11, 2, 3, 'Glucose Reagent', 1, 'ml', 200),
(12, 2, 3, 'Cholesterol Kit', 1, 'kit', 5),
(13, 2, 3, 'Triglyceride Kit', 1, 'kit', 5),
(14, 2, 3, 'Creatinine Reagent', 1, 'ml', 150),
(15, 2, 3, 'Uric Acid Reagent', 1, 'ml', 150),
(16, 2, 3, 'SGPT/ALT Kit', 1, 'kit', 3),
(17, 2, 3, 'SGOT/AST Kit', 1, 'kit', 3),
(18, 2, 4, 'Cuvette', 1, 'pcs', 100),
(19, 2, 7, 'Test Tube 10ml', 1, 'pcs', 50),
(20, 2, 7, 'Pipette Tips', 1, 'box', 10),
(21, 2, 1, 'Distilled Water', 1, 'liter', 5),
(22, 3, 3, 'Blood Agar', 1, 'plate', 20),
(23, 3, 3, 'MacConkey Agar', 1, 'plate', 20),
(24, 3, 3, 'Chocolate Agar', 1, 'plate', 15),
(25, 3, 3, 'Gram Stain Kit', 1, 'kit', 3),
(26, 3, 4, 'Sterile Swab', 1, 'pcs', 50),
(27, 3, 7, 'Petri Dish', 1, 'pcs', 50),
(28, 3, 9, 'Alcohol 70%', 1, 'liter', 3),
(29, 3, 4, 'Inoculating Loop', 1, 'pcs', 20),
(30, 3, 8, 'Crystal Violet', 1, 'ml', 100),
(31, 3, 8, 'Safranin', 1, 'ml', 100),
(32, 4, 3, 'CBC Reagent Pack', 1, 'kit', 5),
(33, 4, 3, 'Hemoglobin Reagent', 1, 'ml', 200),
(34, 4, 4, 'EDTA Tube', 1, 'pcs', 100),
(35, 4, 4, 'Capillary Tube', 1, 'box', 10),
(36, 4, 7, 'Blood Smear Slide', 1, 'box', 10),
(37, 4, 8, 'Giemsa Stain', 1, 'ml', 100),
(38, 4, 8, 'Wright Stain', 1, 'ml', 100),
(39, 4, 4, 'Lancet', 1, 'box', 20),
(40, 4, 4, 'Blood Collection', 1, 'pcs', 50),
(41, 4, 1, 'Anticoagulant Sol', 1, 'ml', 100),
(42, 1, 9, 'Latex Gloves', 1, 'box', 10),
(43, 2, 9, 'Latex Gloves', 1, 'box', 10),
(44, 3, 9, 'Latex Gloves', 1, 'box', 10),
(45, 4, 9, 'Latex Gloves', 1, 'box', 10),
(46, 1, 9, 'Face Mask', 1, 'box', 15),
(47, 2, 9, 'Face Mask', 1, 'box', 15),
(48, 3, 9, 'Lab Coat', 1, 'pcs', 5),
(49, 4, 9, 'Lab Coat', 1, 'pcs', 5),
(50, 2, 4, 'Syringe 5ml', 1, 'pcs', 100),
(51, 2, 4, 'Syringe 10ml', 1, 'pcs', 100),
(52, 3, 4, 'Cotton Balls', 1, 'pack', 10),
(53, 1, 1, 'Tissue Paper', 1, 'roll', 20),
(54, 2, 1, 'Paper Towel', 1, 'roll', 15),
(55, 3, 9, 'Biohazard Bag', 1, 'roll', 5),
(56, 4, 1, 'Marker Pen', 1, 'pcs', 20),
(57, 3, 15, 'New Name', 0, 'pcs', 10);

-- --------------------------------------------------------

--
-- Table structure for table `item_type`
--

CREATE TABLE `item_type` (
  `item_type_id` int(10) NOT NULL,
  `label` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_type`
--

INSERT INTO `item_type` (`item_type_id`, `label`) VALUES
(1, 'Supply'),
(2, 'Equipment'),
(3, 'Reagent'),
(4, 'Consumable'),
(5, 'Reagent'),
(6, 'Consumable'),
(7, 'Glassware'),
(8, 'Chemical'),
(9, 'PPE'),
(10, 'Reagent'),
(11, 'Consumable'),
(12, 'Glassware'),
(13, 'Chemical'),
(14, 'PPE'),
(15, 'Testing'),
(16, 'ngengek');

-- --------------------------------------------------------

--
-- Table structure for table `lab_result`
--

CREATE TABLE `lab_result` (
  `lab_result_id` int(10) NOT NULL,
  `order_test_id` int(10) DEFAULT NULL,
  `lab_test_order_id` int(10) NOT NULL,
  `patient_id` int(10) NOT NULL,
  `test_id` int(10) NOT NULL,
  `result_date` datetime DEFAULT current_timestamp(),
  `findings` text DEFAULT NULL,
  `normal_range` varchar(100) DEFAULT NULL,
  `result_value` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `performed_by` int(10) DEFAULT NULL,
  `verified_by` int(10) DEFAULT NULL,
  `status` enum('draft','final','revised') DEFAULT 'draft',
  `datetime_added` datetime DEFAULT current_timestamp(),
  `datetime_modified` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_test_order`
--

CREATE TABLE `lab_test_order` (
  `lab_test_order_id` int(10) NOT NULL,
  `patient_id` int(10) NOT NULL,
  `physician_id` int(10) DEFAULT NULL,
  `test_id` int(10) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `remarks` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_test_order`
--

INSERT INTO `lab_test_order` (`lab_test_order_id`, `patient_id`, `physician_id`, `test_id`, `order_date`, `status`, `remarks`) VALUES
(37, 9, 5, 0, '2026-01-29 13:16:46', 'completed', NULL),
(38, 6, 6, 0, '2026-01-29 14:26:54', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_history`
--

CREATE TABLE `maintenance_history` (
  `history_id` int(10) NOT NULL,
  `equipment_id` int(10) NOT NULL,
  `maintenance_date` date NOT NULL,
  `performed_by` int(10) DEFAULT NULL,
  `maintenance_type` enum('preventive','corrective','emergency') NOT NULL,
  `notes` text DEFAULT NULL,
  `attachments` varchar(255) DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_schedule`
--

CREATE TABLE `maintenance_schedule` (
  `schedule_id` int(10) NOT NULL,
  `equipment_id` int(10) NOT NULL,
  `frequency` enum('weekly','monthly','quarterly','semi-annual','annual') NOT NULL,
  `next_due_date` date NOT NULL,
  `responsible_employee_id` int(10) DEFAULT NULL,
  `responsible_section_id` int(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_schedule`
--

INSERT INTO `maintenance_schedule` (`schedule_id`, `equipment_id`, `frequency`, `next_due_date`, `responsible_employee_id`, `responsible_section_id`, `is_active`, `datetime_added`) VALUES
(1, 1, 'monthly', '2026-02-01', NULL, 1, 1, '2026-01-23 14:34:54'),
(2, 2, 'monthly', '2026-02-15', NULL, 2, 1, '2026-01-23 14:34:54'),
(3, 3, 'quarterly', '2026-04-01', NULL, 1, 1, '2026-01-23 14:34:54'),
(4, 4, 'monthly', '2026-02-10', NULL, 1, 1, '2026-01-23 14:34:54'),
(5, 1, 'monthly', '2026-02-01', NULL, 1, 1, '2026-01-23 14:39:57'),
(6, 2, 'monthly', '2026-02-15', NULL, 2, 1, '2026-01-23 14:39:57'),
(7, 3, 'quarterly', '2026-04-01', NULL, 1, 1, '2026-01-23 14:39:57'),
(8, 4, 'monthly', '2026-02-10', NULL, 1, 1, '2026-01-23 14:39:57');

-- --------------------------------------------------------

--
-- Table structure for table `order_tests`
--

CREATE TABLE `order_tests` (
  `order_test_id` int(10) NOT NULL,
  `order_id` int(10) NOT NULL,
  `test_id` int(10) NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `assigned_to` int(10) DEFAULT NULL COMMENT 'Employee/technician assigned',
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_tests`
--

INSERT INTO `order_tests` (`order_test_id`, `order_id`, `test_id`, `status`, `assigned_to`, `datetime_added`) VALUES
(28, 38, 2, 'pending', NULL, '2026-01-29 14:26:54'),
(29, 38, 29, 'pending', NULL, '2026-01-29 14:26:54');

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `patient_id` int(10) NOT NULL,
  `patient_type` enum('Internal','External') NOT NULL DEFAULT 'External',
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `status_code` int(10) DEFAULT 1,
  `datetime_added` datetime DEFAULT current_timestamp(),
  `datetime_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patient_id`, `patient_type`, `firstname`, `middlename`, `lastname`, `birthdate`, `gender`, `contact_number`, `address`, `status_code`, `datetime_added`, `datetime_updated`) VALUES
(2, 'External', 'Mikko', '', 'Jardenico', '2003-06-13', 'Male', '09272948757', 'Km 7.5, Orange Groove, Matina Pangi, Davao City', 1, '2026-01-26 09:10:32', '2026-01-29 11:28:27'),
(3, 'External', 'Martin Rey', '', 'Tolang', '2000-09-14', 'Male', '09123456789', 'Indangan', 1, '2026-01-26 09:14:43', '2026-01-29 11:28:27'),
(4, 'External', 'Raver', '', 'Cruz', '1999-06-13', 'Male', '09781623712', 'Matina Pangi', 1, '2026-01-28 08:49:02', '2026-01-29 11:28:27'),
(5, 'External', 'Shamcy', 'Sup', 'Sup', '2000-01-01', 'Male', '09781623712', 'Matina Pangi', 1, '2026-01-28 16:37:01', '2026-01-29 11:28:27'),
(6, 'External', 'Mikha', '', 'Lim', '2000-01-01', 'Female', '095467857628', 'Father Selga St.', 1, '2026-01-29 07:47:59', '2026-01-29 11:28:27'),
(7, 'External', 'Sheena', '', 'Geronimo', '2002-02-02', 'Female', '09676283266', 'Agdao', 1, '2026-01-29 07:48:55', '2026-01-29 11:28:27'),
(8, 'External', 'Jhon', 'Norban', 'Rulona', '1998-03-03', 'Male', '09888888888', 'Bislig', 1, '2026-01-29 09:33:56', '2026-01-29 11:28:27'),
(9, 'External', 'Test', '', 'Name', '2003-01-29', 'Male', '09123456789', 'Sesame Street', 1, '2026-01-29 11:24:46', '2026-01-29 11:28:27'),
(10, 'Internal', 'Test', '', 'Name', '2026-01-29', 'Male', '09127836723', 'Address', 1, '2026-01-29 14:01:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(10) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_key`, `module`, `action`, `description`, `created_at`) VALUES
(1, 'patients.view', 'patients', 'view', 'View patient list and profiles', '2026-01-26 10:37:12'),
(2, 'patients.add_internal', 'patients', 'add_internal', 'Add internal patients (employees/students)', '2026-01-26 10:37:12'),
(3, 'patients.add_walkin', 'patients', 'add_walkin', 'Add walk-in patients', '2026-01-26 10:37:12'),
(4, 'patients.update', 'patients', 'update', 'Update patient information', '2026-01-26 10:37:12'),
(5, 'patients.delete', 'patients', 'delete', 'Delete patient records', '2026-01-26 10:37:12'),
(6, 'physicians.view', 'physicians', 'view', 'View physician list', '2026-01-26 10:37:12'),
(7, 'physicians.manage', 'physicians', 'manage', 'Add, edit, delete physicians', '2026-01-26 10:37:12'),
(8, 'results.view', 'results', 'view', 'View laboratory results', '2026-01-26 10:37:12'),
(9, 'results.create', 'results', 'create', 'Create/add laboratory results', '2026-01-26 10:37:12'),
(10, 'results.update', 'results', 'update', 'Update laboratory results', '2026-01-26 10:37:12'),
(11, 'results.delete', 'results', 'delete', 'Delete laboratory results', '2026-01-26 10:37:12'),
(12, 'transactions.view', 'transactions', 'view', 'View transaction list', '2026-01-26 10:37:12'),
(13, 'transactions.create', 'transactions', 'create', 'Create new transactions', '2026-01-26 10:37:12'),
(14, 'transactions.update', 'transactions', 'update', 'Update transaction information', '2026-01-26 10:37:12'),
(15, 'transactions.delete', 'transactions', 'delete', 'Delete transactions', '2026-01-26 10:37:12'),
(16, 'items.view', 'items', 'view', 'View item master list', '2026-01-26 10:37:12'),
(17, 'items.create', 'items', 'create', 'Add new items', '2026-01-26 10:37:12'),
(18, 'items.update', 'items', 'update', 'Update item information', '2026-01-26 10:37:12'),
(19, 'items.delete', 'items', 'delete', 'Delete items', '2026-01-26 10:37:12'),
(20, 'items.usage', 'items', 'usage', 'Record item usage', '2026-01-26 10:37:12'),
(21, 'inventory.view', 'inventory', 'view', 'View inventory levels and stock', '2026-01-26 10:37:12'),
(22, 'inventory.stock_in', 'inventory', 'stock_in', 'Record stock in/receive supplies', '2026-01-26 10:37:12'),
(23, 'inventory.stock_out', 'inventory', 'stock_out', 'Record stock out/issue supplies', '2026-01-26 10:37:12'),
(24, 'inventory.usage', 'inventory', 'usage', 'Record inventory usage', '2026-01-26 10:37:12'),
(25, 'equipment.view', 'equipment', 'view', 'View equipment list', '2026-01-26 10:37:12'),
(26, 'equipment.manage', 'equipment', 'manage', 'Add, edit, delete equipment', '2026-01-26 10:37:12'),
(27, 'maintenance.manage', 'maintenance', 'manage', 'Manage equipment maintenance schedules', '2026-01-26 10:37:12'),
(28, 'calibration.view', 'calibration', 'view', 'View calibration schedules and records', '2026-01-26 10:37:12'),
(29, 'calibration.manage', 'calibration', 'manage', 'Manage calibration procedures and records', '2026-01-26 10:37:12'),
(30, 'certificates.view', 'certificates', 'view', 'View generated certificates', '2026-01-26 10:37:12'),
(31, 'certificates.generate', 'certificates', 'generate', 'Generate new certificates', '2026-01-26 10:37:12'),
(32, 'certificates.verify', 'certificates', 'verify', 'Verify certificate authenticity', '2026-01-26 10:37:12'),
(33, 'reports.view', 'reports', 'view', 'View available reports', '2026-01-26 10:37:12'),
(34, 'reports.generate', 'reports', 'generate', 'Generate and export reports', '2026-01-26 10:37:12'),
(35, 'logs.view', 'logs', 'view', 'View user activity logs and audit trails', '2026-01-26 10:37:12'),
(36, 'users.view', 'users', 'view', 'View user/employee list', '2026-01-26 10:37:12'),
(37, 'users.manage', 'users', 'manage', 'Add, edit, delete users and manage accounts', '2026-01-26 10:37:12'),
(38, 'tests.view', 'tests', 'view', 'View test list', '2026-01-26 10:37:12'),
(39, 'tests.manage', 'tests', 'manage', 'Add, edit, delete test types', '2026-01-26 10:37:12'),
(40, 'sections.view', 'sections', 'view', 'View section list', '2026-01-26 10:37:12'),
(41, 'sections.manage', 'sections', 'manage', 'Add, edit, delete laboratory sections', '2026-01-26 10:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `physician`
--

CREATE TABLE `physician` (
  `physician_id` int(10) NOT NULL,
  `physician_name` varchar(100) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status_code` int(10) DEFAULT 1,
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `physician`
--

INSERT INTO `physician` (`physician_id`, `physician_name`, `specialization`, `contact_number`, `email`, `status_code`, `datetime_added`) VALUES
(1, 'Dr. Juan Dela Cruz', 'General Practitioner', '09171234567', 'jdelacruz@clinic.com', 1, '2026-01-23 13:40:12'),
(2, 'Dr. Maria Santos', 'Pathologist', '09181234567', 'msantos@clinic.com', 1, '2026-01-23 13:40:12'),
(3, 'Dr. Pedro Garcia', 'Internal Medicine', '09191234567', 'pgarcia@clinic.com', 1, '2026-01-23 13:40:12'),
(4, 'Mikko Jardenico', 'Internal Medicine', '09272948757', 'mjardenico@gmai.com', 0, '2026-01-24 09:08:37'),
(5, 'Martin Rey Tolang', 'General Luna', '091296837182', 'mtolang@gmail.com', 1, '2026-01-26 10:16:53'),
(6, 'Dr. Miggy Caloyko', 'General Medicine', '09123456670', 'mcaloyko@gmail.com', 1, '2026-01-29 09:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `report_id` int(10) NOT NULL,
  `report_type` varchar(20) NOT NULL COMMENT 'Quarterly, Midyear, YearEnd',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `generated_by` int(10) NOT NULL,
  `datetime_generated` datetime NOT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(10) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status_code` int(10) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `display_name`, `description`, `status_code`, `created_at`) VALUES
(1, 'MIT_STAFF', 'MIT Staff', 'System administrator for accounts and master data management', 1, '2026-01-26 10:37:12'),
(2, 'LAB_MANAGER', 'Laboratory Manager', 'Head of clinical laboratory with full operational access', 1, '2026-01-26 10:37:12'),
(3, 'STAFF_IN_CHARGE', 'Staff-in-Charge', 'Laboratory staff with operational duties', 1, '2026-01-26 10:37:12'),
(4, 'SECRETARY', 'Secretary', 'Administrative staff handling transactions and inventory', 1, '2026-01-26 10:37:12'),
(5, 'OPTIONAL_VIEWER', 'Auditor/Compliance Viewer', 'View-only access for auditing and compliance', 1, '2026-01-26 10:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_permission_id` int(10) NOT NULL,
  `role_id` int(10) NOT NULL,
  `permission_id` int(10) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_permission_id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 41, '2026-01-26 10:37:12'),
(2, 1, 40, '2026-01-26 10:37:12'),
(3, 1, 39, '2026-01-26 10:37:12'),
(4, 1, 38, '2026-01-26 10:37:12'),
(5, 1, 37, '2026-01-26 10:37:12'),
(6, 1, 36, '2026-01-26 10:37:12'),
(8, 2, 29, '2026-01-26 10:37:12'),
(9, 2, 28, '2026-01-26 10:37:12'),
(10, 2, 31, '2026-01-26 10:37:12'),
(11, 2, 32, '2026-01-26 10:37:12'),
(12, 2, 30, '2026-01-26 10:37:12'),
(13, 2, 26, '2026-01-26 10:37:12'),
(14, 2, 25, '2026-01-26 10:37:12'),
(15, 2, 22, '2026-01-26 10:37:12'),
(16, 2, 23, '2026-01-26 10:37:12'),
(17, 2, 24, '2026-01-26 10:37:12'),
(18, 2, 21, '2026-01-26 10:37:12'),
(19, 2, 17, '2026-01-26 10:37:12'),
(20, 2, 19, '2026-01-26 10:37:12'),
(21, 2, 18, '2026-01-26 10:37:12'),
(22, 2, 20, '2026-01-26 10:37:12'),
(23, 2, 16, '2026-01-26 10:37:12'),
(24, 2, 35, '2026-01-26 10:37:12'),
(25, 2, 27, '2026-01-26 10:37:12'),
(26, 2, 2, '2026-01-26 10:37:12'),
(27, 2, 3, '2026-01-26 10:37:12'),
(28, 2, 5, '2026-01-26 10:37:12'),
(29, 2, 4, '2026-01-26 10:37:12'),
(30, 2, 1, '2026-01-26 10:37:12'),
(31, 2, 7, '2026-01-26 10:37:12'),
(32, 2, 6, '2026-01-26 10:37:12'),
(33, 2, 34, '2026-01-26 10:37:12'),
(34, 2, 33, '2026-01-26 10:37:12'),
(35, 2, 9, '2026-01-26 10:37:12'),
(36, 2, 11, '2026-01-26 10:37:12'),
(37, 2, 10, '2026-01-26 10:37:12'),
(38, 2, 8, '2026-01-26 10:37:12'),
(39, 2, 13, '2026-01-26 10:37:12'),
(40, 2, 15, '2026-01-26 10:37:12'),
(41, 2, 14, '2026-01-26 10:37:12'),
(42, 2, 12, '2026-01-26 10:37:12'),
(71, 3, 28, '2026-01-26 10:37:12'),
(72, 3, 25, '2026-01-26 10:37:12'),
(73, 3, 24, '2026-01-26 10:37:12'),
(74, 3, 21, '2026-01-26 10:37:12'),
(75, 3, 17, '2026-01-26 10:37:12'),
(76, 3, 18, '2026-01-26 10:37:12'),
(77, 3, 20, '2026-01-26 10:37:12'),
(78, 3, 16, '2026-01-26 10:37:12'),
(79, 3, 2, '2026-01-26 10:37:12'),
(80, 3, 4, '2026-01-26 10:37:12'),
(81, 3, 1, '2026-01-26 10:37:12'),
(82, 3, 7, '2026-01-26 10:37:12'),
(83, 3, 6, '2026-01-26 10:37:12'),
(84, 3, 34, '2026-01-26 10:37:12'),
(85, 3, 33, '2026-01-26 10:37:12'),
(86, 3, 9, '2026-01-26 10:37:12'),
(87, 3, 10, '2026-01-26 10:37:12'),
(88, 3, 8, '2026-01-26 10:37:12'),
(89, 3, 13, '2026-01-26 10:37:12'),
(90, 3, 14, '2026-01-26 10:37:12'),
(91, 3, 12, '2026-01-26 10:37:12'),
(102, 4, 22, '2026-01-26 10:37:12'),
(103, 4, 23, '2026-01-26 10:37:12'),
(104, 4, 24, '2026-01-26 10:37:12'),
(105, 4, 21, '2026-01-26 10:37:12'),
(106, 4, 13, '2026-01-26 10:37:12'),
(107, 4, 14, '2026-01-26 10:37:12'),
(108, 4, 12, '2026-01-26 10:37:12'),
(109, 5, 32, '2026-01-26 10:37:12'),
(110, 5, 30, '2026-01-26 10:37:12'),
(111, 5, 33, '2026-01-26 10:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(10) NOT NULL,
  `label` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `label`) VALUES
(1, 'Immunology-Serology'),
(2, 'Clinical Chemistry'),
(3, 'Urinalysis-Parasitology'),
(4, 'Hematology');

-- --------------------------------------------------------

--
-- Table structure for table `status_code`
--

CREATE TABLE `status_code` (
  `status_code` int(10) NOT NULL,
  `label` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status_code`
--

INSERT INTO `status_code` (`status_code`, `label`) VALUES
(0, 'Cancelled'),
(1, 'Active'),
(2, 'Inactive');

-- --------------------------------------------------------

--
-- Table structure for table `stock_in`
--

CREATE TABLE `stock_in` (
  `stock_in_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `quantity` int(5) NOT NULL,
  `performed_by` int(10) DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL COMMENT 'Purchase/Invoice/DR number',
  `expiry_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `datetime_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_in`
--

INSERT INTO `stock_in` (`stock_in_id`, `item_id`, `quantity`, `performed_by`, `supplier`, `reference_number`, `expiry_date`, `remarks`, `datetime_added`) VALUES
(1, 1, 2, NULL, NULL, NULL, NULL, NULL, '2026-01-23 13:31:36'),
(2, 28, 60, 1, 'supplier', '5656', '2026-01-31', 'huiiuhu', '2026-01-27 10:44:25');

-- --------------------------------------------------------

--
-- Table structure for table `stock_out`
--

CREATE TABLE `stock_out` (
  `stock_out_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `quantity` int(5) NOT NULL,
  `performed_by` int(10) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL COMMENT 'Requisition/Request number',
  `remarks` text DEFAULT NULL,
  `datetime_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_out`
--

INSERT INTO `stock_out` (`stock_out_id`, `item_id`, `quantity`, `performed_by`, `reference_number`, `remarks`, `datetime_added`) VALUES
(1, 1, 1, NULL, NULL, 'I just want to', '2026-01-23 13:32:01'),
(2, 28, 1, 1, NULL, 'Used by: 5656 - bhhbj', '2026-01-27 10:44:46');

-- --------------------------------------------------------

--
-- Table structure for table `stock_usage`
--

CREATE TABLE `stock_usage` (
  `stock_usage_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `quantity` int(5) NOT NULL DEFAULT 1,
  `employee_id` int(10) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `middlename` varchar(20) DEFAULT NULL,
  `lastname` varchar(20) NOT NULL,
  `purpose` varchar(30) NOT NULL,
  `datetime_added` datetime NOT NULL,
  `or_number` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_usage`
--

INSERT INTO `stock_usage` (`stock_usage_id`, `item_id`, `quantity`, `employee_id`, `firstname`, `middlename`, `lastname`, `purpose`, `datetime_added`, `or_number`) VALUES
(1, 28, 1, 1, 'John', '', 'Admin', 'bhhbj', '2026-01-27 10:44:46', 5656);

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `test_id` int(10) NOT NULL,
  `section_id` int(10) NOT NULL,
  `label` varchar(20) NOT NULL,
  `current_price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test`
--

INSERT INTO `test` (`test_id`, `section_id`, `label`, `current_price`) VALUES
(1, 4, 'Complete Blood Count', 115.00),
(2, 4, 'Clothing Time', 50.00),
(3, 4, 'Bleeding Time', 50.00),
(4, 4, 'CT/BT', 100.00),
(5, 4, 'Pheripheral Blood Sm', 280.00),
(6, 4, 'Blood Typing', 100.00),
(7, 4, 'Reticulocyte Count', 80.00),
(8, 4, 'ESR ', 150.00),
(29, 3, 'Urinalysis ', 40.00),
(30, 3, 'Fecalysis', 25.00),
(31, 3, 'Semenalysis ', 200.00),
(32, 3, 'Occult Blood', 80.00),
(33, 3, 'Pregnancy Test (Urin', 160.00),
(34, 2, 'Blood Sugar', 100.00),
(35, 2, 'Oral Glucose Toleran', 565.00),
(36, 2, 'Oral Glucose Toleran', 500.00),
(37, 2, '2-hr. Post-Prandial ', 120.00),
(38, 2, 'Creatinine ', 150.00),
(39, 2, 'SGPT/ALT', 170.00),
(40, 2, 'SGOT/AST', 170.00),
(41, 2, 'Total Cholesterol', 150.00),
(42, 2, 'Triglyceride ', 205.00),
(43, 2, 'LIPID Profile', 590.00),
(44, 2, 'Hemoglobin (HBA1c)', 650.00),
(45, 2, 'Cloride Package', 500.00),
(46, 1, 'THYROID PROFILE', 1215.00),
(47, 1, 'T3', 375.00),
(48, 1, 'T4', 375.00),
(49, 1, 'Free T4', 600.00),
(50, 1, 'TSH ', 500.00),
(51, 1, 'HBsAg Qualitative', 140.00),
(52, 1, 'HBsAg Quantitative', 510.00),
(53, 1, 'HAV (IgM)  ', 650.00),
(54, 1, 'α-Feto-protein', 700.00),
(55, 1, 'Prostatic Specific A', 910.00),
(56, 1, 'CA 125', 750.00),
(57, 1, 'CA 15-3', 850.00),
(58, 1, 'DENGUE IgG/IgM', 300.00),
(59, 1, 'PAP SMEAR', 280.00),
(60, 1, 'GRAM STAIN', 150.00),
(61, 1, 'AFB STAIN  (2 TAKES)', 280.00),
(62, 1, 'KOH ', 160.00);

-- --------------------------------------------------------

--
-- Table structure for table `test_price_history`
--

CREATE TABLE `test_price_history` (
  `price_history_id` int(11) NOT NULL,
  `test_id` int(10) NOT NULL,
  `previous_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `new_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_price_history`
--

INSERT INTO `test_price_history` (`price_history_id`, `test_id`, `previous_price`, `new_price`, `updated_by`, `updated_at`) VALUES
(3, 1, 0.00, 115.00, 1, '2026-01-28 06:00:42'),
(4, 29, 0.00, 40.00, 12, '2026-01-29 05:35:55'),
(5, 30, 0.00, 25.00, 10, '2026-01-29 06:30:14'),
(6, 31, 0.00, 200.00, 10, '2026-01-29 06:30:29'),
(7, 32, 0.00, 80.00, 10, '2026-01-29 06:30:41'),
(8, 33, 0.00, 160.00, 10, '2026-01-29 06:30:53'),
(9, 34, 0.00, 100.00, 10, '2026-01-29 06:31:08'),
(10, 35, 0.00, 565.00, 10, '2026-01-29 06:31:30'),
(11, 36, 0.00, 500.00, 10, '2026-01-29 06:31:54'),
(12, 37, 0.00, 120.00, 10, '2026-01-29 06:32:17'),
(13, 38, 0.00, 150.00, 10, '2026-01-29 06:32:38'),
(14, 39, 0.00, 170.00, 10, '2026-01-29 06:32:50'),
(15, 40, 0.00, 170.00, 10, '2026-01-29 06:33:10'),
(16, 41, 0.00, 150.00, 10, '2026-01-29 06:33:23'),
(17, 42, 0.00, 205.00, 10, '2026-01-29 06:33:33'),
(18, 43, 0.00, 590.00, 10, '2026-01-29 06:33:45'),
(19, 44, 0.00, 650.00, 10, '2026-01-29 06:33:59'),
(20, 45, 0.00, 500.00, 10, '2026-01-29 06:34:10'),
(21, 46, 0.00, 1215.00, 10, '2026-01-29 06:34:58'),
(22, 47, 0.00, 375.00, 10, '2026-01-29 06:35:18'),
(23, 48, 0.00, 375.00, 10, '2026-01-29 06:35:30'),
(24, 49, 0.00, 600.00, 10, '2026-01-29 06:35:42'),
(25, 50, 0.00, 500.00, 10, '2026-01-29 06:36:06'),
(26, 51, 0.00, 140.00, 10, '2026-01-29 06:36:21'),
(27, 52, 0.00, 510.00, 10, '2026-01-29 06:36:45'),
(28, 53, 0.00, 650.00, 10, '2026-01-29 06:37:00'),
(29, 54, 0.00, 700.00, 10, '2026-01-29 06:37:13'),
(30, 55, 0.00, 910.00, 10, '2026-01-29 06:37:21'),
(31, 56, 0.00, 750.00, 10, '2026-01-29 06:37:32'),
(32, 57, 0.00, 850.00, 10, '2026-01-29 06:37:50'),
(33, 58, 0.00, 300.00, 10, '2026-01-29 06:38:04'),
(34, 59, 0.00, 280.00, 10, '2026-01-29 06:38:16'),
(35, 60, 0.00, 150.00, 10, '2026-01-29 06:38:29'),
(36, 61, 0.00, 280.00, 10, '2026-01-29 06:38:40'),
(37, 62, 0.00, 150.00, 10, '2026-01-29 06:38:50'),
(38, 7, 0.00, 80.00, 10, '2026-01-29 06:39:04'),
(39, 8, 0.00, 150.00, 10, '2026-01-29 06:39:35'),
(40, 6, 0.00, 100.00, 10, '2026-01-29 06:41:50'),
(41, 5, 0.00, 280.00, 10, '2026-01-29 06:42:15'),
(42, 4, 0.00, 100.00, 10, '2026-01-29 06:42:27'),
(43, 3, 0.00, 50.00, 10, '2026-01-29 06:42:45'),
(44, 2, 0.00, 50.00, 10, '2026-01-29 06:43:02'),
(45, 62, 150.00, 160.00, 10, '2026-01-29 06:43:13');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `transaction_id` int(10) NOT NULL,
  `client_id` int(10) NOT NULL,
  `or_number` int(10) NOT NULL,
  `client_designation` varchar(50) DEFAULT NULL,
  `datetime_added` datetime NOT NULL,
  `status_code` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`transaction_id`, `client_id`, `or_number`, `client_designation`, `datetime_added`, `status_code`) VALUES
(7, 2, 2147483647, NULL, '2026-01-28 16:43:34', 1),
(8, 5, 2147483647, NULL, '2026-01-28 16:58:53', 0),
(9, 3, 2147483647, NULL, '2026-01-29 09:35:14', 0);

-- --------------------------------------------------------

--
-- Table structure for table `transaction_detail`
--

CREATE TABLE `transaction_detail` (
  `transaction_detail_id` int(10) NOT NULL,
  `transaction_id` int(10) NOT NULL,
  `test_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` int(10) NOT NULL,
  `employee_id` int(10) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_current_stock`
-- (See below for the actual view)
--
CREATE TABLE `v_current_stock` (
`item_id` int(10)
,`item_name` varchar(20)
,`item_type` varchar(20)
,`total_in` decimal(32,0)
,`total_out` decimal(32,0)
,`current_stock` decimal(33,0)
,`reorder_level` int(10)
,`unit` varchar(20)
,`stock_status` varchar(12)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_role_permissions_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_role_permissions_summary` (
`role_name` varchar(50)
,`display_name` varchar(100)
,`total_permissions` bigint(21)
,`modules_access` mediumtext
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_stock_expiry`
-- (See below for the actual view)
--
CREATE TABLE `v_stock_expiry` (
`stock_in_id` int(10)
,`item_id` int(10)
,`item_name` varchar(20)
,`section_name` varchar(50)
,`quantity` int(5)
,`expiry_date` date
,`supplier` varchar(100)
,`reference_number` varchar(50)
,`datetime_added` datetime
,`days_until_expiry` int(7)
,`expiry_status` varchar(13)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_stock_movements`
-- (See below for the actual view)
--
CREATE TABLE `v_stock_movements` (
`movement_type` varchar(5)
,`movement_id` int(11)
,`item_id` int(11)
,`item_name` varchar(20)
,`section_name` varchar(50)
,`quantity` int(11)
,`performed_by` int(11)
,`performed_by_name` varchar(41)
,`supplier` varchar(100)
,`reference_number` varchar(50)
,`remarks` mediumtext
,`datetime_added` datetime
,`expiry_date` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_upcoming_calibration`
-- (See below for the actual view)
--
CREATE TABLE `v_upcoming_calibration` (
`equipment_id` int(10)
,`equipment_name` varchar(100)
,`model` varchar(100)
,`procedure_name` varchar(200)
,`next_due_date` date
,`frequency` enum('monthly','quarterly','semi-annual','annual')
,`days_until_due` int(7)
,`status` varchar(9)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_upcoming_maintenance`
-- (See below for the actual view)
--
CREATE TABLE `v_upcoming_maintenance` (
`equipment_id` int(10)
,`equipment_name` varchar(100)
,`model` varchar(100)
,`serial_no` varchar(100)
,`section_name` varchar(50)
,`next_due_date` date
,`frequency` enum('weekly','monthly','quarterly','semi-annual','annual')
,`days_until_due` int(7)
,`status` varchar(9)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_user_permissions`
-- (See below for the actual view)
--
CREATE TABLE `v_user_permissions` (
`employee_id` int(10)
,`employee_name` varchar(41)
,`position` varchar(20)
,`role_name` varchar(50)
,`role_display` varchar(100)
,`permission_key` varchar(100)
,`module` varchar(50)
,`action` varchar(50)
,`permission_description` varchar(255)
);

-- --------------------------------------------------------

--
-- Structure for view `v_current_stock`
--
DROP TABLE IF EXISTS `v_current_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_current_stock`  AS SELECT `i`.`item_id` AS `item_id`, `i`.`label` AS `item_name`, `it`.`label` AS `item_type`, coalesce(sum(`si`.`quantity`),0) AS `total_in`, coalesce(sum(`so`.`quantity`),0) AS `total_out`, coalesce(sum(`si`.`quantity`),0) - coalesce(sum(`so`.`quantity`),0) AS `current_stock`, `i`.`reorder_level` AS `reorder_level`, `i`.`unit` AS `unit`, CASE WHEN coalesce(sum(`si`.`quantity`),0) - coalesce(sum(`so`.`quantity`),0) <= 0 THEN 'Out of Stock' WHEN coalesce(sum(`si`.`quantity`),0) - coalesce(sum(`so`.`quantity`),0) <= `i`.`reorder_level` THEN 'Low Stock' ELSE 'In Stock' END AS `stock_status` FROM (((`item` `i` left join `item_type` `it` on(`i`.`item_type_id` = `it`.`item_type_id`)) left join `stock_in` `si` on(`i`.`item_id` = `si`.`item_id`)) left join `stock_out` `so` on(`i`.`item_id` = `so`.`item_id`)) GROUP BY `i`.`item_id` ORDER BY coalesce(sum(`si`.`quantity`),0) - coalesce(sum(`so`.`quantity`),0) ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_role_permissions_summary`
--
DROP TABLE IF EXISTS `v_role_permissions_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_role_permissions_summary`  AS SELECT `r`.`role_name` AS `role_name`, `r`.`display_name` AS `display_name`, count(distinct `rp`.`permission_id`) AS `total_permissions`, group_concat(distinct `p`.`module` order by `p`.`module` ASC separator ', ') AS `modules_access` FROM ((`roles` `r` left join `role_permissions` `rp` on(`r`.`role_id` = `rp`.`role_id`)) left join `permissions` `p` on(`rp`.`permission_id` = `p`.`permission_id`)) WHERE `r`.`status_code` = 1 GROUP BY `r`.`role_id`, `r`.`role_name`, `r`.`display_name` ;

-- --------------------------------------------------------

--
-- Structure for view `v_stock_expiry`
--
DROP TABLE IF EXISTS `v_stock_expiry`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stock_expiry`  AS SELECT `si`.`stock_in_id` AS `stock_in_id`, `si`.`item_id` AS `item_id`, `i`.`label` AS `item_name`, `s`.`label` AS `section_name`, `si`.`quantity` AS `quantity`, `si`.`expiry_date` AS `expiry_date`, `si`.`supplier` AS `supplier`, `si`.`reference_number` AS `reference_number`, `si`.`datetime_added` AS `datetime_added`, to_days(`si`.`expiry_date`) - to_days(curdate()) AS `days_until_expiry`, CASE WHEN `si`.`expiry_date` is null THEN 'no_expiry' WHEN `si`.`expiry_date` < curdate() THEN 'expired' WHEN to_days(`si`.`expiry_date`) - to_days(curdate()) <= 30 THEN 'expiring_soon' ELSE 'valid' END AS `expiry_status` FROM ((`stock_in` `si` join `item` `i` on(`si`.`item_id` = `i`.`item_id`)) left join `section` `s` on(`i`.`section_id` = `s`.`section_id`)) WHERE `si`.`expiry_date` is not null ORDER BY `si`.`expiry_date` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_stock_movements`
--
DROP TABLE IF EXISTS `v_stock_movements`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stock_movements`  AS SELECT 'IN' AS `movement_type`, `si`.`stock_in_id` AS `movement_id`, `si`.`item_id` AS `item_id`, `i`.`label` AS `item_name`, `s`.`label` AS `section_name`, `si`.`quantity` AS `quantity`, `si`.`performed_by` AS `performed_by`, concat_ws(' ',`e`.`firstname`,`e`.`lastname`) AS `performed_by_name`, `si`.`supplier` AS `supplier`, `si`.`reference_number` AS `reference_number`, `si`.`remarks` AS `remarks`, `si`.`datetime_added` AS `datetime_added`, `si`.`expiry_date` AS `expiry_date` FROM (((`stock_in` `si` join `item` `i` on(`si`.`item_id` = `i`.`item_id`)) left join `section` `s` on(`i`.`section_id` = `s`.`section_id`)) left join `employee` `e` on(`si`.`performed_by` = `e`.`employee_id`))union all select 'OUT' AS `movement_type`,`so`.`stock_out_id` AS `movement_id`,`so`.`item_id` AS `item_id`,`i`.`label` AS `item_name`,`s`.`label` AS `section_name`,`so`.`quantity` AS `quantity`,`so`.`performed_by` AS `performed_by`,concat_ws(' ',`e`.`firstname`,`e`.`lastname`) AS `performed_by_name`,NULL AS `supplier`,`so`.`reference_number` AS `reference_number`,`so`.`remarks` AS `remarks`,`so`.`datetime_added` AS `datetime_added`,NULL AS `expiry_date` from (((`stock_out` `so` join `item` `i` on(`so`.`item_id` = `i`.`item_id`)) left join `section` `s` on(`i`.`section_id` = `s`.`section_id`)) left join `employee` `e` on(`so`.`performed_by` = `e`.`employee_id`)) union all select 'USAGE' AS `movement_type`,`su`.`stock_usage_id` AS `movement_id`,`su`.`item_id` AS `item_id`,`i`.`label` AS `item_name`,`s`.`label` AS `section_name`,`su`.`quantity` AS `quantity`,`su`.`employee_id` AS `performed_by`,concat_ws(' ',`su`.`firstname`,`su`.`lastname`) AS `performed_by_name`,NULL AS `supplier`,`su`.`or_number` AS `reference_number`,`su`.`purpose` AS `remarks`,`su`.`datetime_added` AS `datetime_added`,NULL AS `expiry_date` from ((`stock_usage` `su` join `item` `i` on(`su`.`item_id` = `i`.`item_id`)) left join `section` `s` on(`i`.`section_id` = `s`.`section_id`)) order by `datetime_added` desc  ;

-- --------------------------------------------------------

--
-- Structure for view `v_upcoming_calibration`
--
DROP TABLE IF EXISTS `v_upcoming_calibration`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_upcoming_calibration`  AS SELECT `e`.`equipment_id` AS `equipment_id`, `e`.`name` AS `equipment_name`, `e`.`model` AS `model`, `cp`.`procedure_name` AS `procedure_name`, `cp`.`next_due_date` AS `next_due_date`, `cp`.`frequency` AS `frequency`, to_days(`cp`.`next_due_date`) - to_days(curdate()) AS `days_until_due`, CASE WHEN `cp`.`next_due_date` < curdate() THEN 'Overdue' WHEN to_days(`cp`.`next_due_date`) - to_days(curdate()) <= 7 THEN 'Due Soon' ELSE 'Scheduled' END AS `status` FROM (`equipment` `e` left join `calibration_procedure` `cp` on(`e`.`equipment_id` = `cp`.`equipment_id`)) WHERE `cp`.`is_active` = 1 ORDER BY `cp`.`next_due_date` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_upcoming_maintenance`
--
DROP TABLE IF EXISTS `v_upcoming_maintenance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_upcoming_maintenance`  AS SELECT `e`.`equipment_id` AS `equipment_id`, `e`.`name` AS `equipment_name`, `e`.`model` AS `model`, `e`.`serial_no` AS `serial_no`, `s`.`label` AS `section_name`, `ms`.`next_due_date` AS `next_due_date`, `ms`.`frequency` AS `frequency`, to_days(`ms`.`next_due_date`) - to_days(curdate()) AS `days_until_due`, CASE WHEN `ms`.`next_due_date` < curdate() THEN 'Overdue' WHEN to_days(`ms`.`next_due_date`) - to_days(curdate()) <= 7 THEN 'Due Soon' ELSE 'Scheduled' END AS `status` FROM ((`equipment` `e` left join `maintenance_schedule` `ms` on(`e`.`equipment_id` = `ms`.`equipment_id`)) left join `section` `s` on(`e`.`section_id` = `s`.`section_id`)) WHERE `ms`.`is_active` = 1 ORDER BY `ms`.`next_due_date` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_user_permissions`
--
DROP TABLE IF EXISTS `v_user_permissions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_user_permissions`  AS SELECT `e`.`employee_id` AS `employee_id`, concat(`e`.`firstname`,' ',`e`.`lastname`) AS `employee_name`, `e`.`position` AS `position`, `r`.`role_name` AS `role_name`, `r`.`display_name` AS `role_display`, `p`.`permission_key` AS `permission_key`, `p`.`module` AS `module`, `p`.`action` AS `action`, `p`.`description` AS `permission_description` FROM (((`employee` `e` left join `roles` `r` on(`e`.`role_id` = `r`.`role_id`)) left join `role_permissions` `rp` on(`r`.`role_id` = `rp`.`role_id`)) left join `permissions` `p` on(`rp`.`permission_id` = `p`.`permission_id`)) WHERE `e`.`status_code` = 1 AND `r`.`status_code` = 1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`activity_log_id`),
  ADD KEY `idx_log_employee` (`employee_id`),
  ADD KEY `idx_log_status` (`status_code`);

--
-- Indexes for table `blood_chemistry`
--
ALTER TABLE `blood_chemistry`
  ADD PRIMARY KEY (`blood_chemistry_id`),
  ADD KEY `idx_bc_employee` (`employee_id`),
  ADD KEY `idx_bc_txn` (`transaction_id`);

--
-- Indexes for table `calibration_procedure`
--
ALTER TABLE `calibration_procedure`
  ADD PRIMARY KEY (`procedure_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `idx_calibration_next_due` (`next_due_date`);

--
-- Indexes for table `calibration_record`
--
ALTER TABLE `calibration_record`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `procedure_id` (`procedure_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `certificate`
--
ALTER TABLE `certificate`
  ADD PRIMARY KEY (`certificate_id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `issued_by` (`issued_by`),
  ADD KEY `idx_certificate_number` (`certificate_number`),
  ADD KEY `idx_certificate_status` (`status`);

--
-- Indexes for table `certificate_template`
--
ALTER TABLE `certificate_template`
  ADD PRIMARY KEY (`template_id`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`client_id`),
  ADD KEY `idx_client_type` (`client_type_id`);

--
-- Indexes for table `client_type`
--
ALTER TABLE `client_type`
  ADD PRIMARY KEY (`client_type_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `uq_employee_username` (`username`),
  ADD KEY `idx_employee_section` (`section_id`),
  ADD KEY `idx_employee_status` (`status_code`),
  ADD KEY `idx_role_id` (`role_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `idx_equipment_status` (`status`);

--
-- Indexes for table `equipment_usage`
--
ALTER TABLE `equipment_usage`
  ADD PRIMARY KEY (`usage_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `idx_date_used` (`date_used`),
  ADD KEY `idx_user_name` (`user_name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `fecalysis`
--
ALTER TABLE `fecalysis`
  ADD PRIMARY KEY (`fecalysis_id`),
  ADD KEY `idx_fec_employee` (`employee_id`),
  ADD KEY `idx_fec_txn` (`transaction_id`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_item_section` (`section_id`),
  ADD KEY `idx_item_type` (`item_type_id`),
  ADD KEY `idx_item_status` (`status_code`);

--
-- Indexes for table `item_type`
--
ALTER TABLE `item_type`
  ADD PRIMARY KEY (`item_type_id`);

--
-- Indexes for table `lab_result`
--
ALTER TABLE `lab_result`
  ADD PRIMARY KEY (`lab_result_id`),
  ADD KEY `lab_test_order_id` (`lab_test_order_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `order_test_id` (`order_test_id`);

--
-- Indexes for table `lab_test_order`
--
ALTER TABLE `lab_test_order`
  ADD PRIMARY KEY (`lab_test_order_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `physician_id` (`physician_id`),
  ADD KEY `test_id` (`test_id`);

--
-- Indexes for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `maintenance_schedule`
--
ALTER TABLE `maintenance_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `responsible_employee_id` (`responsible_employee_id`),
  ADD KEY `responsible_section_id` (`responsible_section_id`),
  ADD KEY `idx_maintenance_next_due` (`next_due_date`);

--
-- Indexes for table `order_tests`
--
ALTER TABLE `order_tests`
  ADD PRIMARY KEY (`order_test_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `idx_order_tests_status` (`status`),
  ADD KEY `idx_order_tests_order` (`order_id`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_key` (`permission_key`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_permission_key` (`permission_key`);

--
-- Indexes for table `physician`
--
ALTER TABLE `physician`
  ADD PRIMARY KEY (`physician_id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_permission_id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `status_code`
--
ALTER TABLE `status_code`
  ADD PRIMARY KEY (`status_code`);

--
-- Indexes for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD PRIMARY KEY (`stock_in_id`),
  ADD KEY `idx_stockin_item` (`item_id`),
  ADD KEY `fk_stock_in_employee` (`performed_by`),
  ADD KEY `idx_stock_in_date` (`datetime_added`),
  ADD KEY `idx_stock_in_expiry` (`expiry_date`);

--
-- Indexes for table `stock_out`
--
ALTER TABLE `stock_out`
  ADD PRIMARY KEY (`stock_out_id`),
  ADD KEY `idx_stockout_item` (`item_id`),
  ADD KEY `fk_stock_out_employee` (`performed_by`),
  ADD KEY `idx_stock_out_date` (`datetime_added`);

--
-- Indexes for table `stock_usage`
--
ALTER TABLE `stock_usage`
  ADD PRIMARY KEY (`stock_usage_id`),
  ADD KEY `idx_usage_item` (`item_id`),
  ADD KEY `idx_usage_employee` (`employee_id`),
  ADD KEY `idx_stock_usage_date` (`datetime_added`);

--
-- Indexes for table `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`test_id`),
  ADD KEY `idx_test_section` (`section_id`);

--
-- Indexes for table `test_price_history`
--
ALTER TABLE `test_price_history`
  ADD PRIMARY KEY (`price_history_id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_txn_client` (`client_id`),
  ADD KEY `idx_txn_status` (`status_code`);

--
-- Indexes for table `transaction_detail`
--
ALTER TABLE `transaction_detail`
  ADD PRIMARY KEY (`transaction_detail_id`),
  ADD KEY `idx_txd_txn` (`transaction_id`),
  ADD KEY `idx_txd_test` (`test_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `session_token` (`session_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `activity_log_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `blood_chemistry`
--
ALTER TABLE `blood_chemistry`
  MODIFY `blood_chemistry_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calibration_procedure`
--
ALTER TABLE `calibration_procedure`
  MODIFY `procedure_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `calibration_record`
--
ALTER TABLE `calibration_record`
  MODIFY `record_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `certificate`
--
ALTER TABLE `certificate`
  MODIFY `certificate_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificate_template`
--
ALTER TABLE `certificate_template`
  MODIFY `template_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `client_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_type`
--
ALTER TABLE `client_type`
  MODIFY `client_type_id` int(8) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `equipment_usage`
--
ALTER TABLE `equipment_usage`
  MODIFY `usage_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fecalysis`
--
ALTER TABLE `fecalysis`
  MODIFY `fecalysis_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `item_type`
--
ALTER TABLE `item_type`
  MODIFY `item_type_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `lab_result`
--
ALTER TABLE `lab_result`
  MODIFY `lab_result_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lab_test_order`
--
ALTER TABLE `lab_test_order`
  MODIFY `lab_test_order_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  MODIFY `history_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_schedule`
--
ALTER TABLE `maintenance_schedule`
  MODIFY `schedule_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_tests`
--
ALTER TABLE `order_tests`
  MODIFY `order_test_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `patient_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `physician`
--
ALTER TABLE `physician`
  MODIFY `physician_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `report_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `role_permission_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `section_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `stock_in_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_out`
--
ALTER TABLE `stock_out`
  MODIFY `stock_out_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_usage`
--
ALTER TABLE `stock_usage`
  MODIFY `stock_usage_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `test`
--
ALTER TABLE `test`
  MODIFY `test_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `test_price_history`
--
ALTER TABLE `test_price_history`
  MODIFY `price_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `transaction_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transaction_detail`
--
ALTER TABLE `transaction_detail`
  MODIFY `transaction_detail_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `session_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `fk_log_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_log_status` FOREIGN KEY (`status_code`) REFERENCES `status_code` (`status_code`) ON UPDATE CASCADE;

--
-- Constraints for table `blood_chemistry`
--
ALTER TABLE `blood_chemistry`
  ADD CONSTRAINT `fk_bc_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bc_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `fk_client_clienttype` FOREIGN KEY (`client_type_id`) REFERENCES `client_type` (`client_type_id`) ON UPDATE CASCADE;

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `fk_employee_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_employee_section` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_status` FOREIGN KEY (`status_code`) REFERENCES `status_code` (`status_code`) ON UPDATE CASCADE;

--
-- Constraints for table `equipment_usage`
--
ALTER TABLE `equipment_usage`
  ADD CONSTRAINT `fk_equipment_usage` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`) ON DELETE CASCADE;

--
-- Constraints for table `fecalysis`
--
ALTER TABLE `fecalysis`
  ADD CONSTRAINT `fk_fec_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fec_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `fk_item_itemtype` FOREIGN KEY (`item_type_id`) REFERENCES `item_type` (`item_type_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_section` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_status` FOREIGN KEY (`status_code`) REFERENCES `status_code` (`status_code`) ON UPDATE CASCADE;

--
-- Constraints for table `order_tests`
--
ALTER TABLE `order_tests`
  ADD CONSTRAINT `fk_order_tests_order` FOREIGN KEY (`order_id`) REFERENCES `lab_test_order` (`lab_test_order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_tests_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`test_id`);

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `employee` (`employee_id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD CONSTRAINT `fk_stock_in_employee` FOREIGN KEY (`performed_by`) REFERENCES `employee` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stockin_item` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON UPDATE CASCADE;

--
-- Constraints for table `stock_out`
--
ALTER TABLE `stock_out`
  ADD CONSTRAINT `fk_stock_out_employee` FOREIGN KEY (`performed_by`) REFERENCES `employee` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stockout_item` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON UPDATE CASCADE;

--
-- Constraints for table `stock_usage`
--
ALTER TABLE `stock_usage`
  ADD CONSTRAINT `fk_usage_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usage_item` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON UPDATE CASCADE;

--
-- Constraints for table `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `fk_test_section` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON UPDATE CASCADE;

--
-- Constraints for table `test_price_history`
--
ALTER TABLE `test_price_history`
  ADD CONSTRAINT `fk_price_history_employee` FOREIGN KEY (`updated_by`) REFERENCES `employee` (`employee_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_price_history_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`test_id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `fk_txn_patient` FOREIGN KEY (`client_id`) REFERENCES `patient` (`patient_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_txn_status` FOREIGN KEY (`status_code`) REFERENCES `status_code` (`status_code`) ON UPDATE CASCADE;

--
-- Constraints for table `transaction_detail`
--
ALTER TABLE `transaction_detail`
  ADD CONSTRAINT `fk_txd_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`test_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_txd_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
