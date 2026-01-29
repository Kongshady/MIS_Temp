<?php
require_once '../db_connection.php';
$page_title = 'Physician Management';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $physician_name = $_POST['physician_name'];
            $specialization = $_POST['specialization'];
            $contact_number = $_POST['contact_number'];
            $email = $_POST['email'];
            $status_code = $_POST['status_code'];
            
            $stmt = $conn->prepare("INSERT INTO physician (physician_name, specialization, contact_number, email, status_code, datetime_added) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssi", $physician_name, $specialization, $contact_number, $email, $status_code);
            
            if ($stmt->execute()) {
                $new_physician_id = $conn->insert_id;
                log_activity($conn, get_user_id(), "Added new physician: $physician_name (ID: $new_physician_id)", 1);
                $message = '<div class="alert alert-success">Physician added successfully!</div>';
            } else {
                log_activity($conn, get_user_id(), "Failed to add physician: $physician_name", 0);
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update') {
            $physician_id = $_POST['physician_id'];
            $physician_name = $_POST['physician_name'];
            $specialization = $_POST['specialization'];
            $contact_number = $_POST['contact_number'];
            $email = $_POST['email'];
            $status_code = $_POST['status_code'];
            
            $stmt = $conn->prepare("UPDATE physician SET physician_name=?, specialization=?, contact_number=?, email=?, status_code=? WHERE physician_id=?");
            $stmt->bind_param("ssssii", $physician_name, $specialization, $contact_number, $email, $status_code, $physician_id);
            
            if ($stmt->execute()) {
                log_activity($conn, get_user_id(), "Updated physician: $physician_name (ID: $physician_id)", 1);
                $message = '<div class="alert alert-success">Physician updated successfully!</div>';
            } else {
                log_activity($conn, get_user_id(), "Failed to update physician ID: $physician_id", 0);
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'delete') {
            $physician_id = $_POST['physician_id'];
            $stmt = $conn->prepare("DELETE FROM physician WHERE physician_id=?");
            $stmt->bind_param("i", $physician_id);
            
            if ($stmt->execute()) {
                log_activity($conn, get_user_id(), "Deleted physician ID: $physician_id", 1);
                $message = '<div class="alert alert-success">Physician deleted successfully!</div>';
            } else {
                log_activity($conn, get_user_id(), "Failed to delete physician ID: $physician_id", 0);
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get filter and pagination parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$specialization_filter = isset($_GET['specialization']) ? $_GET['specialization'] : '';
$rows_per_page = isset($_GET['rows']) && $_GET['rows'] !== 'all' ? (int)$_GET['rows'] : 0;

// Build query with filters
$query = "SELECT p.*, s.label as status_label FROM physician p LEFT JOIN status_code s ON p.status_code = s.status_code WHERE 1=1";
$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "(p.physician_name LIKE ? OR p.contact_number LIKE ? OR p.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($specialization_filter)) {
    $conditions[] = "p.specialization LIKE ?";
    $spec_param = "%$specialization_filter%";
    $params[] = $spec_param;
    $types .= 's';
}

if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " ORDER BY p.physician_name ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $physicians = $stmt->get_result();
} else {
    $physicians = $conn->query($query);
}

// Apply pagination if needed
if ($rows_per_page > 0) {
    $all_physicians = [];
    while ($row = $physicians->fetch_assoc()) {
        $all_physicians[] = $row;
    }
    $displayed_physicians = array_slice($all_physicians, 0, $rows_per_page);
} else {
    $all_physicians = [];
    while ($row = $physicians->fetch_assoc()) {
        $all_physicians[] = $row;
    }
    $displayed_physicians = $all_physicians;
}
?>

<div class="container">
    <?php echo $message; ?>
    
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-md"></i> Physician Management</h2>
        </div>
        
        <!-- Add New Physician Form -->
        <form method="POST" style="margin-bottom: 2rem;">
            <input type="hidden" name="action" value="add">
            <h3>Add New Physician</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Physician Name *</label>
                    <input type="text" name="physician_name" class="form-control" required placeholder="Dr. Juan Dela Cruz">
                </div>
                
                <div class="form-group">
                    <label>Specialization</label>
                    <input type="text" name="specialization" class="form-control" placeholder="e.g., Pathologist">
                </div>
                
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" placeholder="09171234567">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="doctor@clinic.com">
                </div>
                
                <input type="hidden" name="status_code" value="1">
            </div>
            
            <button type="submit" class="btn btn-primary">Add Physician</button>
        </form>
        
        <!-- Filter Section -->
        <form method="GET" style="margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: 2fr 1fr 150px auto auto; gap: 0.75rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.875rem; margin-bottom: 0.25rem;">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, contact, or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.875rem; margin-bottom: 0.25rem;">Specialization</label>
                    <input type="text" name="specialization" class="form-control" placeholder="e.g., Pathologist" value="<?php echo htmlspecialchars($specialization_filter); ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.875rem; margin-bottom: 0.25rem;">Rows</label>
                    <select name="rows" class="form-control">
                        <option value="10" <?php echo $rows_per_page == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo $rows_per_page == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo $rows_per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $rows_per_page == 100 ? 'selected' : ''; ?>>100</option>
                        <option value="all" <?php echo $rows_per_page == 0 ? 'selected' : ''; ?>>All</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <?php if ($search || $specialization_filter || (isset($_GET['rows']) && $_GET['rows'] != 10)): ?>
                    <a href="physicians.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Physicians List -->
        <h3>Physicians List</h3>
        <?php if (count($displayed_physicians) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($displayed_physicians as $physician): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($physician['physician_name']); ?></td>
                            <td><?php echo htmlspecialchars($physician['specialization']); ?></td>
                            <td><?php echo htmlspecialchars($physician['contact_number']); ?></td>
                            <td><?php echo htmlspecialchars($physician['email']); ?></td>
                            <td class="table-actions">
                                <button class="btn btn-warning btn-sm" onclick="editPhysician(<?php echo htmlspecialchars(json_encode($physician)); ?>)">Edit</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this physician?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="physician_id" value="<?php echo $physician['physician_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No physicians found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px; max-height: 90vh; overflow-y: auto;">
        <h3>Edit Physician</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="physician_id" id="edit_physician_id">
            
            <div class="form-group">
                <label>Physician Name *</label>
                <input type="text" name="physician_name" id="edit_physician_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Specialization</label>
                <input type="text" name="specialization" id="edit_specialization" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact_number" id="edit_contact_number" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="edit_email" class="form-control">
            </div>
            
            <input type="hidden" name="status_code" id="edit_status_code" value="1">
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPhysician(physician) {
    document.getElementById('edit_physician_id').value = physician.physician_id;
    document.getElementById('edit_physician_name').value = physician.physician_name;
    document.getElementById('edit_specialization').value = physician.specialization;
    document.getElementById('edit_contact_number').value = physician.contact_number;
    document.getElementById('edit_email').value = physician.email;
    document.getElementById('edit_status_code').value = physician.status_code;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
