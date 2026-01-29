<?php
require_once '../db_connection.php';
$page_title = 'Equipment Maintenance Management';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_equipment') {
            $name = $_POST['name'];
            $model = $_POST['model'];
            $serial_no = $_POST['serial_no'];
            $section_id = !empty($_POST['section_id']) ? $_POST['section_id'] : NULL;
            $status = $_POST['status'];
            $purchase_date = $_POST['purchase_date'];
            $supplier = $_POST['supplier'];
            $remarks = $_POST['remarks'];
            
            $stmt = $conn->prepare("INSERT INTO equipment (name, model, serial_no, section_id, status, purchase_date, supplier, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssissss", $name, $model, $serial_no, $section_id, $status, $purchase_date, $supplier, $remarks);
            
            if ($stmt->execute()) {
                $equipment_id = $stmt->insert_id;
                log_activity($conn, get_user_id(), "Added new equipment: $name - $model (ID: $equipment_id)", 1);
                $message = '<div class="alert alert-success">Equipment added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update_equipment') {
            $equipment_id = $_POST['equipment_id'];
            $name = $_POST['name'];
            $model = $_POST['model'];
            $serial_no = $_POST['serial_no'];
            $section_id = !empty($_POST['section_id']) ? $_POST['section_id'] : NULL;
            $status = $_POST['status'];
            $purchase_date = $_POST['purchase_date'];
            $supplier = $_POST['supplier'];
            $remarks = $_POST['remarks'];
            
            $stmt = $conn->prepare("UPDATE equipment SET name=?, model=?, serial_no=?, section_id=?, status=?, purchase_date=?, supplier=?, remarks=? WHERE equipment_id=?");
            $stmt->bind_param("sssissssi", $name, $model, $serial_no, $section_id, $status, $purchase_date, $supplier, $remarks, $equipment_id);
            
            if ($stmt->execute()) {
                log_activity($conn, get_user_id(), "Updated equipment: $name - $model (ID: $equipment_id)", 1);
                $message = '<div class="alert alert-success">Equipment updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'delete_equipment') {
            $equipment_id = $_POST['equipment_id'];
            $equipment_info = $conn->query("SELECT name, model FROM equipment WHERE equipment_id = $equipment_id")->fetch_assoc();
            $stmt = $conn->prepare("DELETE FROM equipment WHERE equipment_id=?");
            $stmt->bind_param("i", $equipment_id);
            
            if ($stmt->execute()) {
                log_activity($conn, get_user_id(), "Deleted equipment: {$equipment_info['name']} - {$equipment_info['model']} (ID: $equipment_id)", 1);
                $message = '<div class="alert alert-success">Equipment deleted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'add_maintenance') {
            $equipment_id = $_POST['equipment_id'];
            $frequency = $_POST['frequency'];
            $next_due_date = $_POST['next_due_date'];
            $responsible_employee_id = !empty($_POST['responsible_employee_id']) ? $_POST['responsible_employee_id'] : NULL;
            $responsible_section_id = !empty($_POST['responsible_section_id']) ? $_POST['responsible_section_id'] : NULL;
            
            $stmt = $conn->prepare("INSERT INTO maintenance_schedule (equipment_id, frequency, next_due_date, responsible_employee_id, responsible_section_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issii", $equipment_id, $frequency, $next_due_date, $responsible_employee_id, $responsible_section_id);
            
            if ($stmt->execute()) {
                $equipment_name = $conn->query("SELECT name FROM equipment WHERE equipment_id = $equipment_id")->fetch_assoc()['name'];
                log_activity($conn, get_user_id(), "Added $frequency maintenance schedule for $equipment_name (ID: $equipment_id)", 1);
                $message = '<div class="alert alert-success">Maintenance schedule added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'add_history') {
            $equipment_id = $_POST['equipment_id'];
            $maintenance_date = $_POST['maintenance_date'];
            $performed_by = $_POST['performed_by'];
            $maintenance_type = $_POST['maintenance_type'];
            $notes = $_POST['notes'];
            $next_maintenance_date = !empty($_POST['next_maintenance_date']) ? $_POST['next_maintenance_date'] : NULL;
            
            $stmt = $conn->prepare("INSERT INTO maintenance_history (equipment_id, maintenance_date, performed_by, maintenance_type, notes, next_maintenance_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isisss", $equipment_id, $maintenance_date, $performed_by, $maintenance_type, $notes, $next_maintenance_date);
            
            if ($stmt->execute()) {
                // Update schedule next due date if provided
                if ($next_maintenance_date) {
                    $conn->query("UPDATE maintenance_schedule SET next_due_date='$next_maintenance_date' WHERE equipment_id=$equipment_id");
                }
                $equipment_name = $conn->query("SELECT name FROM equipment WHERE equipment_id = $equipment_id")->fetch_assoc()['name'];
                log_activity($conn, get_user_id(), "Recorded $maintenance_type maintenance for $equipment_name (ID: $equipment_id)", 1);
                $message = '<div class="alert alert-success">Maintenance history recorded successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'add_usage') {
            $equipment_id = $_POST['equipment_id'];
            $date_used = $_POST['date_used'];
            $user_name = $_POST['user_name'];
            $item_name = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $purpose = $_POST['purpose'];
            $or_number = !empty($_POST['or_number']) ? $_POST['or_number'] : NULL;
            $status = $_POST['status'];
            $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;
            
            $stmt = $conn->prepare("INSERT INTO equipment_usage (equipment_id, date_used, user_name, item_name, quantity, purpose, or_number, status, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssissss", $equipment_id, $date_used, $user_name, $item_name, $quantity, $purpose, $or_number, $status, $remarks);
            
            if ($stmt->execute()) {
                $equipment_name = $conn->query("SELECT name FROM equipment WHERE equipment_id = $equipment_id")->fetch_assoc()['name'];
                log_activity($conn, get_user_id(), "Recorded usage of $equipment_name by $user_name - $item_name (Qty: $quantity)", 1);
                $message = '<div class="alert alert-success">Equipment usage recorded successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Pagination for Equipment Usage
$rows_usage = isset($_GET['rows_usage']) ? intval($_GET['rows_usage']) : 10;
$page_usage = isset($_GET['page_usage']) ? intval($_GET['page_usage']) : 1;

// Pagination for Equipment Management
$rows_management = isset($_GET['rows_management']) ? intval($_GET['rows_management']) : 10;
$page_management = isset($_GET['page_management']) ? intval($_GET['page_management']) : 1;

// Get all equipment usage records
$usage_query = "SELECT eu.*, e.name as equipment_name FROM equipment_usage eu 
    LEFT JOIN equipment e ON eu.equipment_id = e.equipment_id 
    ORDER BY eu.date_used DESC, eu.datetime_added DESC";
$all_usage = $conn->query($usage_query);
$usage_items = [];
if ($all_usage) {
    while($row = $all_usage->fetch_assoc()) {
        $usage_items[] = $row;
    }
}
$total_usage = count($usage_items);
$total_usage_pages = ceil($total_usage / $rows_usage);
$page_usage = max(1, min($page_usage, max(1, $total_usage_pages)));
$offset_usage = ($page_usage - 1) * $rows_usage;
$recent_usage = array_slice($usage_items, $offset_usage, $rows_usage);

// Get all equipment
$equipment_query = "SELECT e.*, s.label as section_name FROM equipment e LEFT JOIN section s ON e.section_id = s.section_id ORDER BY e.equipment_id DESC";
$all_equipment = $conn->query($equipment_query);
$equipment_items = [];
if ($all_equipment) {
    while($row = $all_equipment->fetch_assoc()) {
        $equipment_items[] = $row;
    }
}
$total_equipment = count($equipment_items);
$total_equipment_pages = ceil($total_equipment / $rows_management);
$page_management = max(1, min($page_management, max(1, $total_equipment_pages)));
$offset_management = ($page_management - 1) * $rows_management;
$equipment_list = array_slice($equipment_items, $offset_management, $rows_management);

// Get upcoming maintenance
// Get filter parameters
$filter_section = isset($_GET['section']) ? $_GET['section'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$rows_per_page = isset($_GET['rows']) ? intval($_GET['rows']) : 10;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Build query with filters
$query = "SELECT DISTINCT e.equipment_id, e.name AS equipment_name, e.model, e.serial_no, 
          s.label AS section_name, ms.next_due_date, ms.frequency,
          TO_DAYS(ms.next_due_date) - TO_DAYS(CURDATE()) AS days_until_due,
          CASE 
              WHEN ms.next_due_date < CURDATE() THEN 'Overdue'
              WHEN TO_DAYS(ms.next_due_date) - TO_DAYS(CURDATE()) <= 7 THEN 'Due Soon'
              ELSE 'Scheduled'
          END AS status
          FROM equipment e
          LEFT JOIN maintenance_schedule ms ON e.equipment_id = ms.equipment_id
          LEFT JOIN section s ON e.section_id = s.section_id
          WHERE ms.is_active = 1";

if ($filter_section) {
    $query .= " AND e.section_id = " . intval($filter_section);
}

$query .= " ORDER BY ms.next_due_date ASC";

$upcoming = $conn->query($query);

// Apply status filter after query (since it's a calculated field)
$all_maintenance_items = [];
if ($upcoming) {
    while($row = $upcoming->fetch_assoc()) {
        if (!$filter_status || $row['status'] == $filter_status) {
            $all_maintenance_items[] = $row;
        }
    }
}

// Pagination calculations
$total_items = count($all_maintenance_items);
$total_pages = ceil($total_items / $rows_per_page);
$current_page = max(1, min($current_page, $total_pages));
$offset = ($current_page - 1) * $rows_per_page;
$maintenance_items = array_slice($all_maintenance_items, $offset, $rows_per_page);

// Get sections for filter dropdown
$sections = $conn->query("SELECT * FROM section ORDER BY label");
?>

<div class="container">
    <?php echo $message; ?>
    
    <!-- Alerts Section -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0;"><i class="fas fa-exclamation-triangle"></i> Maintenance Alerts</h2>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-secondary" onclick="showAddUsageModal()">
                    <i class="fas fa-clipboard-list"></i> Record Equipment Usage
                </button>
                <button class="btn btn-secondary" onclick="showAddEquipmentModal()">
                    <i class="fas fa-plus"></i> Add Equipment
                </button>
            </div>
        </div>
        
        <!-- Status Legend -->
        <div style="padding: 1rem; background: #f8f9fa; border-radius: 5px; margin-bottom: 1rem;">
            <strong>Status Legend:</strong>
            <span style="color: red; font-weight: bold; margin-left: 1rem;">● Overdue</span> (Past due date)
            <span style="color: orange; font-weight: bold; margin-left: 1rem;">● Due Soon</span> (Within 7 days)
            <span style="color: green; font-weight: bold; margin-left: 1rem;">● Scheduled</span> (More than 7 days)
        </div>
        
        <!-- Filters -->
        <form method="GET" style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Filter by Section</label>
                <select name="section" class="form-control" onchange="this.form.submit()">
                    <option value="">All Sections</option>
                    <?php while($sec = $sections->fetch_assoc()): ?>
                        <option value="<?php echo $sec['section_id']; ?>" <?php echo ($filter_section == $sec['section_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sec['label']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label>Filter by Status</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Overdue" <?php echo ($filter_status == 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
                    <option value="Due Soon" <?php echo ($filter_status == 'Due Soon') ? 'selected' : ''; ?>>Due Soon</option>
                    <option value="Scheduled" <?php echo ($filter_status == 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label>Rows per page</label>
                <select name="rows" class="form-control" onchange="this.form.submit()">
                    <option value="5" <?php echo ($rows_per_page == 5) ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?php echo ($rows_per_page == 10) ? 'selected' : ''; ?>>10</option>
                    <option value="20" <?php echo ($rows_per_page == 20) ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?php echo ($rows_per_page == 50) ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo ($rows_per_page == 100) ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            
            <input type="hidden" name="page" value="<?php echo $current_page; ?>">
            
            <?php if ($filter_section || $filter_status): ?>
                <a href="equipment.php" class="btn btn-secondary">Clear Filters</a>
            <?php endif; ?>
        </form>
        
        <?php if (count($maintenance_items) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Model</th>
                        <th>Section</th>
                        <th>Next Due Date</th>
                        <th>Days Until Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($maintenance_items as $item): 
                        $status_color = '';
                        if ($item['status'] == 'Overdue') $status_color = 'style="color: red; font-weight: bold;"';
                        elseif ($item['status'] == 'Due Soon') $status_color = 'style="color: orange; font-weight: bold;"';
                        else $status_color = 'style="color: green; font-weight: bold;"';
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['equipment_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['model']); ?></td>
                            <td><?php echo htmlspecialchars($item['section_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($item['next_due_date'])); ?></td>
                            <td><?php echo $item['days_until_due']; ?> days</td>
                            <td <?php echo $status_color; ?>><?php echo $item['status']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                    <div style="color: #666;">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $rows_per_page, $total_items); ?> of <?php echo $total_items; ?> entries
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($current_page > 1): ?>
                            <a href="?section=<?php echo $filter_section; ?>&status=<?php echo $filter_status; ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $current_page - 1; ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        if ($start_page > 1): ?>
                            <a href="?section=<?php echo $filter_section; ?>&status=<?php echo $filter_status; ?>&rows=<?php echo $rows_per_page; ?>&page=1" class="btn btn-sm btn-secondary">1</a>
                            <?php if ($start_page > 2): ?>
                                <span style="padding: 0.25rem 0.5rem;">...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="?section=<?php echo $filter_section; ?>&status=<?php echo $filter_status; ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $i; ?>" 
                               class="btn btn-sm <?php echo ($i == $current_page) ? 'btn-primary' : 'btn-secondary'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span style="padding: 0.25rem 0.5rem;">...</span>
                            <?php endif; ?>
                            <a href="?section=<?php echo $filter_section; ?>&status=<?php echo $filter_status; ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $total_pages; ?>" class="btn btn-sm btn-secondary"><?php echo $total_pages; ?></a>
                        <?php endif; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?section=<?php echo $filter_section; ?>&status=<?php echo $filter_status; ?>&rows=<?php echo $rows_per_page; ?>&page=<?php echo $current_page + 1; ?>" class="btn btn-sm btn-secondary">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>No upcoming maintenance scheduled.</p>
        <?php endif; ?>
    </div>
    
    <!-- Equipment Tabs -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: end; border-bottom: 2px solid #dee2e6;">
                <div style="display: flex; gap: 0;">
                    <button class="tab-button active" onclick="switchTab('usage')" id="usageTab" style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid #007bff; color: #007bff; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-clipboard-list"></i> Equipment Usage Records
                    </button>
                    <button class="tab-button" onclick="switchTab('management')" id="managementTab" style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent; color: #666; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-tools"></i> Equipment Management
                    </button>
                </div>
                <div style="display: flex; gap: 0.5rem; padding-bottom: 0.5rem;">
                    <form method="GET" style="display: flex; gap: 0.5rem; align-items: center;" id="usageRowsForm">
                        <label style="margin: 0; font-size: 0.875rem; color: #666;">Rows:</label>
                        <select name="rows_usage" class="form-control" style="width: 80px; padding: 0.25rem 0.5rem; height: auto;" onchange="this.form.submit()">
                            <option value="5" <?php echo ($rows_usage == 5) ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo ($rows_usage == 10) ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo ($rows_usage == 20) ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo ($rows_usage == 50) ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo ($rows_usage == 100) ? 'selected' : ''; ?>>100</option>
                        </select>
                        <input type="hidden" name="page_usage" value="<?php echo $page_usage; ?>">
                    </form>
                    <form method="GET" style="display: none; gap: 0.5rem; align-items: center;" id="managementRowsForm">
                        <label style="margin: 0; font-size: 0.875rem; color: #666;">Rows:</label>
                        <select name="rows_management" class="form-control" style="width: 80px; padding: 0.25rem 0.5rem; height: auto;" onchange="this.form.submit()">
                            <option value="5" <?php echo ($rows_management == 5) ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo ($rows_management == 10) ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo ($rows_management == 20) ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo ($rows_management == 50) ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo ($rows_management == 100) ? 'selected' : ''; ?>>100</option>
                        </select>
                        <input type="hidden" name="page_management" value="<?php echo $page_management; ?>">
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Equipment Usage Tab Content -->
        <div id="usageContent" class="tab-content">
            <button class="btn btn-success" onclick="showAddUsageModal()" style="margin-bottom: 1rem;">Record Equipment Usage</button>
            
            <?php if (count($recent_usage) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date Used</th>
                            <th>Equipment</th>
                            <th>Item Name</th>
                            <th>User's Name</th>
                            <th>Quantity (Uses)</th>
                            <th>Purpose</th>
                            <th>OR Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_usage as $usage): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($usage['date_used'])); ?></td>
                                <td><?php echo htmlspecialchars($usage['equipment_name']); ?></td>
                                <td><?php echo htmlspecialchars($usage['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($usage['user_name']); ?></td>
                                <td><?php echo $usage['quantity']; ?></td>
                                <td><?php echo htmlspecialchars(substr($usage['purpose'], 0, 50)) . (strlen($usage['purpose']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($usage['or_number'] ?? 'N/A'); ?></td>
                                <td>
                                    <span style="color: <?php echo $usage['status'] == 'functional' ? 'green' : 'red'; ?>; font-weight: bold;">
                                        <?php echo strtoupper(str_replace('_', ' ', $usage['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination for Usage -->
                <?php if ($total_usage_pages > 1): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                        <div style="color: #666;">
                            Showing <?php echo $offset_usage + 1; ?> to <?php echo min($offset_usage + $rows_usage, $total_usage); ?> of <?php echo $total_usage; ?> entries
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <?php if ($page_usage > 1): ?>
                                <a href="?rows_usage=<?php echo $rows_usage; ?>&page_usage=<?php echo $page_usage - 1; ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page_usage - 2);
                            $end_page = min($total_usage_pages, $page_usage + 2);
                            
                            if ($start_page > 1): ?>
                                <a href="?rows_usage=<?php echo $rows_usage; ?>&page_usage=1" class="btn btn-sm btn-secondary">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span style="padding: 0.25rem 0.5rem;">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="?rows_usage=<?php echo $rows_usage; ?>&page_usage=<?php echo $i; ?>" 
                                   class="btn btn-sm <?php echo ($i == $page_usage) ? 'btn-primary' : 'btn-secondary'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $total_usage_pages): ?>
                                <?php if ($end_page < $total_usage_pages - 1): ?>
                                    <span style="padding: 0.25rem 0.5rem;">...</span>
                                <?php endif; ?>
                                <a href="?rows_usage=<?php echo $rows_usage; ?>&page_usage=<?php echo $total_usage_pages; ?>" class="btn btn-sm btn-secondary"><?php echo $total_usage_pages; ?></a>
                            <?php endif; ?>
                            
                            <?php if ($page_usage < $total_usage_pages): ?>
                                <a href="?rows_usage=<?php echo $rows_usage; ?>&page_usage=<?php echo $page_usage + 1; ?>" class="btn btn-sm btn-secondary">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No usage records found.</p>
            <?php endif; ?>
        </div>
        
        <!-- Equipment Management Tab Content -->
        <div id="managementContent" class="tab-content" style="display: none;">
            <button class="btn btn-success" onclick="showAddEquipmentModal()" style="margin-bottom: 1rem;">Add Equipment</button>
            
            <?php if (count($equipment_list) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Serial No.</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Purchase Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($equipment_list as $equip): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($equip['name']); ?></td>
                                <td><?php echo htmlspecialchars($equip['model']); ?></td>
                                <td><?php echo htmlspecialchars($equip['serial_no']); ?></td>
                                <td><?php echo htmlspecialchars($equip['section_name']); ?></td>
                                <td><?php echo strtoupper(str_replace('_', ' ', $equip['status'])); ?></td>
                                <td><?php echo $equip['purchase_date'] ? date('M d, Y', strtotime($equip['purchase_date'])) : 'N/A'; ?></td>
                                <td class="table-actions">
                                    <a href="equipment_details.php?id=<?php echo $equip['equipment_id']; ?>" class="btn btn-info btn-sm">Details</a>
                                    <button class="btn btn-warning btn-sm" onclick="editEquipment(<?php echo htmlspecialchars(json_encode($equip)); ?>)">Edit</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this equipment?');">
                                        <input type="hidden" name="action" value="delete_equipment">
                                        <input type="hidden" name="equipment_id" value="<?php echo $equip['equipment_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination for Management -->
                <?php if ($total_equipment_pages > 1): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                        <div style="color: #666;">
                            Showing <?php echo $offset_management + 1; ?> to <?php echo min($offset_management + $rows_management, $total_equipment); ?> of <?php echo $total_equipment; ?> entries
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <?php if ($page_management > 1): ?>
                                <a href="?rows_management=<?php echo $rows_management; ?>&page_management=<?php echo $page_management - 1; ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page_management - 2);
                            $end_page = min($total_equipment_pages, $page_management + 2);
                            
                            if ($start_page > 1): ?>
                                <a href="?rows_management=<?php echo $rows_management; ?>&page_management=1" class="btn btn-sm btn-secondary">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span style="padding: 0.25rem 0.5rem;">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="?rows_management=<?php echo $rows_management; ?>&page_management=<?php echo $i; ?>" 
                                   class="btn btn-sm <?php echo ($i == $page_management) ? 'btn-primary' : 'btn-secondary'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $total_equipment_pages): ?>
                                <?php if ($end_page < $total_equipment_pages - 1): ?>
                                    <span style="padding: 0.25rem 0.5rem;">...</span>
                                <?php endif; ?>
                                <a href="?rows_management=<?php echo $rows_management; ?>&page_management=<?php echo $total_equipment_pages; ?>" class="btn btn-sm btn-secondary"><?php echo $total_equipment_pages; ?></a>
                            <?php endif; ?>
                            
                            <?php if ($page_management < $total_equipment_pages): ?>
                                <a href="?rows_management=<?php echo $rows_management; ?>&page_management=<?php echo $page_management + 1; ?>" class="btn btn-sm btn-secondary">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No equipment found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Equipment Usage Modal -->
<div id="addUsageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 700px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Record Equipment Usage</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_usage">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Equipment *</label>
                    <select name="equipment_id" class="form-control" required>
                        <option value="">Select Equipment</option>
                        <?php foreach($equipment_items as $equip): ?>
                            <option value="<?php echo $equip['equipment_id']; ?>">
                                <?php echo htmlspecialchars($equip['name']) . ' - ' . htmlspecialchars($equip['model']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date Used *</label>
                    <input type="date" name="date_used" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>User's Name *</label>
                    <input type="text" name="user_name" class="form-control" placeholder="Name of person using the equipment" required>
                </div>
                
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="item_name" class="form-control" placeholder="Name of item/equipment being used" required>
                </div>
                
                <div class="form-group">
                    <label>Quantity (Number of Uses) *</label>
                    <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                </div>
                
                <div class="form-group">
                    <label>OR Number</label>
                    <input type="text" name="or_number" class="form-control" placeholder="Official Receipt Number">
                </div>
            </div>
            
            <div class="form-group">
                <label>Purpose *</label>
                <textarea name="purpose" class="form-control" rows="3" placeholder="Describe the purpose of equipment usage" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" class="form-control" required>
                    <option value="functional">Functional</option>
                    <option value="not_functional">Not Functional</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="2" placeholder="Additional notes or observations"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Record Usage</button>
                <button type="button" class="btn btn-danger" onclick="closeAddUsageModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Equipment Modal -->
<div id="addEquipmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 700px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Add Equipment</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_equipment">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Equipment Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Serial Number</label>
                    <input type="text" name="serial_no" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Section</label>
                    <select name="section_id" class="form-control">
                        <option value="">Select Section</option>
                        <?php 
                        $sections = $conn->query("SELECT * FROM section");
                        while($section = $sections->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $section['section_id']; ?>"><?php echo htmlspecialchars($section['label']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="operational">Operational</option>
                        <option value="under_maintenance">Under Maintenance</option>
                        <option value="decommissioned">Decommissioned</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label>Supplier</label>
                <input type="text" name="supplier" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Add Equipment</button>
                <button type="button" class="btn btn-danger" onclick="closeAddEquipmentModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Equipment Modal -->
<div id="editEquipmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 700px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Edit Equipment</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_equipment">
            <input type="hidden" name="equipment_id" id="edit_equipment_id">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Equipment Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" id="edit_model" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Serial Number</label>
                    <input type="text" name="serial_no" id="edit_serial_no" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Section</label>
                    <select name="section_id" id="edit_section_id" class="form-control">
                        <option value="">Select Section</option>
                        <?php 
                        $sections = $conn->query("SELECT * FROM section");
                        while($section = $sections->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $section['section_id']; ?>"><?php echo htmlspecialchars($section['label']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" id="edit_status" class="form-control" required>
                        <option value="operational">Operational</option>
                        <option value="under_maintenance">Under Maintenance</option>
                        <option value="decommissioned">Decommissioned</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" id="edit_purchase_date" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label>Supplier</label>
                <input type="text" name="supplier" id="edit_supplier" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" id="edit_remarks" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update Equipment</button>
                <button type="button" class="btn btn-danger" onclick="closeEditEquipmentModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.style.borderBottom = '3px solid transparent';
        button.style.color = '#666';
    });
    
    // Show selected tab content and activate tab
    if (tab === 'usage') {
        document.getElementById('usageContent').style.display = 'block';
        document.getElementById('usageTab').style.borderBottom = '3px solid #007bff';
        document.getElementById('usageTab').style.color = '#007bff';
        document.getElementById('usageRowsForm').style.display = 'flex';
        document.getElementById('managementRowsForm').style.display = 'none';
    } else if (tab === 'management') {
        document.getElementById('managementContent').style.display = 'block';
        document.getElementById('managementTab').style.borderBottom = '3px solid #007bff';
        document.getElementById('managementTab').style.color = '#007bff';
        document.getElementById('usageRowsForm').style.display = 'none';
        document.getElementById('managementRowsForm').style.display = 'flex';
    }
}

function showAddUsageModal() {
    document.getElementById('addUsageModal').style.display = 'block';
}

function closeAddUsageModal() {
    document.getElementById('addUsageModal').style.display = 'none';
}

function showAddEquipmentModal() {
    document.getElementById('addEquipmentModal').style.display = 'block';
}

function closeAddEquipmentModal() {
    document.getElementById('addEquipmentModal').style.display = 'none';
}

function editEquipment(equip) {
    document.getElementById('edit_equipment_id').value = equip.equipment_id;
    document.getElementById('edit_name').value = equip.name;
    document.getElementById('edit_model').value = equip.model;
    document.getElementById('edit_serial_no').value = equip.serial_no;
    document.getElementById('edit_section_id').value = equip.section_id || '';
    document.getElementById('edit_status').value = equip.status;
    document.getElementById('edit_purchase_date').value = equip.purchase_date;
    document.getElementById('edit_supplier').value = equip.supplier;
    document.getElementById('edit_remarks').value = equip.remarks;
    document.getElementById('editEquipmentModal').style.display = 'block';
}

function closeEditEquipmentModal() {
    document.getElementById('editEquipmentModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
