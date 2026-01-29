<?php
require_once '../db_connection.php';
$page_title = 'Patient Details';
include '../includes/header.php';

// Get patient ID
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$patient_id) {
    header('Location: patients.php');
    exit;
}

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_result') {
            $test_id = $_POST['test_id'];
            $result_value = $_POST['result_value'];
            $result_date = $_POST['result_date'];
            $remarks = $_POST['remarks'];
            $performed_by = $_SESSION['user_id'] ?? 1; // Get from session or default to 1
            
            // Create a lab test order first (required by schema)
            $order_stmt = $conn->prepare("INSERT INTO lab_test_order (patient_id, test_id, order_date, status) VALUES (?, ?, NOW(), 'completed')");
            $order_stmt->bind_param("ii", $patient_id, $test_id);
            $order_stmt->execute();
            $lab_test_order_id = $conn->insert_id;
            
            $stmt = $conn->prepare("INSERT INTO lab_result (lab_test_order_id, patient_id, test_id, result_value, result_date, remarks, performed_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'final')");
            $stmt->bind_param("iiisssi", $lab_test_order_id, $patient_id, $test_id, $result_value, $result_date, $remarks, $performed_by);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Lab result added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update_result') {
            $lab_result_id = $_POST['lab_result_id'];
            $result_value = $_POST['result_value'];
            $result_date = $_POST['result_date'];
            $remarks = $_POST['remarks'];
            
            $stmt = $conn->prepare("UPDATE lab_result SET result_value=?, result_date=?, remarks=? WHERE lab_result_id=?");
            $stmt->bind_param("sssi", $result_value, $result_date, $remarks, $lab_result_id);
            
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
$patient_query = $conn->prepare("SELECT p.* 
                                 FROM patient p 
                                 WHERE p.patient_id = ?");
$patient_query->bind_param("i", $patient_id);
$patient_query->execute();
$patient_result = $patient_query->get_result();

if ($patient_result->num_rows == 0) {
    header('Location: patients.php');
    exit;
}

$patient = $patient_result->fetch_assoc();

// Get lab results for this patient
$lab_results = $conn->query("SELECT lr.*, t.label as test_name, e.firstname, e.lastname 
                             FROM lab_result lr 
                             LEFT JOIN test t ON lr.test_id = t.test_id 
                             LEFT JOIN employee e ON lr.performed_by = e.employee_id
                             WHERE lr.patient_id = $patient_id 
                             ORDER BY lr.result_date DESC, lr.datetime_added DESC");
?>

<div class="container">
    <?php echo $message; ?>
    
    <!-- Patient Information Card -->
    <div class="card">
        <div class="card-header">
            <h2>👤 Patient Information</h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div>
                <strong>Patient ID:</strong>
                <p><?php echo $patient['patient_id']; ?></p>
            </div>
            <div>
                <strong>Patient Type:</strong>
                <p>
                    <span class="badge <?php echo $patient['patient_type'] == 'Internal' ? 'badge-info' : 'badge-success'; ?>">
                        <?php echo htmlspecialchars($patient['patient_type']); ?>
                    </span>
                </p>
            </div>
            <div>
                <strong>Full Name:</strong>
                <p><?php echo htmlspecialchars($patient['firstname'] . ' ' . ($patient['middlename'] ? $patient['middlename'] . ' ' : '') . $patient['lastname']); ?></p>
            </div>
            <div>
                <strong>Date of Birth:</strong>
                <p><?php echo $patient['birthdate'] ? date('F d, Y', strtotime($patient['birthdate'])) : 'N/A'; ?></p>
            </div>
            <div>
                <strong>Age:</strong>
                <p><?php 
                    if ($patient['birthdate']) {
                        $dob = new DateTime($patient['birthdate']);
                        $now = new DateTime();
                        echo $dob->diff($now)->y . ' years old';
                    } else {
                        echo 'N/A';
                    }
                ?></p>
            </div>
            <div>
                <strong>Gender:</strong>
                <p><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <strong>Contact Number:</strong>
                <p><?php echo htmlspecialchars($patient['contact_number'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <strong>Address:</strong>
                <p><?php echo htmlspecialchars($patient['address'] ?? 'N/A'); ?></p>
            </div>
        </div>
        
        <div style="margin-top: 1.5rem;">
            <a href="patients.php" class="btn btn-secondary">Back to Patient List</a>
        </div>
    </div>
    
    <!-- Laboratory Test Results -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-microscope"></i> Laboratory Test Results</h2>
        </div>
        <?php if ($lab_results && $lab_results->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Result Date</th>
                        <th>Test Name</th>
                        <th>Result Value</th>
                        <th>Remarks</th>
                        <th>Processed By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($result = $lab_results->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($result['result_date'])); ?></td>
                            <td><?php echo htmlspecialchars($result['test_name']); ?></td>
                            <td><strong><?php echo htmlspecialchars($result['result_value']); ?></strong></td>
                            <td><?php echo htmlspecialchars($result['remarks'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($result['firstname'] . ' ' . $result['lastname']); ?></td>
                            <td class="table-actions">
                                <button class="btn btn-info btn-sm" onclick="viewResult(<?php echo htmlspecialchars(json_encode($result)); ?>)">View</button>
                            </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
            </table>
        <?php else: ?>
            <p>No laboratory results found for this patient.</p>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Add Result Modal -->
<div id="addResultModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px; max-height: 90vh; overflow-y: auto;">
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
                        <option value="<?php echo $test['test_id']; ?>">
                            <?php echo htmlspecialchars($test['label']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Result Value *</label>
                <input type="text" name="result_value" class="form-control" required placeholder="Enter result value">
            </div>
            
            <div class="form-group">
                <label>Result Date *</label>
                <input type="date" name="result_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="Optional notes or remarks"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Add Result</button>
                <button type="button" class="btn btn-danger" onclick="closeAddResultModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- View Result Modal -->
<div id="editResultModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px; max-height: 90vh; overflow-y: auto;">
        <h3>View Laboratory Result</h3>
        <div>
            <input type="hidden" id="edit_lab_result_id">
            
            <div class="form-group">
                <label>Test Name</label>
                <input type="text" id="edit_test_name" class="form-control" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Result Value</label>
                <input type="text" id="edit_result_value" class="form-control" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Result Date</label>
                <input type="date" id="edit_result_date" class="form-control" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Remarks</label>
                <textarea id="edit_remarks" class="form-control" rows="3" readonly style="background: #f5f5f5;"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeEditResultModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showAddResultModal() {
    document.getElementById('addResultModal').style.display = 'block';
}

function closeAddResultModal() {
    document.getElementById('addResultModal').style.display = 'none';
}

function viewResult(result) {
    document.getElementById('edit_lab_result_id').value = result.lab_result_id;
    document.getElementById('edit_test_name').value = result.test_name;
    document.getElementById('edit_result_value').value = result.result_value;
    document.getElementById('edit_result_date').value = result.result_date;
    document.getElementById('edit_remarks').value = result.remarks || '';
    
    document.getElementById('editResultModal').style.display = 'block';
}

function closeEditResultModal() {
    document.getElementById('editResultModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addResultModal');
    const editModal = document.getElementById('editResultModal');
    
    if (event.target == addModal) {
        addModal.style.display = 'none';
    }
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
