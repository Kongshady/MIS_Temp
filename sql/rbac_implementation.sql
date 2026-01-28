-- ============================================================================
-- RBAC (Role-Based Access Control) Implementation
-- Clinical Laboratory Management System
-- ============================================================================

-- Step 1: Create RBAC Tables
-- ============================================================================

-- Roles Table
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT(10) NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(50) NOT NULL UNIQUE,
  `display_name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `status_code` INT(10) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Permissions Table
CREATE TABLE IF NOT EXISTS `permissions` (
  `permission_id` INT(10) NOT NULL AUTO_INCREMENT,
  `permission_key` VARCHAR(100) NOT NULL UNIQUE,
  `module` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`),
  INDEX `idx_module` (`module`),
  INDEX `idx_permission_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Role-Permissions Mapping Table
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_permission_id` INT(10) NOT NULL AUTO_INCREMENT,
  `role_id` INT(10) NOT NULL,
  `permission_id` INT(10) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_permission_id`),
  UNIQUE KEY `unique_role_permission` (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`permission_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 2: Alter Employee Table to Add role_id
-- ============================================================================

ALTER TABLE `employee` 
ADD COLUMN IF NOT EXISTS `role_id` INT(10) DEFAULT NULL AFTER `position`,
ADD COLUMN IF NOT EXISTS `password_hash` VARCHAR(255) DEFAULT NULL AFTER `role_id`,
ADD INDEX `idx_role_id` (`role_id`);

-- Add foreign key constraint (may fail if data doesn't exist yet - run after seeding roles)
-- ALTER TABLE `employee` ADD CONSTRAINT `fk_employee_role` 
-- FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE SET NULL;

-- Step 3: Seed Roles
-- ============================================================================

INSERT INTO `roles` (`role_name`, `display_name`, `description`, `status_code`) VALUES
('MIT_STAFF', 'MIT Staff', 'System administrator for accounts and master data management', 1),
('LAB_MANAGER', 'Laboratory Manager', 'Head of clinical laboratory with full operational access', 1),
('STAFF_IN_CHARGE', 'Staff-in-Charge', 'Laboratory staff with operational duties', 1),
('SECRETARY', 'Secretary', 'Administrative staff handling transactions and inventory', 1),
('OPTIONAL_VIEWER', 'Auditor/Compliance Viewer', 'View-only access for auditing and compliance', 1);

-- Step 4: Seed Permissions
-- ============================================================================

-- Patient Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('patients.view', 'patients', 'view', 'View patient list and profiles'),
('patients.add_internal', 'patients', 'add_internal', 'Add internal patients (employees/students)'),
('patients.add_walkin', 'patients', 'add_walkin', 'Add walk-in patients'),
('patients.update', 'patients', 'update', 'Update patient information'),
('patients.delete', 'patients', 'delete', 'Delete patient records');

-- Physician Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('physicians.view', 'physicians', 'view', 'View physician list'),
('physicians.manage', 'physicians', 'manage', 'Add, edit, delete physicians');

-- Lab Results Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('results.view', 'results', 'view', 'View laboratory results'),
('results.create', 'results', 'create', 'Create/add laboratory results'),
('results.update', 'results', 'update', 'Update laboratory results'),
('results.delete', 'results', 'delete', 'Delete laboratory results');

-- Transaction Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('transactions.view', 'transactions', 'view', 'View transaction list'),
('transactions.create', 'transactions', 'create', 'Create new transactions'),
('transactions.update', 'transactions', 'update', 'Update transaction information'),
('transactions.delete', 'transactions', 'delete', 'Delete transactions');

-- Item Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('items.view', 'items', 'view', 'View item master list'),
('items.create', 'items', 'create', 'Add new items'),
('items.update', 'items', 'update', 'Update item information'),
('items.delete', 'items', 'delete', 'Delete items'),
('items.usage', 'items', 'usage', 'Record item usage');

-- Inventory & Supplies
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('inventory.view', 'inventory', 'view', 'View inventory levels and stock'),
('inventory.stock_in', 'inventory', 'stock_in', 'Record stock in/receive supplies'),
('inventory.stock_out', 'inventory', 'stock_out', 'Record stock out/issue supplies'),
('inventory.usage', 'inventory', 'usage', 'Record inventory usage');

-- Equipment Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('equipment.view', 'equipment', 'view', 'View equipment list'),
('equipment.manage', 'equipment', 'manage', 'Add, edit, delete equipment'),
('maintenance.manage', 'maintenance', 'manage', 'Manage equipment maintenance schedules');

-- Calibration Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('calibration.view', 'calibration', 'view', 'View calibration schedules and records'),
('calibration.manage', 'calibration', 'manage', 'Manage calibration procedures and records');

-- Certificate Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('certificates.view', 'certificates', 'view', 'View generated certificates'),
('certificates.generate', 'certificates', 'generate', 'Generate new certificates'),
('certificates.verify', 'certificates', 'verify', 'Verify certificate authenticity');

-- Reports
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('reports.view', 'reports', 'view', 'View available reports'),
('reports.generate', 'reports', 'generate', 'Generate and export reports');

-- Activity Logs
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('logs.view', 'logs', 'view', 'View user activity logs and audit trails');

-- User/Employee Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('users.view', 'users', 'view', 'View user/employee list'),
('users.manage', 'users', 'manage', 'Add, edit, delete users and manage accounts');

-- Test Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('tests.view', 'tests', 'view', 'View test list'),
('tests.manage', 'tests', 'manage', 'Add, edit, delete test types');

-- Section Management
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('sections.view', 'sections', 'view', 'View section list'),
('sections.manage', 'sections', 'manage', 'Add, edit, delete laboratory sections');

-- Step 5: Map Roles to Permissions
-- ============================================================================

-- MIT_STAFF Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'MIT_STAFF'),
    permission_id
