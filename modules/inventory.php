<?php
require_once '../db_connection.php';
$page_title = 'Inventory Management';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'stock_in') {
            $item_id = $_POST['item_id'];
            $quantity = intval($_POST['quantity']);
            $performed_by = $_SESSION['user_id'] ?? 1;
            $supplier = !empty($_POST['supplier']) ? $_POST['supplier'] : NULL;
            $reference_number = !empty($_POST['reference_number']) ? $_POST['reference_number'] : NULL;
            $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;
            $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;
            
            $stmt = $conn->prepare("INSERT INTO stock_in (item_id, quantity, performed_by, supplier, reference_number, expiry_date, remarks, datetime_added) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiissss", $item_id, $quantity, $performed_by, $supplier, $reference_number, $expiry_date, $remarks);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Stock added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'stock_out') {
            $item_id = $_POST['item_id'];
            $quantity = intval($_POST['quantity']);
            $performed_by = $_SESSION['user_id'] ?? 1;
            $reference_number = !empty($_POST['reference_number']) ? $_POST['reference_number'] : NULL;
            $remarks = $_POST['remarks'];
            
            // Check current stock to prevent negative
            $stock_check = $conn->query("SELECT COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as current_stock 
                                         FROM item i 
                                         LEFT JOIN stock_in si ON i.item_id = si.item_id 
                                         LEFT JOIN stock_out so ON i.item_id = so.item_id 
                                         WHERE i.item_id = $item_id 
                                         GROUP BY i.item_id")->fetch_assoc();
            
            $current_stock = $stock_check ? $stock_check['current_stock'] : 0;
            
            if ($current_stock < $quantity) {
                $message = '<div class="alert alert-danger">Error: Insufficient stock! Current stock: ' . $current_stock . '</div>';
            } else {
                $stmt = $conn->prepare("INSERT INTO stock_out (item_id, quantity, performed_by, reference_number, remarks, datetime_added) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiiss", $item_id, $quantity, $performed_by, $reference_number, $remarks);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Stock removed successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
            }
        } elseif ($_POST['action'] == 'stock_usage') {
            $item_id = $_POST['item_id'];
            $quantity = intval($_POST['quantity']);
            $employee_id = $_POST['employee_id'];
            $purpose = $_POST['purpose'];
            $or_number = $_POST['or_number'];
            
            // Check current stock to prevent negative
            $stock_check = $conn->query("SELECT COALESCE(SUM(si.quantity), 0) - COALESCE(SUM(so.quantity), 0) as current_stock 
                                         FROM item i 
                                         LEFT JOIN stock_in si ON i.item_id = si.item_id 
                                         LEFT JOIN stock_out so ON i.item_id = so.item_id 
                                         WHERE i.item_id = $item_id 
                                         GROUP BY i.item_id")->fetch_assoc();
            
            $current_stock = $stock_check ? $stock_check['current_stock'] : 0;
            
            if ($current_stock < $quantity) {
                $message = '<div class="alert alert-danger">Error: Insufficient stock! Current stock: ' . $current_stock . '</div>';
            } else {
                // Get employee details
                $emp = $conn->query("SELECT firstname, middlename, lastname FROM employee WHERE employee_id = $employee_id")->fetch_assoc();
                
                $stmt = $conn->prepare("INSERT INTO stock_usage (item_id, quantity, employee_id, firstname, middlename, lastname, purpose, or_number, datetime_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiisssss", $item_id, $quantity, $employee_id, $emp['firstname'], $emp['middlename'], $emp['lastname'], $purpose, $or_number);
                
                if ($stmt->execute()) {
                    // Also create stock_out record
                    $performed_by = $_SESSION['user_id'] ?? 1;
                    $conn->query("INSERT INTO stock_out (item_id, quantity, performed_by, remarks, datetime_added) VALUES ($item_id, $quantity, $performed_by, 'Used by: $or_number - $purpose', NOW())");
                    $message = '<div class="alert alert-success">Stock usage recorded successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
            }
        }
    }
}

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
LEFT JOIN (SELECT item_id, SUM(quantity) as total_out FROM stock_out GROUP BY item_id) so ON i.item_id = so.item_id";
$stats = $conn->query($stats_query)->fetch_assoc();

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
                   LEFT JOIN stock_in si ON i.item_id = si.item_id
                   LEFT JOIN stock_out so ON i.item_id = so.item_id
                   LEFT JOIN section s ON i.section_id = s.section_id
                   WHERE 1=1";

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
    <?php echo $message; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>📦 Inventory & Supplies Management</h2>
        </div>
        
        <!-- Summary Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $stats['total_items']; ?></div>
                <div style="opacity: 0.9;">Total Items</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $stats['out_of_stock_count']; ?></div>
                <div style="opacity: 0.9;">Out of Stock</div>
            </div>
            
            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $stats['low_stock_count']; ?></div>
                <div style="opacity: 0.9;">Low Stock</div>
            </div>
            
            <?php if ($expiring_count > 0): ?>
            <div style="background: linear-gradient(135deg, #ff9a56 0%, #ff6a00 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $expiring_count; ?></div>
                <div style="opacity: 0.9;">Expiring Soon</div>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <a href="inventory.php" class="btn btn-primary">Stock Management</a>
            <a href="inventory_reports.php" class="btn btn-secondary">📊 View Reports</a>
        </div>
    </div>
    
    <!-- Expiry Alerts -->
    <?php if ($expiry_alerts && $expiry_alerts->num_rows > 0): ?>
    <div class="card" style="border-left: 4px solid #ff5722;">
        <div class="card-header" style="background-color: #ffe0b2;">
            <h2>⏰ Expiry Alerts</h2>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Section</th>
                    <th>Quantity</th>
                    <th>Expiry Date</th>
                    <th>Days Left</th>
                    <th>Supplier</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($alert = $expiry_alerts->fetch_assoc()): 
                    $status_color = '';
                    if ($alert['expiry_status'] == 'expired') $status_color = 'style="background-color: #ffcdd2;"';
                    elseif ($alert['expiry_status'] == 'expiring_soon') $status_color = 'style="background-color: #fff9c4;"';
                ?>
                    <tr <?php echo $status_color; ?>>
                        <td><?php echo htmlspecialchars($alert['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($alert['section_name']); ?></td>
                        <td><?php echo $alert['quantity']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($alert['expiry_date'])); ?></td>
                        <td><?php echo $alert['days_until_expiry']; ?> days</td>
                        <td><?php echo htmlspecialchars($alert['supplier'] ?? 'N/A'); ?></td>
                        <td>
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
            <h2>⚠️ Low Stock Alerts</h2>
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
            <h2>📋 Stock Management</h2>
        </div>
        
        <!-- Tab Navigation -->
        <div class="tab-navigation" style="display: flex; border-bottom: 2px solid #e0e0e0; margin-bottom: 0; background: #f5f5f5;">
            <button class="tab-btn active" onclick="switchTab('stock-in')" style="flex: 1; padding: 1rem; border: none; background: transparent; cursor: pointer; font-weight: bold; border-bottom: 3px solid transparent; transition: all 0.3s;">
                ➕ Stock In
            </button>
            <button class="tab-btn" onclick="switchTab('stock-out')" style="flex: 1; padding: 1rem; border: none; background: transparent; cursor: pointer; font-weight: bold; border-bottom: 3px solid transparent; transition: all 0.3s;">
                ➖ Stock Out
            </button>
            <button class="tab-btn" onclick="switchTab('stock-usage')" style="flex: 1; padding: 1rem; border: none; background: transparent; cursor: pointer; font-weight: bold; border-bottom: 3px solid transparent; transition: all 0.3s;">
                📝 Stock Usage
            </button>
        </div>
        
        <!-- Tab Content -->
        <div style="padding: 1.5rem;">
            <!-- Stock In Form - Green Theme -->
            <div id="stock-in" class="tab-content" style="display: block; border: 2px solid #4CAF50; border-radius: 8px; padding: 1.5rem; background: linear-gradient(to bottom, #e8f5e9 0%, white 100%);">
                <h3 style="color: #4CAF50; margin-bottom: 1rem;">➕ Stock In</h3>
                <form method="POST" id="stockInForm">
                    <input type="hidden" name="action" value="stock_in">
                    
                    <div class="form-group">
                        <label>Item *</label>
                        <select name="item_id" class="form-control" required>
                            <option value="">Select Item</option>
                            <?php 
                            $items = $conn->query("SELECT * FROM item ORDER BY label");
                            while($item = $items->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['label']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label>Supplier</label>
                        <input type="text" name="supplier" class="form-control" placeholder="Supplier name">
                    </div>
                    
                    <div class="form-group">
                        <label>Reference No.</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Invoice/PO/DR number">
                    </div>
                    
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Optional notes">
                    </div>
                    
                    <button type="submit" class="btn btn-success" style="width: 100%;">✓ Add Stock</button>
                </form>
            </div>
            
            <!-- Stock Out Form - Red Theme -->
            <div id="stock-out" class="tab-content" style="display: none; border: 2px solid #f44336; border-radius: 8px; padding: 1.5rem; background: linear-gradient(to bottom, #ffebee 0%, white 100%);">
                <h3 style="color: #f44336; margin-bottom: 1rem;">➖ Stock Out</h3>
                <form method="POST" id="stockOutForm" onsubmit="return confirmStockOut()">
                    <input type="hidden" name="action" value="stock_out">
                    
                    <div class="form-group">
                        <label>Item *</label>
                        <select name="item_id" id="stockOutItem" class="form-control" required onchange="checkStock(this.value, 'stockOutQty')">
                            <option value="">Select Item</option>
                            <?php 
                            $items = $conn->query("SELECT * FROM item ORDER BY label");
                            while($item = $items->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['label']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" id="stockOutQty" class="form-control" required min="1">
                        <small id="stockOutAvailable" style="color: #666;"></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Reference No.</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Requisition number">
                    </div>
                    
                    <div class="form-group">
                        <label>Remarks *</label>
                        <input type="text" name="remarks" class="form-control" required placeholder="Reason for removal">
                    </div>
                    
                    <button type="submit" class="btn btn-danger" style="width: 100%;">✓ Remove Stock</button>
                </form>
            </div>
            
            <!-- Stock Usage Form - Blue Theme -->
            <div id="stock-usage" class="tab-content" style="display: none; border: 2px solid #2196F3; border-radius: 8px; padding: 1.5rem; background: linear-gradient(to bottom, #e3f2fd 0%, white 100%);">
                <h3 style="color: #2196F3; margin-bottom: 1rem;">📝 Record Usage</h3>
                <form method="POST" id="usageForm" onsubmit="return confirmUsage()">
                    <input type="hidden" name="action" value="stock_usage">
                    
                    <div class="form-group">
                        <label>Item *</label>
                        <select name="item_id" id="usageItem" class="form-control" required onchange="checkStock(this.value, 'usageQty')">
                            <option value="">Select Item</option>
                            <?php 
                            $items = $conn->query("SELECT * FROM item ORDER BY label");
                            while($item = $items->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['label']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" id="usageQty" class="form-control" required min="1" value="1">
                        <small id="usageAvailable" style="color: #666;"></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Employee *</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">Select Employee</option>
                            <?php 
                            $employees = $conn->query("SELECT * FROM employee WHERE status_code = 1");
                            while($emp = $employees->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Purpose *</label>
                        <input type="text" name="purpose" class="form-control" required placeholder="Purpose of use">
                    </div>
                    
                    <div class="form-group">
                        <label>OR Number</label>
                        <input type="text" name="or_number" class="form-control" placeholder="Official receipt number">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">✓ Record Usage</button>
                </form>
            </div>
        </div>
        
        <!-- Current Inventory -->
        <h3>Current Inventory Levels</h3>
        
        <!-- Search and Filters -->
        <form method="GET" style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Search by Item Name</label>
                    <input type="text" name="search" class="form-control" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Filter by Section</label>
                    <select name="section" class="form-control">
                        <option value="">All Sections</option>
                        <?php 
                        $sections_filter = $conn->query("SELECT * FROM section ORDER BY label");
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
                            $status_badge = '<span class="badge badge-success">✅ IN STOCK</span>';
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
            <h3>📋 Recent Stock Movements</h3>
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
        btn.classList.remove('active');
        btn.style.borderBottom = '3px solid transparent';
        btn.style.background = 'transparent';
        btn.style.color = '#666';
    });
    
    // Show selected tab content
    document.getElementById(tabId).style.display = 'block';
    
    // Add active class to clicked tab
    event.target.classList.add('active');
    
    // Style active tab based on type
    if (tabId === 'stock-in') {
        event.target.style.borderBottom = '3px solid #4CAF50';
        event.target.style.background = '#e8f5e9';
        event.target.style.color = '#4CAF50';
    } else if (tabId === 'stock-out') {
        event.target.style.borderBottom = '3px solid #f44336';
        event.target.style.background = '#ffebee';
        event.target.style.color = '#f44336';
    } else if (tabId === 'stock-usage') {
        event.target.style.borderBottom = '3px solid #2196F3';
        event.target.style.background = '#e3f2fd';
        event.target.style.color = '#2196F3';
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
</script>

<?php include '../includes/footer.php'; ?>
