-- Remove status_code column from item and transaction tables
-- Run this SQL migration in phpMyAdmin

-- Remove status_code from item table
ALTER TABLE `item` 
DROP COLUMN IF EXISTS `status_code`;

-- Remove status_code from transaction table
ALTER TABLE `transaction` 
DROP COLUMN IF EXISTS `status_code`;
