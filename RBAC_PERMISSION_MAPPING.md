# ============================================================================
# RBAC Page → Permission Mapping
# Clinical Laboratory Management System
# ============================================================================

## Quick Reference: Module Protection

Use this table to add permission checks to your existing module pages.

### Format:
```php
require_once '../includes/auth.php';
require_login();
require_permission('permission.key');
```

---

## 📋 Complete Permission Mapping Table

| Module File | Required Permission(s) | Additional Action Permissions | Role Access |
|------------|------------------------|-------------------------------|-------------|
| **dashboard.php** | *(none - all logged in users)* | - | ALL ROLES |
| **patients.php** | `patients.view` | `patients.add_internal`, `patients.add_walkin`, `patients.update`, `patients.delete` | LAB_MANAGER, STAFF_IN_CHARGE |
| **patient_details.php** | `patients.view` | `results.view`, `results.create`, `results.update`, `results.delete` | LAB_MANAGER, STAFF_IN_CHARGE |
| **physicians.php** | `physicians.view` | `physicians.manage` | LAB_MANAGER, STAFF_IN_CHARGE |
| **transactions.php** | `transactions.view` | `transactions.create`, `transactions.update`, `transactions.delete` | LAB_MANAGER, STAFF_IN_CHARGE, SECRETARY |
| **items.php** | `items.view` | `items.create`, `items.update`, `items.delete`, `items.usage` | LAB_MANAGER, STAFF_IN_CHARGE |
| **inventory.php** | `inventory.view` | `inventory.stock_in`, `inventory.stock_out`, `inventory.usage` | LAB_MANAGER, SECRETARY, STAFF_IN_CHARGE |
| **inventory_reports.php** | `reports.view` | `reports.generate` | LAB_MANAGER, STAFF_IN_CHARGE |
| **equipment.php** | `equipment.view` | `equipment.manage`, `maintenance.manage` | LAB_MANAGER, STAFF_IN_CHARGE |
| **calibration.php** | `calibration.view` | `calibration.manage` | LAB_MANAGER, STAFF_IN_CHARGE |
| **certificates.php** | `certificates.view` | `certificates.generate`, `certificates.verify` | LAB_MANAGER, OPTIONAL_VIEWER |
| **reports.php** | `reports.view` | `reports.generate` | LAB_MANAGER, STAFF_IN_CHARGE, OPTIONAL_VIEWER |
| **tests.php** | `tests.manage` | *(full CRUD)* | MIT_STAFF only |
| **sections.php** | `sections.manage` | *(full CRUD)* | MIT_STAFF only |
| **users.php** | `users.manage` | *(full CRUD)* | MIT_STAFF only |
| **logs.php** | `logs.view` | - | LAB_MANAGER only |

---

## 🔐 Implementation Examples by Module Type

### 1. **View-Only Pages** (e.g., Reports Viewer)
```php
<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

// Protect the page
require_login();
require_permission('reports.view');

$page_title = 'Reports';
include '../includes/header.php';
?>
<!-- Your page content -->
```

### 2. **Pages with Multiple Actions** (e.g., Inventory)
```php
<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

// Protect the page - require VIEW permission at minimum
require_login();
require_permission('inventory.view');

$page_title = 'Inventory Management';
include '../includes/header.php';

// Handle form submissions with action-specific checks
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        
        // Stock In action
        if ($_POST['action'] == 'stock_in') {
            if (!has_permission('inventory.stock_in')) {
                $message = '<div class="alert alert-danger">You do not have permission to add stock.</div>';
            } else {
                // Process stock in...
            }
        }
        
        // Stock Out action
        elseif ($_POST['action'] == 'stock_out') {
            if (!has_permission('inventory.stock_out')) {
                $message = '<div class="alert alert-danger">You do not have permission to remove stock.</div>';
            } else {
                // Process stock out...
            }
        }
        
        // Usage action
        elseif ($_POST['action'] == 'usage') {
            if (!has_permission('inventory.usage')) {
                $message = '<div class="alert alert-danger">You do not have permission to record usage.</div>';
            } else {
                // Process usage...
            }
        }
    }
}
?>

<!-- In the UI, show/hide buttons based on permissions -->
<?php if (has_permission('inventory.stock_in')): ?>
    <button class="btn btn-success" onclick="showStockInModal()">Stock In</button>
<?php endif; ?>

<?php if (has_permission('inventory.stock_out')): ?>
    <button class="btn btn-danger" onclick="showStockOutModal()">Stock Out</button>
<?php endif; ?>

<?php if (has_permission('inventory.usage')): ?>
    <button class="btn btn-primary" onclick="showUsageModal()">Record Usage</button>
<?php endif; ?>
```

### 3. **Admin-Only Pages** (e.g., Users Management)
```php
<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

// Strict protection - only MIT_STAFF
require_login();
require_permission('users.manage');

$page_title = 'User Management';
include '../includes/header.php';
?>
```

### 4. **Special Access Pages** (e.g., Activity Logs)
```php
<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

// Only LAB_MANAGER can view logs
require_login();
require_permission('logs.view');

$page_title = 'Activity Logs';
include '../includes/header.php';
?>
```

