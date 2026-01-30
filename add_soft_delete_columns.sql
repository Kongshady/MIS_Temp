-- =====================================================
-- ADD SOFT DELETE SUPPORT TO CRITICAL TABLES
-- Migration Date: January 30, 2026
-- Purpose: Add is_deleted and deleted_at columns for soft delete functionality
--          to preserve audit trails and prevent breaking foreign key relationships
-- =====================================================

-- EMPLOYEE TABLE
-- Prevents breaking: activity_log, stock_in, stock_out, stock_usage, reports, lab results
ALTER TABLE `employee` 
ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Soft delete flag: 0=active, 1=deleted',
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp when record was soft deleted',
ADD COLUMN `deleted_by` INT(10) NULL DEFAULT NULL COMMENT 'Employee ID who performed the deletion';

-- PATIENT TABLE
-- Prevents breaking: transaction, lab_test_order, lab_result, certificate history
ALTER TABLE `patient` 
ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Soft delete flag: 0=active, 1=deleted',
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp when record was soft deleted',
ADD COLUMN `deleted_by` INT(10) NULL DEFAULT NULL COMMENT 'Employee ID who performed the deletion';

-- PHYSICIAN TABLE
-- Prevents breaking: past lab results and orders that reference physicians
ALTER TABLE `physician` 
ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Soft delete flag: 0=active, 1=deleted',
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp when record was soft deleted',
ADD COLUMN `deleted_by` INT(10) NULL DEFAULT NULL COMMENT 'Employee ID who performed the deletion';

-- SECTION TABLE
-- Prevents breaking: test groupings, item categorization, equipment assignments
ALTER TABLE `section` 
ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Soft delete flag: 0=active, 1=deleted',
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp when record was soft deleted',
ADD COLUMN `deleted_by` INT(10) NULL DEFAULT NULL COMMENT 'Employee ID who performed the deletion';

-- TEST TABLE
-- Prevents breaking: transaction_detail, lab_test_order, historical test results
ALTER TABLE `test` 
ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Soft delete flag: 0=active, 1=deleted',
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp when record was soft deleted',
ADD COLUMN `deleted_by` INT(10) NULL DEFAULT NULL COMMENT 'Employee ID who performed the deletion';

-- ITEM TABLE
-- Prevents breaking: stock_in, stock_out, stock_usage, inventory movement history
ALTER TABLE `item` 
ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Soft delete flag: 0=active, 1=deleted',
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp when record was soft deleted',
ADD COLUMN `deleted_by` INT(10) NULL DEFAULT NULL COMMENT 'Employee ID who performed the deletion';

-- EQUIPMENT TABLE
-- Prevents breaking: equipment_usage, maintenance_schedule, maintenance_history, calibration_record, certificate
ALTER TABLE `equipment` 
ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Soft delete flag: 0=active, 1=deleted',
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp when record was soft deleted',
ADD COLUMN `deleted_by` INT(10) NULL DEFAULT NULL COMMENT 'Employee ID who performed the deletion';

-- CERTIFICATE_TEMPLATE TABLE
-- Prevents breaking: certificates that were generated using older template versions
ALTER TABLE `certificate_template` 
ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Soft delete flag: 0=active, 1=deleted',
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp when record was soft deleted',
ADD COLUMN `deleted_by` INT(10) NULL DEFAULT NULL COMMENT 'Employee ID who performed the deletion';

-- =====================================================
-- CREATE INDEXES FOR PERFORMANCE
-- These indexes will speed up queries that filter by is_deleted
-- =====================================================

CREATE INDEX idx_employee_is_deleted ON employee(is_deleted);
CREATE INDEX idx_patient_is_deleted ON patient(is_deleted);
CREATE INDEX idx_physician_is_deleted ON physician(is_deleted);
CREATE INDEX idx_section_is_deleted ON section(is_deleted);
CREATE INDEX idx_test_is_deleted ON test(is_deleted);
CREATE INDEX idx_item_is_deleted ON item(is_deleted);
CREATE INDEX idx_equipment_is_deleted ON equipment(is_deleted);
CREATE INDEX idx_certificate_template_is_deleted ON certificate_template(is_deleted);

-- =====================================================
-- NOTES FOR IMPLEMENTATION:
-- =====================================================
-- After running this migration, update your PHP code to:
-- 
-- 1. ADD "AND is_deleted = 0" to all SELECT queries for these tables
--    Example: SELECT * FROM employee WHERE status_code = 1 AND is_deleted = 0
--
-- 2. CHANGE DELETE operations to UPDATE operations:
--    Instead of: DELETE FROM employee WHERE employee_id = ?
--    Use:        UPDATE employee SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE employee_id = ?
--
-- 3. ADD "Restore" functionality in admin panels if needed:
--    UPDATE employee SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE employee_id = ?
--
-- 4. CREATE views for active records only (recommended):
--    CREATE VIEW v_active_employees AS SELECT * FROM employee WHERE is_deleted = 0;
--    CREATE VIEW v_active_patients AS SELECT * FROM patient WHERE is_deleted = 0;
--    CREATE VIEW v_active_physicians AS SELECT * FROM physician WHERE is_deleted = 0;
--    CREATE VIEW v_active_sections AS SELECT * FROM section WHERE is_deleted = 0;
--    CREATE VIEW v_active_tests AS SELECT * FROM test WHERE is_deleted = 0;
--    CREATE VIEW v_active_items AS SELECT * FROM item WHERE is_deleted = 0;
--    CREATE VIEW v_active_equipment AS SELECT * FROM equipment WHERE is_deleted = 0;
--    CREATE VIEW v_active_certificate_templates AS SELECT * FROM certificate_template WHERE is_deleted = 0;
--
-- 5. AUDIT TRAIL: The deleted_at and deleted_by columns will help you track:
--    - When was the record deleted
--    - Who deleted it
--    This is crucial for compliance and troubleshooting
-- =====================================================

-- Verification queries to check the migration was successful:
-- SELECT COUNT(*) FROM employee WHERE is_deleted = 0; -- Should show all active employees
-- SELECT COUNT(*) FROM patient WHERE is_deleted = 0;   -- Should show all active patients
-- SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'clinlab' AND COLUMN_NAME = 'is_deleted';
