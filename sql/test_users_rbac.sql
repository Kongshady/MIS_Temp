-- ============================================================================
-- Test Users Setup for RBAC System
-- Clinical Laboratory Management System
-- ============================================================================
-- This script creates test users with different roles for testing the RBAC system
-- 
-- Default password for all test users: "password123"
-- Password hash generated with: password_hash('password123', PASSWORD_DEFAULT)
-- ============================================================================

-- First, ensure you have run rbac_implementation.sql and rbac_additional_permissions.sql

-- Create test users with different roles
-- Password: password123 (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)

-- 1. MIT Staff User
INSERT INTO employee (firstname, lastname, username, password, password_hash, position, role_id, status_code, datetime_added)
VALUES 
('John', 'Doe', 'mit_staff', 'password123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'MIT Staff Administrator', 
 (SELECT role_id FROM roles WHERE role_name = 'MIT_STAFF'), 
 1, NOW())
ON DUPLICATE KEY UPDATE 
    role_id = (SELECT role_id FROM roles WHERE role_name = 'MIT_STAFF'),
    password = 'password123',
    password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 2. Laboratory Manager User
INSERT INTO employee (firstname, lastname, username, password, password_hash, position, role_id, status_code, datetime_added)
VALUES 
('Jane', 'Smith', 'lab_manager', 'password123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'Laboratory Manager', 
 (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER'), 
 1, NOW())
ON DUPLICATE KEY UPDATE 
    role_id = (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER'),
    password = 'password123',
    password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 3. Staff-in-Charge User
INSERT INTO employee (firstname, lastname, username, password, password_hash, position, role_id, status_code, datetime_added)
VALUES 
('Bob', 'Johnson', 'staff_charge', 'password123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'Staff-in-Charge', 
 (SELECT role_id FROM roles WHERE role_name = 'STAFF_IN_CHARGE'), 
 1, NOW())
ON DUPLICATE KEY UPDATE 
    role_id = (SELECT role_id FROM roles WHERE role_name = 'STAFF_IN_CHARGE'),
    password = 'password123',
    password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 4. Secretary User
INSERT INTO employee (firstname, lastname, username, password, password_hash, position, role_id, status_code, datetime_added)
VALUES 
('Alice', 'Williams', 'secretary', 'password123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'Secretary', 
 (SELECT role_id FROM roles WHERE role_name = 'SECRETARY'), 
 1, NOW())
ON DUPLICATE KEY UPDATE 
    role_id = (SELECT role_id FROM roles WHERE role_name = 'SECRETARY'),
    password = 'password123',
    password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 5. Auditor/Viewer User
INSERT INTO employee (firstname, lastname, username, password, password_hash, position, role_id, status_code, datetime_added)
VALUES 
('Charlie', 'Brown', 'auditor', 'password123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'Compliance Auditor', 
 (SELECT role_id FROM roles WHERE role_name = 'OPTIONAL_VIEWER'), 
 1, NOW())
ON DUPLICATE KEY UPDATE 
    role_id = (SELECT role_id FROM roles WHERE role_name = 'OPTIONAL_VIEWER'),
    password = 'password123',
    password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Display created users
SELECT 
    e.employee_id,
    e.username,
    CONCAT(e.firstname, ' ', e.lastname) as full_name,
    e.position,
    r.role_name,
    r.display_name as role_display,
    'password123' as password_note
FROM employee e
LEFT JOIN roles r ON e.role_id = r.role_id
WHERE e.username IN ('mit_staff', 'lab_manager', 'staff_charge', 'secretary', 'auditor')
ORDER BY e.employee_id;
