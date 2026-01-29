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

// Get all equipment
$equipment_list = $conn->query("SELECT e.*, s.label as section_name FROM equipment e LEFT JOIN section s ON e.section_id = s.section_id ORDER BY e.equipment_id DESC");

// Get recent equipment usage
$recent_usage = $conn->query("SELECT eu.*, e.name as equipment_name FROM equipment_usage eu 
    LEFT JOIN equipment e ON eu.equipment_id = e.equipment_id 
    ORDER BY eu.date_used DESC, eu.datetime_added DESC LIMIT 20");

// Get upcoming maintenance
// Get filter parameters
$filter_section = isset($_GET['section']) ? $_GET['section'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

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

$query .= " ORDER BY ms.next_due_date ASC LIMIT 20";

$upcoming = $conn->query($query);

// Apply status filter after query (since it's a calculated field)
$maintenance_items = [];
if ($upcoming) {
    while($row = $upcoming->fetch_assoc()) {
        if (!$filter_status || $row['status'] == $filter_status) {
            $maintenance_items[] = $row;
        }
    }
}

// Get sections for filter dropdown
$sections = $conn->query("SELECT * FROM section ORDER BY label");
?>

<div class="container">
    <?php echo $message; ?>
    
    <!-- Alerts Section -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Maintenance Alerts</h2>
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
        <?php else: ?>
            <p>No upcoming maintenance scheduled.</p>
        <?php endif; ?>
    </div>
    
    <!-- Equipment Usage/Borrowing -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-clipboard-list"></i> Equipment Usage Records</h2>
        </div>
        
        <button class="btn btn-success" onclick="showAddUsageModal()" style="margin-bottom: 1rem;">Record Equipment Usage</button>
        
        <?php if ($recent_usage && $recent_usage->num_rows > 0): ?>
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
                    <?php while($usage = $recent_usage->fetch_assoc()): ?>
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
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No usage records found.</p>
        <?php endif; ?>
    </div>
    
    <!-- Equipment Management -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-tools"></i> Equipment Management</h2>
        </div>
        
        <button class="btn btn-success" onclick="showAddEquipmentModal()" style="margin-bottom: 1rem;">Add Equipment</button>
        
        <?php if ($equipment_list->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
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
                    <?php 
                    $equipment_list->data_seek(0); // Reset pointer
                    while($equip = $equipment_list->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><?php echo $equip['equipment_id']; ?></td>
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
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No equipment found.</p>
        <?php endif; ?>
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
                        <?php 
                        $equipment_list->data_seek(0);
                        while($equip = $equipment_list->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $equip['equipment_id']; ?>">
                                <?php echo htmlspecialchars($equip['name']) . ' - ' . htmlspecialchars($equip['model']); ?>
                            </option>
                        <?php endwhile; ?>
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
