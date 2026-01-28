-- ============================================================================
-- RBAC ROLLBACK SCRIPT
-- Clinical Laboratory Management System
-- ============================================================================
-- 
-- ⚠️  WARNING: This script will remove all RBAC implementation
-- 
-- Use this script to revert back to your original database structure
-- if you decide not to use the RBAC system.
--
-- IMPORTANT: This will DELETE:
-- - All roles, permissions, and role_permissions data
-- - role_id and password_hash columns from employee table
-- - RBAC views
--
-- BACKUP YOUR DATABASE BEFORE RUNNING THIS SCRIPT!
-- ============================================================================

-- Step 1: Drop RBAC Views
-- ============================================================================

DROP VIEW IF EXISTS `v_user_permissions`;
DROP VIEW IF EXISTS `v_role_permissions_summary`;

-- Step 2: Drop Foreign Key Constraints
-- ============================================================================

-- Drop foreign key from employee table if it exists
ALTER TABLE `employee` DROP FOREIGN KEY IF EXISTS `fk_employee_role`;

-- Step 3: Remove RBAC Columns from Employee Table
-- ============================================================================

-- Remove role_id column (this will set all role assignments to NULL first)
ALTER TABLE `employee` DROP COLUMN IF EXISTS `role_id`;

-- Remove password_hash column (keep original password column if you had one)
ALTER TABLE `employee` DROP COLUMN IF EXISTS `password_hash`;

-- Step 4: Drop RBAC Tables
-- ============================================================================

-- Drop role_permissions junction table first (has foreign keys)
DROP TABLE IF EXISTS `role_permissions`;

-- Drop permissions table
DROP TABLE IF EXISTS `permissions`;

-- Drop roles table
DROP TABLE IF EXISTS `roles`;

-- ============================================================================
-- VERIFICATION
-- ============================================================================

-- Check that RBAC tables are removed
-- Run these queries to verify:

-- This should return 0 rows:
-- SELECT COUNT(*) FROM information_schema.TABLES 
-- WHERE TABLE_SCHEMA = 'clinlab' 
-- AND TABLE_NAME IN ('roles', 'permissions', 'role_permissions');

-- Check employee table structure:
-- DESCRIBE employee;

-- ============================================================================
-- NOTES
-- ============================================================================
-- 
-- After running this rollback script:
-- 
-- 1. Remove or comment out these files from your project:
--    - includes/auth.php
--    - login.php (or revert to old login)
--    - RBAC_*.md files (documentation)
--    
-- 2. Remove these lines from all module files:
--    - require_once '../includes/auth.php';
--    - require_login();
--    - require_permission('...');
--    - if (has_permission('...')) conditionals
--    
-- 3. Restore your original authentication method (if any)
--    
-- 4. Your database is now back to pre-RBAC state
--    
-- ============================================================================

-- Success message
SELECT 'RBAC rollback completed successfully. Your database is restored to original state.' as Status;