### 5. **Pages with OR Logic** (Multiple permissions accepted)
```php
<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

require_login();

// Allow access if user has ANY of these permissions
require_any_permission(['physicians.view', 'physicians.manage']);

$page_title = 'Physicians';
include '../includes/header.php';
?>
```

---

## 🎯 Action Permission Checks in Forms

### Pattern for CRUD Operations:

```php
// CREATE
if ($_POST['action'] == 'add') {
    if (!has_permission('module.create')) {
        die('Access denied');
    }
    // ... SQL INSERT
}

// UPDATE
if ($_POST['action'] == 'update') {
    if (!has_permission('module.update')) {
        die('Access denied');
    }
    // ... SQL UPDATE
}

// DELETE
if ($_POST['action'] == 'delete') {
    if (!has_permission('module.delete')) {
        die('Access denied');
    }
    // ... SQL DELETE
}
```

### UI Element Visibility:

```php
<!-- Show Add button only if user can create -->
<?php if (has_permission('items.create')): ?>
    <button type="submit" class="btn btn-primary">Add Item</button>
<?php else: ?>
    <p class="text-muted">You do not have permission to add items.</p>
<?php endif; ?>

<!-- Show Edit/Delete buttons per row -->
<?php while($item = $items->fetch_assoc()): ?>
    <tr>
        <td><?php echo $item['name']; ?></td>
        <td>
            <?php if (has_permission('items.update')): ?>
                <button class="btn btn-sm btn-warning">Edit</button>
            <?php endif; ?>
            
            <?php if (has_permission('items.delete')): ?>
                <button class="btn btn-sm btn-danger">Delete</button>
            <?php endif; ?>
        </td>
    </tr>
<?php endwhile; ?>
```

---

## 📊 Role Summary

| Role | Total Permissions | Can Access |
|------|-------------------|------------|
| **MIT_STAFF** | 6 | Users, Tests, Sections (master data only) |
| **LAB_MANAGER** | 40+ | Everything except master data management |
| **STAFF_IN_CHARGE** | 20+ | Patients, Results, Items, Reports (operational) |
| **SECRETARY** | 7 | Transactions, Inventory Stock In/Out |
| **OPTIONAL_VIEWER** | 3 | Reports, Certificates (read-only) |

---

## 🚀 Migration Checklist

For each existing module file:

1. ✅ Add `require_once '../includes/auth.php';` at top
2. ✅ Add `require_login();` after db_connection
3. ✅ Add `require_permission('module.action');` for page access
4. ✅ Wrap all POST action handlers with `has_permission()` checks
5. ✅ Wrap UI buttons/forms with `<?php if (has_permission()): ?>` conditionals
6. ✅ Test with different role accounts

---

## 🔍 Testing Matrix

| Test Case | MIT_STAFF | LAB_MANAGER | STAFF_IN_CHARGE | SECRETARY | VIEWER |
|-----------|-----------|-------------|-----------------|-----------|--------|
| Login | ✅ | ✅ | ✅ | ✅ | ✅ |
| View Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ |
| Add Patient | ❌ | ✅ | ✅ | ❌ | ❌ |
| Add Lab Result | ❌ | ✅ | ✅ | ❌ | ❌ |
| Delete Result | ❌ | ✅ | ❌ | ❌ | ❌ |
| Stock In | ❌ | ✅ | ❌ | ✅ | ❌ |
| Stock Out | ❌ | ✅ | ❌ | ✅ | ❌ |
| Record Usage | ❌ | ✅ | ✅ | ✅ | ❌ |
| Manage Users | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage Tests | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage Sections | ✅ | ❌ | ❌ | ❌ | ❌ |
| View Logs | ❌ | ✅ | ❌ | ❌ | ❌ |
| Generate Reports | ❌ | ✅ | ✅ | ❌ | ❌ |
| View Reports | ❌ | ✅ | ✅ | ❌ | ✅ |
| Generate Certificate | ❌ | ✅ | ❌ | ❌ | ❌ |
| Verify Certificate | ❌ | ✅ | ❌ | ❌ | ✅ |

---

## 💡 Pro Tips

1. **Always check server-side**: UI hiding is NOT security - always validate permissions in PHP before executing SQL
2. **Use granular permissions**: Check specific action permissions (create/update/delete), not just view
3. **Log everything**: Use activity_log table for all sensitive operations
4. **Fail securely**: Default to denying access if permission check fails
5. **Test thoroughly**: Create test accounts for each role and verify access restrictions

---

## 🆘 Troubleshooting

**Problem**: User has role but no permissions loaded
- **Solution**: Check that role_permissions table has entries for that role_id

**Problem**: Permission check always fails
- **Solution**: Verify permission_key spelling matches exactly (case-sensitive)

**Problem**: Session loses permissions
- **Solution**: Check session timeout settings, ensure session_start() is called

**Problem**: User can access page via direct URL
- **Solution**: Ensure require_permission() is at TOP of page, before any HTML output

---

## 📝 Notes

- Permission keys use dot notation: `module.action`
- Always use exact permission keys from the permissions table
- Multiple roles can share permissions (many-to-many relationship)
- Soft delete recommended: set status_code=0 instead of DELETE
- Activity logging helps audit trail for compliance
