<?php
require_once '../db_connection.php';
$page_title = 'Item Management';
include '../includes/header.php';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $section_id = $_POST['section_id'];
            $item_type_id = $_POST['item_type_id'];
            $label = $_POST['label'];
            $status_code = intval($_POST['status_code']); // Ensure it's an integer
            
            $stmt = $conn->prepare("INSERT INTO item (section_id, item_type_id, label, status_code) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $section_id, $item_type_id, $label, $status_code);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Item added successfully!</div>';
                echo "<script>
                    setTimeout(function() {
                        document.querySelector('form[action=\"\"]').reset();
                    }, 100);
                </script>";
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update') {
            $item_id = $_POST['item_id'];
            $section_id = $_POST['section_id'];
            $item_type_id = $_POST['item_type_id'];
            $label = $_POST['label'];
            $status_code = intval($_POST['status_code']); // Ensure it's an integer
            
            $stmt = $conn->prepare("UPDATE item SET section_id=?, item_type_id=?, label=?, status_code=? WHERE item_id=?");
            $stmt->bind_param("iisii", $section_id, $item_type_id, $label, $status_code, $item_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Item updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'delete') {
            $item_id = $_POST['item_id'];
            $stmt = $conn->prepare("DELETE FROM item WHERE item_id=?");
            $stmt->bind_param("i", $item_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Item deleted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get status codes from database
$status_codes_result = $conn->query("SELECT status_code, label FROM status_code ORDER BY status_code");
$status_codes = [];
while ($sc = $status_codes_result->fetch_assoc()) {
    $status_codes[$sc['status_code']] = $sc['label'];
}

// Get all items with search
$query = "SELECT i.*, s.label as section_name, it.label as item_type_name 
          FROM item i 
          LEFT JOIN section s ON i.section_id = s.section_id 
          LEFT JOIN item_type it ON i.item_type_id = it.item_type_id";

if ($search) {
    $search_param = "%$search%";
    $stmt = $conn->prepare("$query WHERE i.label LIKE ? OR s.label LIKE ? OR it.label LIKE ? ORDER BY i.item_id DESC");
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $items = $stmt->get_result();
} else {
    $items = $conn->query("$query ORDER BY i.item_id DESC");
}
?>

<div class="container">
    <?php echo $message; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>📦 Item Management</h2>
        </div>
        
        <!-- Add New Item Form -->
        <form method="POST" id="addItemForm" style="margin-bottom: 2rem;">
            <input type="hidden" name="action" value="add">
            <h3>Add New Item</h3>
            
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
                    <label>Item Type *</label>
                    <select name="item_type_id" class="form-control" required>
                        <option value="">Select Item Type</option>
                        <?php 
                        $item_types = $conn->query("SELECT * FROM item_type");
                        while($type = $item_types->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $type['item_type_id']; ?>"><?php echo htmlspecialchars($type['label']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="label" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status_code" class="form-control" required>
                        <option value="">Select Status</option>
                        <?php foreach($status_codes as $code => $label): ?>
                            <option value="<?php echo $code; ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Item</button>
        </form>
        
        <!-- Search Bar -->
        <form method="GET" style="margin-bottom: 1rem;">
            <div style="display: flex; gap: 1rem; align-items: center;">
                <input type="text" name="search" class="form-control" placeholder="Search by item name, section, or type..." 
                       value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if ($search): ?>
                    <a href="items.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Items List -->
        <h3>Items List</h3>
        <?php if ($items->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Section</th>
                        <th>Item Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $items->fetch_assoc()): 
                        // Map status codes to labels
                        $status_code = intval($item['status_code']);
                        $status_label = isset($status_codes[$status_code]) ? $status_codes[$status_code] : 'Unknown';
                        
                        // Badge colors based on status
                        $badge_class = 'badge-info';
                        if ($status_label == 'Active') $badge_class = 'badge-success';
                        elseif ($status_label == 'Inactive') $badge_class = 'badge-danger';
                        elseif ($status_label == 'Cancelled') $badge_class = 'badge-warning';
                    ?>
                        <tr>
                            <td><?php echo $item['item_id']; ?></td>
                            <td><?php echo htmlspecialchars($item['label']); ?></td>
                            <td><?php echo htmlspecialchars($item['section_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_type_name']); ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $status_label; ?></span></td>
                            <td class="table-actions">
                                <button class="btn btn-warning btn-sm" onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $item['item_id']; ?>, '<?php echo htmlspecialchars($item['label']); ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No items found<?php echo $search ? ' matching your search' : ''; ?>.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 600px; margin: 50px auto; padding: 2rem; border-radius: 10px;">
        <h3>Edit Item</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="item_id" id="edit_item_id">
            
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
                <label>Item Type *</label>
                <select name="item_type_id" id="edit_item_type_id" class="form-control" required>
                    <?php 
                    $item_types = $conn->query("SELECT * FROM item_type");
                    while($type = $item_types->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $type['item_type_id']; ?>"><?php echo htmlspecialchars($type['label']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Item Name *</label>
                <input type="text" name="label" id="edit_label" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status_code" id="edit_status_code" class="form-control" required>
                    <?php foreach($status_codes as $code => $label): ?>
                        <option value="<?php echo $code; ?>"><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 500px; margin: 100px auto; padding: 2rem; border-radius: 10px; text-align: center;">
        <h3 style="color: #dc3545;">⚠️ Confirm Delete</h3>
        <p>Are you sure you want to delete this item?</p>
        <p><strong id="deleteItemName" style="color: #FFB6C1;"></strong></p>
        <p style="color: #666; font-size: 0.9rem;">This action cannot be undone.</p>
        
        <form method="POST" id="deleteForm" style="display: inline;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="item_id" id="delete_item_id">
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editItem(item) {
    document.getElementById('edit_item_id').value = item.item_id;
    document.getElementById('edit_section_id').value = item.section_id;
    document.getElementById('edit_item_type_id').value = item.item_type_id;
    document.getElementById('edit_label').value = item.label;
    document.getElementById('edit_status_code').value = item.status_code;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function confirmDelete(itemId, itemName) {
    document.getElementById('delete_item_id').value = itemId;
    document.getElementById('deleteItemName').textContent = itemName;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Auto-dismiss success messages
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 3000);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
