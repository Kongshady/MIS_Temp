# RBAC Setup Instructions

## Overview
The system now implements Role-Based Access Control (RBAC) to control what each user can see and do based on their assigned role.

## Available Roles

### 1. MIT Staff
**Access:** Tests, Employees, Sections
- User/Employee management
- Test type configuration
- Laboratory section management

### 2. Laboratory Manager
**Access:** Full system access
- All patient management features
- Lab results management
- Inventory and equipment
- Calibration and certificates
- Reports and activity logs
- All operational features

### 3. Staff-in-Charge
**Access:** Operational features
- Patient management (Internal/Update only)
- Physician management
- Lab results (view, create, update)
- Transactions (view, create, update)
- Items and inventory (view and usage)
- Equipment viewing
- Calibration viewing

### 4. Secretary
**Access:** Administrative features
- Patient viewing
- Transaction management
- Inventory stock operations
- Item management
- Equipment viewing
- Certificate generation

### 5. Auditor/Compliance Viewer
**Access:** View-only access
- View patients, physicians
- View lab results
- View transactions, items, inventory
- View equipment, calibration records
- View certificates
- Access reports

## Setup Steps

### Step 1: Execute RBAC SQL Scripts (In Order)

Run these SQL scripts in phpMyAdmin or your MySQL client:

```sql
-- 1. First, run the main RBAC implementation
SOURCE c:/xampp/htdocs/mis_project/sql/rbac_implementation.sql;

-- 2. Then add additional permissions for Employees and Lab Results
SOURCE c:/xampp/htdocs/mis_project/sql/rbac_additional_permissions.sql;

-- 3. Finally, create test users (optional - for testing)
SOURCE c:/xampp/htdocs/mis_project/sql/test_users_rbac.sql;
```

### Step 2: Test Login Credentials

If you ran the test users SQL, you can login with:

| Username | Password | Role | Access |
|----------|----------|------|--------|
| `mit_staff` | `password123` | MIT Staff | Tests, Employees, Sections |
| `lab_manager` | `password123` | Lab Manager | Full System Access |
| `staff_charge` | `password123` | Staff-in-Charge | Operational Features |
| `secretary` | `password123` | Secretary | Administrative Features |
| `auditor` | `password123` | Auditor | View-Only Access |

### Step 3: Verify Navigation Filtering

1. **Login as MIT Staff** - You should only see:
   - Dashboard
   - Tests
   - Employees
   - Sections
   - Logout

2. **Login as Lab Manager** - You should see all menu items

3. **Login as Staff-in-Charge** - You should see operational items

## How It Works

### Navigation Filtering
The navigation menu in [includes/header.php](includes/header.php) now uses the `get_accessible_menu()` function from [includes/auth.php](includes/auth.php) to:

1. Load the user's permissions from session when they login
2. Filter menu items based on required permissions
3. Only show menu items the user has access to

### Permission System
Permissions are stored in the database:
- `roles` table - Defines user roles
- `permissions` table - Defines all available permissions
- `role_permissions` table - Maps which permissions each role has
- `employee.role_id` - Links users to their assigned role

### Session Storage
When a user logs in:
```php
$_SESSION['role_id'] = 1;
$_SESSION['role_name'] = 'MIT_STAFF';
$_SESSION['permissions'] = ['users.view', 'users.manage', 'tests.manage', ...];
```

### Checking Permissions in Code
```php
// Check if user has permission
if (has_permission('patients.add_internal')) {
    // Show add patient button
}

// Require permission (redirects if unauthorized)
require_permission('patients.delete');

// Check for any of multiple permissions
if (has_any_permission(['patients.update', 'patients.add_internal'])) {
    // Allow action
}
```

## Customization

### Adding New Permissions
```sql
INSERT INTO permissions (permission_key, module, action, description) 
VALUES ('newmodule.action', 'newmodule', 'action', 'Description');

-- Assign to role
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER'),
    permission_id
FROM permissions WHERE permission_key = 'newmodule.action';
```

### Adding Menu Items
Edit [includes/auth.php](includes/auth.php) - `get_accessible_menu()` function:
```php
[
    'label' => 'New Module',
    'url' => '/mis_project/modules/newmodule.php',
    'icon' => '🆕',
    'permission' => 'newmodule.view' // or null for all users
]
```

## Troubleshooting

### Navigation Not Showing Items
1. Check if RBAC SQL was executed
2. Verify user has `role_id` set in employee table
3. Check role has permissions in `role_permissions` table
4. Clear browser cache and re-login

### Permission Denied Errors
1. Check user's assigned role
2. Verify role has required permissions
3. Check permission_key matches exactly

### Need to Reset Permissions
```sql
-- Re-run the RBAC implementation SQL
source c:/xampp/htdocs/mis_project/sql/rbac_implementation.sql;
```

## Security Notes

- All protected pages should include `require_login()` at the top
- Use `require_permission()` for actions requiring specific permissions
- Never expose permission logic in frontend code
- Log unauthorized access attempts (already implemented)
- Regularly audit user roles and permissions
