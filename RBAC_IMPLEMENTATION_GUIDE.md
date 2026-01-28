# ============================================================================
# RBAC Implementation Guide
# Clinical Laboratory Management System
# Step-by-Step Instructions
# ============================================================================

## 📋 Table of Contents
1. Prerequisites
2. Database Setup
3. File Structure
4. Implementation Steps
5. Testing & Verification
6. Troubleshooting
7. Security Best Practices

---

## 1️⃣ Prerequisites

Before implementing RBAC, ensure you have:
- ✅ Working PHP + MySQL application
- ✅ Database backup (IMPORTANT!)
- ✅ Access to phpMyAdmin or MySQL command line
- ✅ Employee/user table with username column
- ✅ Session support enabled in PHP

---

## 2️⃣ Database Setup

### Step 2.1: Run the RBAC SQL Script

1. Open phpMyAdmin or MySQL CLI
2. Select your database (e.g., `clinlab`)
3. Import or execute: `sql/rbac_implementation.sql`

This will create:
- `roles` table (5 roles)
- `permissions` table (40+ permissions)
- `role_permissions` table (role-to-permission mappings)
- Update `employee` table to add `role_id` and `password_hash` columns
- Create helpful views

### Step 2.2: Set Up Default Passwords

Run this to hash passwords for existing users:

```sql
-- Option 1: Set a default password for all users (TEMPORARY - change after first login)
UPDATE employee 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE password_hash IS NULL;
-- This hash = "password123"

-- Option 2: Generate individual passwords using PHP
-- Create a migration script: tools/hash_passwords.php
```

**hash_passwords.php** (create this file):
```php
<?php
require_once '../db_connection.php';

// Set default password for all users without one
$default_password = 'TempPass2026!';
$hash = password_hash($default_password, PASSWORD_BCRYPT);

$result = $conn->query("UPDATE employee SET password_hash = '$hash' WHERE password_hash IS NULL");

echo "Password reset complete. Default password: $default_password\n";
echo "Users affected: " . $conn->affected_rows . "\n";
echo "IMPORTANT: Have users change their passwords immediately!\n";
?>
```

### Step 2.3: Assign Roles to Users

```sql
-- Assign specific roles manually
UPDATE employee 
SET role_id = (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER')
WHERE employee_id = 1; -- Your admin account

UPDATE employee 
SET role_id = (SELECT role_id FROM roles WHERE role_name = 'MIT_STAFF')
WHERE position = 'IT Administrator';

UPDATE employee 
SET role_id = (SELECT role_id FROM roles WHERE role_name = 'SECRETARY')
WHERE position = 'Secretary';

-- Or set default for everyone else
UPDATE employee 
SET role_id = (SELECT role_id FROM roles WHERE role_name = 'STAFF_IN_CHARGE')
WHERE role_id IS NULL;
```

### Step 2.4: Add Username Column (if not exists)

```sql
-- If your employee table doesn't have username
ALTER TABLE employee 
ADD COLUMN IF NOT EXISTS username VARCHAR(50) UNIQUE AFTER employee_id;

-- Populate usernames (e.g., from email or create pattern)
UPDATE employee 
SET username = LOWER(CONCAT(firstname, '.', lastname))
WHERE username IS NULL;
```

---

## 3️⃣ File Structure

After implementation, your project should have:

```
mis_project/
├── db_connection.php
├── login.php                          ← NEW/MODIFIED
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── auth.php                       ← NEW
├── modules/
│   ├── dashboard.php
│   ├── patients.php                   ← MODIFY
│   ├── physicians.php                 ← MODIFY
│   ├── transactions.php               ← MODIFY
│   ├── items.php                      ← MODIFY
│   ├── inventory.php                  ← MODIFY
│   ├── equipment.php                  ← MODIFY
│   ├── calibration.php                ← MODIFY
│   ├── certificates.php               ← MODIFY
│   ├── reports.php                    ← MODIFY
│   ├── tests.php                      ← MODIFY
│   ├── sections.php                   ← MODIFY
│   ├── users.php                      ← MODIFY
│   └── logs.php                       ← MODIFY
├── sql/
│   └── rbac_implementation.sql        ← NEW
├── RBAC_PERMISSION_MAPPING.md         ← NEW (Reference)
└── RBAC_INTEGRATION_EXAMPLES.php      ← NEW (Reference)
```

