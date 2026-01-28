-- Updated Patient Management System
-- Run this SQL to update your database structure
-- Date: January 26, 2026

-- Drop the walk_in table
DROP TABLE IF EXISTS walk_in;

-- Modify the patient table to be the primary client/patient table
ALTER TABLE patient
  DROP COLUMN IF EXISTS walk_in_id,
  DROP COLUMN IF EXISTS employee_id,
  DROP COLUMN IF EXISTS student_id,
  MODIFY COLUMN patient_id INT(10) NOT NULL AUTO_INCREMENT,
  ADD COLUMN IF NOT EXISTS patient_type ENUM('Walk-in', 'Student/Faculty') NOT NULL DEFAULT 'Walk-in' AFTER patient_id,
  ADD COLUMN IF NOT EXISTS firstname VARCHAR(100) NOT NULL AFTER patient_type,
  ADD COLUMN IF NOT EXISTS middlename VARCHAR(100) DEFAULT NULL AFTER firstname,
  ADD COLUMN IF NOT EXISTS lastname VARCHAR(100) NOT NULL AFTER middlename,
  ADD COLUMN IF NOT EXISTS birthdate DATE DEFAULT NULL AFTER lastname,
  ADD COLUMN IF NOT EXISTS gender ENUM('Male', 'Female', 'Other') DEFAULT NULL  AFTER birthdate,
  ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) DEFAULT NULL AFTER gender,
  ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL AFTER contact_number,
  ADD COLUMN IF NOT EXISTS physician_id INT(10) DEFAULT NULL AFTER address,
  ADD COLUMN IF NOT EXISTS status_code INT(2) DEFAULT 1 AFTER physician_id,
  ADD COLUMN IF NOT EXISTS datetime_added DATETIME DEFAULT CURRENT_TIMESTAMP AFTER status_code,
  ADD COLUMN IF NOT EXISTS datetime_updated DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER datetime_added;

-- If you have existing patient table with different structure, use this alternative:
-- This will create a clean patient table from scratch (WARNING: This will delete existing data)

/*
DROP TABLE IF EXISTS patient;

CREATE TABLE patient (
  patient_id INT(10) NOT NULL AUTO_INCREMENT,
  patient_type ENUM('Walk-in', 'Student/Faculty') NOT NULL DEFAULT 'Walk-in',
  firstname VARCHAR(100) NOT NULL,
  middlename VARCHAR(100) DEFAULT NULL,
  lastname VARCHAR(100) NOT NULL,
  birthdate DATE DEFAULT NULL,
  gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
  contact_number VARCHAR(20) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  physician_id INT(10) DEFAULT NULL,
  status_code INT(2) DEFAULT 1,
  datetime_added DATETIME DEFAULT CURRENT_TIMESTAMP,
  datetime_updated DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (patient_id),
  KEY physician_id (physician_id),
  FOREIGN KEY (physician_id) REFERENCES physician(physician_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/

-- Update any existing references to walk_in table in other tables
-- Check if you have any foreign keys or references to walk_in table and update them accordingly

-- Example: If lab_result or transaction tables reference walk_in_id, you might need to update them
-- ALTER TABLE lab_result DROP COLUMN IF EXISTS walk_in_id;
-- ALTER TABLE transaction DROP COLUMN IF EXISTS walk_in_id;

-- Ensure status_code table has Active and Inactive
INSERT IGNORE INTO status_code (status_code, label) VALUES 
(1, 'Active'),
(2, 'Inactive');
