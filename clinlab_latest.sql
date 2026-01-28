-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 26, 2026 at 02:17 AM
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
(1, 1, '2026-01-23 11:48:50', 'User logged in', 0);

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
  `role` varchar(20) DEFAULT 'Staff',
  `status_code` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`employee_id`, `section_id`, `firstname`, `middlename`, `lastname`, `username`, `password`, `position`, `role`, `status_code`) VALUES
(1, 1, 'John', '', 'Admin', 'admin', '$2y$10$nLHvD2dR6dtoFJ69ADm3QOGbUk6kM2snE.D2S33MWLs2ecxzdqU/2', 'MIT Staff', 'Admin', 0),
(2, 1, 'Maria', '', 'Manager', 'manager', '$2y$10$oXEjLBjOo1Gq0zjzZRerZuD1YsJUzo91uSCFzYBP8s4BLSej1Qs0a', 'Laboratory Manager', 'Staff', 0),
(3, 1, 'Ana', '', 'Secretary', 'secretary', '$2y$10$Xwy6zxgSOsoiCvwHbpeZeuYOmxNlUz7Cpe9g56r3hGN0jXf9l8S62', 'Secretary', 'Staff', 0),
(4, 1, 'Pedro', '', 'Staff', 'staff', '$2y$10$ss2qg.d5RzfgdzamPPttS.TWNUbB1xgghTYtpIlseKnK3sMQZkgF6', 'Staff in-charge', 'Staff', 0),
(5, 4, 'Martin Rey', '', 'Tolang', 'mtolang', '$2y$10$HGmqzSnuf/rSxfoQfS5loewYIhWnhV2rWhv3qy3v1aNGZpqPC/WWS', 'MIT Staff', 'Staff', 0);

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
(1, 1, 1, 'Hello', 0, 'pcs', 10);

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
(14, 'PPE');

-- --------------------------------------------------------

--
-- Table structure for table `lab_result`
--

CREATE TABLE `lab_result` (
  `lab_result_id` int(10) NOT NULL,
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
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `patient_id` int(10) NOT NULL,
  `patient_type` enum('walk-in','employee','student') NOT NULL DEFAULT 'walk-in',
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `physician_id` int(10) DEFAULT NULL,
  `status_code` int(10) DEFAULT 1,
  `datetime_added` datetime DEFAULT current_timestamp(),
  `datetime_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patient_id`, `patient_type`, `firstname`, `middlename`, `lastname`, `birthdate`, `gender`, `contact_number`, `address`, `physician_id`, `status_code`, `datetime_added`, `datetime_updated`) VALUES
(1, 'walk-in', 'Mikko', 'of Immaculate', 'Jardenico', '2026-01-23', 'Male', '09272948757', 'Km 7.5, Prk-4B, Matina Pangi, Davao City', 3, 0, '2026-01-23 13:44:20', NULL),
(2, 'walk-in', 'Mikko', '', 'Jardenico', '2003-06-13', 'Male', '09272948757', 'Km 7.5, Orange Groove, Matina Pangi, Davao City', 2, 1, '2026-01-26 09:10:32', NULL),
(3, 'walk-in', 'Mikko', '', 'Jardenico', '2003-06-13', 'Male', '09272948757', 'Km 7.5, Orange Groove, Matina Pangi, Davao City', 2, 1, '2026-01-26 09:14:43', NULL);

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
(4, 'Mikko Jardenico', 'Internal Medicine', '09272948757', 'mjardenico@gmai.com', 0, '2026-01-24 09:08:37');

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
(1, 'Clinical Microsopy'),
(2, 'Clinical Chemistry'),
(3, 'Microbiology'),
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
  `datetime_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_in`
--

INSERT INTO `stock_in` (`stock_in_id`, `item_id`, `quantity`, `datetime_added`) VALUES
(1, 1, 2, '2026-01-23 13:31:36');

-- --------------------------------------------------------

--
-- Table structure for table `stock_out`
--

