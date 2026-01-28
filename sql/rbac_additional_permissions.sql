-- ============================================================================
-- RBAC Additional Permissions for Employees and Lab Results
-- Clinical Laboratory Management System
-- ============================================================================

-- Add Employee Management Permissions
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('employees.view', 'employees', 'view', 'View employee list'),
('employees.manage', 'employees', 'manage', 'Add, edit, delete employees');

-- Add Lab Results Management Permissions
INSERT INTO `permissions` (`permission_key`, `module`, `action`, `description`) VALUES
('lab_results.view', 'lab_results', 'view', 'View laboratory test orders and results'),
('lab_results.manage', 'lab_results', 'manage', 'Create and manage laboratory orders and results');

-- Add permissions to MIT_STAFF role (Tests, Employees, Sections)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'MIT_STAFF'),
    permission_id
FROM permissions
WHERE permission_key IN ('employees.view', 'employees.manage');

-- Add permissions to LAB_MANAGER role (Full access)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER'),
    permission_id
FROM permissions
WHERE permission_key IN ('employees.view', 'employees.manage', 'lab_results.view', 'lab_results.manage');

-- Add permissions to STAFF_IN_CHARGE role (Can view employees and manage lab results)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'STAFF_IN_CHARGE'),
    permission_id
FROM permissions
WHERE permission_key IN ('employees.view', 'lab_results.view', 'lab_results.manage');

-- Add permissions to SECRETARY role (Can view employees and lab results)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'SECRETARY'),
    permission_id
FROM permissions
WHERE permission_key IN ('employees.view', 'lab_results.view');

-- Add permissions to OPTIONAL_VIEWER role (View only)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'OPTIONAL_VIEWER'),
    permission_id
FROM permissions
WHERE permission_key IN ('employees.view', 'lab_results.view');
