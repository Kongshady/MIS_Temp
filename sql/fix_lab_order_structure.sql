-- Proper Lab Order Structure: One Order → Many Tests → Individual Results

-- Step 1: Create order_tests junction table
CREATE TABLE IF NOT EXISTS `order_tests` (
  `order_test_id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NOT NULL,
  `test_id` int(10) NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `assigned_to` int(10) DEFAULT NULL COMMENT 'Employee/technician assigned',
  `datetime_added` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`order_test_id`),
  KEY `order_id` (`order_id`),
  KEY `test_id` (`test_id`),
  CONSTRAINT `fk_order_tests_order` FOREIGN KEY (`order_id`) REFERENCES `lab_test_order` (`lab_test_order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_tests_test` FOREIGN KEY (`test_id`) REFERENCES `test` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 2: Update lab_result to reference order_tests instead
ALTER TABLE `lab_result` 
  ADD COLUMN IF NOT EXISTS `order_test_id` int(10) DEFAULT NULL AFTER `lab_result_id`,
  ADD KEY IF NOT EXISTS `order_test_id` (`order_test_id`);

-- Step 3: Add computed status to lab_test_order
-- This will be 'pending' if all tests pending, 'completed' if all done, 'partial' if mixed

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_order_tests_status ON order_tests(status);
CREATE INDEX IF NOT EXISTS idx_order_tests_order ON order_tests(order_id);
