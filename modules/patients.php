<?php
require_once '../db_connection.php';
$page_title = 'Patient Profile Management';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $patient_type = $_POST['patient_type'];
            $firstname = $_POST['firstname'];
            $middlename = $_POST['middlename'];
            $lastname = $_POST['lastname'];
            $birthdate = $_POST['birthdate'];
            $gender = $_POST['gender'];
            $contact_number = $_POST['contact_number'];
            $address = $_POST['address'];
            $status_code = 1; // Default to Active
            
            $stmt = $conn->prepare("INSERT INTO patient (patient_type, firstname, middlename, lastname, birthdate, gender, contact_number, address, status_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssi", $patient_type, $firstname, $middlename, $lastname, $birthdate, $gender, $contact_number, $address, $status_code);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Patient added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update') {
            $patient_id = $_POST['patient_id'];
            $patient_type = $_POST['patient_type'];
            $firstname = $_POST['firstname'];
            $middlename = $_POST['middlename'];
            $lastname = $_POST['lastname'];
            $birthdate = $_POST['birthdate'];
            $gender = $_POST['gender'];
            $contact_number = $_POST['contact_number'];
            $address = $_POST['address'];
            
            $stmt = $conn->prepare("UPDATE patient SET patient_type=?, firstname=?, middlename=?, lastname=?, birthdate=?, gender=?, contact_number=?, address=? WHERE patient_id=?");
            $stmt->bind_param("ssssssssi", $patient_type, $firstname, $middlename, $lastname, $birthdate, $gender, $contact_number, $address, $patient_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Patient updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'delete') {
            $patient_id = $_POST['patient_id'];
            $stmt = $conn->prepare("UPDATE patient SET status_code = 2 WHERE patient_id=?");
            $stmt->bind_param("i", $patient_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Patient deactivated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Search functionality
$search_query = '';
$search_param = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_param = $_GET['search'];
    $search_query = " AND (p.firstname LIKE '%" . $conn->real_escape_string($search_param) . "%' 
                      OR p.lastname LIKE '%" . $conn->real_escape_string($search_param) . "%' 
                      OR p.patient_id LIKE '%" . $conn->real_escape_string($search_param) . "%')";
}

// Get all active patients
$patients = $conn->query("SELECT p.*
                         FROM patient p 
                         WHERE p.status_code = 1 $search_query
                         ORDER BY p.datetime_added DESC");
?>

<div class="container">
    <?php echo $message; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>👥 Patient Profile Management</h2>
        </div>
        
        <!-- Add New Patient Form -->
        <form method="POST" style="margin-bottom: 2rem;">
            <input type="hidden" name="action" value="add">
            <h3>Add New Patient</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Patient Type *</label>
                    <select name="patient_type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="Internal">Internal</option>
                        <option value="External">External</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="firstname" class="form-control" required placeholder="Juan">
                </div>
                
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middlename" class="form-control" placeholder="Santos">
                </div>
                
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lastname" class="form-control" required placeholder="Dela Cruz">
                </div>
                
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="birthdate" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" placeholder="09123456789">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" placeholder="123 Street, City">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Patient</button>
        </form>
        
        <!-- Search Bar -->
        <form method="GET" style="margin-bottom: 1.5rem; display: flex; gap: 0.75rem;">
            <input type="text" name="search" class="form-control" placeholder="Search by name or patient ID..." value="<?php echo htmlspecialchars($search_param); ?>" style="flex: 1;">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search_param): ?>
                <a href="patients.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
        
        <!-- Patients List -->
        <h3>Patients List</h3>
        <?php if ($patients && $patients->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Full Name</th>
                        <th>Birthdate</th>
                        <th>Gender</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($patient = $patients->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $patient['patient_id']; ?></td>
                            <td>
                                <span class="badge <?php echo $patient['patient_type'] == 'Internal' ? 'badge-info' : 'badge-success'; ?>">
                                    <?php echo htmlspecialchars($patient['patient_type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($patient['firstname'] . ' ' . ($patient['middlename'] ? $patient['middlename'] . ' ' : '') . $patient['lastname']); ?></td>
                            <td><?php echo $patient['birthdate'] ? date('M d, Y', strtotime($patient['birthdate'])) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($patient['contact_number'] ?? 'N/A'); ?></td>
                            <td class="table-actions">
                                <a href="patient_details.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-info btn-sm">Details</a>
                                <button class="btn btn-warning btn-sm" onclick="editPatient(<?php echo htmlspecialchars(json_encode($patient)); ?>)">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php echo $search_param ? 'No patients found matching your search.' : 'No patient records found.'; ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Patient Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 800px; margin: 50px auto; padding: 2rem; border-radius: 10px; max-height: 90vh; overflow-y: auto;">
        <h3>Edit Patient</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="patient_id" id="edit_patient_id">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>Patient Type *</label>
                    <select name="patient_type" id="edit_patient_type" class="form-control" required>
                        <option value="Internal">Internal</option>
                        <option value="External">External</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="firstname" id="edit_firstname" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middlename" id="edit_middlename" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lastname" id="edit_lastname" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="birthdate" id="edit_birthdate" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" id="edit_gender" class="form-control" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" id="edit_contact_number" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" id="edit_address" class="form-control">
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPatient(patient) {
    document.getElementById('edit_patient_id').value = patient.patient_id;
    document.getElementById('edit_patient_type').value = patient.patient_type;
    document.getElementById('edit_firstname').value = patient.firstname;
    document.getElementById('edit_middlename').value = patient.middlename || '';
    document.getElementById('edit_lastname').value = patient.lastname;
    document.getElementById('edit_birthdate').value = patient.birthdate;
    document.getElementById('edit_gender').value = patient.gender;
    document.getElementById('edit_contact_number').value = patient.contact_number || '';
    document.getElementById('edit_address').value = patient.address || '';
    
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
