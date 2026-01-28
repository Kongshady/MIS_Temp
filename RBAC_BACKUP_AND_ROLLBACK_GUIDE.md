# ============================================================================
# RBAC Backup & Rollback Guide
# Clinical Laboratory Management System
# ============================================================================

## 🔄 How to Safely Test and Revert RBAC

This guide shows you how to safely implement, test, and rollback the RBAC system if needed.

---

## 📋 Before You Start - Create Backups

### Option 1: Full Database Backup (RECOMMENDED)

**Using phpMyAdmin:**
1. Go to phpMyAdmin
2. Select your database (e.g., `clinlab`)
3. Click "Export" tab
4. Choose "Quick" export method
5. Format: SQL
6. Click "Go"
7. Save file as: `clinlab_backup_BEFORE_RBAC_2026-01-26.sql`

**Using Command Line:**
```bash
# Windows (in XAMPP)
cd C:\xampp\mysql\bin
.\mysqldump -u root -p clinlab > C:\backups\clinlab_backup_BEFORE_RBAC.sql

# Linux/Mac
mysqldump -u root -p clinlab > ~/backups/clinlab_backup_BEFORE_RBAC.sql
```

### Option 2: Backup Just Employee Table

```sql
-- Create a backup table
CREATE TABLE employee_backup_before_rbac AS SELECT * FROM employee;

-- Verify backup
SELECT COUNT(*) FROM employee_backup_before_rbac;
```

---

## ✅ Implementation Steps (Safe Testing)

### Step 1: Backup (see above)

### Step 2: Implement RBAC
```bash
# Run the RBAC implementation script
# In phpMyAdmin: Import sql/rbac_implementation.sql
```

### Step 3: Test the System
- Create test accounts for each role
- Login with each role
- Verify permissions work correctly
- Check that unauthorized access is blocked
- Test all CRUD operations

### Step 4: Decision Point

**If you like it:** ✅ Keep it and deploy to production

**If you don't like it:** ⏮️ Proceed to rollback (see below)

---

## 🔙 Rollback Options

### Option A: Full Database Restore (SAFEST)

**This completely restores your database to pre-RBAC state**

**Using phpMyAdmin:**
1. Go to phpMyAdmin
2. Select your database
3. Click "Import" tab
4. Choose your backup file: `clinlab_backup_BEFORE_RBAC_2026-01-26.sql`
5. Click "Go"
6. Wait for completion

**Using Command Line:**
```bash
# Windows (XAMPP)
cd C:\xampp\mysql\bin
.\mysql -u root -p clinlab < C:\backups\clinlab_backup_BEFORE_RBAC.sql

# Linux/Mac
mysql -u root -p clinlab < ~/backups/clinlab_backup_BEFORE_RBAC.sql
```

**Result:** Everything is exactly as it was before RBAC implementation.

---

### Option B: Selective Rollback (Using Our Script)

**This removes only RBAC components, keeps other changes**

1. **Run the rollback SQL script:**
   ```bash
   # In phpMyAdmin: Import sql/rbac_rollback.sql
   ```

2. **Or run manually:**
   ```sql
   -- Drop RBAC views
   DROP VIEW IF EXISTS v_user_permissions;
   DROP VIEW IF EXISTS v_role_permissions_summary;
   
   -- Remove foreign key
   ALTER TABLE employee DROP FOREIGN KEY IF EXISTS fk_employee_role;
   
   -- Remove RBAC columns from employee
   ALTER TABLE employee DROP COLUMN IF EXISTS role_id;
   ALTER TABLE employee DROP COLUMN IF EXISTS password_hash;
   
   -- Drop RBAC tables
   DROP TABLE IF EXISTS role_permissions;
   DROP TABLE IF EXISTS permissions;
   DROP TABLE IF EXISTS roles;
   ```

3. **Restore employee table from backup (optional):**
   ```sql
   -- If you made a backup table
   DROP TABLE employee;
   CREATE TABLE employee AS SELECT * FROM employee_backup_before_rbac;
   
   -- Restore indexes and auto_increment
   ALTER TABLE employee ADD PRIMARY KEY (employee_id);
   ALTER TABLE employee MODIFY employee_id INT AUTO_INCREMENT;
   ```

---

### Option C: Keep Database, Remove PHP Code Only

**If you want to keep the database but disable RBAC in code:**

1. **Comment out auth checks in module files:**
   ```php
   <?php
   require_once '../db_connection.php';
   // require_once '../includes/auth.php';  // COMMENTED OUT
   // require_login();                      // COMMENTED OUT
   // require_permission('module.action');  // COMMENTED OUT
   
   $page_title = 'Module Name';
   include '../includes/header.php';
   ```

2. **Rename auth.php to disable it:**
   ```bash
   # Rename so it's not loaded
   mv includes/auth.php includes/auth.php.disabled
   ```

3. **Revert login.php:**
   - Either restore your old login file
   - Or remove RBAC code from current login.php

**Result:** RBAC tables remain in database but system doesn't use them.

---

## 📝 Rollback Checklist

If you decide to fully remove RBAC:

