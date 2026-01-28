<?php
require_once '../db_connection.php';
$page_title = 'Order Details';
include '../includes/header.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    header('Location: lab_results_v2.php');
    exit;
}

$current_user_id = $_SESSION['user_id'] ?? 1;

// Handle result submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_result') {
            $order_test_id = $_POST['order_test_id'];
            $result_value = $_POST['result_value'];
            $result_date = $_POST['result_date'];
            $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;
            $status = $_POST['status'];
            
            // Get order_test details
            $ot = $conn->query("SELECT ot.*, lto.patient_id FROM order_tests ot 
                               JOIN lab_test_order lto ON ot.order_id = lto.lab_test_order_id 
                               WHERE ot.order_test_id = $order_test_id")->fetch_assoc();
            
            if ($ot) {
                $stmt = $conn->prepare("INSERT INTO lab_result (order_test_id, lab_test_order_id, patient_id, test_id, result_value, result_date, remarks, performed_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiisssss", $order_test_id, $ot['order_id'], $ot['patient_id'], $ot['test_id'], $result_value, $result_date, $remarks, $current_user_id, $status);
                
                if ($stmt->execute()) {
                    // Update order_test status
                    $conn->query("UPDATE order_tests SET status = 'completed' WHERE order_test_id = $order_test_id");
                    
                    // Update overall order status
                    $pending = $conn->query("SELECT COUNT(*) as count FROM order_tests WHERE order_id = {$ot['order_id']} AND status = 'pending'")->fetch_assoc()['count'];
                    
                    if ($pending == 0) {
                        $conn->query("UPDATE lab_test_order SET status = 'completed' WHERE lab_test_order_id = {$ot['order_id']}");
                    } else {
                        $conn->query("UPDATE lab_test_order SET status = 'in_progress' WHERE lab_test_order_id = {$ot['order_id']}");
                    }
                    
                    $message = '<div class="alert alert-success">✓ Result added successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
            }
        } elseif ($_POST['action'] == 'update_result') {
            $lab_result_id = $_POST['lab_result_id'];
            $result_value = $_POST['result_value'];
            $result_date = $_POST['result_date'];
            $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;
            $status = $_POST['status'];
            
            $stmt = $conn->prepare("UPDATE lab_result SET result_value=?, result_date=?, remarks=?, status=? WHERE lab_result_id=?");
            $stmt->bind_param("ssssi", $result_value, $result_date, $remarks, $status, $lab_result_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">✓ Result updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get order details
$order = $conn->query("SELECT lto.*, CONCAT(p.firstname, ' ', p.lastname) as patient_name, p.patient_type
                       FROM lab_test_order lto
                       JOIN patient p ON lto.patient_id = p.patient_id
                       WHERE lto.lab_test_order_id = $order_id")->fetch_assoc();

if (!$order) {
    header('Location: lab_results_v2.php');
    exit;
}

// Get all tests in this order
$tests = $conn->query("SELECT ot.*, t.label as test_name, s.label as section_name,
                       lr.lab_result_id, lr.result_value, lr.result_date, lr.remarks, lr.status as result_status,
                       CONCAT(e.firstname, ' ', e.lastname) as performed_by_name
                       FROM order_tests ot
                       JOIN test t ON ot.test_id = t.test_id
                       LEFT JOIN section s ON t.section_id = s.section_id
                       LEFT JOIN lab_result lr ON ot.order_test_id = lr.order_test_id
                       LEFT JOIN employee e ON lr.performed_by = e.employee_id
                       WHERE ot.order_id = $order_id
                       ORDER BY s.label, t.label");
?>

<div class="container">
    <a href="lab_results_v2.php" class="btn btn-secondary" style="margin-bottom: 1rem;">← Back to Orders</a>
    
    <?php echo $message; ?>
    
    <!-- Order Information -->
    <div class="card">
        <div class="card-header">
            <h2>📋 Order #<?php echo $order['lab_test_order_id']; ?> Details</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
            <div>
                <strong>Patient:</strong><br>
                <a href="patient_details.php?id=<?php echo $order['patient_id']; ?>">
                    <?php echo htmlspecialchars($order['patient_name']); ?>
                </a>
                <span class="badge badge-<?php echo $order['patient_type'] == 'Student/Faculty' ? 'info' : 'secondary'; ?>">
                    <?php echo htmlspecialchars($order['patient_type']); ?>
                </span>
            </div>
            <div>
                <strong>Order Date:</strong><br>
                <?php echo date('F d, Y h:i A', strtotime($order['order_date'])); ?>
            </div>
            <div>
                <strong>Order Status:</strong><br>
                <?php
                $status_colors = [
                    'pending' => 'warning',
                    'in_progress' => 'info',
                    'completed' => 'success',
                    'cancelled' => 'danger'
                ];
                $color = $status_colors[$order['status']] ?? 'secondary';
                ?>
                <span class="badge badge-<?php echo $color; ?>" style="font-size: 1rem;">
                    <?php echo strtoupper(str_replace('_', ' ', $order['status'])); ?>
                </span>
            </div>
            <?php if ($order['remarks']): ?>
            <div>
                <strong>Remarks:</strong><br>
                <?php echo htmlspecialchars($order['remarks']); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tests and Results -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h2>🧪 Tests & Results</h2>
        </div>
        
        <div style="display: grid; gap: 1.5rem;">
            <?php 
            $test_num = 1;
            while($test = $tests->fetch_assoc()): 
            ?>
                <div style="border: 2px solid <?php echo $test['lab_result_id'] ? '#28a745' : '#ffc107'; ?>; border-radius: 10px; padding: 1.5rem; background: #fff;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="margin: 0; color: #333;">
                                <?php echo $test_num++; ?>. <?php echo htmlspecialchars($test['test_name']); ?>
                            </h3>
                            <p style="color: #666; margin: 0.25rem 0 0 0;">
                                <strong>Section:</strong> <?php echo htmlspecialchars($test['section_name']); ?>
                            </p>
                        </div>
                        <div>
                            <?php
                            $test_status_colors = [
                                'pending' => 'warning',
                                'in_progress' => 'info',
                                'completed' => 'success'
                            ];
                            $test_color = $test_status_colors[$test['status']] ?? 'secondary';
                            ?>
                            <span class="badge badge-<?php echo $test_color; ?>">
                                <?php echo strtoupper(str_replace('_', ' ', $test['status'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($test['lab_result_id']): ?>
                        <!-- Show Result -->
                        <div style="background: #e8f5e9; padding: 1rem; border-radius: 5px; border-left: 4px solid #28a745;">
                            <div style="margin-bottom: 0.5rem;">
                                <strong>Result:</strong>
                                <div style="white-space: pre-wrap; margin-top: 0.5rem; padding: 0.5rem; background: white; border-radius: 3px;">
                                    <?php echo htmlspecialchars($test['result_value']); ?>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                                <div>
                                    <strong>Result Date:</strong> <?php echo date('M d, Y', strtotime($test['result_date'])); ?>
                                </div>
                                <div>
                                    <strong>Status:</strong> 
                                    <span class="badge badge-<?php echo $test['result_status'] == 'final' ? 'success' : 'warning'; ?>">
                                        <?php echo strtoupper($test['result_status']); ?>
                                    </span>
                                </div>
                                <?php if ($test['performed_by_name']): ?>
                                <div>
                                    <strong>Performed By:</strong> <?php echo htmlspecialchars($test['performed_by_name']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($test['remarks']): ?>
                                <div style="margin-top: 0.5rem;">
                                    <strong>Remarks:</strong> <?php echo htmlspecialchars($test['remarks']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-top: 1rem;">
                                <button class="btn btn-warning btn-sm" onclick="editResult(<?php echo htmlspecialchars(json_encode($test)); ?>)">
                                    ✏️ Edit Result
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Add Result Form -->
                        <div style="background: #fff3cd; padding: 1rem; border-radius: 5px; border-left: 4px solid #ffc107;">
                            <p style="margin: 0 0 1rem 0; color: #856404;">
                                <strong>⚠️ No result entered yet</strong>
                            </p>
                            <button class="btn btn-primary" onclick="showAddResultForm(<?php echo htmlspecialchars(json_encode($test)); ?>)">
                                + Add Result
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Result Modal -->
<div id="resultModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 700px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3 id="modalTitle">Add Lab Result</h3>
        <form method="POST" id="resultForm">
            <input type="hidden" name="action" id="action" value="add_result">
            <input type="hidden" name="order_test_id" id="order_test_id">
            <input type="hidden" name="lab_result_id" id="lab_result_id">
            
            <div id="testInfo" style="background: #f0f8ff; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                <!-- Will be populated by JS -->
            </div>
            
            <div class="form-group">
                <label>Result Value *</label>
                <textarea name="result_value" id="result_value" class="form-control" rows="4" required placeholder="Enter test result value"></textarea>
            </div>
            
            <div class="form-group">
                <label>Result Date *</label>
                <input type="date" name="result_date" id="result_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Result Status *</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="preliminary">Preliminary</option>
                    <option value="final" selected>Final</option>
                    <option value="corrected">Corrected</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="Additional notes"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Save Result</button>
                <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddResultForm(test) {
    document.getElementById('modalTitle').textContent = 'Add Result - ' + test.test_name;
    document.getElementById('action').value = 'add_result';
    document.getElementById('order_test_id').value = test.order_test_id;
    document.getElementById('lab_result_id').value = '';
    
    document.getElementById('testInfo').innerHTML = `
        <strong>Test:</strong> ${test.test_name}<br>
        <strong>Section:</strong> ${test.section_name}
    `;
    
    document.getElementById('result_value').value = '';
    document.getElementById('result_date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('status').value = 'final';
    document.getElementById('remarks').value = '';
    
    document.getElementById('resultModal').style.display = 'block';
}

function editResult(test) {
    document.getElementById('modalTitle').textContent = 'Edit Result - ' + test.test_name;
    document.getElementById('action').value = 'update_result';
    document.getElementById('order_test_id').value = test.order_test_id;
    document.getElementById('lab_result_id').value = test.lab_result_id;
    
    document.getElementById('testInfo').innerHTML = `
        <strong>Test:</strong> ${test.test_name}<br>
        <strong>Section:</strong> ${test.section_name}
    `;
    
    document.getElementById('result_value').value = test.result_value || '';
    document.getElementById('result_date').value = test.result_date ? test.result_date.split(' ')[0] : '<?php echo date('Y-m-d'); ?>';
    document.getElementById('status').value = test.result_status || 'final';
    document.getElementById('remarks').value = test.remarks || '';
    
    document.getElementById('resultModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('resultModal').style.display = 'none';
}

// Badge styles
const style = document.createElement('style');
style.textContent = `
    .badge {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    .badge-warning { background-color: #ffc107; color: #000; }
    .badge-info { background-color: #17a2b8; color: #fff; }
    .badge-success { background-color: #28a745; color: #fff; }
    .badge-danger { background-color: #dc3545; color: #fff; }
    .badge-secondary { background-color: #6c757d; color: #fff; }
`;
document.head.appendChild(style);
</script>

<?php include '../includes/footer.php'; ?>
