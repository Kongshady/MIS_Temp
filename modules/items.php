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
            
            $stmt = $conn->prepare("INSERT INTO item (section_id, item_type_id, label) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $section_id, $item_type_id, $label);
            
            if ($stmt->execute()) {
                $new_item_id = $conn->insert_id;
                log_activity($conn, get_user_id(), "Added new item: $label (ID: $new_item_id)", 1);
                $message = '<div class="alert alert-success">Item added successfully!</div>';
                echo "<script>
                    setTimeout(function() {
                        document.querySelector('form[action=\"\"]').reset();
                    }, 100);
                </script>";
            } else {
                log_activity($conn, get_user_id(), "Failed to add item: $label", 0);
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'update') {
            $item_id = $_POST['item_id'];
            $section_id = $_POST['section_id'];
            $item_type_id = $_POST['item_type_id'];
            $label = $_POST['label'];
            
            $stmt = $conn->prepare("UPDATE item SET section_id=?, item_type_id=?, label=? WHERE item_id=?");
            $stmt->bind_param("iisi", $section_id, $item_type_id, $label, $item_id);
            
            if ($stmt->execute()) {
                log_activity($conn, get_user_id(), "Updated item: $label (ID: $item_id)", 1);
                $message = '<div class="alert alert-success">Item updated successfully!</div>';
            } else {
                log_activity($conn, get_user_id(), "Failed to update item ID: $item_id", 0);
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        } elseif ($_POST['action'] == 'delete') {
            $item_id = $_POST['item_id'];
            $stmt = $conn->prepare("DELETE FROM item WHERE item_id=?");
            $stmt->bind_param("i", $item_id);
            
            if ($stmt->execute()) {
                log_activity($conn, get_user_id(), "Deleted item ID: $item_id", 1);
                $message = '<div class="alert alert-success">Item deleted successfully!</div>';
            } else {
                log_activity($conn, get_user_id(), "Failed to delete item ID: $item_id", 0);
                $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

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
            <h2><i class="fas fa-box"></i> Item Management</h2>
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
                    <div class="custom-combobox">
                        <input type="text" id="itemTypeSearch" class="form-control" placeholder="Select or search item type..." autocomplete="off" required>
                        <input type="hidden" name="item_type_id" id="itemTypeValue" required>
                        <div class="combobox-dropdown" id="itemTypeDropdown">
                            <?php 
                            $item_types = $conn->query("SELECT * FROM item_type");
                            while($type = $item_types->fetch_assoc()): 
                            ?>
                                <div class="combobox-option" data-value="<?php echo $type['item_type_id']; ?>">
                                    <?php echo htmlspecialchars($type['label']); ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="label" class="form-control" required>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $item['item_id']; ?></td>
                            <td><?php echo htmlspecialchars($item['label']); ?></td>
                            <td><?php echo htmlspecialchars($item['section_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_type_name']); ?></td>
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
                <div class="custom-combobox">
                    <input type="text" id="editItemTypeSearch" class="form-control" placeholder="Select or search item type..." autocomplete="off" required>
                    <input type="hidden" name="item_type_id" id="edit_item_type_id">
                    <div class="combobox-dropdown" id="editItemTypeDropdown">
                        <?php 
                        $item_types = $conn->query("SELECT * FROM item_type");
                        while($type = $item_types->fetch_assoc()): 
                        ?>
                            <div class="combobox-option" data-value="<?php echo $type['item_type_id']; ?>">
                                <?php echo htmlspecialchars($type['label']); ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Item Name *</label>
                <input type="text" name="label" id="edit_label" class="form-control" required>
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
        <h3 style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h3>
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
    
    // Set item type for custom combobox
    const editItemTypeSearch = document.getElementById('editItemTypeSearch');
    const editItemTypeValue = document.getElementById('edit_item_type_id');
    const editOptions = document.querySelectorAll('#editItemTypeDropdown .combobox-option');
    
    editOptions.forEach(option => {
        if (option.getAttribute('data-value') == item.item_type_id) {
            editItemTypeSearch.value = option.textContent.trim();
            editItemTypeValue.value = item.item_type_id;
        }
    });
    
    document.getElementById('edit_label').value = item.label;
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

<style>
/* Custom Combobox Styling */
.custom-combobox {
    position: relative;
}

.custom-combobox input[type="text"] {
    width: 100%;
    cursor: pointer;
}

.combobox-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    z-index: 1000;
    margin-top: 2px;
}

.combobox-dropdown.show {
    display: block;
}

.combobox-option {
    padding: 10px 15px;
    cursor: pointer;
    transition: background 0.2s;
}

.combobox-option:hover {
    background: #f0f0f0;
}

.combobox-option.selected {
    background: #667eea;
    color: white;
}
</style>

<script>
// Custom Combobox for Add Form
const itemTypeSearch = document.getElementById('itemTypeSearch');
const itemTypeValue = document.getElementById('itemTypeValue');
const itemTypeDropdown = document.getElementById('itemTypeDropdown');

if (itemTypeSearch) {
    itemTypeSearch.addEventListener('focus', function() {
        itemTypeDropdown.classList.add('show');
    });

    itemTypeSearch.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const options = itemTypeDropdown.querySelectorAll('.combobox-option');
        
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(filter)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        itemTypeDropdown.classList.add('show');
    });

    itemTypeDropdown.querySelectorAll('.combobox-option').forEach(option => {
        option.addEventListener('click', function() {
            itemTypeSearch.value = this.textContent.trim();
            itemTypeValue.value = this.getAttribute('data-value');
            itemTypeDropdown.classList.remove('show');
        });
    });
}

// Custom Combobox for Edit Form
const editItemTypeSearch = document.getElementById('editItemTypeSearch');
const editItemTypeValue = document.getElementById('edit_item_type_id');
const editItemTypeDropdown = document.getElementById('editItemTypeDropdown');

if (editItemTypeSearch) {
    editItemTypeSearch.addEventListener('focus', function() {
        editItemTypeDropdown.classList.add('show');
    });

    editItemTypeSearch.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const options = editItemTypeDropdown.querySelectorAll('.combobox-option');
        
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(filter)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        editItemTypeDropdown.classList.add('show');
    });

    editItemTypeDropdown.querySelectorAll('.combobox-option').forEach(option => {
        option.addEventListener('click', function() {
            editItemTypeSearch.value = this.textContent.trim();
            editItemTypeValue.value = this.getAttribute('data-value');
            editItemTypeDropdown.classList.remove('show');
        });
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-combobox')) {
        document.querySelectorAll('.combobox-dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
