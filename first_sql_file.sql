-- Clinical Laboratory Management System Database
-- Additional Tables for Patient and Physician Management

-- Table for Physicians
CREATE TABLE IF NOT EXISTS `physician` (
  `physician_id` int(10) NOT NULL AUTO_INCREMENT,
  `physician_name` varchar(100) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status_code` int(10) DEFAULT 1,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`physician_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Patients (linked to walk_in or can be employee/student)
CREATE TABLE IF NOT EXISTS `patient` (
  `patient_id` int(10) NOT NULL AUTO_INCREMENT,
  `patient_type` enum('walk-in','employee','student') NOT NULL DEFAULT 'walk-in',
  `walk_in_id` int(10) DEFAULT NULL,
  `employee_id` int(10) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `physician_id` int(10) DEFAULT NULL,
  `status_code` int(10) DEFAULT 1,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`patient_id`),
  KEY `walk_in_id` (`walk_in_id`),
  KEY `employee_id` (`employee_id`),
  KEY `physician_id` (`physician_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Laboratory Test Orders
CREATE TABLE IF NOT EXISTS `lab_test_order` (
  `lab_test_order_id` int(10) NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) NOT NULL,
  `physician_id` int(10) DEFAULT NULL,
  `test_id` int(10) NOT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `remarks` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`lab_test_order_id`),
  KEY `patient_id` (`patient_id`),
  KEY `physician_id` (`physician_id`),
  KEY `test_id` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Laboratory Results
CREATE TABLE IF NOT EXISTS `lab_result` (
  `lab_result_id` int(10) NOT NULL AUTO_INCREMENT,
  `lab_test_order_id` int(10) NOT NULL,
  `patient_id` int(10) NOT NULL,
  `test_id` int(10) NOT NULL,
  `result_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `findings` text,
  `normal_range` varchar(100) DEFAULT NULL,
  `result_value` varchar(100) DEFAULT NULL,
  `remarks` text,
  `performed_by` int(10) DEFAULT NULL,
  `verified_by` int(10) DEFAULT NULL,
  `status` enum('draft','final','revised') DEFAULT 'draft',
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  `datetime_modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lab_result_id`),
  KEY `lab_test_order_id` (`lab_test_order_id`),
  KEY `patient_id` (`patient_id`),
  KEY `test_id` (`test_id`),
  KEY `performed_by` (`performed_by`),
  KEY `verified_by` (`verified_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Student (if not exists)
CREATE TABLE IF NOT EXISTS `student` (
  `student_id` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` int(2) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status_code` int(10) DEFAULT 1,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for physicians
INSERT INTO `physician` (`physician_name`, `specialization`, `contact_number`, `email`, `status_code`) VALUES
('Dr. Juan Dela Cruz', 'General Practitioner', '09171234567', 'jdelacruz@clinic.com', 1),
('Dr. Maria Santos', 'Pathologist', '09181234567', 'msantos@clinic.com', 1),
('Dr. Pedro Garcia', 'Internal Medicine', '09191234567', 'pgarcia@clinic.com', 1);

-- Sample data for students (optional)
INSERT INTO `student` (`student_id`, `firstname`, `middlename`, `lastname`, `course`, `year_level`, `contact_number`) VALUES
('2024-001', 'Jose', 'M', 'Rizal', 'BS Computer Science', 3, '09201234567'),
('2024-002', 'Andres', 'B', 'Bonifacio', 'BS Information Technology', 2, '09211234567'),
('2024-003', 'Emilio', 'A', 'Aguinaldo', 'BS Computer Engineering', 4, '09221234567');
