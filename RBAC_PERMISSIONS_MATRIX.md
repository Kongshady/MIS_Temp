# RBAC Role Permissions Matrix

## Navigation Access by Role

| Menu Item | MIT Staff | Lab Manager | Staff-in-Charge | Secretary | Auditor |
|-----------|:---------:|:-----------:|:---------------:|:---------:|:-------:|
| 🏠 Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ |
| 👥 Patients | ❌ | ✅ | ✅ | 👁️ View | 👁️ View |
| 👨‍⚕️ Physicians | ❌ | ✅ | ✅ | ❌ | 👁️ View |
| 🔬 Lab Results | ❌ | ✅ | ✅ | 👁️ View | 👁️ View |
| 🧪 Tests | ✅ | ✅ | ❌ | ❌ | ❌ |
| 🏢 Sections | ✅ | ✅ | ❌ | ❌ | ❌ |
| 👨‍💼 Employees | ✅ | ✅ | 👁️ View | 👁️ View | 👁️ View |
| 📝 Transactions | ❌ | ✅ | ✅ | ✅ | 👁️ View |
| 📦 Items | ❌ | ✅ | ✅ | ✅ | 👁️ View |
| 📊 Inventory | ❌ | ✅ | ✅ | ✅ | 👁️ View |
| 🔧 Equipment | ❌ | ✅ | 👁️ View | 👁️ View | 👁️ View |
| ⚖️ Calibration | ❌ | ✅ | 👁️ View | ❌ | 👁️ View |
| 📜 Certificates | ❌ | ✅ | ❌ | ✅ | 👁️ View |
| 📈 Reports | ❌ | ✅ | ❌ | ❌ | ✅ |
| 📋 Activity Logs | ❌ | ✅ | ❌ | ❌ | ❌ |

**Legend:**
- ✅ = Full Access (View, Add, Edit, Delete)
- 👁️ View = View Only
- ❌ = No Access (Not visible in navigation)

---

## Detailed Permission Breakdown

### 🎓 MIT Staff (System Administrator)
**Focus:** Master data management and user administration

**Can Access:**
- Tests Management (Add/Edit/Delete test types)
- Sections Management (Add/Edit/Delete laboratory sections)
- Employees Management (Add/Edit/Delete user accounts)

**Cannot Access:**
- Patient data
- Laboratory operations
- Inventory and equipment
- Billing and transactions

**Use Case:**
> *"I need to add a new test type 'Blood Sugar Fasting', create a new user account for a new lab technician, and organize the Hematology section."*

---

### 🔬 Lab Manager (Head of Clinical Laboratory)
**Focus:** Complete operational oversight

**Can Access:**
- **EVERYTHING** - Full system access
- Activity Logs (only role with this access)
- All patient management features
- All operational features
- All administrative features

**Use Case:**
> *"I oversee all laboratory operations, review activity logs, manage staff, approve calibrations, and generate compliance reports."*

---

### 👨‍🔬 Staff-in-Charge
**Focus:** Daily laboratory operations

**Can Access:**
- Patient Management (Add internal patients, Update records)
- Physician Management
- Lab Results (View, Create, Update)
- Transactions (View, Create, Update)
- Items & Inventory (View and record usage)
- Equipment (View only)
- Calibration (View only)

**Cannot Access:**
- Delete operations (patients, results)
- Test/Section configuration
- User management
- Activity logs

**Use Case:**
> *"I process patient samples, enter lab results, record reagent usage, and manage daily transactions."*

---

### 📋 Secretary
**Focus:** Administrative and billing support

**Can Access:**
- Patient Information (View only)
- Transactions (Full management)
- Items & Inventory (Full management - stock in/out)
- Certificates (Generate)
- Equipment (View only)

**Cannot Access:**
- Lab results entry
- Test configuration
- Calibration
- Reports

**Use Case:**
> *"I handle billing transactions, manage inventory stock, generate patient certificates, and process walk-in registrations."*

