<?php
require_once '../db_connection.php';
$page_title = 'Section Management';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $label = $_POST['label'];
            
            $stmt = $conn->prepare("INSERT INTO section (label) VALUES (?)");
            $stmt->bind_param("s", $label);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Section added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update') {
            $section_id = $_POST['section_id'];
            $label = $_POST['label'];
            
            $stmt = $conn->prepare("UPDATE section SET label=? WHERE section_id=?");
            $stmt->bind_param("si", $label, $section_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Section updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'delete') {
            $section_id = $_POST['section_id'];
            $user_id = get_user_id();
            $stmt = $conn->prepare("UPDATE section SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE section_id = ?");
            $stmt->bind_param("ii", $user_id, $section_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Section deleted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get all sections
$sections = $conn->query("SELECT * FROM section WHERE is_deleted = 0 ORDER BY section_id DESC");
?>

<div class="container">
    <?php echo $message; ?>
    
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-building"></i> Section Management</h2>
        </div>
        
        <!-- Add New Section Form -->
        <form method="POST" style="margin-bottom: 2rem;">
            <input type="hidden" name="action" value="add">
            <h3>Add New Section</h3>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Section Name *</label>
                    <input type="text" name="label" class="form-control" required placeholder="e.g., Hematology, Chemistry, etc.">
                </div>
                
                <div class="form-group" style="align-self: end;">
                    <button type="submit" class="btn btn-primary">Add Section</button>
                </div>
            </div>
        </form>
        
        <!-- Sections List -->
        <h3>Sections List</h3>
        <?php if ($sections->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Section Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($section = $sections->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($section['label']); ?></td>
                            <td class="table-actions">
                                <button class="btn btn-warning btn-sm" onclick="editSection(<?php echo htmlspecialchars(json_encode($section)); ?>)">Edit</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this section?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="section_id" value="<?php echo $section['section_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No sections found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 500px; margin: 100px auto; padding: 2rem; border-radius: 10px;">
        <h3>Edit Section</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="section_id" id="edit_section_id">
            
            <div class="form-group">
                <label>Section Name *</label>
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
function editSection(section) {
    document.getElementById('edit_section_id').value = section.section_id;
    document.getElementById('edit_label').value = section.label;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
