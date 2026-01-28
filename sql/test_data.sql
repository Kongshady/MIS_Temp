-- ============================================================================
-- Test Table Data - Clinical Laboratory Management System
-- ============================================================================
-- This file contains INSERT statements for common laboratory tests
-- Organize by section: Clinical Microscopy, Clinical Chemistry, Microbiology
-- ============================================================================

USE clinlab;

-- Clear existing test data (optional - comment out if you want to keep existing tests)
-- TRUNCATE TABLE test;

-- ============================================================================
-- Section 1: Clinical Microscopy Tests
-- ============================================================================
INSERT INTO `test` (`section_id`, `label`) VALUES
(1, 'Urinalysis'),
(1, 'Fecalysis'),
(1, 'Pregnancy Test'),
(1, 'Occult Blood Test'),
(1, 'Stool Exam');

-- ============================================================================
-- Section 2: Clinical Chemistry Tests
-- ============================================================================
INSERT INTO `test` (`section_id`, `label`) VALUES
(2, 'FBS'),
(2, 'RBS'),
(2, 'Cholesterol'),
(2, 'Triglycerides'),
(2, 'HDL'),
(2, 'LDL'),
(2, 'SGPT'),
(2, 'SGOT'),
(2, 'Creatinine'),
(2, 'BUN'),
(2, 'Uric Acid'),
(2, 'HbA1c'),
(2, 'Total Protein'),
(2, 'Albumin'),
(2, 'Bilirubin Total'),
(2, 'Bilirubin Direct');

-- ============================================================================
-- Section 3: Microbiology Tests
-- ============================================================================
INSERT INTO `test` (`section_id`, `label`) VALUES
(3, 'Gram Stain'),
(3, 'AFB Stain'),
(3, 'Culture & Sensitivity'),
(3, 'Blood Culture'),
(3, 'Urine Culture');

-- ============================================================================
-- Additional Tests (Add more as needed)
-- ============================================================================
-- INSERT INTO `test` (`section_id`, `label`) VALUES
-- (1, 'Your Test Name'),
-- (2, 'Another Test'),
-- (3, 'Custom Test');

-- ============================================================================
-- Verify inserted data
-- ============================================================================
SELECT 'Test data inserted successfully!' as Status;
SELECT s.label as Section, COUNT(t.test_id) as TestCount 
FROM section s 
LEFT JOIN test t ON s.section_id = t.section_id 
GROUP BY s.section_id, s.label
ORDER BY s.section_id;
