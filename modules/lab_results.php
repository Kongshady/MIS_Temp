<?php
require_once '../db_connection.php';
$page_title = 'Laboratory Results Management';
include '../includes/header.php';

// Get current user info
$current_user_id = $_SESSION['user_id'] ?? 1;
$current_user_name = $_SESSION['username'] ?? 'Admin';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'create_order') {
            $patient_id = $_POST['patient_id'];
            $test_ids = isset($_POST['test_id']) ? (array)$_POST['test_id'] : [];
            $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;
            
            if (empty($test_ids)) {
                $message = '<div class="alert alert-danger">Please select at least one test!</div>';
            } else {
                // Create ONE order for this patient request
                $stmt = $conn->prepare("INSERT INTO lab_test_order (patient_id, order_date, status, remarks) VALUES (?, NOW(), 'pending', ?)");
                $stmt->bind_param("is", $patient_id, $remarks);
                
                if ($stmt->execute()) {
                    $order_id = $conn->insert_id;
                    
                    // Link all selected tests to this ONE order
                    $test_stmt = $conn->prepare("INSERT INTO order_tests (order_id, test_id, status) VALUES (?, ?, 'pending')");
                    $success_count = 0;
                    
                    foreach ($test_ids as $test_id) {
                        $test_id = intval($test_id);
                        $test_stmt->bind_param("ii", $order_id, $test_id);
                        if ($test_stmt->execute()) {
                            $success_count++;
                        }
                    }
                    
                    $message = '<div class="alert alert-success">✓ Order #' . $order_id . ' created with ' . $success_count . ' test(s)!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
            }
        } elseif ($_POST['action'] == 'add_result') {
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
                    // Update order_test status to completed
                    $conn->query("UPDATE order_tests SET status = 'completed' WHERE order_test_id = $order_test_id");
                    
                    // Update overall order status
                    $pending_tests = $conn->query("SELECT COUNT(*) as count FROM order_tests WHERE order_id = {$ot['order_id']} AND status = 'pending'")->fetch_assoc()['count'];
                    $total_tests = $conn->query("SELECT COUNT(*) as count FROM order_tests WHERE order_id = {$ot['order_id']}")->fetch_assoc()['count'];
                    
                    if ($pending_tests == 0) {
                        $conn->query("UPDATE lab_test_order SET status = 'completed' WHERE lab_test_order_id = {$ot['order_id']}");
                    } else {
                        $conn->query("UPDATE lab_test_order SET status = 'in_progress' WHERE lab_test_order_id = {$ot['order_id']}");
                    }
                    
                    $message = '<div class="alert alert-success">✓ Lab result added successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Error: Test order not found!</div>';
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
                $message = '<div class="alert alert-success">✓ Lab result updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'pending';
$filter_section = isset($_GET['section']) ? $_GET['section'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Build query - ONE row per ORDER
$query = "SELECT lto.*, 
          CONCAT(p.firstname, ' ', p.lastname) as patient_name,
          p.patient_type,
          COUNT(ot.order_test_id) as total_tests,
          SUM(CASE WHEN ot.status = 'completed' THEN 1 ELSE 0 END) as completed_tests
          FROM lab_test_order lto
          JOIN patient p ON lto.patient_id = p.patient_id
          LEFT JOIN order_tests ot ON lto.lab_test_order_id = ot.order_id
          WHERE 1=1";

if ($filter_status && $filter_status != 'all') {
    $query .= " AND lto.status = '$filter_status'";
}

if ($filter_date) {
    $query .= " AND DATE(lto.order_date) = '$filter_date'";
}

$query .= " GROUP BY lto.lab_test_order_id ORDER BY lto.order_date DESC, lto.lab_test_order_id DESC";

$orders = $conn->query($query);

// Get pending orders count
$pending_count = $conn->query("SELECT COUNT(*) as count FROM lab_test_order WHERE status IN ('pending', 'in_progress')")->fetch_assoc()['count'];

// Get sections for filter
$sections = $conn->query("SELECT * FROM section ORDER BY label");
?>

<div class="container">
    <?php echo $message; ?>
    
    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Pending Orders</h4>
            <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0 0 0;"><?php echo $pending_count; ?></p>
        </div>
        
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Today's Orders</h4>
            <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0 0 0;">
                <?php 
                $today = $conn->query("SELECT COUNT(*) as count FROM lab_test_order WHERE DATE(order_date) = CURDATE()")->fetch_assoc()['count'];
                echo $today; 
                ?>
            </p>
        </div>
        
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Completed Today</h4>
            <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0 0 0;">
                <?php 
                $completed = $conn->query("SELECT COUNT(*) as count FROM lab_test_order WHERE status = 'completed' AND DATE(order_date) = CURDATE()")->fetch_assoc()['count'];
                echo $completed; 
                ?>
            </p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-microscope"></i> Laboratory Test Orders & Results</h2>
        </div>
        
        <!-- Action Buttons -->
        <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <button class="btn btn-success" onclick="showCreateOrderModal()">+ Create Test Order</button>
        </div>
        
        <!-- Filters -->
        <form method="GET" style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label>Filter by Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $filter_status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label>Filter by Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $filter_date; ?>" onchange="this.form.submit()">
                </div>
                
                <?php if ($filter_status || $filter_date): ?>
                    <a href="lab_results.php" class="btn btn-secondary">Clear Filters</a>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Orders Table -->
        <?php if ($orders && $orders->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Order Date</th>
                            <th>Patient</th>
                            <th>Patient Type</th>
                            <th>Tests</th>
                            <th>Order Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $orders->fetch_assoc()): 
                            // Get tests for this order
                            $order_id = $order['lab_test_order_id'];
                            $tests_query = $conn->query("SELECT ot.*, t.label as test_name, s.label as section_name,
                                                         lr.lab_result_id, lr.result_value, lr.status as result_status
                                                         FROM order_tests ot
                                                         JOIN test t ON ot.test_id = t.test_id
                                                         LEFT JOIN section s ON t.section_id = s.section_id
                                                         LEFT JOIN lab_result lr ON ot.order_test_id = lr.order_test_id
                                                         WHERE ot.order_id = $order_id
                                                         ORDER BY s.label, t.label");
                        ?>
                            <tr>
                                <td><strong>#<?php echo $order['lab_test_order_id']; ?></strong></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="patient_details.php?id=<?php echo $order['patient_id']; ?>">
                                        <?php echo htmlspecialchars($order['patient_name']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge <?php echo $order['patient_type'] == 'Internal' ? 'badge-info' : 'badge-secondary'; ?>">
                                        <?php echo htmlspecialchars($order['patient_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $tests_array = [];
                                    while($test = $tests_query->fetch_assoc()) {
                                        $tests_array[] = $test;
                                    }
                                    $test_count = count($tests_array);
                                    ?>
                                    
                                    <?php if ($test_count > 0): ?>
                                        <div style="cursor: pointer;" onclick="toggleTests('order-<?php echo $order_id; ?>')">
                                            <strong><?php echo $test_count; ?> test<?php echo $test_count > 1 ? 's' : ''; ?></strong>
                                            <span id="toggle-icon-order-<?php echo $order_id; ?>" style="float: right;">▼</span>
                                        </div>
                                        
                                        <div id="tests-order-<?php echo $order_id; ?>" style="display: none; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid #ddd;">
                                            <?php foreach($tests_array as $test): ?>
                                                <div style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                                    <strong><?php echo htmlspecialchars($test['test_name']); ?></strong>
                                                    <small style="color: #666; display: block;"><?php echo htmlspecialchars($test['section_name']); ?></small>
                                                    <?php if ($test['result_value']): ?>
                                                        <span style="color: green; font-size: 0.85rem;">
                                                            ✓ Result: <?php echo htmlspecialchars(substr($test['result_value'], 0, 30)) . (strlen($test['result_value']) > 30 ? '...' : ''); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <em style="color: #999; font-size: 0.85rem;">No result yet</em>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <em style="color: #999;">No tests</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $color = $status_colors[$order['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?php echo $color; ?>">
                                        <?php echo strtoupper(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                    <br>
                                    <small style="color: #666;">
                                        <?php echo $order['completed_tests']; ?> / <?php echo $order['total_tests']; ?> tests completed
                                    </small>
                                </td>
                                <td class="table-actions">
                                    <a href="order_details.php?id=<?php echo $order['lab_test_order_id']; ?>" class="btn btn-primary btn-sm">
                                        View/Add Results
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <strong><i class="fas fa-info-circle"></i> No orders found.</strong> 
                <a href="#" onclick="showCreateOrderModal(); return false;">Create a new test order</a> to get started.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Test Order Modal -->
<div id="createOrderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 700px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Create New Test Order</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create_order">
            
            <div class="form-group">
                <label>Patient *</label>
                <select name="patient_id" class="form-control" required>
                    <option value="">Select Patient</option>
                    <?php 
                    $patients = $conn->query("SELECT patient_id, firstname, lastname, patient_type FROM patient ORDER BY lastname, firstname");
                    while($patient = $patients->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $patient['patient_id']; ?>">
                            <?php echo htmlspecialchars($patient['firstname'] . ' ' . $patient['lastname']) . ' (' . $patient['patient_type'] . ')'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tests * (Select one or more)</label>
                <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 1rem; border-radius: 5px; background: #f9f9f9;">
                    <?php 
                    $tests = $conn->query("SELECT t.*, s.label as section_name FROM test t LEFT JOIN section s ON t.section_id = s.section_id ORDER BY s.label, t.label");
                    $current_section = '';
                    while($test = $tests->fetch_assoc()): 
                        if ($current_section != $test['section_name']) {
                            if ($current_section != '') echo '</div>';
                            $current_section = $test['section_name'];
                            echo '<div style="margin-bottom: 1rem;"><strong style="color: #333; display: block; margin-bottom: 0.5rem; border-bottom: 2px solid #007bff; padding-bottom: 0.25rem;">' . htmlspecialchars($current_section) . '</strong>';
                        }
                    ?>
                        <label style="display: block; padding: 0.3rem 0; cursor: pointer; font-weight: normal;">
                            <input type="checkbox" name="test_id[]" value="<?php echo $test['test_id']; ?>" style="margin-right: 0.5rem;">
                            <?php echo htmlspecialchars($test['label']); ?>
                        </label>
                    <?php 
                    endwhile; 
                    if ($current_section != '') echo '</div>';
                    ?>
                </div>
                <small class="form-text text-muted">Check all tests that the patient needs</small>
            </div>
            
            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes or special instructions"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-success">Create Order</button>
                <button type="button" class="btn btn-danger" onclick="closeCreateOrderModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateOrderModal() {
    document.getElementById('createOrderModal').style.display = 'block';
}

function closeCreateOrderModal() {
    document.getElementById('createOrderModal').style.display = 'none';
}

function toggleTests(orderId) {
    const testsDiv = document.getElementById('tests-' + orderId);
    const icon = document.getElementById('toggle-icon-' + orderId);
    
    if (testsDiv.style.display === 'none') {
        testsDiv.style.display = 'block';
        icon.textContent = '▲';
    } else {
        testsDiv.style.display = 'none';
        icon.textContent = '▼';
    }
}

// Add badge styles
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
