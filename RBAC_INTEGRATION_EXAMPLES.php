# ============================================================================
# RBAC Integration Examples
# Clinical Laboratory Management System
# ============================================================================
# 
# This file shows how to integrate RBAC into your existing module pages.
# Copy the relevant patterns to your actual module files.
# ============================================================================

## EXAMPLE 1: users.php (MIT_STAFF only - Full CRUD)
## ============================================================================

<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

// ============================================================================
// STEP 1: Protect the page - require login and specific permission
// ============================================================================
require_login();
require_permission('users.manage'); // Only MIT_STAFF has this permission

$page_title = 'User Management';
include '../includes/header.php';

// ============================================================================
// STEP 2: Handle form submissions with permission checks (defense in depth)
// ============================================================================
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Double-check permission even though page requires it
    // This protects against CSRF or session hijacking
    if (!has_permission('users.manage')) {
        die('Access denied');
    }
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $position = $_POST['position'];
            $role_id = $_POST['role_id'];
            
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare("INSERT INTO employee (username, password_hash, firstname, lastname, position, role_id, status_code, datetime_added) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("sssssi", $username, $password_hash, $firstname, $lastname, $position, $role_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">User added successfully!</div>';
                
                // Log the action
                $new_user_id = $stmt->insert_id;
                $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())");
                $activity_type = 'user_created';
                $description = "Created new user: $username (ID: $new_user_id)";
                $current_user_id = get_user_id();
                $log_stmt->bind_param("iss", $current_user_id, $activity_type, $description);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
        
        elseif ($_POST['action'] == 'update') {
            $employee_id = $_POST['employee_id'];
            $username = $_POST['username'];
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $position = $_POST['position'];
            $role_id = $_POST['role_id'];
            
            $stmt = $conn->prepare("UPDATE employee SET username=?, firstname=?, lastname=?, position=?, role_id=? WHERE employee_id=?");
            $stmt->bind_param("ssssii", $username, $firstname, $lastname, $position, $role_id, $employee_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">User updated successfully!</div>';
                
                // Log the action
                $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())");
                $activity_type = 'user_updated';
                $description = "Updated user: $username (ID: $employee_id)";
                $current_user_id = get_user_id();
                $log_stmt->bind_param("iss", $current_user_id, $activity_type, $description);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
        
        elseif ($_POST['action'] == 'delete') {
            $employee_id = $_POST['employee_id'];
            
            // Prevent self-deletion
            if ($employee_id == get_user_id()) {
                $message = '<div class="alert alert-danger">You cannot delete your own account!</div>';
            } else {
                // Soft delete (set status_code = 0)
                $stmt = $conn->prepare("UPDATE employee SET status_code = 0 WHERE employee_id=?");
                $stmt->bind_param("i", $employee_id);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">User deleted successfully!</div>';
                    
                    // Log the action
                    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())");
                    $activity_type = 'user_deleted';
                    $description = "Deleted user ID: $employee_id";
                    $current_user_id = get_user_id();
                    $log_stmt->bind_param("iss", $current_user_id, $activity_type, $description);
                    $log_stmt->execute();
                    $log_stmt->close();
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
                $stmt->close();
            }
        }
        
        elseif ($_POST['action'] == 'reset_password') {
            $employee_id = $_POST['employee_id'];
            $new_password = $_POST['new_password'];
            $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare("UPDATE employee SET password_hash=? WHERE employee_id=?");
            $stmt->bind_param("si", $password_hash, $employee_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Password reset successfully!</div>';
                
                // Log the action
                $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())");
                $activity_type = 'password_reset';
                $description = "Reset password for user ID: $employee_id";
                $current_user_id = get_user_id();
                $log_stmt->bind_param("iss", $current_user_id, $activity_type, $description);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }
}

