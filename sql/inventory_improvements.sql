-- Inventory System Improvements
-- Add performed_by, supplier, and reference to stock_in and stock_out tables

-- Update stock_in table
ALTER TABLE `stock_in` 
ADD COLUMN `performed_by` INT(10) NULL AFTER `quantity`,
ADD COLUMN `supplier` VARCHAR(100) NULL AFTER `performed_by`,
ADD COLUMN `reference_number` VARCHAR(50) NULL COMMENT 'Purchase/Invoice/DR number' AFTER `supplier`,
ADD COLUMN `expiry_date` DATE NULL AFTER `reference_number`,
ADD COLUMN `remarks` TEXT NULL AFTER `expiry_date`;

-- Add foreign key for performed_by
ALTER TABLE `stock_in`
ADD CONSTRAINT `fk_stock_in_employee` FOREIGN KEY (`performed_by`) REFERENCES `employee` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Update stock_out table
ALTER TABLE `stock_out` 
ADD COLUMN `performed_by` INT(10) NULL AFTER `quantity`,
ADD COLUMN `reference_number` VARCHAR(50) NULL COMMENT 'Requisition/Request number' AFTER `performed_by`,
MODIFY COLUMN `remarks` TEXT NULL;

-- Add foreign key for performed_by
ALTER TABLE `stock_out`
ADD CONSTRAINT `fk_stock_out_employee` FOREIGN KEY (`performed_by`) REFERENCES `employee` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Add quantity to stock_usage (currently missing)
ALTER TABLE `stock_usage`
ADD COLUMN `quantity` INT(5) NOT NULL DEFAULT 1 AFTER `item_id`;

-- Create view for stock with expiry tracking
CREATE OR REPLACE VIEW `v_stock_expiry` AS
SELECT 
    si.stock_in_id,
    si.item_id,
    i.label as item_name,
    s.label as section_name,
    si.quantity,
    si.expiry_date,
    si.supplier,
    si.reference_number,
    si.datetime_added,
    DATEDIFF(si.expiry_date, CURDATE()) as days_until_expiry,
    CASE 
        WHEN si.expiry_date IS NULL THEN 'no_expiry'
        WHEN si.expiry_date < CURDATE() THEN 'expired'
        WHEN DATEDIFF(si.expiry_date, CURDATE()) <= 30 THEN 'expiring_soon'
        ELSE 'valid'
    END as expiry_status
FROM stock_in si
JOIN item i ON si.item_id = i.item_id
LEFT JOIN section s ON i.section_id = s.section_id
WHERE si.expiry_date IS NOT NULL
ORDER BY si.expiry_date ASC;

-- Create view for comprehensive stock movements with employee info
CREATE OR REPLACE VIEW `v_stock_movements` AS
SELECT 
    'IN' as movement_type,
    si.stock_in_id as movement_id,
    si.item_id,
    i.label as item_name,
    s.label as section_name,
    si.quantity,
    si.performed_by,
    CONCAT_WS(' ', e.firstname, e.lastname) as performed_by_name,
    si.supplier,
    si.reference_number,
    si.remarks,
    si.datetime_added,
    si.expiry_date
FROM stock_in si
JOIN item i ON si.item_id = i.item_id
LEFT JOIN section s ON i.section_id = s.section_id
LEFT JOIN employee e ON si.performed_by = e.employee_id

UNION ALL

SELECT 
    'OUT' as movement_type,
    so.stock_out_id as movement_id,
    so.item_id,
    i.label as item_name,
    s.label as section_name,
    so.quantity,
    so.performed_by,
    CONCAT_WS(' ', e.firstname, e.lastname) as performed_by_name,
    NULL as supplier,
    so.reference_number,
    so.remarks,
    so.datetime_added,
    NULL as expiry_date
FROM stock_out so
JOIN item i ON so.item_id = i.item_id
LEFT JOIN section s ON i.section_id = s.section_id
LEFT JOIN employee e ON so.performed_by = e.employee_id

UNION ALL

SELECT 
    'USAGE' as movement_type,
    su.stock_usage_id as movement_id,
    su.item_id,
    i.label as item_name,
    s.label as section_name,
    su.quantity,
    su.employee_id as performed_by,
    CONCAT_WS(' ', su.firstname, su.lastname) as performed_by_name,
    NULL as supplier,
    su.or_number as reference_number,
    su.purpose as remarks,
    su.datetime_added,
    NULL as expiry_date
FROM stock_usage su
JOIN item i ON su.item_id = i.item_id
LEFT JOIN section s ON i.section_id = s.section_id

ORDER BY datetime_added DESC;

-- Add indexes for better performance
ALTER TABLE `stock_in` ADD INDEX `idx_stock_in_date` (`datetime_added`);
ALTER TABLE `stock_in` ADD INDEX `idx_stock_in_expiry` (`expiry_date`);
ALTER TABLE `stock_out` ADD INDEX `idx_stock_out_date` (`datetime_added`);
ALTER TABLE `stock_usage` ADD INDEX `idx_stock_usage_date` (`datetime_added`);
