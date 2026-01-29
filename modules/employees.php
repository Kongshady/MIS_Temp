<?php
require_once '../db_connection.php';
$page_title = 'Employee/User Management';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $firstname = $_POST['firstname'];
            $middlename = $_POST['middlename'];
            $lastname = $_POST['lastname'];
            $username = $_POST['username'];
            $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $position = $_POST['position'];
            $status_code = 1; // Always set to Active when adding new employee
            $role_id = isset($_POST['role_id']) ? $_POST['role_id'] : null;
            
            $stmt = $conn->prepare("INSERT INTO employee (firstname, middlename, lastname, username, password_hash, position, role_id, status_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssii", $firstname, $middlename, $lastname, $username, $password_hash, $position, $role_id, $status_code);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Employee added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update') {
            $employee_id = $_POST['employee_id'];
            $section_id = $_POST['section_id'];
            $firstname = $_POST['firstname'];
            $middlename = $_POST['middlename'];
            $lastname = $_POST['lastname'];
            $username = $_POST['username'];
            $position = $_POST['position'];
            $status_code = $_POST['status_code'];
            $role_id = isset($_POST['role_id']) ? $_POST['role_id'] : null;
            
            // Update password only if provided
            if (!empty($_POST['password'])) {
                $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE employee SET section_id=?, firstname=?, middlename=?, lastname=?, username=?, password_hash=?, position=?, role_id=?, status_code=? WHERE employee_id=?");
                $stmt->bind_param("isssssssii", $section_id, $firstname, $middlename, $lastname, $username, $password_hash, $position, $role_id, $status_code, $employee_id);
            } else {
                $stmt = $conn->prepare("UPDATE employee SET section_id=?, firstname=?, middlename=?, lastname=?, username=?, position=?, role_id=?, status_code=? WHERE employee_id=?");
                $stmt->bind_param("issssssii", $section_id, $firstname, $middlename, $lastname, $username, $position, $role_id, $status_code, $employee_id);
            }
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Employee updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'delete') {
            $employee_id = $_POST['employee_id'];
            
            // Use soft delete instead of hard delete to preserve foreign key relationships
            $stmt = $conn->prepare("UPDATE employee SET status_code = 0 WHERE employee_id = ?");
            $stmt->bind_param("i", $employee_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Employee deleted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get filter parameters
$filter_section = isset($_GET['section']) ? $_GET['section'] : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '1'; // Default to active
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with filters
$query = "SELECT e.employee_id, e.section_id, e.firstname, e.middlename, e.lastname, 
                 e.username, e.position, e.role_id, e.status_code,
                 s.label as section_name, st.label as status_label, r.display_name as role_name
          FROM employee e 
          LEFT JOIN section s ON e.section_id = s.section_id 
          LEFT JOIN status_code st ON e.status_code = st.status_code 
          LEFT JOIN roles r ON e.role_id = r.role_id
          WHERE 1=1";

// Apply filters
if ($filter_status !== '') {
    $query .= " AND e.status_code = " . intval($filter_status);
}

if ($filter_section) {
    $query .= " AND e.section_id = " . intval($filter_section);
}

if ($filter_role !== '') {
    if ($filter_role === 'none') {
        $query .= " AND e.role_id IS NULL";
    } else {
        $query .= " AND e.role_id = " . intval($filter_role);
    }
}

if ($search) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (e.firstname LIKE '%$search_term%' OR e.middlename LIKE '%$search_term%' 
                OR e.lastname LIKE '%$search_term%' OR e.username LIKE '%$search_term%' 
                OR e.position LIKE '%$search_term%')";
}

$query .= " ORDER BY e.employee_id DESC";

$employees = $conn->query($query);
?>

<div class="container">
    <?php echo $message; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>👥 Employee/User Management</h2>
        </div>
        
        <!-- Add New Employee Form -->
        <form method="POST" style="margin-bottom: 2rem;">
            <input type="hidden" name="action" value="add">
            <h3>Add New Employee</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="firstname" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middlename" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lastname" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Position *</label>
                    <input type="text" name="position" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <select name="role_id" class="form-control">
                        <option value="">Select Role (Optional)</option>
                        <?php 
                        $roles = $conn->query("SELECT * FROM roles WHERE status_code = 1 ORDER BY display_name");
                        if ($roles) {
                            while($role = $roles->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['display_name']); ?></option>
                        <?php 
                            endwhile;
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Employee</button>
        </form>
        
        <!-- Employees List -->
        <h3>Employees List</h3>
        
        <!-- Filter Section -->
        <form method="GET" style="background: #f5f5f5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Search by Name/Username/Position</label>
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Filter by Section</label>
                    <select name="section" class="form-control">
                        <option value="">All Sections</option>
                        <?php 
                        $sections_filter = $conn->query("SELECT * FROM section ORDER BY label");
                        while($section = $sections_filter->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $section['section_id']; ?>" <?php echo $filter_section == $section['section_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($section['label']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Filter by Role</label>
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="none" <?php echo $filter_role === 'none' ? 'selected' : ''; ?>>No Role</option>
                        <?php 
                        $roles_filter = $conn->query("SELECT * FROM roles WHERE status_code = 1 ORDER BY display_name");
                        if ($roles_filter) {
                            while($role = $roles_filter->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $role['role_id']; ?>" <?php echo $filter_role == $role['role_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['display_name']); ?>
                            </option>
                        <?php 
                            endwhile;
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Filter by Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <?php 
                        $status_filter = $conn->query("SELECT * FROM status_code");
                        while($status = $status_filter->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $status['status_code']; ?>" <?php echo $filter_status == $status['status_code'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status['label']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <?php if ($search || $filter_section || $filter_role !== '' || $filter_status !== '1'): ?>
                        <a href="employees.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        
        <?php if ($employees->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Section</th>
                        <th>Position</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($employee = $employees->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $employee['employee_id']; ?></td>
                            <td><?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['middlename'] . ' ' . $employee['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($employee['username']); ?></td>
                            <td><?php echo htmlspecialchars($employee['section_name']); ?></td>
                            <td><?php echo htmlspecialchars($employee['position']); ?></td>
                            <td>
                                <?php if ($employee['role_name']): ?>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($employee['role_name']); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">No Role</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($employee['status_label']); ?></td>
                            <td class="table-actions">
                                <button class="btn btn-warning btn-sm" onclick="editEmployee(<?php echo htmlspecialchars(json_encode($employee)); ?>)">Edit</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="employee_id" value="<?php echo $employee['employee_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No employees found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px; max-height: 90vh; overflow-y: auto;">
        <h3>Edit Employee</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="employee_id" id="edit_employee_id">
            
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
                <label>Username *</label>
                <input type="text" name="username" id="edit_username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Password (leave blank to keep current)</label>
                <input type="password" name="password" id="edit_password" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Position *</label>
                <input type="text" name="position" id="edit_position" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Role (Optional)</label>
                <select name="role_id" id="edit_role_id" class="form-control">
                    <option value="">-- No Role --</option>
                    <?php 
                    $roles_edit = $conn->query("SELECT * FROM roles WHERE status_code = 1 ORDER BY display_name");
                    while($role = $roles_edit->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['display_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status_code" id="edit_status_code" class="form-control" required>
                    <?php 
                    $status_codes = $conn->query("SELECT * FROM status_code");
                    while($status = $status_codes->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $status['status_code']; ?>"><?php echo htmlspecialchars($status['label']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editEmployee(employee) {
    document.getElementById('edit_employee_id').value = employee.employee_id;
    document.getElementById('edit_section_id').value = employee.section_id;
    document.getElementById('edit_firstname').value = employee.firstname;
    document.getElementById('edit_middlename').value = employee.middlename;
    document.getElementById('edit_lastname').value = employee.lastname;
    document.getElementById('edit_username').value = employee.username;
    document.getElementById('edit_position').value = employee.position;
    document.getElementById('edit_role_id').value = employee.role_id || '';
    document.getElementById('edit_status_code').value = employee.status_code;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