// Get all users with their roles
$users = $conn->query("SELECT e.*, r.role_name, r.display_name as role_display 
                       FROM employee e 
                       LEFT JOIN roles r ON e.role_id = r.role_id 
                       WHERE e.status_code = 1 
                       ORDER BY e.lastname, e.firstname");

// Get all roles for dropdown
$roles = $conn->query("SELECT * FROM roles WHERE status_code = 1 ORDER BY display_name");
?>

<div class="container">
    <?php echo $message; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>👤 User Management</h2>
            <p style="color: #666; margin-top: 0.5rem;">
                <strong>Your Role:</strong> <?php echo htmlspecialchars($_SESSION['role_display']); ?>
                (<?php echo htmlspecialchars($_SESSION['role_name']); ?>)
            </p>
        </div>
        
        <!-- Add New User Form -->
        <form method="POST" style="margin-bottom: 2rem;">
            <input type="hidden" name="action" value="add">
            <h3>Add New User</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="firstname" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lastname" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role_id" class="form-control" required>
                        <option value="">Select Role</option>
                        <?php 
                        $roles->data_seek(0); // Reset pointer
                        while($role = $roles->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $role['role_id']; ?>">
                                <?php echo htmlspecialchars($role['display_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Add User</button>
        </form>
        
        <!-- Users List -->
        <h3>Users List</h3>
        <?php if ($users->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Role</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['employee_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($user['position']); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo htmlspecialchars($user['role_display'] ?? 'No Role'); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['datetime_added'])); ?></td>
                            <td class="table-actions">
                                <button class="btn btn-warning btn-sm" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                                <button class="btn btn-info btn-sm" onclick="resetPassword(<?php echo $user['employee_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">Reset Password</button>
                                <?php if ($user['employee_id'] != get_user_id()): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="employee_id" value="<?php echo $user['employee_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal (truncated for brevity - similar to your existing modals) -->

<?php include '../includes/footer.php'; ?>


## ============================================================================
## EXAMPLE 2: inventory.php (Multiple Roles - Different Permissions)
## ============================================================================

<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

// ============================================================================
// STEP 1: Require minimum permission to view the page
// ============================================================================
require_login();
require_permission('inventory.view'); // LAB_MANAGER, SECRETARY, STAFF_IN_CHARGE

$page_title = 'Inventory Management';
include '../includes/header.php';

// ============================================================================
// STEP 2: Check specific permissions for each action
// ============================================================================
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        
        // Stock In - LAB_MANAGER and SECRETARY only
        if ($_POST['action'] == 'stock_in') {
            if (!has_permission('inventory.stock_in')) {
                $message = '<div class="alert alert-danger">⛔ You do not have permission to add stock.</div>';
            } else {
                $item_id = intval($_POST['item_id']);
                $quantity = intval($_POST['quantity']);
                $supplier = $_POST['supplier'] ?? '';
                $reference_number = $_POST['reference_number'] ?? '';
                $performed_by = get_user_id();
                
                $stmt = $conn->prepare("INSERT INTO stock_in (item_id, quantity, supplier, reference_number, performed_by, transaction_date) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iissi", $item_id, $quantity, $supplier, $reference_number, $performed_by);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">✅ Stock added successfully!</div>';
                    
                    // Log activity
                    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())");
                    $activity_type = 'stock_in';
                    $description = "Added stock: Item ID $item_id, Quantity: $quantity";
                    $log_stmt->bind_param("iss", $performed_by, $activity_type, $description);
                    $log_stmt->execute();
                    $log_stmt->close();
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
                $stmt->close();
            }
        }
        
        // Stock Out - LAB_MANAGER and SECRETARY only
        elseif ($_POST['action'] == 'stock_out') {
            if (!has_permission('inventory.stock_out')) {
                $message = '<div class="alert alert-danger">⛔ You do not have permission to remove stock.</div>';
            } else {
                $item_id = intval($_POST['item_id']);
                $quantity = intval($_POST['quantity']);
                $performed_by = get_user_id();
                
                // Check stock availability
                $check = $conn->query("SELECT SUM(si.quantity) - COALESCE(SUM(so.quantity), 0) as current_stock 
                                       FROM stock_in si 
                                       LEFT JOIN stock_out so ON si.item_id = so.item_id 
                                       WHERE si.item_id = $item_id");
                $stock = $check->fetch_assoc();
                
                if ($stock['current_stock'] < $quantity) {
                    $message = '<div class="alert alert-danger">❌ Insufficient stock! Available: ' . $stock['current_stock'] . '</div>';
                } else {
                    $stmt = $conn->prepare("INSERT INTO stock_out (item_id, quantity, performed_by, transaction_date) VALUES (?, ?, ?, NOW())");
                    $stmt->bind_param("iii", $item_id, $quantity, $performed_by);
                    
                    if ($stmt->execute()) {
                        $message = '<div class="alert alert-success">✅ Stock removed successfully!</div>';
                        
                        // Log activity
                        $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())");
                        $activity_type = 'stock_out';
                        $description = "Removed stock: Item ID $item_id, Quantity: $quantity";
                        $log_stmt->bind_param("iss", $performed_by, $activity_type, $description);
                        $log_stmt->execute();
                        $log_stmt->close();
                    } else {
                        $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                    }
                    $stmt->close();
                }
            }
        }
        
        // Record Usage - ALL roles with inventory.view can record usage
        elseif ($_POST['action'] == 'usage') {
            if (!has_permission('inventory.usage')) {
                $message = '<div class="alert alert-danger">⛔ You do not have permission to record usage.</div>';
            } else {
                $item_id = intval($_POST['item_id']);
                $quantity = intval($_POST['quantity']);
                $section_id = intval($_POST['section_id']);
                $performed_by = get_user_id();
                
                $stmt = $conn->prepare("INSERT INTO stock_usage (item_id, quantity, section_id, performed_by, usage_date) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiii", $item_id, $quantity, $section_id, $performed_by);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">✅ Usage recorded successfully!</div>';
                    
                    // Log activity
                    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())");
                    $activity_type = 'stock_usage';
                    $description = "Recorded usage: Item ID $item_id, Quantity: $quantity";
                    $log_stmt->bind_param("iss", $performed_by, $activity_type, $description);
                    $log_stmt->execute();
                    $log_stmt->close();
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
                $stmt->close();
            }
        }
    }
}

// Get inventory data
$inventory = $conn->query("SELECT * FROM v_current_stock ORDER BY item_name");
$items = $conn->query("SELECT * FROM item WHERE status_code = 1 ORDER BY label");
$sections = $conn->query("SELECT * FROM section ORDER BY label");
?>

<div class="container">
    <?php echo $message; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>📊 Inventory Management</h2>
            <p style="color: #666;">
                <strong>Logged in as:</strong> <?php echo get_user_name(); ?> 
                (<?php echo htmlspecialchars($_SESSION['role_display']); ?>)
            </p>
        </div>
        
        <!-- ================================================================ -->
        <!-- STEP 3: Show/Hide action forms based on permissions -->
        <!-- ================================================================ -->
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            
            <!-- Stock In Form - Only if user has permission -->
            <?php if (has_permission('inventory.stock_in')): ?>
            <div style="border: 2px solid #4CAF50; border-radius: 8px; padding: 1rem; background: linear-gradient(135deg, #f0fff4 0%, #e8f5e9 100%);">
                <h4 style="color: #2e7d32; margin-bottom: 1rem;">📥 Stock In</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="stock_in">
                    
                    <div class="form-group">
                        <label>Item *</label>
                        <select name="item_id" class="form-control" required>
                            <option value="">Select Item</option>
                            <?php 
                            $items->data_seek(0);
                            while($item = $items->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $item['item_id']; ?>">
                                    <?php echo htmlspecialchars($item['label']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label>Supplier</label>
                        <input type="text" name="supplier" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Reference #</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                    
                    <button type="submit" class="btn btn-success" style="width: 100%;">Add Stock</button>
                </form>
            </div>
            <?php else: ?>
            <div style="border: 2px dashed #ccc; border-radius: 8px; padding: 2rem; text-align: center; color: #999;">
                <p>🔒 Stock In</p>
                <small>Requires permission</small>
            </div>
            <?php endif; ?>
            
            <!-- Stock Out Form - Only if user has permission -->
            <?php if (has_permission('inventory.stock_out')): ?>
            <div style="border: 2px solid #f44336; border-radius: 8px; padding: 1rem; background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);">
                <h4 style="color: #c62828; margin-bottom: 1rem;">📤 Stock Out</h4>
                <form method="POST" onsubmit="return confirmStockOut();">
                    <input type="hidden" name="action" value="stock_out">
                    
                    <div class="form-group">
                        <label>Item *</label>
                        <select name="item_id" class="form-control" required>
                            <option value="">Select Item</option>
                            <?php 
                            $items->data_seek(0);
                            while($item = $items->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $item['item_id']; ?>">
                                    <?php echo htmlspecialchars($item['label']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                    
                    <button type="submit" class="btn btn-danger" style="width: 100%;">Remove Stock</button>
                </form>
            </div>
            <?php else: ?>
            <div style="border: 2px dashed #ccc; border-radius: 8px; padding: 2rem; text-align: center; color: #999;">
                <p>🔒 Stock Out</p>
                <small>Requires permission</small>
            </div>
            <?php endif; ?>
            
            <!-- Record Usage Form - If user has permission -->
            <?php if (has_permission('inventory.usage')): ?>
            <div style="border: 2px solid #2196F3; border-radius: 8px; padding: 1rem; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                <h4 style="color: #1565c0; margin-bottom: 1rem;">📝 Record Usage</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="usage">
                    
                    <div class="form-group">
                        <label>Item *</label>
                        <select name="item_id" class="form-control" required>
                            <option value="">Select Item</option>
                            <?php 
                            $items->data_seek(0);
                            while($item = $items->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $item['item_id']; ?>">
                                    <?php echo htmlspecialchars($item['label']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label>Section *</label>
                        <select name="section_id" class="form-control" required>
                            <option value="">Select Section</option>
                            <?php while($section = $sections->fetch_assoc()): ?>
                                <option value="<?php echo $section['section_id']; ?>">
                                    <?php echo htmlspecialchars($section['label']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Record Usage</button>
                </form>
            </div>
            <?php else: ?>
            <div style="border: 2px dashed #ccc; border-radius: 8px; padding: 2rem; text-align: center; color: #999;">
                <p>🔒 Record Usage</p>
                <small>Requires permission</small>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Inventory Table (Everyone with inventory.view can see this) -->
        <h3>Current Inventory Levels</h3>
        <?php if ($inventory->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Section</th>
                        <th>Current Stock</th>
                        <th>Unit</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($inv = $inventory->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inv['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($inv['section_name']); ?></td>
                            <td><?php echo $inv['current_stock']; ?></td>
                            <td><?php echo htmlspecialchars($inv['unit']); ?></td>
                            <td>
                                <?php if ($inv['stock_status'] == 'IN STOCK'): ?>
                                    <span class="badge badge-success">✅ IN STOCK</span>
                                <?php elseif ($inv['stock_status'] == 'LOW STOCK'): ?>
                                    <span class="badge badge-warning">🟠 LOW STOCK</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">🔴 OUT OF STOCK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No inventory data available.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmStockOut() {
    return confirm('Are you sure you want to remove stock? This action will be logged.');
}
</script>

<?php include '../includes/footer.php'; ?>


## ============================================================================
## EXAMPLE 3: logs.php (LAB_MANAGER only - View Only)
## ============================================================================

<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

// ============================================================================
// STEP 1: Strict protection - ONLY LAB_MANAGER can access
// ============================================================================
require_login();
require_permission('logs.view'); // Only LAB_MANAGER has this

$page_title = 'Activity Logs';
include '../includes/header.php';

// ============================================================================
// STEP 2: NO write operations - this is read-only
// ============================================================================

// Filters
$filter_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$filter_activity = isset($_GET['activity_type']) ? $_GET['activity_type'] : null;
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;

// Build query
$where = ["1=1"];
$params = [];
$types = "";

if ($filter_user) {
    $where[] = "al.user_id = ?";
    $params[] = $filter_user;
    $types .= "i";
}

if ($filter_activity) {
    $where[] = "al.activity_type = ?";
    $params[] = $filter_activity;
    $types .= "s";
}

if ($filter_date_from) {
    $where[] = "DATE(al.created_at) >= ?";
    $params[] = $filter_date_from;
    $types .= "s";
}

if ($filter_date_to) {
    $where[] = "DATE(al.created_at) <= ?";
    $params[] = $filter_date_to;
    $types .= "s";
}

$where_clause = implode(" AND ", $where);

$query = "SELECT al.*, 
                 CONCAT(COALESCE(e.firstname, 'System'), ' ', COALESCE(e.lastname, '')) as user_name,
                 e.position
          FROM activity_log al
          LEFT JOIN employee e ON al.user_id = e.employee_id
          WHERE $where_clause
          ORDER BY al.created_at DESC
          LIMIT 500";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $logs = $stmt->get_result();
} else {
    $logs = $conn->query($query);
}

// Get unique activity types for filter
$activity_types = $conn->query("SELECT DISTINCT activity_type FROM activity_log ORDER BY activity_type");

// Get users for filter
$users = $conn->query("SELECT employee_id, firstname, lastname FROM employee WHERE status_code = 1 ORDER BY lastname, firstname");
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>📋 Activity Logs</h2>
            <p style="color: #666;">
                <strong>Access Level:</strong> Laboratory Manager Only<br>
                <strong>Logged in as:</strong> <?php echo get_user_name(); ?>
            </p>
        </div>
        
        <!-- Filters -->
        <form method="GET" style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <h4>Filter Logs</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>User</label>
                    <select name="user_id" class="form-control">
                        <option value="">All Users</option>
                        <?php while($user = $users->fetch_assoc()): ?>
                            <option value="<?php echo $user['employee_id']; ?>" <?php echo ($filter_user == $user['employee_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Activity Type</label>
                    <select name="activity_type" class="form-control">
                        <option value="">All Types</option>
                        <?php while($type = $activity_types->fetch_assoc()): ?>
                            <option value="<?php echo $type['activity_type']; ?>" <?php echo ($filter_activity == $type['activity_type']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['activity_type']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filter_date_from ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filter_date_to ?? ''); ?>">
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="logs.php" class="btn btn-secondary">Clear Filters</a>
            </div>
        </form>
        
        <!-- Logs Table -->
        <h3>Activity Log Entries (Last 500)</h3>
        <?php if ($logs->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Activity Type</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $log['log_id']; ?></td>
                            <td><?php echo date('M d, Y h:i:s A', strtotime($log['created_at'])); ?></td>
                            <td>
                                <?php echo htmlspecialchars($log['user_name']); ?>
                                <?php if ($log['position']): ?>
                                    <br><small style="color: #666;"><?php echo htmlspecialchars($log['position']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo htmlspecialchars($log['activity_type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($log['description']); ?></td>
                            <td><small><?php echo htmlspecialchars($log['ip_address']); ?></small></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No activity logs found matching your filters.</p>
        <?php endif; ?>
        
        <p style="margin-top: 2rem; padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
            <strong>ℹ️ Note:</strong> Activity logs are automatically recorded for security-sensitive operations 
            such as logins, user management, stock movements, and result modifications. Logs are retained for 
            compliance and audit purposes.
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


## ============================================================================
## NOTES
## ============================================================================
# 
# 1. Always include auth.php at the top of each page
# 2. Use require_permission() to protect entire pages
# 3. Use has_permission() to check individual actions
# 4. Show/hide UI elements based on permissions
# 5. Log all sensitive operations to activity_log
# 6. Use get_user_id() to track who performed actions
# 7. Never trust client-side checks - always validate server-side
# 
## ============================================================================
