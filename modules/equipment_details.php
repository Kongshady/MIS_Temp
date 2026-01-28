<?php
require_once '../db_connection.php';
$page_title = 'Equipment Details';
include '../includes/header.php';

$equipment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_schedule') {
            $frequency = $_POST['frequency'];
            $next_due_date = $_POST['next_due_date'];
            $responsible_employee_id = !empty($_POST['responsible_employee_id']) ? $_POST['responsible_employee_id'] : NULL;
            $responsible_section_id = !empty($_POST['responsible_section_id']) ? $_POST['responsible_section_id'] : NULL;
            
            $stmt = $conn->prepare("INSERT INTO maintenance_schedule (equipment_id, frequency, next_due_date, responsible_employee_id, responsible_section_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issii", $equipment_id, $frequency, $next_due_date, $responsible_employee_id, $responsible_section_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Maintenance schedule added!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'add_history') {
            $maintenance_date = $_POST['maintenance_date'];
            $performed_by = $_POST['performed_by'];
            $maintenance_type = $_POST['maintenance_type'];
            $notes = $_POST['notes'];
            $next_maintenance_date = !empty($_POST['next_maintenance_date']) ? $_POST['next_maintenance_date'] : NULL;
            
            $stmt = $conn->prepare("INSERT INTO maintenance_history (equipment_id, maintenance_date, performed_by, maintenance_type, notes, next_maintenance_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isisss", $equipment_id, $maintenance_date, $performed_by, $maintenance_type, $notes, $next_maintenance_date);
            
            if ($stmt->execute()) {
                if ($next_maintenance_date) {
                    $conn->query("UPDATE maintenance_schedule SET next_due_date='$next_maintenance_date' WHERE equipment_id=$equipment_id");
                }
                $message = '<div class="alert alert-success">Maintenance record added!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get equipment details
$equipment = $conn->query("SELECT e.*, s.label as section_name FROM equipment e LEFT JOIN section s ON e.section_id = s.section_id WHERE e.equipment_id = $equipment_id")->fetch_assoc();

if (!$equipment) {
    echo '<div class="container"><div class="alert alert-danger">Equipment not found!</div></div>';
    include '../includes/footer.php';
    exit;
}

// Get maintenance schedules
$schedules = $conn->query("SELECT ms.*, e.firstname, e.lastname, s.label as section_name 
                          FROM maintenance_schedule ms
                          LEFT JOIN employee e ON ms.responsible_employee_id = e.employee_id
                          LEFT JOIN section s ON ms.responsible_section_id = s.section_id
                          WHERE ms.equipment_id = $equipment_id AND ms.is_active = 1");

// Get maintenance history
$history = $conn->query("SELECT mh.*, e.firstname, e.lastname 
                        FROM maintenance_history mh
                        LEFT JOIN employee e ON mh.performed_by = e.employee_id
                        WHERE mh.equipment_id = $equipment_id
                        ORDER BY mh.maintenance_date DESC");
?>

<div class="container">
    <?php echo $message; ?>
    
    <!-- Equipment Information -->
    <div class="card">
        <div class="card-header">
            <h2>🔧 Equipment Information</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div><strong>Equipment ID:</strong><br><?php echo $equipment['equipment_id']; ?></div>
            <div><strong>Name:</strong><br><?php echo htmlspecialchars($equipment['name']); ?></div>
            <div><strong>Model:</strong><br><?php echo htmlspecialchars($equipment['model']); ?></div>
            <div><strong>Serial No:</strong><br><?php echo htmlspecialchars($equipment['serial_no']); ?></div>
            <div><strong>Section:</strong><br><?php echo htmlspecialchars($equipment['section_name']); ?></div>
            <div><strong>Status:</strong><br><?php echo strtoupper(str_replace('_', ' ', $equipment['status'])); ?></div>
            <div><strong>Purchase Date:</strong><br><?php echo $equipment['purchase_date'] ? date('M d, Y', strtotime($equipment['purchase_date'])) : 'N/A'; ?></div>
            <div><strong>Supplier:</strong><br><?php echo htmlspecialchars($equipment['supplier']); ?></div>
        </div>
        <div style="margin-top: 1rem;">
            <strong>Remarks:</strong><br><?php echo htmlspecialchars($equipment['remarks']); ?>
        </div>
    </div>
    
    <!-- Maintenance Schedule -->
    <div class="card">
        <div class="card-header">
            <h2>📅 Maintenance Schedule</h2>
        </div>
        
        <button class="btn btn-success" onclick="showAddScheduleModal()" style="margin-bottom: 1rem;">Add Schedule</button>
        
        <?php if ($schedules->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Frequency</th>
                        <th>Next Due Date</th>
                        <th>Responsible Employee</th>
                        <th>Responsible Section</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($sched = $schedules->fetch_assoc()): 
                        $days_until = (strtotime($sched['next_due_date']) - time()) / (60 * 60 * 24);
                        $status_class = '';
                        if ($days_until < 0) $status_class = 'style="color: red; font-weight: bold;"';
                        elseif ($days_until <= 7) $status_class = 'style="color: orange; font-weight: bold;"';
                    ?>
                        <tr>
                            <td><?php echo strtoupper($sched['frequency']); ?></td>
                            <td <?php echo $status_class; ?>><?php echo date('M d, Y', strtotime($sched['next_due_date'])); ?></td>
                            <td><?php echo htmlspecialchars($sched['firstname'] . ' ' . $sched['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($sched['section_name']); ?></td>
                            <td><?php echo $days_until < 0 ? 'OVERDUE' : ($days_until <= 7 ? 'DUE SOON' : 'Scheduled'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No maintenance schedule set.</p>
        <?php endif; ?>
    </div>
    
    <!-- Maintenance History -->
    <div class="card">
        <div class="card-header">
            <h2>📝 Maintenance History</h2>
        </div>
        
        <button class="btn btn-success" onclick="showAddHistoryModal()" style="margin-bottom: 1rem;">Record Maintenance</button>
        
        <?php if ($history->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Performed By</th>
                        <th>Notes</th>
                        <th>Next Maintenance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($hist = $history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($hist['maintenance_date'])); ?></td>
                            <td><?php echo strtoupper($hist['maintenance_type']); ?></td>
                            <td><?php echo htmlspecialchars($hist['firstname'] . ' ' . $hist['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($hist['notes']); ?></td>
                            <td><?php echo $hist['next_maintenance_date'] ? date('M d, Y', strtotime($hist['next_maintenance_date'])) : 'N/A'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No maintenance history.</p>
        <?php endif; ?>
    </div>
    
    <a href="equipment.php" class="btn btn-primary">Back to Equipment List</a>
</div>

<!-- Add Schedule Modal -->
<div id="addScheduleModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Add Maintenance Schedule</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_schedule">
            
            <div class="form-group">
                <label>Frequency *</label>
                <select name="frequency" class="form-control" required>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="semi-annual">Semi-Annual</option>
                    <option value="annual">Annual</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Next Due Date *</label>
                <input type="date" name="next_due_date" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Responsible Employee</label>
                <select name="responsible_employee_id" class="form-control">
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
                <label>Responsible Section</label>
                <select name="responsible_section_id" class="form-control">
                    <option value="">Select Section</option>
                    <?php 
                    $sections = $conn->query("SELECT * FROM section");
                    while($sec = $sections->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $sec['section_id']; ?>"><?php echo htmlspecialchars($sec['label']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Add Schedule</button>
                <button type="button" class="btn btn-danger" onclick="closeAddScheduleModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Add History Modal -->
<div id="addHistoryModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Record Maintenance</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_history">
            
            <div class="form-group">
                <label>Maintenance Date *</label>
                <input type="date" name="maintenance_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label>Performed By *</label>
                <select name="performed_by" class="form-control" required>
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
                <label>Maintenance Type *</label>
                <select name="maintenance_type" class="form-control" required>
                    <option value="preventive">Preventive</option>
                    <option value="corrective">Corrective</option>
                    <option value="emergency">Emergency</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Next Maintenance Date</label>
                <input type="date" name="next_maintenance_date" class="form-control">
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Record Maintenance</button>
                <button type="button" class="btn btn-danger" onclick="closeAddHistoryModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddScheduleModal() {
    document.getElementById('addScheduleModal').style.display = 'block';
}

function closeAddScheduleModal() {
    document.getElementById('addScheduleModal').style.display = 'none';
}

function showAddHistoryModal() {
    document.getElementById('addHistoryModal').style.display = 'block';
}

function closeAddHistoryModal() {
    document.getElementById('addHistoryModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
