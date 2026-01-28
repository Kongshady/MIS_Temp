<?php
require_once '../db_connection.php';
$page_title = 'Transaction Management';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $client_id = $_POST['client_id'];
            $or_number = $_POST['or_number'];
            
            // Validate that client exists
            $check_client = $conn->query("SELECT patient_id FROM patient WHERE patient_id = " . intval($client_id));
            if ($check_client->num_rows == 0) {
                $message = '<div class="alert alert-danger">Error: Selected client does not exist. Please select a valid client or <a href="patients.php">add a new patient first</a>.</div>';
            } else {
                $stmt = $conn->prepare("INSERT INTO transaction (client_id, or_number, datetime_added) VALUES (?, ?, NOW())");
                $stmt->bind_param("is", $client_id, $or_number);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Transaction added successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
            }
        } elseif ($_POST['action'] == 'update') {
            $transaction_id = $_POST['transaction_id'];
            $client_id = $_POST['client_id'];
            $or_number = $_POST['or_number'];
            
            // Validate that client exists
            $check_client = $conn->query("SELECT patient_id FROM patient WHERE patient_id = " . intval($client_id));
            if ($check_client->num_rows == 0) {
                $message = '<div class="alert alert-danger">Error: Selected client does not exist. Please select a valid client.</div>';
            } else {
                $stmt = $conn->prepare("UPDATE transaction SET client_id=?, or_number=? WHERE transaction_id=?");
                $stmt->bind_param("isi", $client_id, $or_number, $transaction_id);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Transaction updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
            }
        } elseif ($_POST['action'] == 'delete') {
            $transaction_id = $_POST['transaction_id'];
            $stmt = $conn->prepare("DELETE FROM transaction WHERE transaction_id=?");
            $stmt->bind_param("i", $transaction_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Transaction deleted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get all transactions
$transactions = $conn->query("SELECT t.*, 
                             CONCAT(COALESCE(p.firstname, ''), ' ', COALESCE(p.lastname, '')) as patient_name
                             FROM transaction t 
                             LEFT JOIN patient p ON t.client_id = p.patient_id 
                             ORDER BY t.datetime_added DESC");

// Check if there are any patients
$patient_count = $conn->query("SELECT COUNT(*) as count FROM patient")->fetch_assoc()['count'];
?>

<div class="container">
    <?php echo $message; ?>
    
    <?php if ($patient_count == 0): ?>
        <div class="alert alert-warning">
            <strong>⚠️ No Patients Found!</strong> You need to <a href="patients.php" class="alert-link">add patients</a> before you can create transactions.
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>📝 Transaction Management</h2>
        </div>
        
        <!-- Add New Transaction Form -->
        <?php if ($patient_count > 0): ?>
        <form method="POST" style="margin-bottom: 2rem;">
            <input type="hidden" name="action" value="add">
            <h3>Add New Transaction</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Patient *</label>
                    <select name="client_id" class="form-control" required>
                        <option value="">Select Patient</option>
                        <?php 
                        $clients = $conn->query("SELECT patient_id as client_id, firstname, lastname FROM patient ORDER BY lastname, firstname");
                        while($client = $clients->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $client['client_id']; ?>">
                                <?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>OR Number *</label>
                    <input type="text" name="or_number" class="form-control" required placeholder="Official Receipt Number">
                </div>
                
                <div class="form-group" style="align-self: end;">
                    <button type="submit" class="btn btn-primary">Add Transaction</button>
                </div>
            </div>
        </form>
        <?php else: ?>
        <div class="alert alert-info">
            <strong>ℹ️ Cannot Add Transactions</strong><br>
            Please <a href="patients.php">add patients to the system</a> first.
        </div>
        <?php endif; ?>
        
        <!-- Transactions List -->
        <h3>Transactions List</h3>
        <?php if ($transactions->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Patient Name</th>
                        <th>OR Number</th>
                        <th>Date & Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($transaction = $transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $transaction['transaction_id']; ?></td>
                            <td><?php echo htmlspecialchars($transaction['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['or_number']); ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($transaction['datetime_added'])); ?></td>
                            <td class="table-actions">
                                <a href="transaction_details.php?id=<?php echo $transaction['transaction_id']; ?>" class="btn btn-info btn-sm">View Details</a>
                                <button class="btn btn-warning btn-sm" onclick="editTransaction(<?php echo htmlspecialchars(json_encode($transaction)); ?>)">Edit</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="transaction_id" value="<?php echo $transaction['transaction_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No transactions found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Edit Transaction</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="transaction_id" id="edit_transaction_id">
            
            <div class="form-group">
                <label>Patient *</label>
                <select name="client_id" id="edit_client_id" class="form-control" required>
                    <?php 
                    $clients = $conn->query("SELECT patient_id as client_id, firstname, lastname FROM patient ORDER BY lastname, firstname");
                    while($client = $clients->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $client['client_id']; ?>">
                            <?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>OR Number *</label>
                <input type="text" name="or_number" id="edit_or_number" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editTransaction(transaction) {
    document.getElementById('edit_transaction_id').value = transaction.transaction_id;
    document.getElementById('edit_client_id').value = transaction.client_id;
    document.getElementById('edit_or_number').value = transaction.or_number;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
