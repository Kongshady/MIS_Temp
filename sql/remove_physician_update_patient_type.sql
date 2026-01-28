-- Remove physician_id from patient table and update patient_type values
-- Run this SQL migration in phpMyAdmin

-- First, remove the foreign key constraint if it exists
ALTER TABLE `patient` 
DROP FOREIGN KEY IF EXISTS `fk_patient_physician`;

-- Remove the physician_id column
ALTER TABLE `patient` 
DROP COLUMN IF EXISTS `physician_id`;

-- Update existing patient_type values
UPDATE `patient` 
SET `patient_type` = 'Internal' 
WHERE `patient_type` = 'Student/Faculty';

UPDATE `patient` 
SET `patient_type` = 'External' 
WHERE `patient_type` = 'Walk-in';
