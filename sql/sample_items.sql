-- Sample Items Data for Clinical Laboratory Management System
-- Instructions: Run this SQL after setting up the database
-- This will populate the item table with realistic laboratory items

-- Clinical Microscopy Section Items
INSERT INTO `item` (`section_id`, `item_type_id`, `label`, `status_code`, `unit`, `reorder_level`) VALUES
(1, 3, 'Urine Reagent Strip', 1, 'box', 5),
(1, 4, 'Urine Container', 1, 'pcs', 50),
(1, 7, 'Microscope Slide', 1, 'box', 10),
(1, 7, 'Cover Slip', 1, 'box', 10),
(1, 3, 'Fecal Occult Blood', 1, 'kit', 3),
(1, 4, 'Stool Container', 1, 'pcs', 30),
(1, 8, 'Crystal Violet', 1, 'ml', 100),
(1, 3, 'Pregnancy Test Kit', 1, 'box', 5);

-- Clinical Chemistry Section Items  
INSERT INTO `item` (`section_id`, `item_type_id`, `label`, `status_code`, `unit`, `reorder_level`) VALUES
(2, 3, 'Glucose Reagent', 1, 'ml', 200),
(2, 3, 'Cholesterol Kit', 1, 'kit', 5),
(2, 3, 'Triglyceride Kit', 1, 'kit', 5),
(2, 3, 'Creatinine Reagent', 1, 'ml', 150),
(2, 3, 'Uric Acid Reagent', 1, 'ml', 150),
(2, 3, 'SGPT/ALT Kit', 1, 'kit', 3),
(2, 3, 'SGOT/AST Kit', 1, 'kit', 3),
(2, 4, 'Cuvette', 1, 'pcs', 100),
(2, 7, 'Test Tube 10ml', 1, 'pcs', 50),
(2, 7, 'Pipette Tips', 1, 'box', 10),
(2, 1, 'Distilled Water', 1, 'liter', 5);

-- Microbiology Section Items
INSERT INTO `item` (`section_id`, `item_type_id`, `label`, `status_code`, `unit`, `reorder_level`) VALUES
(3, 3, 'Blood Agar', 1, 'plate', 20),
(3, 3, 'MacConkey Agar', 1, 'plate', 20),
(3, 3, 'Chocolate Agar', 1, 'plate', 15),
(3, 3, 'Gram Stain Kit', 1, 'kit', 3),
(3, 4, 'Sterile Swab', 1, 'pcs', 50),
(3, 7, 'Petri Dish', 1, 'pcs', 50),
(3, 9, 'Alcohol 70%', 1, 'liter', 3),
(3, 4, 'Inoculating Loop', 1, 'pcs', 20),
(3, 8, 'Crystal Violet', 1, 'ml', 100),
(3, 8, 'Safranin', 1, 'ml', 100);

-- Hematology Section Items
INSERT INTO `item` (`section_id`, `item_type_id`, `label`, `status_code`, `unit`, `reorder_level`) VALUES
(4, 3, 'CBC Reagent Pack', 1, 'kit', 5),
(4, 3, 'Hemoglobin Reagent', 1, 'ml', 200),
(4, 4, 'EDTA Tube', 1, 'pcs', 100),
(4, 4, 'Capillary Tube', 1, 'box', 10),
(4, 7, 'Blood Smear Slide', 1, 'box', 10),
(4, 8, 'Giemsa Stain', 1, 'ml', 100),
(4, 8, 'Wright Stain', 1, 'ml', 100),
(4, 4, 'Lancet', 1, 'box', 20),
(4, 4, 'Blood Collection', 1, 'pcs', 50),
(4, 1, 'Anticoagulant Sol', 1, 'ml', 100);

-- PPE and General Supplies
INSERT INTO `item` (`section_id`, `item_type_id`, `label`, `status_code`, `unit`, `reorder_level`) VALUES
(1, 9, 'Latex Gloves', 1, 'box', 10),
(2, 9, 'Latex Gloves', 1, 'box', 10),
(3, 9, 'Latex Gloves', 1, 'box', 10),
(4, 9, 'Latex Gloves', 1, 'box', 10),
(1, 9, 'Face Mask', 1, 'box', 15),
(2, 9, 'Face Mask', 1, 'box', 15),
(3, 9, 'Lab Coat', 1, 'pcs', 5),
(4, 9, 'Lab Coat', 1, 'pcs', 5);

-- Additional Common Items
INSERT INTO `item` (`section_id`, `item_type_id`, `label`, `status_code`, `unit`, `reorder_level`) VALUES
(2, 4, 'Syringe 5ml', 1, 'pcs', 100),
(2, 4, 'Syringe 10ml', 1, 'pcs', 100),
(3, 4, 'Cotton Balls', 1, 'pack', 10),
(1, 1, 'Tissue Paper', 1, 'roll', 20),
(2, 1, 'Paper Towel', 1, 'roll', 15),
(3, 9, 'Biohazard Bag', 1, 'roll', 5),
(4, 1, 'Marker Pen', 1, 'pcs', 20);

-- Total: 61 sample items across all sections
-- Status code 1 = Active
-- Sections: 1=Clinical Microscopy, 2=Clinical Chemistry, 3=Microbiology, 4=Hematology
-- Item Types: 1=Supply, 3=Reagent, 4=Consumable, 7=Glassware, 8=Chemical, 9=PPE
