-- ============================================================================
-- Add Soft Delete Functionality - Clinical Laboratory Management System
-- ============================================================================
-- This script adds status_code columns to tables that need soft delete
-- and creates indexes for better query performance
-- ============================================================================

USE clinlab;

-- ============================================================================
-- Add status_code column to tables that don't have it
-- ============================================================================

-- Section table (master data)
ALTER TABLE `section` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Test table (master data)
ALTER TABLE `test` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Item Type table (master data)
ALTER TABLE `item_type` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Client table
ALTER TABLE `client` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Client Type table
ALTER TABLE `client_type` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Stock In records
ALTER TABLE `stock_in` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted/Cancelled';

-- Stock Out records
ALTER TABLE `stock_out` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted/Cancelled';

-- Stock Usage records
ALTER TABLE `stock_usage` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted/Cancelled';

-- Transaction Detail
ALTER TABLE `transaction_detail` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Report
ALTER TABLE `report` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Calibration Procedure
ALTER TABLE `calibration_procedure` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Calibration Record
ALTER TABLE `calibration_record` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Maintenance Schedule
ALTER TABLE `maintenance_schedule` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted/Cancelled';

-- Maintenance History
ALTER TABLE `maintenance_history` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Blood Chemistry (if it stores test results)
ALTER TABLE `blood_chemistry` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Fecalysis (if it stores test results)
ALTER TABLE `fecalysis` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- Permissions table
ALTER TABLE `permissions` 
ADD COLUMN `status_code` INT(10) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted';

-- ============================================================================
-- Add indexes for better query performance
-- ============================================================================

ALTER TABLE `section` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `test` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `item_type` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `client` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `client_type` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `stock_in` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `stock_out` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `stock_usage` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `transaction_detail` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `report` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `calibration_procedure` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `calibration_record` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `maintenance_schedule` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `maintenance_history` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `blood_chemistry` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `fecalysis` ADD INDEX `idx_status_code` (`status_code`);
ALTER TABLE `permissions` ADD INDEX `idx_status_code` (`status_code`);

-- ============================================================================
-- Add foreign key constraints to status_code table (optional)
-- ============================================================================

-- Note: Only add FK constraints if your status_code table structure supports it
-- Uncomment these if needed:

-- ALTER TABLE `section` ADD CONSTRAINT `fk_section_status` 
--     FOREIGN KEY (`status_code`) REFERENCES `status_code`(`status_code`);
-- 
-- ALTER TABLE `test` ADD CONSTRAINT `fk_test_status` 
--     FOREIGN KEY (`status_code`) REFERENCES `status_code`(`status_code`);
-- 
-- ALTER TABLE `item_type` ADD CONSTRAINT `fk_item_type_status` 
--     FOREIGN KEY (`status_code`) REFERENCES `status_code`(`status_code`);

-- ============================================================================
-- Add datetime_deleted column for audit trail (optional enhancement)
-- ============================================================================

ALTER TABLE `section` ADD COLUMN `datetime_deleted` DATETIME NULL;
ALTER TABLE `test` ADD COLUMN `datetime_deleted` DATETIME NULL;
ALTER TABLE `item_type` ADD COLUMN `datetime_deleted` DATETIME NULL;
ALTER TABLE `client` ADD COLUMN `datetime_deleted` DATETIME NULL;
ALTER TABLE `stock_in` ADD COLUMN `datetime_deleted` DATETIME NULL;
ALTER TABLE `stock_out` ADD COLUMN `datetime_deleted` DATETIME NULL;
ALTER TABLE `transaction_detail` ADD COLUMN `datetime_deleted` DATETIME NULL;
ALTER TABLE `calibration_record` ADD COLUMN `datetime_deleted` DATETIME NULL;
ALTER TABLE `maintenance_history` ADD COLUMN `datetime_deleted` DATETIME NULL;

-- ============================================================================
-- Verify changes
-- ============================================================================

SELECT 'Soft delete columns added successfully!' as Status;

-- Show all tables with status_code column
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'clinlab' 
  AND COLUMN_NAME = 'status_code' 
ORDER BY TABLE_NAME;

-- ============================================================================
-- USAGE EXAMPLES
-- ============================================================================
-- 
-- Soft delete a record:
-- UPDATE section SET status_code = 0, datetime_deleted = NOW() WHERE section_id = 1;
--
-- Restore a deleted record:
-- UPDATE section SET status_code = 1, datetime_deleted = NULL WHERE section_id = 1;
--
-- Query only active records:
-- SELECT * FROM section WHERE status_code = 1;
--
-- Query deleted records:
-- SELECT * FROM section WHERE status_code = 0;
--
-- ============================================================================