CREATE TABLE `stock_out` (
  `stock_out_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `quantity` int(5) NOT NULL,
  `remarks` varchar(30) NOT NULL,
  `datetime_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_out`
--

INSERT INTO `stock_out` (`stock_out_id`, `item_id`, `quantity`, `remarks`, `datetime_added`) VALUES
(1, 1, 1, 'I just want to', '2026-01-23 13:32:01');

-- --------------------------------------------------------

--
-- Table structure for table `stock_usage`
--

CREATE TABLE `stock_usage` (
  `stock_usage_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `employee_id` int(10) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `middlename` varchar(20) DEFAULT NULL,
  `lastname` varchar(20) NOT NULL,
  `purpose` varchar(30) NOT NULL,
  `datetime_added` datetime NOT NULL,
  `or_number` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` int(2) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status_code` int(10) DEFAULT 1,
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `firstname`, `middlename`, `lastname`, `course`, `year_level`, `contact_number`, `email`, `status_code`, `datetime_added`) VALUES
('2024-001', 'Jose', 'M', 'Rizal', 'BS Computer Science', 3, '09201234567', NULL, 1, '2026-01-23 13:40:12'),
('2024-002', 'Andres', 'B', 'Bonifacio', 'BS Information Technology', 2, '09211234567', NULL, 1, '2026-01-23 13:40:12'),
('2024-003', 'Emilio', 'A', 'Aguinaldo', 'BS Computer Engineering', 4, '09221234567', NULL, 1, '2026-01-23 13:40:12');

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `test_id` int(10) NOT NULL,
  `section_id` int(10) NOT NULL,
  `label` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test`
--

INSERT INTO `test` (`test_id`, `section_id`, `label`) VALUES
(1, 1, 'Sample');

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
-- Structure for view `v_current_stock`
--
DROP TABLE IF EXISTS `v_current_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_current_stock`  AS SELECT `i`.`item_id` AS `item_id`, `i`.`label` AS `item_name`, `it`.`label` AS `item_type`, coalesce(sum(`si`.`quantity`),0) AS `total_in`, coalesce(sum(`so`.`quantity`),0) AS `total_out`, coalesce(sum(`si`.`quantity`),0) - coalesce(sum(`so`.`quantity`),0) AS `current_stock`, `i`.`reorder_level` AS `reorder_level`, `i`.`unit` AS `unit`, CASE WHEN coalesce(sum(`si`.`quantity`),0) - coalesce(sum(`so`.`quantity`),0) <= 0 THEN 'Out of Stock' WHEN coalesce(sum(`si`.`quantity`),0) - coalesce(sum(`so`.`quantity`),0) <= `i`.`reorder_level` THEN 'Low Stock' ELSE 'In Stock' END AS `stock_status` FROM (((`item` `i` left join `item_type` `it` on(`i`.`item_type_id` = `it`.`item_type_id`)) left join `stock_in` `si` on(`i`.`item_id` = `si`.`item_id`)) left join `stock_out` `so` on(`i`.`item_id` = `so`.`item_id`)) GROUP BY `i`.`item_id` ORDER BY coalesce(sum(`si`.`quantity`),0) - coalesce(sum(`so`.`quantity`),0) ASC ;

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
  ADD KEY `idx_employee_status` (`status_code`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `idx_equipment_status` (`status`);

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
  ADD KEY `verified_by` (`verified_by`);

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
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `physician_id` (`physician_id`);

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
  ADD KEY `idx_stockin_item` (`item_id`);

--
-- Indexes for table `stock_out`
--
ALTER TABLE `stock_out`
  ADD PRIMARY KEY (`stock_out_id`),
  ADD KEY `idx_stockout_item` (`item_id`);

--
-- Indexes for table `stock_usage`
--
ALTER TABLE `stock_usage`
  ADD PRIMARY KEY (`stock_usage_id`),
  ADD KEY `idx_usage_item` (`item_id`),
  ADD KEY `idx_usage_employee` (`employee_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`test_id`),
  ADD KEY `idx_test_section` (`section_id`);

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
  MODIFY `activity_log_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `record_id` int(10) NOT NULL AUTO_INCREMENT;

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
  MODIFY `employee_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fecalysis`
--
ALTER TABLE `fecalysis`
  MODIFY `fecalysis_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `item_type`
--
ALTER TABLE `item_type`
  MODIFY `item_type_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `lab_result`
--
ALTER TABLE `lab_result`
  MODIFY `lab_result_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_test_order`
--
ALTER TABLE `lab_test_order`
  MODIFY `lab_test_order_id` int(10) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `patient_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `physician`
--
ALTER TABLE `physician`
  MODIFY `physician_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `report_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `section_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `stock_in_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_out`
--
ALTER TABLE `stock_out`
  MODIFY `stock_out_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_usage`
--
ALTER TABLE `stock_usage`
  MODIFY `stock_usage_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test`
--
ALTER TABLE `test`
  MODIFY `test_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `transaction_id` int(10) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `fk_employee_section` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_status` FOREIGN KEY (`status_code`) REFERENCES `status_code` (`status_code`) ON UPDATE CASCADE;

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
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `employee` (`employee_id`);

--
-- Constraints for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD CONSTRAINT `fk_stockin_item` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON UPDATE CASCADE;

--
-- Constraints for table `stock_out`
--
ALTER TABLE `stock_out`
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
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `fk_txn_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON UPDATE CASCADE,
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