FROM permissions
WHERE permission_key IN (
    'users.view',
    'users.manage',
    'tests.view',
    'tests.manage',
    'sections.view',
    'sections.manage'
);

-- LAB_MANAGER Permissions (Head of Clinical Laboratory - Full Access)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER'),
    permission_id
FROM permissions
WHERE permission_key IN (
    -- Patients
    'patients.view',
    'patients.add_internal',
    'patients.add_walkin',
    'patients.update',
    'patients.delete',
    -- Physicians
    'physicians.view',
    'physicians.manage',
    -- Results
    'results.view',
    'results.create',
    'results.update',
    'results.delete',
    -- Transactions
    'transactions.view',
    'transactions.create',
    'transactions.update',
    'transactions.delete',
    -- Items
    'items.view',
    'items.create',
    'items.update',
    'items.delete',
    'items.usage',
    -- Inventory
    'inventory.view',
    'inventory.stock_in',
    'inventory.stock_out',
    'inventory.usage',
    -- Equipment
    'equipment.view',
    'equipment.manage',
    'maintenance.manage',
    -- Calibration
    'calibration.view',
    'calibration.manage',
    -- Certificates
    'certificates.view',
    'certificates.generate',
    'certificates.verify',
    -- Reports
    'reports.view',
    'reports.generate',
    -- Logs (ONLY LAB_MANAGER has this)
    'logs.view'
);

-- STAFF_IN_CHARGE Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'STAFF_IN_CHARGE'),
    permission_id
FROM permissions
WHERE permission_key IN (
    -- Patients
    'patients.view',
    'patients.add_internal',
    'patients.update',
    -- Physicians
    'physicians.view',
    'physicians.manage',
    -- Results
    'results.view',
    'results.create',
    'results.update',
    -- Transactions
    'transactions.view',
    'transactions.create',
    'transactions.update',
    -- Items
    'items.view',
    'items.create',
    'items.update',
    'items.usage',
    -- Inventory
    'inventory.view',
    'inventory.usage',
    -- Equipment
    'equipment.view',
    -- Calibration
    'calibration.view',
    -- Reports
    'reports.view',
    'reports.generate'
);

-- SECRETARY Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'SECRETARY'),
    permission_id
FROM permissions
WHERE permission_key IN (
    -- Transactions
    'transactions.view',
    'transactions.create',
    'transactions.update',
    -- Inventory
    'inventory.view',
    'inventory.stock_in',
    'inventory.stock_out',
    'inventory.usage'
);

-- OPTIONAL_VIEWER Permissions (Auditor/Compliance)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'OPTIONAL_VIEWER'),
    permission_id
