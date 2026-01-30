<?php
require_once '../db_connection.php';
require_once '../includes/auth.php';

// Handle form submissions BEFORE including header
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'stock_in') {
            $item_ids = $_POST['item_id'];
            $quantities = $_POST['quantity'];
            $performed_by = $_SESSION['user_id'] ?? 1;
            $supplier = !empty($_POST['supplier']) ? $_POST['supplier'] : NULL;
            $reference_number = !empty($_POST['reference_number']) ? $_POST['reference_number'] : NULL;
            $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;
            $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;
            
            $success_count = 0;
            $error_count = 0;
            $added_items = [];
            
            // Loop through all items
            for ($i = 0; $i < count($item_ids); $i++) {
                $item_id = intval($item_ids[$i]);
                $quantity = intval($quantities[$i]);
                
                if ($item_id > 0 && $quantity > 0) {
                    $stmt = $conn->prepare("INSERT INTO stock_in (item_id, quantity, performed_by, supplier, reference_number, expiry_date, remarks, datetime_added) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("iiissss", $item_id, $quantity, $performed_by, $supplier, $reference_number, $expiry_date, $remarks);
                    
                    if ($stmt->execute()) {
                        $item_name = $conn->query("SELECT label FROM item WHERE item_id = $item_id")->fetch_assoc()['label'];
                        $added_items[] = "$quantity unit(s) of $item_name";
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                    $stmt->close();
                }
            }
            
            if ($success_count > 0) {
                $items_list = implode(', ', $added_items);
                log_activity($conn, get_user_id(), "Added stock (bulk): $items_list", 1);
                $_SESSION['success_message'] = 'Successfully added ' . $success_count . ' item(s) to stock!';
                if ($error_count > 0) {
                    $_SESSION['warning_message'] = $error_count . ' item(s) failed to add.';
                }
                header('Location: inventory.php');
                exit();
            } else {
                $message = '<div class="alert alert-danger">Error adding stock items.</div>';
            }
        } elseif ($_POST['action'] == 'stock_out') {
            $item_ids = $_POST['item_id'];
            $quantities = $_POST['quantity'];
            $performed_by = $_SESSION['user_id'] ?? 1;
            $reference_number = !empty($_POST['reference_number']) ? $_POST['reference_number'] : NULL;
            $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : 'Stock out';
            
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            $removed_items = [];
            
            // Loop through all items
            for ($i = 0; $i < count($item_ids); $i++) {
                $item_id = intval($item_ids[$i]);
                $quantity = intval($quantities[$i]);
                
                if ($item_id > 0 && $quantity > 0) {
                    // Check current stock to prevent negative
                    $stock_check = $conn->query("SELECT COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as current_stock 
                                                 FROM item i 
                                                 LEFT JOIN stock_in si ON i.item_id = si.item_id 
                                                 LEFT JOIN stock_out so ON i.item_id = so.item_id 
                                                 WHERE i.item_id = $item_id 
                                                 GROUP BY i.item_id")->fetch_assoc();
                    
                    $current_stock = $stock_check ? $stock_check['current_stock'] : 0;
                    $item_name = $conn->query("SELECT label FROM item WHERE item_id = $item_id")->fetch_assoc()['label'];
                    
                    if ($current_stock < $quantity) {
                        $errors[] = "$item_name: Insufficient stock (Available: $current_stock)";
                        $error_count++;
                    } else {
                        $stmt = $conn->prepare("INSERT INTO stock_out (item_id, quantity, performed_by, reference_number, remarks, datetime_added) VALUES (?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("iiiss", $item_id, $quantity, $performed_by, $reference_number, $remarks);
                        
                        if ($stmt->execute()) {
                            $removed_items[] = "$quantity unit(s) of $item_name";
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                        $stmt->close();
                    }
                }
            }
            
            if ($success_count > 0) {
                $items_list = implode(', ', $removed_items);
                log_activity($conn, get_user_id(), "Removed stock (bulk): $items_list", 1);
                $message = '<div class="alert alert-success">Successfully removed ' . $success_count . ' item(s) from stock!</div>';
            }
            if ($error_count > 0) {
                $message .= '<div class="alert alert-danger">Failed: ' . implode(', ', $errors) . '</div>';
            }
        } elseif ($_POST['action'] == 'stock_usage') {
            $item_ids = $_POST['item_id'];
            $quantities = $_POST['quantity'];
            $employee_id = $_POST['employee_id'];
            $purpose = $_POST['purpose'];
            $or_number = $_POST['or_number'];
            
            // Get employee details
            $emp = $conn->query("SELECT firstname, middlename, lastname FROM employee WHERE employee_id = $employee_id")->fetch_assoc();
            
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            $used_items = [];
            $performed_by = $_SESSION['user_id'] ?? 1;
            
            // Loop through all items
            for ($i = 0; $i < count($item_ids); $i++) {
                $item_id = intval($item_ids[$i]);
                $quantity = intval($quantities[$i]);
                
                if ($item_id > 0 && $quantity > 0) {
                    // Check current stock to prevent negative
                    $stock_check = $conn->query("SELECT COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as current_stock 
                                                 FROM item i 
                                                 LEFT JOIN stock_in si ON i.item_id = si.item_id 
                                                 LEFT JOIN stock_out so ON i.item_id = so.item_id 
                                                 WHERE i.item_id = $item_id 
                                                 GROUP BY i.item_id")->fetch_assoc();
                    
                    $current_stock = $stock_check ? $stock_check['current_stock'] : 0;
                    $item_name = $conn->query("SELECT label FROM item WHERE item_id = $item_id")->fetch_assoc()['label'];
                    
                    if ($current_stock < $quantity) {
                        $errors[] = "$item_name: Insufficient stock (Available: $current_stock)";
                        $error_count++;
                    } else {
                        $stmt = $conn->prepare("INSERT INTO stock_usage (item_id, quantity, employee_id, firstname, middlename, lastname, purpose, or_number, datetime_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("iiisssss", $item_id, $quantity, $employee_id, $emp['firstname'], $emp['middlename'], $emp['lastname'], $purpose, $or_number);
                        
                        if ($stmt->execute()) {
                            // Also create stock_out record
                            $conn->query("INSERT INTO stock_out (item_id, quantity, performed_by, remarks, datetime_added) VALUES ($item_id, $quantity, $performed_by, 'Used by: $or_number - $purpose', NOW())");
                            $used_items[] = "$quantity unit(s) of $item_name";
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                        $stmt->close();
                    }
                }
            }
            
            if ($success_count > 0) {
                $items_list = implode(', ', $used_items);
                $user_name = $emp['firstname'] . ' ' . $emp['lastname'];
                log_activity($conn, get_user_id(), "Recorded stock usage (bulk): $items_list by $user_name (OR: $or_number)", 1);
                $message = '<div class="alert alert-success">Successfully recorded usage of ' . $success_count . ' item(s)!</div>';
            }
            if ($error_count > 0) {
                $message .= '<div class="alert alert-danger">Failed: ' . implode(', ', $errors) . '</div>';
            }
        }
    }
}

// Now include header after POST processing
$page_title = 'Inventory Management';
include '../includes/header.php';

// Get low stock alerts
$low_stock_alerts = $conn->query("SELECT * FROM v_current_stock WHERE stock_status IN ('out_of_stock', 'low_stock') ORDER BY current_stock");

// Get expiry alerts (items expiring within 30 days)
$expiry_alerts = $conn->query("SELECT * FROM v_stock_expiry WHERE expiry_status IN ('expired', 'expiring_soon') ORDER BY expiry_date");

// Get summary statistics
$stats_query = "SELECT 
    COUNT(DISTINCT i.item_id) as total_items,
    SUM(CASE WHEN (COALESCE(si.total_in, 0) - COALESCE(so.total_out, 0)) <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
    SUM(CASE WHEN (COALESCE(si.total_in, 0) - COALESCE(so.total_out, 0)) > 0 
         AND (COALESCE(si.total_in, 0) - COALESCE(so.total_out, 0)) <= i.reorder_level THEN 1 ELSE 0 END) as low_stock_count
FROM item i
LEFT JOIN (SELECT item_id, SUM(quantity) as total_in FROM stock_in GROUP BY item_id) si ON i.item_id = si.item_id
LEFT JOIN (SELECT item_id, SUM(quantity) as total_out FROM stock_out GROUP BY item_id) so ON i.item_id = so.item_id
WHERE i.is_deleted = 0";
$stats = $conn->query($stats_query)->fetch_assoc();

// Get today's statistics
$today = date('Y-m-d');
$today_stats = [
    'items_added' => $conn->query("SELECT COUNT(DISTINCT item_id) as count FROM stock_in WHERE DATE(datetime_added) = '$today'")->fetch_assoc()['count'],
    'items_out' => $conn->query("SELECT COUNT(DISTINCT item_id) as count FROM stock_out WHERE DATE(datetime_added) = '$today'")->fetch_assoc()['count'],
    'low_stock_today' => $conn->query("SELECT COUNT(DISTINCT i.item_id) as count FROM item i 
        LEFT JOIN (SELECT item_id, SUM(quantity) as total_in FROM stock_in GROUP BY item_id) si ON i.item_id = si.item_id
        LEFT JOIN (SELECT item_id, SUM(quantity) as total_out FROM stock_out GROUP BY item_id) so ON i.item_id = so.item_id
        WHERE i.is_deleted = 0 AND (COALESCE(si.total_in, 0) - COALESCE(so.total_out, 0)) > 0 
        AND (COALESCE(si.total_in, 0) - COALESCE(so.total_out, 0)) <= i.reorder_level")->fetch_assoc()['count'],
    'expiring_today' => $conn->query("SELECT COUNT(*) as count FROM stock_in WHERE DATE(expiry_date) = '$today'")->fetch_assoc()['count']
];

$expiring_count = $expiry_alerts ? $expiry_alerts->num_rows : 0;

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_section = isset($_GET['section']) ? $_GET['section'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Calculate inventory levels
$inventory_query = "SELECT i.item_id, i.label, i.unit, i.reorder_level,
                   COALESCE(SUM(si.quantity), 0) as total_in,
                   COALESCE(SUM(so.quantity), 0) as total_out,
                   COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as current_stock,
                   s.label as section_name
                   FROM item i
                   INNER JOIN stock_in si ON i.item_id = si.item_id
                   LEFT JOIN stock_out so ON i.item_id = so.item_id
                   LEFT JOIN section s ON i.section_id = s.section_id
                   WHERE i.is_deleted = 0";

if ($search) {
    $inventory_query .= " AND i.label LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if ($filter_section) {
    $inventory_query .= " AND i.section_id = " . intval($filter_section);
}

$inventory_query .= " GROUP BY i.item_id";

// Apply status filter after grouping
$inventory_temp = $conn->query($inventory_query);
$filtered_inventory = [];

while ($item = $inventory_temp->fetch_assoc()) {
    $stock = $item['current_stock'];
    $reorder = $item['reorder_level'];
    
    $status = 'in_stock';
    if ($stock <= 0) {
        $status = 'out_of_stock';
    } elseif ($reorder && $stock <= $reorder) {
        $status = 'low_stock';
    }
    
    if (!$filter_status || $filter_status == $status) {
        $item['stock_status'] = $status;
        $filtered_inventory[] = $item;
    }
}

// Calculate inventory levels
$inventory_query = "SELECT i.item_id, i.label, i.unit, i.reorder_level,
                   COALESCE(SUM(si.quantity), 0) as total_in,
                   COALESCE(SUM(so.quantity), 0) as total_out,
                   COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as current_stock,
                   s.label as section_name
                   FROM item i
                   LEFT JOIN stock_in si ON i.item_id = si.item_id
                   LEFT JOIN stock_out so ON i.item_id = so.item_id
                   LEFT JOIN section s ON i.section_id = s.section_id
                   GROUP BY i.item_id
                   ORDER BY i.label";
$inventory = $conn->query($inventory_query);

// Get recent stock movements
$recent_movements = $conn->query("SELECT * FROM v_stock_movements ORDER BY datetime_added DESC LIMIT 20");
?>

<div class="container">
    <?php 
    // Display session messages
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['warning_message'])) {
        echo '<div class="alert alert-warning">' . $_SESSION['warning_message'] . '</div>';
        unset($_SESSION['warning_message']);
    }
    echo $message; 
    ?>
    
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-warehouse"></i> Inventory & Supplies Management</h2>
        </div>
        
        <!-- Summary Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
                <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Total Items</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 2rem; font-weight: bold; color: #667eea;"><?php echo $stats['total_items']; ?></div>
                    <div style="color: #28a745; font-size: 1.25rem; font-weight: 600;">
                        <i class="fas fa-arrow-up" style="font-size: 0.9rem;"></i> <?php echo $today_stats['items_added']; ?>
                    </div>
                </div>
                <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                    Added today: <?php echo $today_stats['items_added']; ?> items
                </div>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
                <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Out of Stock</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 2rem; font-weight: bold; color: #dc3545;"><?php echo $stats['out_of_stock_count']; ?></div>
                    <div style="color: #dc3545; font-size: 1.25rem; font-weight: 600;">
                        <i class="fas fa-exclamation-circle" style="font-size: 0.9rem;"></i> <?php echo $today_stats['items_out']; ?>
                    </div>
                </div>
                <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                    Stock out today: <?php echo $today_stats['items_out']; ?> items
                </div>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
                <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Low Stock</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 2rem; font-weight: bold; color: #ffc107;"><?php echo $stats['low_stock_count']; ?></div>
                    <div style="color: #ffc107; font-size: 1.25rem; font-weight: 600;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 0.9rem;"></i> <?php echo $today_stats['low_stock_today']; ?>
                    </div>
                </div>
                <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                    Low stock alerts: <?php echo $today_stats['low_stock_today']; ?> items
                </div>
            </div>
            
            <?php if ($expiring_count > 0): ?>
            <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
                <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Expiring Soon</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 2rem; font-weight: bold; color: #ff6b6b;"><?php echo $expiring_count; ?></div>
                    <div style="color: #ff6b6b; font-size: 1.25rem; font-weight: 600;">
                        <i class="fas fa-clock" style="font-size: 0.9rem;"></i> <?php echo $today_stats['expiring_today']; ?>
                    </div>
                </div>
                <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                    Expiring today: <?php echo $today_stats['expiring_today']; ?> items
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <a href="inventory.php" class="btn btn-primary">Stock Management</a>
            <a href="inventory_reports.php" class="btn btn-secondary"><i class="fas fa-chart-bar"></i> View Reports</a>
        </div>
    </div>
    
    <!-- Expiry Alerts -->
    <?php if ($expiry_alerts && $expiry_alerts->num_rows > 0): ?>
    <div class="card">
        <div class="card-header">
            <h2>⏰ Expiry Alerts</h2>
        </div>
        
        <table class="table">
            <thead style="background-color: #C2185B !important; color: white !important;">
                <tr style="background-color: #C2185B !important;">
                    <th style="background-color: #C2185B !important; color: white !important; padding: 12px;">Item</th>
                    <th style="background-color: #C2185B !important; color: white !important; padding: 12px;">Section</th>
                    <th style="background-color: #C2185B !important; color: white !important; padding: 12px;">Quantity</th>
                    <th style="background-color: #C2185B !important; color: white !important; padding: 12px;">Expiry Date</th>
                    <th style="background-color: #C2185B !important; color: white !important; padding: 12px;">Days Left</th>
                    <th style="background-color: #C2185B !important; color: white !important; padding: 12px;">Supplier</th>
                    <th style="background-color: #C2185B !important; color: white !important; padding: 12px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($alert = $expiry_alerts->fetch_assoc()): ?>
                    <tr style="background-color: #FFE4E9; color: black;">
                        <td style="color: black;"><?php echo htmlspecialchars($alert['item_name']); ?></td>
                        <td style="color: black;"><?php echo htmlspecialchars($alert['section_name']); ?></td>
                        <td style="color: black;"><?php echo $alert['quantity']; ?></td>
                        <td style="color: black;"><?php echo date('M d, Y', strtotime($alert['expiry_date'])); ?></td>
                        <td style="color: black;"><?php echo $alert['days_until_expiry']; ?> days</td>
                        <td style="color: black;"><?php echo htmlspecialchars($alert['supplier'] ?? 'N/A'); ?></td>
                        <td style="color: black;">
                            <?php if ($alert['expiry_status'] == 'expired'): ?>
                                <span style="color: red; font-weight: bold;">EXPIRED</span>
                            <?php else: ?>
                                <span style="color: orange; font-weight: bold;">EXPIRING SOON</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- Low Stock Alerts -->
    <?php if ($low_stock_alerts && $low_stock_alerts->num_rows > 0): ?>
    <div class="card" style="border-left: 4px solid #f44336;">
        <div class="card-header" style="background-color: #ffebee;">
            <h2><i class="fas fa-exclamation-triangle"></i> Low Stock Alerts</h2>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Section</th>
                    <th>Current Stock</th>
                    <th>Reorder Level</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($alert = $low_stock_alerts->fetch_assoc()): 
                    $status_color = '';
                    if ($alert['stock_status'] == 'out_of_stock') $status_color = 'style="background-color: #ffcdd2;"';
                    elseif ($alert['stock_status'] == 'low_stock') $status_color = 'style="background-color: #ffe0b2;"';
                ?>
                    <tr <?php echo $status_color; ?>>
                        <td><?php echo htmlspecialchars($alert['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($alert['section_name']); ?></td>
                        <td style="font-weight: bold;"><?php echo $alert['current_stock']; ?></td>
                        <td><?php echo $alert['reorder_level']; ?></td>
                        <td><?php echo htmlspecialchars($alert['unit']); ?></td>
                        <td><?php echo strtoupper(str_replace('_', ' ', $alert['stock_status'])); ?></td>
                        <td><button class="btn btn-sm btn-success" onclick="quickStockIn(<?php echo $alert['item_id']; ?>, '<?php echo htmlspecialchars($alert['item_name']); ?>')">Restock</button></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: end; border-bottom: 2px solid #dee2e6;">
                <div style="display: flex; gap: 0;">
                    <button class="tab-btn active" onclick="switchTab('stock-in')" style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid #48bb78; color: #48bb78; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-plus-circle"></i> Stock In
                    </button>
                    <button class="tab-btn" onclick="switchTab('stock-out')" style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent; color: #666; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-minus-circle"></i> Stock Out
                    </button>
                    <button class="tab-btn" onclick="switchTab('stock-usage')" style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent; color: #666; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-clipboard-list"></i> Stock Usage
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tab Content -->
        <div style="padding: 2rem;">
            <!-- Stock In Form -->
            <div id="stock-in" class="tab-content" style="display: block;">
                <form method="POST" id="stockInForm">
                    <input type="hidden" name="action" value="stock_in">
                    
                    <div id="stockInItems">
                        <div class="stock-item-row" style="margin-bottom: 1rem;">
                            <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Item *</label>
                                    <select name="item_id[]" class="form-control" required>
                                        <option value="">Select Item</option>
                                        <?php 
                                        $items = $conn->query("SELECT * FROM item WHERE is_deleted = 0 ORDER BY label");
                                        while($item = $items->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['label']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Quantity *</label>
                                    <input type="number" name="quantity[]" class="form-control" required min="1" placeholder="0">
                                </div>
                                
                                <div>
                                    <button type="button" class="btn btn-success" onclick="addStockInRow()" title="Add another item" style="padding: 0.5rem 1rem;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Supplier</label>
                            <input type="text" name="supplier" class="form-control" placeholder="Supplier name">
                        </div>
                        
                        <div class="form-group">
                            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Reference No.</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Invoice/PO/DR number">
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional notes">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success" style="width: 100%; padding: 0.75rem; font-weight: 600;"><i class="fas fa-check"></i> Add Stock</button>
                </form>
            </div>
            
            <!-- Stock Out Form -->
            <div id="stock-out" class="tab-content" style="display: none;">
                <form method="POST" id="stockOutForm" onsubmit="return confirmStockOut()">
                    <input type="hidden" name="action" value="stock_out">
                    
                    <div id="stockOutItems">
                        <div class="stock-item-row" style="margin-bottom: 1rem;">
                            <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Item *</label>
                                    <select name="item_id[]" class="form-control" required onchange="handleItemChange(this, 'stockOutItems')">
                                        <option value="">Select Item</option>
                                        <?php 
                                        $items = $conn->query("SELECT i.*, 
                                                              COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as available_stock
                                                              FROM item i
                                                              LEFT JOIN stock_in si ON i.item_id = si.item_id
                                                              LEFT JOIN stock_out so ON i.item_id = so.item_id
                                                              WHERE i.status_code = 1
                                                              GROUP BY i.item_id
                                                              HAVING available_stock > 0
                                                              ORDER BY i.label");
                                        while($item = $items->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['label']); ?> (Stock: <?php echo $item['available_stock']; ?>)</option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Quantity *</label>
                                    <input type="number" name="quantity[]" class="form-control" required min="1" placeholder="0">
                                    <small class="stock-available" style="color: #48bb78; font-size: 0.75rem; display: block; margin-top: 0.25rem;"></small>
                                </div>
                                
                                <div>
                                    <button type="button" class="btn btn-success" onclick="addStockOutRow()" title="Add another item" style="padding: 0.5rem 1rem;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Reference No.</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Requisition number">
                        </div>
                        
                        <div class="form-group">
                            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Remarks *</label>
                            <input type="text" name="remarks" class="form-control" required placeholder="Reason for removal">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-danger" style="width: 100%; padding: 0.75rem; font-weight: 600;"><i class="fas fa-check"></i> Remove Stock</button>
                </form>
            </div>
            
            <!-- Stock Usage Form -->
            <div id="stock-usage" class="tab-content" style="display: none;">
                <form method="POST" id="usageForm" onsubmit="return confirmUsage()">
                    <input type="hidden" name="action" value="stock_usage">
                    
                    <div id="stockUsageItems">
                        <div class="stock-item-row" style="margin-bottom: 1rem;">
                            <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Item *</label>
                                    <select name="item_id[]" class="form-control" required onchange="handleItemChange(this, 'stockUsageItems')">
                                        <option value="">Select Item</option>
                                        <?php 
                                        $items = $conn->query("SELECT i.*, 
                                                              COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as available_stock
                                                              FROM item i
                                                              LEFT JOIN stock_in si ON i.item_id = si.item_id
                                                              LEFT JOIN stock_out so ON i.item_id = so.item_id
                                                              WHERE i.status_code = 1
                                                              GROUP BY i.item_id
                                                              HAVING available_stock > 0
                                                              ORDER BY i.label");
                                        while($item = $items->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['label']); ?> (Stock: <?php echo $item['available_stock']; ?>)</option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Quantity *</label>
                                    <input type="number" name="quantity[]" class="form-control" required min="1" value="1" placeholder="0">
                                    <small class="stock-available" style="color: #48bb78; font-size: 0.75rem; display: block; margin-top: 0.25rem;"></small>
                                </div>
                                
                                <div>
                                    <button type="button" class="btn btn-success" onclick="addStockUsageRow()" title="Add another item" style="padding: 0.5rem 1rem;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Employee *</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                <?php 
                                $employees = $conn->query("SELECT * FROM employee WHERE status_code = 1 AND is_deleted = 0");
                                while($emp = $employees->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Purpose *</label>
                            <input type="text" name="purpose" class="form-control" required placeholder="Purpose of use">
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">OR Number</label>
                            <input type="text" name="or_number" class="form-control" placeholder="Official receipt number">
                        </div>
                        <div></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 600;"><i class="fas fa-check"></i> Record Usage</button>
                </form>
            </div>
        </div>
    </div>
        
    <!-- Current Inventory -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h2><i class="fas fa-boxes"></i> Current Inventory Levels</h2>
        </div>
        
        <!-- Search and Filters -->
        <form method="GET" style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Search by Item Name</label>
                    <input type="text" name="search" class="form-control" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Filter by Section</label>
                    <select name="section" class="form-control">
                        <option value="">All Sections</option>
                        <?php 
                        $sections_filter = $conn->query("SELECT * FROM section WHERE is_deleted = 0 ORDER BY label");
                        while($section = $sections_filter->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $section['section_id']; ?>" <?php echo $filter_section == $section['section_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($section['label']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Filter by Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="in_stock" <?php echo $filter_status == 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                        <option value="low_stock" <?php echo $filter_status == 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="out_of_stock" <?php echo $filter_status == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Rows per page</label>
                    <select name="rows_per_page" class="form-control" onchange="this.form.submit()">
                        <option value="10" <?php echo (!isset($_GET['rows_per_page']) || $_GET['rows_per_page'] == 10) ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo (isset($_GET['rows_per_page']) && $_GET['rows_per_page'] == 25) ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo (isset($_GET['rows_per_page']) && $_GET['rows_per_page'] == 50) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo (isset($_GET['rows_per_page']) && $_GET['rows_per_page'] == 100) ? 'selected' : ''; ?>>100</option>
                        <option value="all" <?php echo (isset($_GET['rows_per_page']) && $_GET['rows_per_page'] == 'all') ? 'selected' : ''; ?>>All</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <?php if ($search || $filter_section || $filter_status): ?>
                        <a href="inventory.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        
        <?php 
        // Pagination logic
        $rows_per_page = isset($_GET['rows_per_page']) ? $_GET['rows_per_page'] : 10;
        $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        
        $total_items = count($filtered_inventory);
        
        if ($rows_per_page == 'all') {
            $paginated_inventory = $filtered_inventory;
            $total_pages = 1;
        } else {
            $rows_per_page = intval($rows_per_page);
            $total_pages = ceil($total_items / $rows_per_page);
            $offset = ($current_page - 1) * $rows_per_page;
            $paginated_inventory = array_slice($filtered_inventory, $offset, $rows_per_page);
        }
        ?>
        
        <?php if (count($filtered_inventory) > 0): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <strong>Showing <?php echo count($paginated_inventory); ?> of <?php echo $total_items; ?> items</strong>
                </div>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Section</th>
                        <th>Unit</th>
                        <th>Total In</th>
                        <th>Total Out</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($paginated_inventory as $item): 
                        $stock = $item['current_stock'];
                        $reorder = $item['reorder_level'];
                        $status = $item['stock_status'];
                        
                        $row_style = '';
                        $status_badge = '';
                        
                        if ($status == 'out_of_stock') {
                            $row_style = 'style="background-color: #ffebee;"';
                            $status_badge = '<span class="badge badge-danger">🔴 OUT OF STOCK</span>';
                        } elseif ($status == 'low_stock') {
                            $row_style = 'style="background-color: #fff3e0;"';
                            $status_badge = '<span class="badge badge-warning">🟠 LOW STOCK</span>';
                        } else {
                            $status_badge = '<span class="badge badge-success"><i class="fas fa-check-circle"></i> IN STOCK</span>';
                        }
                    ?>
                        <tr <?php echo $row_style; ?>>
                            <td><?php echo htmlspecialchars($item['label']); ?></td>
                            <td><?php echo htmlspecialchars($item['section_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['unit'] ?: 'pcs'); ?></td>
                            <td><?php echo $item['total_in']; ?></td>
                            <td><?php echo $item['total_out']; ?></td>
                            <td><strong style="font-size: 1.1em;"><?php echo $stock; ?></strong></td>
                            <td><?php echo $reorder ?: 'Not set'; ?></td>
                            <td><?php echo $status_badge; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($rows_per_page != 'all' && $total_pages > 1): ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <?php if ($current_page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" class="btn btn-sm btn-secondary">First</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>" class="btn btn-sm btn-secondary">Previous</a>
                <?php endif; ?>
                
                <span style="padding: 0 1rem;">
                    Page <strong><?php echo $current_page; ?></strong> of <strong><?php echo $total_pages; ?></strong>
                </span>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>" class="btn btn-sm btn-secondary">Next</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" class="btn btn-sm btn-secondary">Last</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <p>No inventory data found.</p>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clipboard-list"></i> Recent Stock Movements</h3>
        </div>
        
        <?php if ($recent_movements->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Performed By</th>
                        <th>Reference/OR</th>
                        <th>Purpose/Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($movement = $recent_movements->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y h:i A', strtotime($movement['datetime_added'])); ?></td>
                            <td>
                                <?php if ($movement['movement_type'] == 'IN'): ?>
                                    <span class="badge badge-success">STOCK IN</span>
                                <?php elseif ($movement['movement_type'] == 'OUT'): ?>
                                    <span class="badge badge-danger">STOCK OUT</span>
                                <?php else: ?>
                                    <span class="badge badge-info">USAGE</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($movement['item_name']); ?></td>
                            <td><strong><?php echo $movement['quantity']; ?></strong></td>
                            <td><?php echo htmlspecialchars($movement['performed_by_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($movement['reference_number'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($movement['remarks'] ?? ''); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No recent movements found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Store all available items for stock out and usage forms
const availableStockOutItems = [
    <?php 
    $items_js = $conn->query("SELECT i.*, 
                              COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as available_stock
                              FROM item i
                              LEFT JOIN stock_in si ON i.item_id = si.item_id
                              LEFT JOIN stock_out so ON i.item_id = so.item_id
                              WHERE i.status_code = 1
                              GROUP BY i.item_id
                              HAVING available_stock > 0
                              ORDER BY i.label");
    $items_array = [];
    while($item = $items_js->fetch_assoc()) {
        $items_array[] = "{id: " . $item['item_id'] . ", label: '" . addslashes(htmlspecialchars($item['label'])) . "', stock: " . $item['available_stock'] . "}";
    }
    echo implode(",\n    ", $items_array);
    ?>
];

const allItems = [
    <?php 
    $items_all = $conn->query("SELECT * FROM item WHERE is_deleted = 0 ORDER BY label");
    $items_array_all = [];
    while($item = $items_all->fetch_assoc()) {
        $items_array_all[] = "{id: " . $item['item_id'] . ", label: '" . addslashes(htmlspecialchars($item['label'])) . "'}";
    }
    echo implode(",\n    ", $items_array_all);
    ?>
];

// Get selected items in a container
function getSelectedItems(containerId) {
    const container = document.getElementById(containerId);
    const selects = container.querySelectorAll('select[name="item_id[]"]');
    const selectedIds = [];
    selects.forEach(select => {
        if (select.value) {
            selectedIds.push(parseInt(select.value));
        }
    });
    return selectedIds;
}

// Update all dropdowns in a container to hide selected items
function updateDropdownOptions(containerId, itemsList) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const selects = container.querySelectorAll('select[name="item_id[]"]');
    const selectedItems = getSelectedItems(containerId);
    
    selects.forEach(select => {
        const currentValue = select.value;
        
        // If this select doesn't have options yet (newly added), build them
        if (select.options.length <= 1) {
            const availableItems = itemsList.filter(item => 
                !selectedItems.includes(item.id) || item.id == currentValue
            );
            
            // Rebuild options
            select.innerHTML = '<option value="">Select Item</option>';
            availableItems.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.stock !== undefined 
                    ? `${item.label} (Stock: ${item.stock})` 
                    : item.label;
                if (item.id == currentValue) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        } else {
            // Just hide/show existing options based on selections
            Array.from(select.options).forEach(option => {
                if (option.value === '') return; // Skip the "Select Item" option
                
                const itemId = parseInt(option.value);
                if (selectedItems.includes(itemId) && itemId != currentValue) {
                    option.style.display = 'none';
                    option.disabled = true;
                } else {
                    option.style.display = '';
                    option.disabled = false;
                }
            });
        }
    });
}

// Tab switching functionality
function switchTab(tabId) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.style.display = 'none';
    });
    
    // Remove active class from all tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.style.borderBottomColor = 'transparent';
        btn.style.color = '#666';
    });
    
    // Show selected tab content
    document.getElementById(tabId).style.display = 'block';
    
    // Style active tab based on type
    const activeBtn = event.target.closest('.tab-btn');
    if (tabId === 'stock-in') {
        activeBtn.style.borderBottomColor = '#48bb78';
        activeBtn.style.color = '#48bb78';
    } else if (tabId === 'stock-out') {
        activeBtn.style.borderBottomColor = '#f56565';
        activeBtn.style.color = '#f56565';
    } else if (tabId === 'stock-usage') {
        activeBtn.style.borderBottomColor = '#4299e1';
        activeBtn.style.color = '#4299e1';
    }
}

// Initialize first tab on page load
document.addEventListener('DOMContentLoaded', function() {
    const firstTab = document.querySelector('.tab-btn.active');
    if (firstTab) {
        firstTab.style.borderBottom = '3px solid #4CAF50';
        firstTab.style.background = '#e8f5e9';
        firstTab.style.color = '#4CAF50';
    }
});

// Confirmation dialogs
function confirmStockOut() {
    const itemSelect = document.querySelector('#stockOutForm select[name="item_id"]');
    const qtyInput = document.querySelector('#stockOutForm input[name="quantity"]');
    const itemName = itemSelect.options[itemSelect.selectedIndex].text;
    const quantity = qtyInput.value;
    
    return confirm(`Remove ${quantity} unit(s) of ${itemName} from stock?\n\nThis action will reduce inventory levels.`);
}

function confirmUsage() {
    const itemSelect = document.querySelector('#usageForm select[name="item_id"]');
    const qtyInput = document.querySelector('#usageForm input[name="quantity"]');
    const itemName = itemSelect.options[itemSelect.selectedIndex].text;
    const quantity = qtyInput.value;
    
    return confirm(`Record usage of ${quantity} unit(s) of ${itemName}?\n\nThis will also reduce stock levels.`);
}

function quickStockIn(itemId, itemName) {
    if (confirm('Restock ' + itemName + '?')) {
        var quantity = prompt('Enter quantity to add:', '10');
        if (quantity) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="action" value="stock_in">' +
                           '<input type="hidden" name="item_id" value="' + itemId + '">' +
                           '<input type="hidden" name="quantity" value="' + quantity + '">';
            document.body.appendChild(form);
            form.submit();
        }
    }
}

// Check stock availability when item is selected
function checkStock(itemId, qtyFieldId) {
    if (!itemId) return;
    
    fetch('check_stock.php?item_id=' + itemId)
        .then(response => response.json())
        .then(data => {
            const availableSpan = document.getElementById(qtyFieldId.replace('Qty', 'Available'));
            if (availableSpan) {
                availableSpan.textContent = 'Available: ' + data.current_stock + ' ' + data.unit;
                availableSpan.style.color = data.current_stock > 0 ? '#4CAF50' : '#f44336';
                availableSpan.style.fontWeight = 'bold';
            }
            
            const qtyField = document.getElementById(qtyFieldId);
            if (qtyField) {
                qtyField.max = data.current_stock;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Auto-dismiss success messages
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 3000);
    });
});

// Add new Stock In row
function addStockInRow() {
    const container = document.getElementById('stockInItems');
    const rowCount = container.querySelectorAll('.stock-item-row').length;
    
    const newRow = document.createElement('div');
    newRow.className = 'stock-item-row';
    newRow.style.cssText = 'margin-bottom: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;';
    
    newRow.innerHTML = `
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Item *</label>
                <select name="item_id[]" class="form-control" required>
                    <option value="">Select Item</option>
                    <?php 
                    $items = $conn->query("SELECT * FROM item WHERE is_deleted = 0 ORDER BY label");
                    while($item = $items->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['label']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Quantity *</label>
                <input type="number" name="quantity[]" class="form-control" required min="1" placeholder="0">
            </div>
            
            <div>
                <button type="button" class="btn btn-danger" onclick="removeStockRow(this)" title="Remove this item" style="padding: 0.5rem 1rem;">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
}

// Add new Stock Out row
function addStockOutRow() {
    const container = document.getElementById('stockOutItems');
    const selectedItems = getSelectedItems('stockOutItems');
    const availableItems = availableStockOutItems.filter(item => !selectedItems.includes(item.id));
    
    const newRow = document.createElement('div');
    newRow.className = 'stock-item-row';
    newRow.style.cssText = 'margin-bottom: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;';
    
    let optionsHtml = '<option value="">Select Item</option>';
    availableItems.forEach(item => {
        optionsHtml += `<option value="${item.id}">${item.label} (Stock: ${item.stock})</option>`;
    });
    
    newRow.innerHTML = `
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Item *</label>
                <select name="item_id[]" class="form-control" required onchange="handleItemChange(this, 'stockOutItems')">
                    ${optionsHtml}
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Quantity *</label>
                <input type="number" name="quantity[]" class="form-control" required min="1" placeholder="0">
                <small class="stock-available" style="color: #48bb78; font-size: 0.75rem; display: block; margin-top: 0.25rem;"></small>
            </div>
            
            <div>
                <button type="button" class="btn btn-danger" onclick="removeStockRow(this, 'stockOutItems')" title="Remove this item" style="padding: 0.5rem 1rem;">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
}

// Remove a stock row
function removeStockRow(button, containerId) {
    const row = button.closest('.stock-item-row');
    const container = row.parentElement;
    
    // Don't remove if it's the only row
    if (container.querySelectorAll('.stock-item-row').length > 1) {
        row.remove();
        // Update dropdowns after removal
        if (containerId === 'stockOutItems' || containerId === 'stockUsageItems') {
            updateDropdownOptions(containerId, availableStockOutItems);
        } else if (containerId === 'stockInItems') {
            updateDropdownOptions(containerId, allItems);
        }
    } else {
        alert('At least one item is required.');
    }
}

// Add new Stock Usage row
function addStockUsageRow() {
    const container = document.getElementById('stockUsageItems');
    const selectedItems = getSelectedItems('stockUsageItems');
    const availableItems = availableStockOutItems.filter(item => !selectedItems.includes(item.id));
    
    const newRow = document.createElement('div');
    newRow.className = 'stock-item-row';
    newRow.style.cssText = 'margin-bottom: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;';
    
    let optionsHtml = '<option value="">Select Item</option>';
    availableItems.forEach(item => {
        optionsHtml += `<option value="${item.id}">${item.label} (Stock: ${item.stock})</option>`;
    });
    
    newRow.innerHTML = `
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Item *</label>
                <select name="item_id[]" class="form-control" required onchange="handleItemChange(this, 'stockUsageItems')">
                    ${optionsHtml}
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem; display: block;">Quantity *</label>
                <input type="number" name="quantity[]" class="form-control" required min="1" value="1" placeholder="0">
                <small class="stock-available" style="color: #48bb78; font-size: 0.75rem; display: block; margin-top: 0.25rem;"></small>
            </div>
            
            <div>
                <button type="button" class="btn btn-danger" onclick="removeStockRow(this, 'stockUsageItems')" title="Remove this item" style="padding: 0.5rem 1rem;">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
}

// Handle item selection change
function handleItemChange(selectElement, containerId) {
    // Update all dropdowns to hide/show items based on current selections
    if (containerId === 'stockOutItems' || containerId === 'stockUsageItems') {
        updateDropdownOptions(containerId, availableStockOutItems);
    }
    
    // Also check stock for the selected item
    checkStockForRow(selectElement);
}

// Check stock for specific row in Stock Out and Stock Usage
function checkStockForRow(selectElement) {
    const itemId = selectElement.value;
    if (!itemId) return;
    
    const row = selectElement.closest('.stock-item-row');
    const availableSpan = row.querySelector('.stock-available');
    const qtyField = row.querySelector('input[name="quantity[]"]');
    
    fetch('check_stock.php?item_id=' + itemId)
        .then(response => response.json())
        .then(data => {
            if (availableSpan) {
                availableSpan.textContent = 'Available: ' + data.current_stock + ' ' + data.unit;
                availableSpan.style.color = data.current_stock > 0 ? '#4CAF50' : '#f44336';
                availableSpan.style.fontWeight = 'bold';
            }
            
            if (qtyField) {
                qtyField.max = data.current_stock;
            }
        })
        .catch(error => console.error('Error:', error));
}
</script>

<?php include '../includes/footer.php'; ?>
