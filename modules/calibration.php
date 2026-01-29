<?php
require_once '../db_connection.php';
$page_title = 'Calibration & Testing Monitoring';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_procedure') {
            $equipment_id = $_POST['equipment_id'];
            $procedure_name = $_POST['procedure_name'];
            $standard_reference = $_POST['standard_reference'];
            $frequency = $_POST['frequency'];
            $next_due_date = $_POST['next_due_date'];
            
            $stmt = $conn->prepare("INSERT INTO calibration_procedure (equipment_id, procedure_name, standard_reference, frequency, next_due_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $equipment_id, $procedure_name, $standard_reference, $frequency, $next_due_date);
            
            if ($stmt->execute()) {
                $equipment_name = $conn->query("SELECT name FROM equipment WHERE equipment_id = $equipment_id")->fetch_assoc()['name'];
                log_activity($conn, get_user_id(), "Added calibration procedure: $procedure_name for $equipment_name ($frequency)", 1);
                $message = '<div class="alert alert-success">Calibration procedure added!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'add_record') {
            $procedure_id = $_POST['procedure_id'];
            $equipment_id = $_POST['equipment_id'];
            $calibration_date = $_POST['calibration_date'];
            $performed_by = $_POST['performed_by'];
            $result_status = $_POST['result_status'];
            $notes = $_POST['notes'];
            $next_calibration_date = !empty($_POST['next_calibration_date']) ? $_POST['next_calibration_date'] : NULL;
            
            $stmt = $conn->prepare("INSERT INTO calibration_record (procedure_id, equipment_id, calibration_date, performed_by, result_status, notes, next_calibration_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisisss", $procedure_id, $equipment_id, $calibration_date, $performed_by, $result_status, $notes, $next_calibration_date);
            
            if ($stmt->execute()) {
                if ($next_calibration_date) {
                    $conn->query("UPDATE calibration_procedure SET next_due_date='$next_calibration_date' WHERE procedure_id=$procedure_id");
                }
                $equipment_name = $conn->query("SELECT name FROM equipment WHERE equipment_id = $equipment_id")->fetch_assoc()['name'];
                log_activity($conn, get_user_id(), "Recorded calibration for $equipment_name - Result: $result_status", 1);
                $message = '<div class="alert alert-success">Calibration record added!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get upcoming calibrations
$upcoming_calibrations = $conn->query("SELECT * FROM v_upcoming_calibration");
?>

<div class="container">
    <?php echo $message; ?>
    
    <h1><i class="fas fa-balance-scale"></i> Calibration & Testing Monitoring</h1>
    
    <!-- Calibration Alerts -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Calibration Alerts</h2>
        </div>
        
        <?php if ($upcoming_calibrations->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Procedure</th>
                        <th>Standard Reference</th>
                        <th>Due Date</th>
                        <th>Days Until Due</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($cal = $upcoming_calibrations->fetch_assoc()): 
                        $status_color = '';
                        if (isset($cal['days_until_due'])) {
                            if ($cal['days_until_due'] < 0) $status_color = 'style="background-color: #ffe5e5;"';
                            elseif ($cal['days_until_due'] <= 7) $status_color = 'style="background-color: #fff3e5;"';
                        }
                    ?>
                        <tr <?php echo $status_color; ?>>
                            <td><?php echo htmlspecialchars($cal['equipment_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($cal['procedure_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($cal['standard_reference'] ?? 'N/A'); ?></td>
                            <td><?php echo isset($cal['next_due_date']) ? date('M d, Y', strtotime($cal['next_due_date'])) : 'N/A'; ?></td>
                            <td><?php echo $cal['days_until_due'] ?? 'N/A'; ?></td>
                            <td><?php echo $cal['alert_status'] ?? 'N/A'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="showRecordCalibrationModal(<?php echo isset($cal['procedure_id']) ? $cal['procedure_id'] : 0; ?>, <?php echo isset($cal['equipment_id']) ? $cal['equipment_id'] : 0; ?>, '<?php echo htmlspecialchars($cal['equipment_name'] ?? ''); ?>', '<?php echo htmlspecialchars($cal['procedure_name'] ?? ''); ?>')">Record Calibration</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>All calibrations are up to date!</p>
        <?php endif; ?>
    </div>
    
    <!-- Calibration Procedures -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-clipboard-list"></i> Calibration Procedures</h2>
        </div>
        
        <button class="btn btn-success" onclick="showAddProcedureModal()" style="margin-bottom: 1rem;">Add New Procedure</button>
        
        <?php
        $procedures = $conn->query("SELECT DISTINCT cp.*, e.name as equipment_name, e.model 
                                   FROM calibration_procedure cp
                                   JOIN equipment e ON cp.equipment_id = e.equipment_id
                                   WHERE cp.is_active = 1
                                   ORDER BY cp.next_due_date");
        ?>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Equipment</th>
                    <th>Procedure Name</th>
                    <th>Standard Reference</th>
                    <th>Frequency</th>
                    <th>Next Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($proc = $procedures->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($proc['equipment_name']); ?> (<?php echo htmlspecialchars($proc['model']); ?>)</td>
                        <td><?php echo htmlspecialchars($proc['procedure_name']); ?></td>
                        <td><?php echo htmlspecialchars($proc['standard_reference']); ?></td>
                        <td><?php echo strtoupper($proc['frequency']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($proc['next_due_date'])); ?></td>
                        <td>
                            <a href="calibration_details.php?id=<?php echo $proc['procedure_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Recent Calibration Records -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-bar"></i> Recent Calibration Records</h2>
        </div>
        
        <?php
        $records = $conn->query("SELECT DISTINCT cr.*, e.name as equipment_name, 
                                cp.procedure_name, emp.firstname, emp.lastname
                                FROM calibration_record cr
                                JOIN equipment e ON cr.equipment_id = e.equipment_id
                                LEFT JOIN calibration_procedure cp ON cr.procedure_id = cp.procedure_id
                                LEFT JOIN employee emp ON cr.performed_by = emp.employee_id
                                ORDER BY cr.calibration_date DESC
                                LIMIT 20");
        ?>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Equipment</th>
                    <th>Procedure</th>
                    <th>Performed By</th>
                    <th>Result</th>
                    <th>Notes</th>
                    <th>Next Due</th>
                </tr>
            </thead>
            <tbody>
                <?php while($rec = $records->fetch_assoc()): 
                    $result_class = '';
                    if ($rec['result_status'] == 'pass') $result_class = 'style="color: green; font-weight: bold;"';
                    elseif ($rec['result_status'] == 'fail') $result_class = 'style="color: red; font-weight: bold;"';
                ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($rec['calibration_date'])); ?></td>
                        <td><?php echo htmlspecialchars($rec['equipment_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($rec['procedure_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(($rec['firstname'] ?? '') . ' ' . ($rec['lastname'] ?? '')); ?></td>
                        <td <?php echo $result_class; ?>><?php echo strtoupper($rec['result_status']); ?></td>
                        <td><?php echo htmlspecialchars($rec['notes'] ?? ''); ?></td>
                        <td><?php echo $rec['next_calibration_date'] ? date('M d, Y', strtotime($rec['next_calibration_date'])) : 'N/A'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Procedure Modal -->
<div id="addProcedureModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Add Calibration Procedure</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_procedure">
            
            <div class="form-group">
                <label>Equipment *</label>
                <select name="equipment_id" class="form-control" required>
                    <option value="">Select Equipment</option>
                    <?php 
                    $equipment = $conn->query("SELECT * FROM equipment WHERE status IN ('operational', 'under_maintenance')");
                    while($eq = $equipment->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $eq['equipment_id']; ?>"><?php echo htmlspecialchars($eq['name'] . ' (' . $eq['model'] . ')'); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Procedure Name *</label>
                <input type="text" name="procedure_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Standard Reference *</label>
                <input type="text" name="standard_reference" class="form-control" required placeholder="e.g., ISO 17025, ASTM D4307">
            </div>
            
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
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Add Procedure</button>
                <button type="button" class="btn btn-danger" onclick="closeAddProcedureModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Record Calibration Modal -->
<div id="recordCalibrationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Record Calibration</h3>
        <div id="calibration-info" style="margin-bottom: 1rem; padding: 1rem; background: #f0f0f0; border-radius: 5px;"></div>
        <form method="POST">
            <input type="hidden" name="action" value="add_record">
            <input type="hidden" name="procedure_id" id="record_procedure_id">
            <input type="hidden" name="equipment_id" id="record_equipment_id">
            
            <div class="form-group">
                <label>Calibration Date *</label>
                <input type="date" name="calibration_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
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
                <label>Result Status *</label>
                <select name="result_status" class="form-control" required>
                    <option value="pass">Pass</option>
                    <option value="fail">Fail</option>
                    <option value="conditional">Conditional</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Next Calibration Date</label>
                <input type="date" name="next_calibration_date" class="form-control">
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Record Calibration</button>
                <button type="button" class="btn btn-danger" onclick="closeRecordCalibrationModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddProcedureModal() {
    document.getElementById('addProcedureModal').style.display = 'block';
}

function closeAddProcedureModal() {
    document.getElementById('addProcedureModal').style.display = 'none';
}

function showRecordCalibrationModal(procedureId, equipmentId, equipmentName, procedureName) {
    document.getElementById('record_procedure_id').value = procedureId;
    document.getElementById('record_equipment_id').value = equipmentId;
    document.getElementById('calibration-info').innerHTML = '<strong>Equipment:</strong> ' + equipmentName + '<br><strong>Procedure:</strong> ' + procedureName;
    document.getElementById('recordCalibrationModal').style.display = 'block';
}

function closeRecordCalibrationModal() {
    document.getElementById('recordCalibrationModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