FROM permissions
WHERE permission_key IN (
    'reports.view',
    'certificates.view',
    'certificates.verify'
);

-- Step 6: Migrate Existing Users (Set Default Role)
-- ============================================================================

-- Option 1: Set all existing employees to STAFF_IN_CHARGE by default
UPDATE `employee` 
SET role_id = (SELECT role_id FROM roles WHERE role_name = 'STAFF_IN_CHARGE')
WHERE role_id IS NULL;

-- Option 2: Set specific users manually (RECOMMENDED - adjust employee_id as needed)
-- UPDATE `employee` SET role_id = (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER') WHERE employee_id = 1;
-- UPDATE `employee` SET role_id = (SELECT role_id FROM roles WHERE role_name = 'MIT_STAFF') WHERE employee_id = 2;
-- UPDATE `employee` SET role_id = (SELECT role_id FROM roles WHERE role_name = 'SECRETARY') WHERE position = 'Secretary';

-- Step 7: Add Foreign Key Constraint (Run after all data is populated)
-- ============================================================================

ALTER TABLE `employee` 
ADD CONSTRAINT `fk_employee_role` 
FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE SET NULL;

-- Step 8: Create Helpful Views
-- ============================================================================

-- View: User Permissions (for quick lookups)
CREATE OR REPLACE VIEW `v_user_permissions` AS
SELECT 
    e.employee_id,
    CONCAT(e.firstname, ' ', e.lastname) AS employee_name,
    e.position,
    r.role_name,
    r.display_name AS role_display,
    p.permission_key,
    p.module,
    p.action,
    p.description AS permission_description
FROM employee e
LEFT JOIN roles r ON e.role_id = r.role_id
LEFT JOIN role_permissions rp ON r.role_id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.permission_id
WHERE e.status_code = 1 AND r.status_code = 1;

-- View: Role Permissions Summary
CREATE OR REPLACE VIEW `v_role_permissions_summary` AS
SELECT 
    r.role_name,
    r.display_name,
    COUNT(DISTINCT rp.permission_id) AS total_permissions,
    GROUP_CONCAT(DISTINCT p.module ORDER BY p.module SEPARATOR ', ') AS modules_access
FROM roles r
LEFT JOIN role_permissions rp ON r.role_id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.permission_id
WHERE r.status_code = 1
GROUP BY r.role_id, r.role_name, r.display_name;

-- ============================================================================
-- VERIFICATION QUERIES (Run these to check your setup)
-- ============================================================================

-- Check all roles
-- SELECT * FROM roles;

-- Check all permissions
-- SELECT module, COUNT(*) as permission_count FROM permissions GROUP BY module;

-- Check LAB_MANAGER permissions
-- SELECT p.permission_key, p.description 
-- FROM role_permissions rp
-- JOIN permissions p ON rp.permission_id = p.permission_id
-- JOIN roles r ON rp.role_id = r.role_id
-- WHERE r.role_name = 'LAB_MANAGER'
-- ORDER BY p.module, p.action;

-- Check employee roles
-- SELECT e.employee_id, CONCAT(e.firstname, ' ', e.lastname) as name, 
--        e.position, r.role_name 
-- FROM employee e 
-- LEFT JOIN roles r ON e.role_id = r.role_id
-- WHERE e.status_code = 1;

-- ============================================================================
-- NOTES FOR IMPLEMENTATION
-- ============================================================================
-- 1. Update login.php to load permissions into $_SESSION['permissions']
-- 2. Create includes/auth.php with helper functions
-- 3. Add permission checks at the top of each module page
-- 4. Use has_permission() to show/hide UI elements
-- 5. Hash existing passwords: UPDATE employee SET password_hash = PASSWORD('password123') WHERE password_hash IS NULL;
-- 6. Or for PHP: UPDATE employee SET password_hash = '$2y$10$...' using password_hash('password', PASSWORD_BCRYPT)
-- 
-- NOTE: Your activity_log table structure:
--   - Uses employee_id (not user_id)
--   - Has columns: activity_log_id, employee_id, datetime_added, description, status_code
--   - No activity_type, ip_address, or user_agent columns
--   - Logging has been adapted to work with your existing structure
-- ============================================================================
