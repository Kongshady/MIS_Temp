-- Add price column to test table
ALTER TABLE `test` 
ADD COLUMN `current_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `label`;

-- Create test_price_history table to track price changes
CREATE TABLE IF NOT EXISTS `test_price_history` (
  `price_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(10) NOT NULL,
  `previous_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `new_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`price_history_id`),
  KEY `test_id` (`test_id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `fk_price_history_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`test_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_price_history_employee` FOREIGN KEY (`updated_by`) REFERENCES `employee` (`employee_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
