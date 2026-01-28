-- Fix transaction table to reference patient table instead of client table
-- Run this SQL migration in phpMyAdmin

-- First, drop the existing foreign key constraint
ALTER TABLE `transaction` 
DROP FOREIGN KEY IF EXISTS `fk_txn_client`;

-- Add new foreign key constraint referencing patient table
ALTER TABLE `transaction` 
ADD CONSTRAINT `fk_txn_patient` 
FOREIGN KEY (`client_id`) 
REFERENCES `patient` (`patient_id`) 
ON UPDATE CASCADE 
ON DELETE RESTRICT;
