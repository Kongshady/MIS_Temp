<?php
require_once '../db_connection.php';
$page_title = 'Test Management';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $section_id = $_POST['section_id'];
            $label = $_POST['label'];
            $current_price = isset($_POST['current_price']) ? $_POST['current_price'] : 0.00;
            
            $stmt = $conn->prepare("INSERT INTO test (section_id, label, current_price) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $section_id, $label, $current_price);
            
            if ($stmt->execute()) {
                // If price was set, record it in price history
                if ($current_price > 0) {
                    $test_id = $conn->insert_id;
                    $user_id = $_SESSION['user_id'] ?? 1;
                    $history_stmt = $conn->prepare("INSERT INTO test_price_history (test_id, previous_price, new_price, updated_by) VALUES (?, 0.00, ?, ?)");
                    $history_stmt->bind_param("idi", $test_id, $current_price, $user_id);
                    $history_stmt->execute();
                }
                $message = '<div class="alert alert-success">Test added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update') {
            $test_id = $_POST['test_id'];
            $section_id = $_POST['section_id'];
            $label = $_POST['label'];
            
            $stmt = $conn->prepare("UPDATE test SET section_id=?, label=? WHERE test_id=?");
            $stmt->bind_param("isi", $section_id, $label, $test_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Test updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'set_price') {
            $test_id = $_POST['test_id'];
            $new_price = $_POST['new_price'];
            $user_id = $_SESSION['user_id'] ?? 1;
            
            // Get current price
            $current_query = $conn->prepare("SELECT current_price FROM test WHERE test_id = ?");
            $current_query->bind_param("i", $test_id);
            $current_query->execute();
            $result = $current_query->get_result()->fetch_assoc();
            $previous_price = $result['current_price'] ?? 0.00;
            
            // Update test price
            $stmt = $conn->prepare("UPDATE test SET current_price=? WHERE test_id=?");
            $stmt->bind_param("di", $new_price, $test_id);
            
            if ($stmt->execute()) {
                // Record price change in history
                $history_stmt = $conn->prepare("INSERT INTO test_price_history (test_id, previous_price, new_price, updated_by) VALUES (?, ?, ?, ?)");
                $history_stmt->bind_param("iddi", $test_id, $previous_price, $new_price, $user_id);
                $history_stmt->execute();
                
                $message = '<div class="alert alert-success">Price updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'delete') {
            $test_id = $_POST['test_id'];
            $stmt = $conn->prepare("DELETE FROM test WHERE test_id=?");
            $stmt->bind_param("i", $test_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Test deleted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get all tests
$tests = $conn->query("SELECT t.*, s.label as section_name FROM test t LEFT JOIN section s ON t.section_id = s.section_id ORDER BY t.test_id DESC");

// Check if current_price column exists
$columns = $conn->query("SHOW COLUMNS FROM test LIKE 'current_price'");
$price_column_exists = $columns && $columns->num_rows > 0;

// Check if test_price_history table exists
$tables = $conn->query("SHOW TABLES LIKE 'test_price_history'");
$history_table_exists = $tables && $tables->num_rows > 0;
?>

<div class="container">
    <?php echo $message; ?>
    
    <?php if (!$price_column_exists || !$history_table_exists): ?>
        <div class="alert alert-warning">
            <strong>⚠️ Database Setup Required!</strong><br>
            The pricing features require database changes. Please run the SQL migration file:
            <code>sql/test_pricing.sql</code> in phpMyAdmin.
            <?php if (!$price_column_exists): ?>
                <br>• Missing: <code>current_price</code> column in test table
            <?php endif; ?>
            <?php if (!$history_table_exists): ?>
                <br>• Missing: <code>test_price_history</code> table
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>🧪 Test Management</h2>
        </div>
        
        <!-- Add New Test Form -->
        <form method="POST" style="margin-bottom: 2rem;">
            <input type="hidden" name="action" value="add">
            <h3>Add New Test</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Section *</label>
                    <select name="section_id" class="form-control" required>
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
                    <label>Test Name *</label>
                    <input type="text" name="label" class="form-control" required placeholder="e.g., Complete Blood Count, Urinalysis">
                </div>
                
                <?php if ($price_column_exists): ?>
                <div class="form-group">
                    <label>Initial Price (₱)</label>
                    <input type="number" name="current_price" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>
                <?php endif; ?>
                
                <div class="form-group" style="align-self: end;">
                    <button type="submit" class="btn btn-primary">Add Test</button>
                </div>
            </div>
        </form>
        
        <!-- Tests List -->
        <h3>Tests List</h3>
        <?php if ($tests->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Test Name</th>
                        <th>Section</th>
                        <?php if ($price_column_exists): ?>
                        <th>Previous Price</th>
                        <th>Current Price</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($test = $tests->fetch_assoc()): 
                        $previous_price = 0.00;
                        if ($price_column_exists && $history_table_exists) {
                            // Get previous price from history
                            $history_query = $conn->query("SELECT previous_price FROM test_price_history WHERE test_id = {$test['test_id']} ORDER BY updated_at DESC LIMIT 1");
                            if ($history_query && $history_query->num_rows > 0) {
                                $history = $history_query->fetch_assoc();
                                $previous_price = $history['previous_price'];
                            }
                        }
                    ?>
                        <tr>
                            <td><?php echo $test['test_id']; ?></td>
                            <td><?php echo htmlspecialchars($test['label']); ?></td>
                            <td><?php echo htmlspecialchars($test['section_name']); ?></td>
                            <?php if ($price_column_exists): ?>
                            <td>₱<?php echo number_format($previous_price, 2); ?></td>
                            <td><strong>₱<?php echo number_format($test['current_price'] ?? 0, 2); ?></strong></td>
                            <?php endif; ?>
                            <td class="table-actions">
                                <button class="btn btn-warning btn-sm" onclick="editTest(<?php echo htmlspecialchars(json_encode($test)); ?>)">Edit</button>
                                <?php if ($price_column_exists && $history_table_exists): ?>
                                <button class="btn btn-success btn-sm" onclick="showSetPriceModal(<?php echo $test['test_id']; ?>, '<?php echo htmlspecialchars($test['label']); ?>', <?php echo $test['current_price'] ?? 0; ?>)">Set Price</button>
                                <button class="btn btn-info btn-sm" onclick="showPriceHistory(<?php echo $test['test_id']; ?>, '<?php echo htmlspecialchars($test['label']); ?>')">View History</button>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this test?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="test_id" value="<?php echo $test['test_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tests found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Set Price Modal -->
<div id="setPriceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 500px; margin: 100px auto; padding: 2rem; border-radius: 10px;">
        <h3>Set New Price</h3>
        <form method="POST">
            <input type="hidden" name="action" value="set_price">
            <input type="hidden" name="test_id" id="price_test_id">
            
            <div class="form-group">
                <label>Test Name</label>
                <div style="padding: 0.5rem; background: #f5f5f5; border-radius: 5px; font-weight: bold;">
                    <span id="price_test_name"></span>
                </div>
            </div>
            
            <div class="form-group">
                <label>Current Price</label>
                <div style="padding: 0.5rem; background: #f5f5f5; border-radius: 5px;">
                    <span id="price_current_price"></span>
                </div>
            </div>
            
            <div class="form-group">
                <label>New Price (₱) *</label>
                <input type="number" name="new_price" id="new_price" class="form-control" step="0.01" min="0" required placeholder="0.00">
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update Price</button>
                <button type="button" class="btn btn-danger" onclick="closeSetPriceModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Price History Modal -->
<div id="priceHistoryModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 800px; margin: 50px auto; padding: 2rem; border-radius: 10px; max-height: 80vh; overflow-y: auto;">
        <h3>Price History - <span id="history_test_name"></span></h3>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Date Updated</th>
                    <th>Previous Price</th>
                    <th>New Price</th>
                    <th>Updated By</th>
                </tr>
            </thead>
            <tbody id="priceHistoryBody">
                <tr><td colspan="4" style="text-align: center;">Loading...</td></tr>
            </tbody>
        </table>
        
        <div style="margin-top: 1rem;">
            <button type="button" class="btn btn-secondary" onclick="closePriceHistoryModal()">Close</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 500px; margin: 100px auto; padding: 2rem; border-radius: 10px;">
        <h3>Edit Test</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="test_id" id="edit_test_id">
            
            <div class="form-group">
                <label>Section *</label>
                <select name="section_id" id="edit_section_id" class="form-control" required>
                    <?php 
                    $sections = $conn->query("SELECT * FROM section");
                    while($section = $sections->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $section['section_id']; ?>"><?php echo htmlspecialchars($section['label']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Test Name *</label>
                <input type="text" name="label" id="edit_label" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editTest(test) {
    document.getElementById('edit_test_id').value = test.test_id;
    document.getElementById('edit_section_id').value = test.section_id;
    document.getElementById('edit_label').value = test.label;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function showSetPriceModal(testId, testName, currentPrice) {
    document.getElementById('price_test_id').value = testId;
    document.getElementById('price_test_name').textContent = testName;
    document.getElementById('price_current_price').textContent = '₱' + parseFloat(currentPrice).toFixed(2);
    document.getElementById('new_price').value = '';
    document.getElementById('setPriceModal').style.display = 'block';
}

function closeSetPriceModal() {
    document.getElementById('setPriceModal').style.display = 'none';
}

async function showPriceHistory(testId, testName) {
    document.getElementById('history_test_name').textContent = testName;
    const tbody = document.getElementById('priceHistoryBody');
    tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading...</td></tr>';
    document.getElementById('priceHistoryModal').style.display = 'block';
    
    try {
        // Fetch price history using AJAX
        const response = await fetch('get_price_history.php?test_id=' + testId);
        const data = await response.json();
        
        if (data.success && data.history.length > 0) {
            tbody.innerHTML = '';
            data.history.forEach(record => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${record.updated_at}</td>
                    <td>₱${parseFloat(record.previous_price).toFixed(2)}</td>
                    <td><strong>₱${parseFloat(record.new_price).toFixed(2)}</strong></td>
                    <td>${record.updated_by}</td>
                `;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No price history found</td></tr>';
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: red;">Error loading history</td></tr>';
    }
}

function closePriceHistoryModal() {
    document.getElementById('priceHistoryModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editModal');
    const setPriceModal = document.getElementById('setPriceModal');
    const priceHistoryModal = document.getElementById('priceHistoryModal');
    
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
    if (event.target == setPriceModal) {
        setPriceModal.style.display = 'none';
    }
    if (event.target == priceHistoryModal) {
        priceHistoryModal.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
