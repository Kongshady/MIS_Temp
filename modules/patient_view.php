<?php
require_once '../db_connection.php';
$page_title = 'View Patient';
include '../includes/header.php';

// Get patient ID
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_result') {
            $test_id = $_POST['test_id'];
            $result_value = $_POST['result_value'];
            $normal_range = $_POST['normal_range'];
            $findings = $_POST['findings'];
            $remarks = $_POST['remarks'];
            $performed_by = $_POST['performed_by'];
            $status = $_POST['status'];
            
            // Create lab test order first
            $stmt = $conn->prepare("INSERT INTO lab_test_order (patient_id, test_id, order_date, status) VALUES (?, ?, NOW(), 'completed')");
            $stmt->bind_param("ii", $patient_id, $test_id);
            $stmt->execute();
            $lab_test_order_id = $conn->insert_id;
            
            // Insert lab result
            $stmt = $conn->prepare("INSERT INTO lab_result (lab_test_order_id, patient_id, test_id, result_date, findings, normal_range, result_value, remarks, performed_by, status, datetime_added) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiissssiss", $lab_test_order_id, $patient_id, $test_id, $findings, $normal_range, $result_value, $remarks, $performed_by, $status);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Lab result added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update_result') {
            $lab_result_id = $_POST['lab_result_id'];
            $result_value = $_POST['result_value'];
            $normal_range = $_POST['normal_range'];
            $findings = $_POST['findings'];
            $remarks = $_POST['remarks'];
            $performed_by = $_POST['performed_by'];
            $status = $_POST['status'];
            
            $stmt = $conn->prepare("UPDATE lab_result SET result_value=?, normal_range=?, findings=?, remarks=?, performed_by=?, status=?, datetime_modified=NOW() WHERE lab_result_id=?");
            $stmt->bind_param("ssssssi", $result_value, $normal_range, $findings, $remarks, $performed_by, $status, $lab_result_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Lab result updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'delete_result') {
            $lab_result_id = $_POST['lab_result_id'];
            $stmt = $conn->prepare("DELETE FROM lab_result WHERE lab_result_id=?");
            $stmt->bind_param("i", $lab_result_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Lab result deleted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get patient details
$patient = $conn->query("SELECT p.*, ph.physician_name, s.label as status_label 
                        FROM patient p 
                        LEFT JOIN physician ph ON p.physician_id = ph.physician_id
                        LEFT JOIN status_code s ON p.status_code = s.status_code 
                        WHERE p.patient_id = $patient_id")->fetch_assoc();

if (!$patient) {
    echo '<div class="container"><div class="alert alert-danger">Patient not found!</div></div>';
    include '../includes/footer.php';
    exit;
}

// Get lab results
$lab_results = $conn->query("SELECT lr.*, t.label as test_name, e.firstname, e.lastname 
                             FROM lab_result lr
                             LEFT JOIN test t ON lr.test_id = t.test_id
                             LEFT JOIN employee e ON lr.performed_by = e.employee_id
                             WHERE lr.patient_id = $patient_id
                             ORDER BY lr.result_date DESC");

$age = date_diff(date_create($patient['birthdate']), date_create('now'))->y;
?>

<div class="container">
    <?php echo $message; ?>
    
    <!-- Patient Information -->
    <div class="card">
        <div class="card-header">
            <h2>👤 Patient Information</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div>
                <strong>Patient ID:</strong><br>
                <?php echo $patient['patient_id']; ?>
            </div>
            <div>
                <strong>Name:</strong><br>
                <?php echo htmlspecialchars($patient['firstname'] . ' ' . $patient['middlename'] . ' ' . $patient['lastname']); ?>
            </div>
            <div>
                <strong>Patient Type:</strong><br>
                <?php echo strtoupper($patient['patient_type']); ?>
            </div>
            <div>
                <strong>Age:</strong><br>
                <?php echo $age; ?> years old
            </div>
            <div>
                <strong>Birthdate:</strong><br>
                <?php echo date('M d, Y', strtotime($patient['birthdate'])); ?>
            </div>
            <div>
                <strong>Gender:</strong><br>
                <?php echo htmlspecialchars($patient['gender']); ?>
            </div>
            <div>
                <strong>Contact Number:</strong><br>
                <?php echo htmlspecialchars($patient['contact_number']); ?>
            </div>
            <div>
                <strong>Address:</strong><br>
                <?php echo htmlspecialchars($patient['address']); ?>
            </div>
            <div>
                <strong>Physician:</strong><br>
                <?php echo htmlspecialchars($patient['physician_name']); ?>
            </div>
            <div>
                <strong>Status:</strong><br>
                <?php echo htmlspecialchars($patient['status_label']); ?>
            </div>
        </div>
    </div>
    
    <!-- Laboratory Results -->
    <div class="card">
        <div class="card-header">
            <h2>🧪 Laboratory Test Results</h2>
        </div>
        
        <button class="btn btn-success" onclick="showAddResultModal()" style="margin-bottom: 1rem;">Add Result</button>
        
        <?php if ($lab_results->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Result Date</th>
                        <th>Test Name</th>
                        <th>Result Value</th>
                        <th>Normal Range</th>
                        <th>Status</th>
                        <th>Performed By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($result = $lab_results->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y h:i A', strtotime($result['result_date'])); ?></td>
                            <td><?php echo htmlspecialchars($result['test_name']); ?></td>
                            <td><?php echo htmlspecialchars($result['result_value']); ?></td>
                            <td><?php echo htmlspecialchars($result['normal_range']); ?></td>
                            <td><?php echo strtoupper($result['status']); ?></td>
                            <td><?php echo htmlspecialchars($result['firstname'] . ' ' . $result['lastname']); ?></td>
                            <td class="table-actions">
                                <button class="btn btn-info btn-sm" onclick="viewResult(<?php echo htmlspecialchars(json_encode($result)); ?>)">View</button>
                                <button class="btn btn-warning btn-sm" onclick="editResult(<?php echo htmlspecialchars(json_encode($result)); ?>)">Update</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this result?');">
                                    <input type="hidden" name="action" value="delete_result">
                                    <input type="hidden" name="lab_result_id" value="<?php echo $result['lab_result_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No laboratory results found for this patient.</p>
        <?php endif; ?>
    </div>
    
    <a href="patients.php" class="btn btn-primary">Back to Patients</a>
</div>

<!-- Add Result Modal -->
<div id="addResultModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 700px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Add Laboratory Result</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_result">
            
            <div class="form-group">
                <label>Laboratory Test *</label>
                <select name="test_id" class="form-control" required>
                    <option value="">Select Test</option>
                    <?php 
                    $tests = $conn->query("SELECT * FROM test ORDER BY label");
                    while($test = $tests->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $test['test_id']; ?>"><?php echo htmlspecialchars($test['label']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Result Value *</label>
                <input type="text" name="result_value" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Normal Range</label>
                <input type="text" name="normal_range" class="form-control" placeholder="e.g., 70-100 mg/dL">
            </div>
            
            <div class="form-group">
                <label>Findings</label>
                <textarea name="findings" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label>Performed By *</label>
                <select name="performed_by" class="form-control" required>
                    <option value="">Select Employee</option>
                    <?php 
                    $employees = $conn->query("SELECT employee_id, firstname, lastname FROM employee WHERE status_code = 1");
                    while($emp = $employees->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" class="form-control" required>
                    <option value="draft">Draft</option>
                    <option value="final">Final</option>
                    <option value="revised">Revised</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Submit</button>
                <button type="button" class="btn btn-danger" onclick="closeAddResultModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Result Modal -->
<div id="editResultModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 700px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Update Laboratory Result</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_result">
            <input type="hidden" name="lab_result_id" id="edit_lab_result_id">
            
            <div class="form-group">
                <label>Result Value *</label>
                <input type="text" name="result_value" id="edit_result_value" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Normal Range</label>
                <input type="text" name="normal_range" id="edit_normal_range" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Findings</label>
                <textarea name="findings" id="edit_findings" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" id="edit_remarks" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label>Performed By *</label>
                <select name="performed_by" id="edit_performed_by" class="form-control" required>
                    <?php 
                    $employees = $conn->query("SELECT employee_id, firstname, lastname FROM employee WHERE status_code = 1");
                    while($emp = $employees->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" id="edit_status" class="form-control" required>
                    <option value="draft">Draft</option>
                    <option value="final">Final</option>
                    <option value="revised">Revised</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Submit</button>
                <button type="button" class="btn btn-danger" onclick="closeEditResultModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- View Result Modal -->
<div id="viewResultModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 700px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Laboratory Result Details</h3>
        <div id="viewResultContent"></div>
        <button type="button" class="btn btn-primary" onclick="closeViewResultModal()">Close</button>
    </div>
</div>

<script>
function showAddResultModal() {
    document.getElementById('addResultModal').style.display = 'block';
}

function closeAddResultModal() {
    document.getElementById('addResultModal').style.display = 'none';
}

function editResult(result) {
    document.getElementById('edit_lab_result_id').value = result.lab_result_id;
    document.getElementById('edit_result_value').value = result.result_value;
    document.getElementById('edit_normal_range').value = result.normal_range;
    document.getElementById('edit_findings').value = result.findings;
    document.getElementById('edit_remarks').value = result.remarks;
    document.getElementById('edit_performed_by').value = result.performed_by;
    document.getElementById('edit_status').value = result.status;
    document.getElementById('editResultModal').style.display = 'block';
}

function closeEditResultModal() {
    document.getElementById('editResultModal').style.display = 'none';
}

function viewResult(result) {
    const content = `
        <div style="line-height: 2;">
            <p><strong>Test Name:</strong> ${result.test_name}</p>
            <p><strong>Result Date:</strong> ${new Date(result.result_date).toLocaleString()}</p>
            <p><strong>Result Value:</strong> ${result.result_value}</p>
            <p><strong>Normal Range:</strong> ${result.normal_range || 'N/A'}</p>
            <p><strong>Findings:</strong><br>${result.findings || 'N/A'}</p>
            <p><strong>Remarks:</strong><br>${result.remarks || 'N/A'}</p>
            <p><strong>Status:</strong> ${result.status.toUpperCase()}</p>
            <p><strong>Performed By:</strong> ${result.firstname} ${result.lastname}</p>
        </div>
    `;
    document.getElementById('viewResultContent').innerHTML = content;
    document.getElementById('viewResultModal').style.display = 'block';
}

function closeViewResultModal() {
    document.getElementById('viewResultModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