---

## 4️⃣ Implementation Steps

### Step 4.1: Create Core Files

**Already created:**
- ✅ `includes/auth.php` - Authorization helper functions
- ✅ `login.php` - Login page with RBAC
- ✅ `sql/rbac_implementation.sql` - Database schema

### Step 4.2: Update Each Module Page

For **EVERY** module in `modules/` folder:

#### Pattern A: Simple Protection (View Only)

```php
<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';  // ADD THIS

require_login();                       // ADD THIS
require_permission('module.view');    // ADD THIS - use correct permission

$page_title = 'Module Name';
include '../includes/header.php';

// Rest of your existing code...
?>
```

#### Pattern B: Multiple Actions (CRUD)

```php
<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

require_login();
require_permission('module.view');  // Minimum permission to view page

$page_title = 'Module Name';
include '../includes/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        
        // CREATE
        if ($_POST['action'] == 'add') {
            if (!has_permission('module.create')) {  // CHECK PERMISSION
                $message = '<div class="alert alert-danger">Access denied</div>';
            } else {
                // Your existing INSERT code
            }
        }
        
        // UPDATE
        elseif ($_POST['action'] == 'update') {
            if (!has_permission('module.update')) {  // CHECK PERMISSION
                $message = '<div class="alert alert-danger">Access denied</div>';
            } else {
                // Your existing UPDATE code
            }
        }
        
        // DELETE
        elseif ($_POST['action'] == 'delete') {
            if (!has_permission('module.delete')) {  // CHECK PERMISSION
                $message = '<div class="alert alert-danger">Access denied</div>';
            } else {
                // Your existing DELETE code
            }
        }
    }
}

// Your existing query code...
?>

<!-- In HTML: Show/hide buttons -->
<?php if (has_permission('module.create')): ?>
    <button type="submit">Add New</button>
<?php endif; ?>

<?php if (has_permission('module.update')): ?>
    <button class="btn-edit">Edit</button>
<?php endif; ?>

<?php if (has_permission('module.delete')): ?>
    <button class="btn-delete">Delete</button>
<?php endif; ?>
```

### Step 4.3: Specific Module Updates

Use the permission mapping table:

| Module | Permission Key | Code Pattern |
|--------|----------------|--------------|
| **sections.php** | `sections.manage` | Pattern A |
| **tests.php** | `tests.manage` | Pattern A |
| **users.php** | `users.manage` | Pattern B (see RBAC_INTEGRATION_EXAMPLES.php) |
| **logs.php** | `logs.view` | Pattern A (read-only) |
| **inventory.php** | `inventory.view` + stock actions | Pattern B (see examples) |
| **patients.php** | `patients.view` | Pattern B |
| **physicians.php** | `physicians.view` | Pattern B |
| **transactions.php** | `transactions.view` | Pattern B |
| **items.php** | `items.view` | Pattern B |
| **equipment.php** | `equipment.view` | Pattern B |
| **calibration.php** | `calibration.view` | Pattern B |
| **certificates.php** | `certificates.view` | Pattern B |
| **reports.php** | `reports.view` | Pattern B |

### Step 4.4: Update Navigation (Optional but Recommended)

In `includes/header.php`, replace static menu with dynamic:

```php
<?php
// After session_start() and before HTML
if (is_logged_in()) {
    $accessible_menu = get_accessible_menu();
}
?>

<!-- In your navigation HTML -->
<nav>
    <?php if (isset($accessible_menu)): ?>
        <?php foreach ($accessible_menu as $item): ?>
            <a href="<?php echo $item['url']; ?>">
                <?php echo $item['icon']; ?> <?php echo $item['label']; ?>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <a href="/mis_project/login.php?logout=1">🚪 Logout</a>
</nav>
```

---

## 5️⃣ Testing & Verification

### Step 5.1: Create Test Accounts

Create one user for each role:

