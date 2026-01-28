-- ============================================================
-- CLINICAL LABORATORY MANAGEMENT SYSTEM - ADVANCED MODULES
-- Equipment, Inventory, Calibration, Reports & Certificates
-- ============================================================

-- ============================================================
-- 1. EQUIPMENT MAINTENANCE MANAGEMENT
-- ============================================================

-- Equipment table
CREATE TABLE IF NOT EXISTS `equipment` (
  `equipment_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `section_id` int(10) DEFAULT NULL,
  `status` enum('operational','under_maintenance','decommissioned') DEFAULT 'operational',
  `purchase_date` date DEFAULT NULL,
  `supplier` varchar(200) DEFAULT NULL,
  `remarks` text,
  `is_deleted` tinyint(1) DEFAULT 0,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`equipment_id`),
  KEY `section_id` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Maintenance schedule table
CREATE TABLE IF NOT EXISTS `maintenance_schedule` (
  `schedule_id` int(10) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(10) NOT NULL,
  `frequency` enum('weekly','monthly','quarterly','semi-annual','annual') NOT NULL,
  `next_due_date` date NOT NULL,
  `responsible_employee_id` int(10) DEFAULT NULL,
  `responsible_section_id` int(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`schedule_id`),
  KEY `equipment_id` (`equipment_id`),
  KEY `responsible_employee_id` (`responsible_employee_id`),
  KEY `responsible_section_id` (`responsible_section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Maintenance history table
CREATE TABLE IF NOT EXISTS `maintenance_history` (
  `history_id` int(10) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(10) NOT NULL,
  `maintenance_date` date NOT NULL,
  `performed_by` int(10) DEFAULT NULL,
  `maintenance_type` enum('preventive','corrective','emergency') NOT NULL,
  `notes` text,
  `attachments` varchar(255) DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `equipment_id` (`equipment_id`),
  KEY `performed_by` (`performed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 2. ENHANCED INVENTORY & SUPPLIES MANAGEMENT
-- ============================================================

-- Item types table (if not exists, expand it)
CREATE TABLE IF NOT EXISTS `item_type` (
  `item_type_id` int(10) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  PRIMARY KEY (`item_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Enhanced item table (add reorder level)
ALTER TABLE `item` 
ADD COLUMN IF NOT EXISTS `unit` varchar(20) DEFAULT 'pcs',
ADD COLUMN IF NOT EXISTS `reorder_level` int(10) DEFAULT 10;

-- Stock usage tracking
CREATE TABLE IF NOT EXISTS `stock_usage` (
  `stock_usage_id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `employee_id` int(10) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `purpose` varchar(200) DEFAULT NULL,
  `or_number` int(10) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stock_usage_id`),
  KEY `item_id` (`item_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. CALIBRATION & TESTING MONITORING
-- ============================================================

-- Calibration procedures table
CREATE TABLE IF NOT EXISTS `calibration_procedure` (
  `procedure_id` int(10) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(10) NOT NULL,
  `procedure_name` varchar(200) NOT NULL,
  `standard_reference` varchar(200) DEFAULT NULL,
  `frequency` enum('monthly','quarterly','semi-annual','annual') NOT NULL,
  `next_due_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`procedure_id`),
  KEY `equipment_id` (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Calibration records table
CREATE TABLE IF NOT EXISTS `calibration_record` (
  `record_id` int(10) NOT NULL AUTO_INCREMENT,
  `procedure_id` int(10) NOT NULL,
  `equipment_id` int(10) NOT NULL,
  `calibration_date` date NOT NULL,
  `performed_by` int(10) DEFAULT NULL,
  `result_status` enum('pass','fail','conditional') NOT NULL,
  `notes` text,
  `attachments` varchar(255) DEFAULT NULL,
  `next_calibration_date` date DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`record_id`),
  KEY `procedure_id` (`procedure_id`),
  KEY `equipment_id` (`equipment_id`),
  KEY `performed_by` (`performed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 4. CERTIFICATE MANAGEMENT
-- ============================================================

-- Certificate templates table
CREATE TABLE IF NOT EXISTS `certificate_template` (
  `template_id` int(10) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `template_type` enum('lab_result','calibration','compliance','safety','other') NOT NULL,
  `html_layout` text NOT NULL,
  `version` varchar(20) DEFAULT '1.0',
  `status` enum('active','inactive') DEFAULT 'active',
  `is_deleted` tinyint(1) DEFAULT 0,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  `datetime_modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Certificates table
CREATE TABLE IF NOT EXISTS `certificate` (
  `certificate_id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_number` varchar(50) NOT NULL UNIQUE,
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
  `certificate_data` text COMMENT 'JSON data for certificate fields',
  `pdf_path` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `datetime_added` datetime DEFAULT CURRENT_TIMESTAMP,
  `datetime_modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`certificate_id`),
  KEY `template_id` (`template_id`),
  KEY `patient_id` (`patient_id`),
  KEY `equipment_id` (`equipment_id`),
  KEY `issued_by` (`issued_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Sample Equipment
INSERT INTO `equipment` (`name`, `model`, `serial_no`, `section_id`, `status`, `purchase_date`, `supplier`, `remarks`) VALUES
('Hematology Analyzer', 'HA-2000', 'SN-HA-001', 1, 'operational', '2023-01-15', 'MedTech Supplies Inc.', 'Automated blood cell counter'),
('Chemistry Analyzer', 'CA-5000', 'SN-CA-002', 2, 'operational', '2023-03-20', 'LabEquip Corp.', 'Biochemistry analyzer'),
('Microscope - Advanced', 'MS-Pro-100', 'SN-MS-003', 1, 'operational', '2022-11-10', 'OpticLab Solutions', 'High-resolution microscope'),
('Centrifuge Machine', 'CF-3000', 'SN-CF-004', 1, 'operational', '2023-05-05', 'MedTech Supplies Inc.', 'High-speed centrifuge');

-- Sample Item Types
INSERT INTO `item_type` (`label`) VALUES
('Reagent'),
('Consumable'),
('Glassware'),
('Chemical'),
('PPE');

-- Sample Calibration Procedures
INSERT INTO `calibration_procedure` (`equipment_id`, `procedure_name`, `standard_reference`, `frequency`, `next_due_date`) VALUES
(1, 'Hematology Analyzer Calibration', 'ISO 15189:2012', 'quarterly', '2026-04-01'),
(2, 'Chemistry Analyzer Calibration', 'CLSI C24-A3', 'quarterly', '2026-04-15'),
(3, 'Microscope Optical Calibration', 'ISO 8039:2014', 'semi-annual', '2026-07-01');

-- Sample Maintenance Schedules
INSERT INTO `maintenance_schedule` (`equipment_id`, `frequency`, `next_due_date`, `responsible_section_id`) VALUES
(1, 'monthly', '2026-02-01', 1),
(2, 'monthly', '2026-02-15', 2),
(3, 'quarterly', '2026-04-01', 1),
(4, 'monthly', '2026-02-10', 1);

-- Sample Certificate Templates
INSERT INTO `certificate_template` (`template_name`, `template_type`, `html_layout`, `version`, `status`) VALUES
('Laboratory Test Result Certificate', 'lab_result', '<div style="text-align:center; padding:50px; border:2px solid #333;">
<h1>LABORATORY TEST RESULT CERTIFICATE</h1>
<p>Certificate No: {certificate_number}</p>
<p>Patient: {patient_name}</p>
<p>Test: {test_name}</p>
<p>Result: {result_value}</p>
<p>Date: {issue_date}</p>
<p>Issued by: {issued_by}</p>
</div>', '1.0', 'active'),

('Equipment Calibration Certificate', 'calibration', '<div style="text-align:center; padding:50px; border:2px solid #333;">
<h1>CALIBRATION CERTIFICATE</h1>
<p>Certificate No: {certificate_number}</p>
<p>Equipment: {equipment_name}</p>
<p>Calibration Date: {calibration_date}</p>
<p>Result: {result_status}</p>
<p>Valid Until: {next_calibration_date}</p>
<p>Certified by: {issued_by}</p>
</div>', '1.0', 'active');

-- ============================================================
-- VIEWS FOR REPORTING
-- ============================================================

-- View: Equipment with upcoming maintenance
CREATE OR REPLACE VIEW `v_upcoming_maintenance` AS
SELECT 
    e.equipment_id,
    e.name AS equipment_name,
    e.model,
    e.serial_no,
    s.label AS section_name,
    ms.next_due_date,
    ms.frequency,
    DATEDIFF(ms.next_due_date, CURDATE()) AS days_until_due,
    CASE 
        WHEN ms.next_due_date < CURDATE() THEN 'Overdue'
        WHEN DATEDIFF(ms.next_due_date, CURDATE()) <= 7 THEN 'Due Soon'
        ELSE 'Scheduled'
    END AS status
FROM equipment e
LEFT JOIN maintenance_schedule ms ON e.equipment_id = ms.equipment_id
LEFT JOIN section s ON e.section_id = s.section_id
WHERE ms.is_active = 1 AND e.is_deleted = 0 AND ms.is_deleted = 0
ORDER BY ms.next_due_date ASC;

-- View: Equipment with upcoming calibration
CREATE OR REPLACE VIEW `v_upcoming_calibration` AS
SELECT 
    e.equipment_id,
    e.name AS equipment_name,
    e.model,
    cp.procedure_id,
    cp.procedure_name,
    cp.standard_reference,
    cp.next_due_date,
    cp.frequency,
    DATEDIFF(cp.next_due_date, CURDATE()) AS days_until_due,
    CASE 
        WHEN cp.next_due_date < CURDATE() THEN 'Overdue'
        WHEN DATEDIFF(cp.next_due_date, CURDATE()) <= 7 THEN 'Due Soon'
        ELSE 'Scheduled'
    END AS alert_status
FROM equipment e
LEFT JOIN calibration_procedure cp ON e.equipment_id = cp.equipment_id
WHERE cp.is_active = 1 AND e.is_deleted = 0 AND cp.is_deleted = 0
ORDER BY cp.next_due_date ASC;

-- View: Current stock levels
CREATE OR REPLACE VIEW `v_current_stock` AS
SELECT 
    i.item_id,
    i.label AS item_name,
    i.section_id,
    s.label AS section_name,
    it.label AS item_type,
    COALESCE(SUM(si.quantity), 0) AS total_in,
    COALESCE(SUM(so.quantity), 0) AS total_out,
    COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) AS current_stock,
    i.reorder_level,
    i.unit,
    CASE 
        WHEN (COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0)) <= 0 THEN 'out_of_stock'
        WHEN (COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0)) <= i.reorder_level THEN 'low_stock'
        ELSE 'in_stock'
    END AS stock_status
FROM item i
LEFT JOIN section s ON i.section_id = s.section_id
LEFT JOIN item_type it ON i.item_type_id = it.item_type_id
LEFT JOIN stock_in si ON i.item_id = si.item_id
LEFT JOIN stock_out so ON i.item_id = so.item_id
GROUP BY i.item_id
ORDER BY current_stock ASC;

-- ============================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================

CREATE INDEX idx_maintenance_next_due ON maintenance_schedule(next_due_date);
CREATE INDEX idx_calibration_next_due ON calibration_procedure(next_due_date);
CREATE INDEX idx_certificate_number ON certificate(certificate_number);
CREATE INDEX idx_certificate_status ON certificate(status);
CREATE INDEX idx_equipment_status ON equipment(status);
CREATE INDEX idx_equipment_is_deleted ON equipment(is_deleted);
CREATE INDEX idx_certificate_is_deleted ON certificate(is_deleted);

-- ============================================================
-- ALTER TABLE STATEMENTS FOR EXISTING TABLES
-- Add is_deleted column to existing core tables for soft delete
-- ============================================================

-- Add is_deleted to existing core tables
ALTER TABLE `patient` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `physician` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `employee` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `walk_in` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `item` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `section` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `test` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `transaction` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `lab_result` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `lab_test_order` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `stock_in` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `stock_out` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;
ALTER TABLE `activity_log` ADD COLUMN IF NOT EXISTS `is_deleted` tinyint(1) DEFAULT 0;

-- Add indexes for is_deleted on core tables
CREATE INDEX IF NOT EXISTS idx_patient_is_deleted ON patient(is_deleted);
CREATE INDEX IF NOT EXISTS idx_physician_is_deleted ON physician(is_deleted);
CREATE INDEX IF NOT EXISTS idx_employee_is_deleted ON employee(is_deleted);
CREATE INDEX IF NOT EXISTS idx_item_is_deleted ON item(is_deleted);
CREATE INDEX IF NOT EXISTS idx_lab_result_is_deleted ON lab_result(is_deleted);

-- ============================================================
-- SOFT DELETE GUIDELINES
-- ============================================================
-- 
-- USAGE:
-- Instead of DELETE FROM table WHERE id = X;
-- Use: UPDATE table SET is_deleted = 1 WHERE id = X;
--
-- QUERIES:
-- Always add WHERE is_deleted = 0 to your SELECT queries
-- Example: SELECT * FROM equipment WHERE is_deleted = 0;
--
-- RECOVERY:
-- To restore deleted records: UPDATE table SET is_deleted = 0 WHERE id = X;
--
-- PERMANENT DELETE (use with caution):
-- DELETE FROM table WHERE is_deleted = 1 AND datetime_added < DATE_SUB(NOW(), INTERVAL 1 YEAR);
--
-- BENEFITS:
-- - Prevents accidental data loss
-- - Allows data recovery
-- - Maintains referential integrity
-- - Provides audit trail
-- - Supports compliance requirements
-- ============================================================

-- ============================================================
-- END OF SQL SCRIPT
-- ============================================================
