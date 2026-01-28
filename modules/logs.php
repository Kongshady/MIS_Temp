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
            <h2>📝 Activity Logs</h2>
        </div>
        
        <?php if ($logs->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Employee</th>
                        <th>Position</th>
                        <th>Description</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $log['activity_log_id']; ?></td>
                            <td><?php echo htmlspecialchars($log['firstname'] . ' ' . $log['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($log['position']); ?></td>
                            <td><?php echo htmlspecialchars($log['description']); ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($log['datetime_added'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No activity logs found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