```sql
-- MIT_STAFF test account
INSERT INTO employee (username, password_hash, firstname, lastname, position, role_id, status_code)
VALUES (
    'admin.test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'Tester',
    'IT Administrator',
    (SELECT role_id FROM roles WHERE role_name = 'MIT_STAFF'),
    1
);

-- LAB_MANAGER test account
INSERT INTO employee (username, password_hash, firstname, lastname, position, role_id, status_code)
VALUES (
    'manager.test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Manager',
    'Tester',
    'Laboratory Manager',
    (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER'),
    1
);

-- STAFF_IN_CHARGE test account
INSERT INTO employee (username, password_hash, firstname, lastname, position, role_id, status_code)
VALUES (
    'staff.test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Staff',
    'Tester',
    'Laboratory Staff',
    (SELECT role_id FROM roles WHERE role_name = 'STAFF_IN_CHARGE'),
    1
);

-- SECRETARY test account
INSERT INTO employee (username, password_hash, firstname, lastname, position, role_id, status_code)
VALUES (
    'secretary.test',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Secretary',
    'Tester',
    'Administrative Secretary',
    (SELECT role_id FROM roles WHERE role_name = 'SECRETARY'),
    1
);

-- All use password: "password123"
```

### Step 5.2: Test Matrix

Login as each user and verify:

| Action | MIT_STAFF | LAB_MANAGER | STAFF_IN_CHARGE | SECRETARY |
|--------|-----------|-------------|-----------------|-----------|
| Login successful | ✅ | ✅ | ✅ | ✅ |
| Access dashboard | ✅ | ✅ | ✅ | ✅ |
| Access Users page | ✅ | ❌ 403 | ❌ 403 | ❌ 403 |
| Access Tests page | ✅ | ❌ 403 | ❌ 403 | ❌ 403 |
| Access Sections page | ✅ | ❌ 403 | ❌ 403 | ❌ 403 |
| Access Logs page | ❌ 403 | ✅ | ❌ 403 | ❌ 403 |
| Access Patients | ❌ 403 | ✅ | ✅ | ❌ 403 |
| Add Patient (walk-in) | ❌ | ✅ | ❌ | ❌ |
| Access Inventory | ❌ 403 | ✅ | ✅ | ✅ |
| Stock In button visible | ❌ | ✅ | ❌ | ✅ |
| Stock Out button visible | ❌ | ✅ | ❌ | ✅ |
| Usage button visible | ❌ | ✅ | ✅ | ✅ |
| Access Reports | ❌ 403 | ✅ | ✅ | ❌ 403 |

### Step 5.3: Verification Queries

Run these to check setup:

```sql
-- Check user roles
SELECT e.employee_id, e.username, 
       CONCAT(e.firstname, ' ', e.lastname) as name,
       r.role_name, r.display_name
FROM employee e
LEFT JOIN roles r ON e.role_id = r.role_id
WHERE e.status_code = 1;

-- Check permission counts per role
SELECT r.role_name, COUNT(rp.permission_id) as total_permissions
FROM roles r
LEFT JOIN role_permissions rp ON r.role_id = rp.role_id
GROUP BY r.role_id;

-- Check specific user permissions
SELECT p.permission_key
FROM employee e
JOIN role_permissions rp ON e.role_id = rp.role_id
JOIN permissions p ON rp.permission_id = p.permission_id
WHERE e.username = 'manager.test'
ORDER BY p.module, p.action;
```

---

## 6️⃣ Troubleshooting

### Problem: 403 Forbidden on all pages

**Causes:**
- auth.php not included
- require_permission() called before require_login()
- Session not started
- Permissions not loaded into session

**Solution:**
```php
// Verify at top of page:
session_start();  // Usually in db_connection.php or header.php
require_once '../includes/auth.php';
require_login();
require_permission('correct.key');

// Debug: Print session data
var_dump($_SESSION);
```

### Problem: User can access page but gets "Access Denied" on actions

**Cause:** Page permission is correct but action permission is wrong/missing

**Solution:**
```php
// Check specific permission before action
if (!has_permission('module.create')) {
    echo "You need: module.create";
    echo "You have: ";
    print_r($_SESSION['permissions']);
    die();
}
```

### Problem: Passwords not working

**Cause:** password_hash column is NULL or using wrong hash

**Solution:**
```sql
-- Check if passwords are hashed
SELECT username, password_hash FROM employee LIMIT 5;

-- If NULL, run migration script or:
UPDATE employee 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE password_hash IS NULL;
```

### Problem: Session loses data

