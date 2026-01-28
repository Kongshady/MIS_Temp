# Quick Start: RBAC Navigation Setup

## ⚡ Fast Setup (3 Steps)

### Step 1: Run SQL Scripts
Open phpMyAdmin → Select `clinlab` database → Click SQL tab → Run these in order:

```sql
SOURCE c:/xampp/htdocs/mis_project/sql/rbac_implementation.sql;
SOURCE c:/xampp/htdocs/mis_project/sql/rbac_additional_permissions.sql;
SOURCE c:/xampp/htdocs/mis_project/sql/test_users_rbac.sql;
```

### Step 2: Test Login
Go to: `http://localhost/mis_project/login.php`

Login with:
- Username: `mit_staff`
- Password: `password123`

### Step 3: Verify Navigation
You should ONLY see these menu items:
- 🏠 Dashboard
- 🧪 Tests
- 🏢 Sections
- 👨‍💼 Employees
- 🚪 Logout

✅ **Success!** Role-based navigation is working!

---

## 🧪 Test All Roles

| Username | Password | What You'll See |
|----------|----------|----------------|
| `mit_staff` | `password123` | Tests, Sections, Employees |
| `lab_manager` | `password123` | **ALL MENU ITEMS** (full access) |
| `staff_charge` | `password123` | Operational items (Patients, Lab Results, Transactions, etc.) |
| `secretary` | `password123` | Administrative items (Transactions, Inventory, Items) |
| `auditor` | `password123` | View-only items (Reports, Patients, Lab Results) |

---

## 📋 Role Summary

### MIT Staff
**Purpose:** System administration for master data
**Access:** Tests, Employees, Sections
**Use Case:** Configure test types, manage user accounts, organize lab sections

### Lab Manager
**Purpose:** Head of Clinical Laboratory
**Access:** EVERYTHING (Full system access)
**Use Case:** Oversee all laboratory operations

### Staff-in-Charge
**Purpose:** Laboratory operational staff
**Access:** Patients, Physicians, Lab Results, Transactions, Items (view/usage), Equipment, Calibration
**Use Case:** Daily laboratory operations, patient management, result entry

### Secretary
**Purpose:** Administrative support
**Access:** Patients (view), Transactions, Items, Inventory, Certificates
**Use Case:** Handle billing, inventory management, administrative tasks

### Auditor/Compliance Viewer
**Purpose:** View-only auditing
**Access:** Read-only access to patients, results, reports, transactions
**Use Case:** Compliance checks, auditing, quality assurance reviews

---

## 🔧 Troubleshooting

### Navigation shows all items to everyone
**Solution:** Run the SQL scripts in Step 1

### Login fails
**Solution:** Check that password is `password123` (case-sensitive)

### Database error
**Solution:** Ensure `clinlab` database exists and is selected

### Still seeing old navigation
**Solution:** Clear browser cache (Ctrl + Shift + R) and logout/login again

---

## 📚 Full Documentation

For detailed information:
- [RBAC_SETUP_INSTRUCTIONS.md](RBAC_SETUP_INSTRUCTIONS.md) - Complete setup guide
- [RBAC_IMPLEMENTATION_SUMMARY.md](RBAC_IMPLEMENTATION_SUMMARY.md) - Technical details
- [RBAC_IMPLEMENTATION_GUIDE.md](RBAC_IMPLEMENTATION_GUIDE.md) - Original RBAC guide

---

## ✨ What Changed

**Before:** Everyone saw the same navigation menu
**After:** Each role sees only what they have permission to access

**Files Modified:**
- `includes/header.php` - Dynamic navigation
- `includes/auth.php` - Permission filtering
- `assets/css/style.css` - Sidebar styling

**SQL Added:**
- `sql/rbac_additional_permissions.sql` - Employees & Lab Results permissions
- `sql/test_users_rbac.sql` - Test user accounts

---

Need help? Check the full documentation files listed above!
