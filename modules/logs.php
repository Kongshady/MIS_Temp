<?php
require_once '../db_connection.php';
$page_title = 'Activity Logs';
include '../includes/header.php';

// Get all activity logs
$logs = $conn->query("SELECT a.*, e.firstname, e.lastname, e.position 
                     FROM activity_log a 
                     LEFT JOIN employee e ON a.employee_id = e.employee_id 
                     ORDER BY a.datetime_added DESC 
                     LIMIT 100");
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-clipboard-list"></i> Activity Logs</h2>
        </div>
        
        <?php if ($logs->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Position</th>
                        <th>Description</th>
                        <th>Date & Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['firstname'] . ' ' . $log['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($log['position']); ?></td>
                            <td><?php echo htmlspecialchars($log['description']); ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($log['datetime_added'])); ?></td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick='viewLogDetails(<?php echo json_encode($log); ?>)'>
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No activity logs found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Log Details Modal -->
<div id="logDetailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: white; width: 90%; max-width: 500px; margin: 50px auto; padding: 0; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; border-radius: 8px 8px 0 0; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.25rem;"><i class="fas fa-info-circle"></i> Log Details</h3>
                <button onclick="closeLogDetailsModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: white; opacity: 0.9;">&times;</button>
            </div>
        </div>
        
        <div style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 0.75rem 0; color: #666; font-size: 0.875rem; width: 40%;">Log ID:</td>
                    <td style="padding: 0.75rem 0; font-weight: 500;" id="log_id"></td>
                </tr>
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 0.75rem 0; color: #666; font-size: 0.875rem;">Employee:</td>
                    <td style="padding: 0.75rem 0; font-weight: 500;" id="log_employee"></td>
                </tr>
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 0.75rem 0; color: #666; font-size: 0.875rem;">Position:</td>
                    <td style="padding: 0.75rem 0; font-weight: 500;" id="log_position"></td>
                </tr>
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 0.75rem 0; color: #666; font-size: 0.875rem; vertical-align: top;">Description:</td>
                    <td style="padding: 0.75rem 0; font-weight: 500; word-wrap: break-word;" id="log_description"></td>
                </tr>
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 0.75rem 0; color: #666; font-size: 0.875rem;">Date & Time:</td>
                    <td style="padding: 0.75rem 0; font-weight: 500;" id="log_datetime"></td>
                </tr>
                <tr>
                    <td style="padding: 0.75rem 0; color: #666; font-size: 0.875rem;">Employee ID:</td>
                    <td style="padding: 0.75rem 0; font-weight: 500;" id="log_employee_id"></td>
                </tr>
            </table>
        </div>
        
        <div style="padding: 1rem 1.5rem; background: #f8f9fa; border-radius: 0 0 8px 8px; text-align: right;">
            <button onclick="closeLogDetailsModal()" class="btn btn-secondary" style="padding: 0.5rem 1.5rem;">Close</button>
        </div>
    </div>
</div>

<script>
function viewLogDetails(log) {
    document.getElementById('log_id').textContent = log.log_id || 'N/A';
    document.getElementById('log_employee').textContent = (log.firstname + ' ' + log.lastname) || 'N/A';
    document.getElementById('log_position').textContent = log.position || 'N/A';
    document.getElementById('log_description').textContent = log.description || 'N/A';
    document.getElementById('log_employee_id').textContent = log.employee_id || 'N/A';
    
    // Format date and time
    if (log.datetime_added) {
        const date = new Date(log.datetime_added);
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
        document.getElementById('log_datetime').textContent = date.toLocaleString('en-US', options);
    } else {
        document.getElementById('log_datetime').textContent = 'N/A';
    }
    
    document.getElementById('logDetailsModal').style.display = 'block';
}

function closeLogDetailsModal() {
    document.getElementById('logDetailsModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('logDetailsModal');
    if (event.target === modal) {
        closeLogDetailsModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
