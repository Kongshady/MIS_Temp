-- Fix: Make section_id nullable in employee table
-- This allows creating employees without assigning a section initially
-- Section can be assigned later through the edit form

ALTER TABLE `employee` 
MODIFY `section_id` int(10) DEFAULT NULL;
