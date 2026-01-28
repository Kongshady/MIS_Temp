-- Table structure for equipment usage/borrowing tracking

CREATE TABLE IF NOT EXISTS `equipment_usage` (
  `usage_id` int(10) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(10) NOT NULL,
  `date_used` date NOT NULL,
  `user_name` varchar(200) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `quantity` int(10) NOT NULL DEFAULT 1,
  `purpose` text NOT NULL,
  `or_number` varchar(50) DEFAULT NULL,
  `status` enum('functional','not_functional') NOT NULL DEFAULT 'functional',
  `remarks` text DEFAULT NULL,
  `datetime_added` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`usage_id`),
  KEY `equipment_id` (`equipment_id`),
  CONSTRAINT `fk_equipment_usage` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Index for faster queries
CREATE INDEX idx_date_used ON equipment_usage(date_used);
CREATE INDEX idx_user_name ON equipment_usage(user_name);
CREATE INDEX idx_status ON equipment_usage(status);