### Database Cleanup:
- [ ] Run full database backup restore OR run rbac_rollback.sql
- [ ] Verify RBAC tables removed: `SHOW TABLES LIKE '%role%';`
- [ ] Verify employee table restored: `DESCRIBE employee;`
- [ ] Check no foreign key errors: `SHOW CREATE TABLE employee;`

### File Cleanup:
- [ ] Delete or rename `includes/auth.php`
- [ ] Restore original `login.php` (or delete new one)
- [ ] Remove RBAC documentation files (RBAC_*.md, RBAC_*.txt)
- [ ] Remove `sql/rbac_implementation.sql` and `sql/rbac_rollback.sql`

### Code Cleanup (in all module files):
- [ ] Remove `require_once '../includes/auth.php';`
- [ ] Remove `require_login();`
- [ ] Remove `require_permission('...');`
- [ ] Remove `if (has_permission('...'))` conditionals
- [ ] Remove `get_user_id()`, `get_user_name()` calls (or replace with old method)

### Verification:
- [ ] Access all pages without login
- [ ] All CRUD operations work
- [ ] No PHP errors about missing auth.php
- [ ] No SQL errors about missing tables/columns

---

## ⚡ Quick Rollback (Emergency)

If something goes wrong and you need to rollback immediately:

```sql
-- EMERGENCY ROLLBACK - Run this in phpMyAdmin SQL tab
SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS v_user_permissions;
DROP VIEW IF EXISTS v_role_permissions_summary;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;

ALTER TABLE employee DROP COLUMN IF EXISTS role_id;
ALTER TABLE employee DROP COLUMN IF EXISTS password_hash;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Emergency rollback complete' as Status;
```

Then comment out auth checks in your PHP files.

---

## 🔄 Comparison: Before vs After Rollback

| Item | Before RBAC | After Implementation | After Rollback |
|------|-------------|---------------------|----------------|
| **Tables** | Original tables | +3 RBAC tables | Back to original |
| **Employee columns** | Original columns | +role_id, +password_hash | Back to original |
| **Login** | Old login (if any) | RBAC login with permissions | Old login restored |
| **Page Access** | Open to all logged in | Permission-based | Open to all again |
| **Data** | Original data | +roles, permissions data | Original data only |

---

## 💡 Tips for Safe Testing

1. **Test on localhost first** - Never test on production
2. **Use test database** - Create a copy: `CREATE DATABASE clinlab_test LIKE clinlab;`
3. **Keep backups** - Make backups before AND during testing
4. **Document changes** - Note which files you modified
5. **Test incrementally** - Don't change all files at once
6. **Create test users** - One for each role to verify access
7. **Check logs** - Monitor activity_log table for issues

---

## 🆘 Common Rollback Scenarios

### Scenario 1: "I just want to start fresh"
**Solution:** Full database restore (Option A)

### Scenario 2: "RBAC broke something"
**Solution:** Emergency rollback SQL + comment out auth code

### Scenario 3: "I want to modify RBAC, not remove it"
**Solution:** Keep database, just modify permissions/roles

### Scenario 4: "I want to pause RBAC temporarily"
**Solution:** Comment out auth checks in code (Option C)

---

## 📞 Troubleshooting After Rollback

**Problem:** PHP errors about missing auth.php
- **Fix:** Remove all `require_once '../includes/auth.php';` lines

**Problem:** SQL errors about missing role_id column
- **Fix:** Search code for `role_id` and remove those references

**Problem:** Can't login after rollback
- **Fix:** Restore your original login system or create a simple one

**Problem:** Tables still exist after rollback
- **Fix:** Manually drop: `DROP TABLE IF EXISTS roles, permissions, role_permissions;`

**Problem:** Employee data is missing
- **Fix:** Restore from employee_backup_before_rbac table

---

## ✅ Final Checklist Before Deciding

Before you decide to keep or rollback RBAC, test these:

**Test Requirements:**
- [ ] Can MIT_STAFF access Users/Tests/Sections?
- [ ] Can LAB_MANAGER access everything except master data?
- [ ] Can LAB_MANAGER view activity logs?
- [ ] Can SECRETARY perform Stock In/Out?
- [ ] Can STAFF_IN_CHARGE add patients and results?
- [ ] Are users blocked from unauthorized pages (403 error)?
- [ ] Do permission checks work in POST actions?
- [ ] Are UI buttons hidden for actions without permission?
- [ ] Does login work smoothly?
- [ ] Are passwords secure (hashed)?

**If all tests pass:** Keep RBAC ✅
**If tests fail:** Debug or rollback ⏮️

---

## 🎯 Recommendation

**Best Practice Approach:**

1. ✅ Create full database backup
2. ✅ Implement RBAC on localhost/test environment
3. ✅ Test for 1-2 days with real workflows
4. ✅ Get feedback from users (if applicable)
5. ✅ Make decision: keep or rollback
6. ✅ If keeping: deploy to production with new backup
7. ✅ If rolling back: use rollback script + restore files

---

**You now have a complete safety net! Test confidently knowing you can always revert back.** 🛡️