---

### 🔍 Auditor/Compliance Viewer
**Focus:** Quality assurance and compliance

**Can Access:**
- **View-only** access to:
  - Patients
  - Physicians
  - Lab Results
  - Transactions
  - Items & Inventory
  - Equipment
  - Calibration
  - Certificates
  - Reports

**Cannot:**
- Modify ANY data
- Add or delete records

**Use Case:**
> *"I review laboratory processes for compliance, audit transactions, verify calibration records, and generate compliance reports."*

---

## Permission Keys Reference

### Module Permission Format
`module.action`

Examples:
- `patients.view` - Can see patient list
- `patients.update` - Can edit patient info
- `patients.delete` - Can remove patients
- `tests.manage` - Can add/edit/delete tests
- `inventory.stock_in` - Can receive inventory

### Checking Permissions in Code

```php
// Simple check
if (has_permission('patients.update')) {
    // Show edit button
}

// Multiple permissions (OR logic)
if (has_any_permission(['patients.update', 'patients.add_internal'])) {
    // Show form
}

// Multiple permissions (AND logic)
if (has_all_permissions(['patients.view', 'results.view'])) {
    // Show patient results page
}

// Require permission (redirect if unauthorized)
require_permission('logs.view');
```

---

## Role Assignment Examples

### Assign Role to Existing Employee
```sql
-- Make employee #5 a Lab Manager
UPDATE employee 
SET role_id = (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER')
WHERE employee_id = 5;
```

### Check User's Role and Permissions
```sql
-- See what permissions a user has
SELECT 
    e.username,
    e.firstname,
    r.display_name as role,
    p.permission_key,
    p.description
FROM employee e
JOIN roles r ON e.role_id = r.role_id
JOIN role_permissions rp ON r.role_id = rp.role_id
JOIN permissions p ON rp.permission_id = p.permission_id
WHERE e.employee_id = 1
ORDER BY p.module, p.action;
```

---

## Common Workflows

### Workflow 1: Process Lab Results (Staff-in-Charge)
1. ✅ Navigate to Patients → View patient list
2. ✅ Navigate to Lab Results → Create new order
3. ✅ Enter test results
4. ✅ Record reagent usage in Items
5. ❌ Cannot generate reports (no permission)

### Workflow 2: Manage Inventory (Secretary)
1. ✅ Navigate to Inventory → Stock In
2. ✅ Record new supplies received
3. ✅ Navigate to Transactions → Create billing
4. ❌ Cannot enter lab results (no permission)

### Workflow 3: Configure System (MIT Staff)
1. ✅ Navigate to Tests → Add new test type
2. ✅ Navigate to Sections → Update section info
3. ✅ Navigate to Employees → Create new user
4. ❌ Cannot view patient data (no permission)

### Workflow 4: Audit Compliance (Auditor)
1. ✅ Navigate to Reports → Generate compliance report
2. ✅ Navigate to Calibration → Review schedules
3. ✅ Navigate to Activity Logs → OH WAIT... ❌
4. 👁️ Only Lab Manager can view Activity Logs!

---

## Adding Custom Permissions

### Step 1: Create Permission
```sql
INSERT INTO permissions (permission_key, module, action, description)
VALUES ('newmodule.action', 'newmodule', 'action', 'Description here');
```

### Step 2: Assign to Role
```sql
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT role_id FROM roles WHERE role_name = 'LAB_MANAGER'),
    permission_id
FROM permissions 
WHERE permission_key = 'newmodule.action';
```

### Step 3: Add to Navigation
Edit `includes/auth.php` → `get_accessible_menu()`:
```php
[
    'label' => 'New Module',
    'url' => '/mis_project/modules/newmodule.php',
    'icon' => '🆕',
    'permission' => 'newmodule.action'
]
```

---

**Navigation will automatically filter based on permissions!**