**Cause:** Session timeout or session_start() not called

**Solution:**
```php
// In db_connection.php or at very top of header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Increase session lifetime (in php.ini or .htaccess)
ini_set('session.gc_maxlifetime', 3600); // 1 hour
```

### Problem: "No role assigned" error on login

**Cause:** User has no role_id

**Solution:**
```sql
-- Assign default role
UPDATE employee 
SET role_id = (SELECT role_id FROM roles WHERE role_name = 'STAFF_IN_CHARGE')
WHERE role_id IS NULL AND status_code = 1;
```

---

## 7️⃣ Security Best Practices

### ✅ DO:
1. **Always validate server-side** - UI hiding is not security
2. **Use password_hash()** - Never store plaintext passwords
3. **Log sensitive actions** - Use activity_log table
4. **Use prepared statements** - Prevent SQL injection
5. **Validate input** - Sanitize all user input
6. **Use HTTPS** - In production, enforce SSL
7. **Set strong passwords** - Minimum 8 characters, complexity requirements
8. **Session timeout** - Auto-logout after inactivity
9. **Rate limiting** - Prevent brute force login attempts
10. **Regular audits** - Review activity logs weekly

### ❌ DON'T:
1. Don't rely only on JavaScript validation
2. Don't use MD5 or SHA1 for passwords
3. Don't give everyone LAB_MANAGER role
4. Don't skip permission checks in POST handlers
5. Don't log passwords or sensitive data
6. Don't allow password reuse
7. Don't share admin credentials
8. Don't disable error reporting in development
9. Don't forget to backup before changes
10. Don't expose system internals in error messages

### 🔐 Additional Security Layers

#### Add CSRF Protection:
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// In forms
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Validate
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF validation failed');
}
```

#### Add Login Rate Limiting:
```php
// Track failed login attempts
if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    
    if ($_SESSION['login_attempts'] >= 5) {
        // Lock account for 15 minutes
        $error = 'Too many failed attempts. Try again in 15 minutes.';
    }
}
```

#### Force Password Change on First Login:
```php
// Add column to employee table
ALTER TABLE employee ADD COLUMN must_change_password TINYINT(1) DEFAULT 1;

// Check after login
if ($user['must_change_password']) {
    header('Location: change_password.php');
    exit();
}
```

---

## 8️⃣ Quick Start Checklist

- [ ] 1. Backup database
- [ ] 2. Run `sql/rbac_implementation.sql`
- [ ] 3. Create `includes/auth.php`
- [ ] 4. Create/update `login.php`
- [ ] 5. Set passwords for all users
- [ ] 6. Assign roles to all users
- [ ] 7. Add `require_once '../includes/auth.php';` to all module pages
- [ ] 8. Add `require_login();` to all module pages
- [ ] 9. Add `require_permission()` to all module pages with correct keys
- [ ] 10. Wrap POST actions with `has_permission()` checks
- [ ] 11. Wrap UI elements with `<?php if (has_permission()): ?>`
- [ ] 12. Create test accounts for each role
- [ ] 13. Test access matrix with each role
- [ ] 14. Review activity logs
- [ ] 15. Update navigation menu to be dynamic
- [ ] 16. Test all CRUD operations per role
- [ ] 17. Verify 403 errors show for unauthorized access
- [ ] 18. Document role assignments for your team
- [ ] 19. Train users on new login system
- [ ] 20. Monitor logs for the first week

---

## 📞 Support & Resources

- **Permission Mapping:** See `RBAC_PERMISSION_MAPPING.md`
- **Code Examples:** See `RBAC_INTEGRATION_EXAMPLES.php`
- **Database Schema:** See `sql/rbac_implementation.sql`

**Common Questions:**

Q: Can a user have multiple roles?
A: Not by default. Modify to use employee_roles junction table if needed.

Q: Can I add custom permissions?
A: Yes! Add to permissions table and assign via role_permissions.

Q: How do I add a new role?
A: INSERT into roles, then map permissions in role_permissions table.

Q: What if I need page-level and record-level permissions?
A: Add ownership checks: `if (has_permission() || $record['created_by'] == get_user_id())`

---

**You're all set! 🎉**

Your Clinical Laboratory Management System now has enterprise-grade role-based access control.
