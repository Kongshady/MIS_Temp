<?php
require_once '../db_connection.php';
$page_title = 'Calibration Details';
include '../includes/header.php';

$procedure_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get procedure details
$procedure = $conn->query("SELECT cp.*, e.name as equipment_name, e.model, e.serial_no 
                          FROM calibration_procedure cp
                          JOIN equipment e ON cp.equipment_id = e.equipment_id
                          WHERE cp.procedure_id = $procedure_id")->fetch_assoc();

if (!$procedure) {
    echo '<div class="container"><div class="alert alert-danger">Calibration procedure not found!</div></div>';
    include '../includes/footer.php';
    exit;
}

// Get calibration history
$history = $conn->query("SELECT cr.*, emp.firstname, emp.lastname 
                        FROM calibration_record cr
                        JOIN employee emp ON cr.performed_by = emp.employee_id
                        WHERE cr.procedure_id = $procedure_id
                        ORDER BY cr.calibration_date DESC");
?>

<div class="container">
    <!-- Procedure Information -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-clipboard-list"></i> Calibration Procedure Details</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div><strong>Procedure ID:</strong><br><?php echo $procedure['procedure_id']; ?></div>
            <div><strong>Equipment:</strong><br><?php echo htmlspecialchars($procedure['equipment_name']); ?></div>
            <div><strong>Model:</strong><br><?php echo htmlspecialchars($procedure['model']); ?></div>
            <div><strong>Serial No:</strong><br><?php echo htmlspecialchars($procedure['serial_no']); ?></div>
            <div><strong>Procedure Name:</strong><br><?php echo htmlspecialchars($procedure['procedure_name']); ?></div>
            <div><strong>Standard Reference:</strong><br><?php echo htmlspecialchars($procedure['standard_reference']); ?></div>
            <div><strong>Frequency:</strong><br><?php echo strtoupper($procedure['frequency']); ?></div>
            <div><strong>Next Due Date:</strong><br><?php echo date('M d, Y', strtotime($procedure['next_due_date'])); ?></div>
        </div>
    </div>
    
    <!-- Calibration History -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-bar"></i> Calibration History</h2>
        </div>
        
        <?php if ($history->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Performed By</th>
                        <th>Result Status</th>
                        <th>Notes</th>
                        <th>Next Calibration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($rec = $history->fetch_assoc()): 
                        $status_class = '';
                        if ($rec['result_status'] == 'pass') $status_class = 'style="color: green; font-weight: bold;"';
                        elseif ($rec['result_status'] == 'fail') $status_class = 'style="color: red; font-weight: bold;"';
                    ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($rec['calibration_date'])); ?></td>
                            <td><?php echo htmlspecialchars($rec['firstname'] . ' ' . $rec['lastname']); ?></td>
                            <td <?php echo $status_class; ?>><?php echo strtoupper($rec['result_status']); ?></td>
                            <td><?php echo htmlspecialchars($rec['notes']); ?></td>
                            <td><?php echo $rec['next_calibration_date'] ? date('M d, Y', strtotime($rec['next_calibration_date'])) : 'N/A'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No calibration records yet.</p>
        <?php endif; ?>
    </div>
    
    <!-- Statistics -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-line"></i> Calibration Statistics</h2>
        </div>
        
        <?php
        $stats = $conn->query("SELECT 
                              COUNT(*) as total_calibrations,
                              SUM(CASE WHEN result_status = 'pass' THEN 1 ELSE 0 END) as passed,
                              SUM(CASE WHEN result_status = 'fail' THEN 1 ELSE 0 END) as failed,
                              SUM(CASE WHEN result_status = 'conditional' THEN 1 ELSE 0 END) as conditional
                              FROM calibration_record
                              WHERE procedure_id = $procedure_id")->fetch_assoc();
        
        $pass_rate = $stats['total_calibrations'] > 0 ? round(($stats['passed'] / $stats['total_calibrations']) * 100, 1) : 0;
        ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div style="text-align: center; padding: 1rem; background: #e3f2fd; border-radius: 10px;">
                <div style="font-size: 2rem; font-weight: bold; color: #1976d2;"><?php echo $stats['total_calibrations']; ?></div>
                <div>Total Calibrations</div>
            </div>
            <div style="text-align: center; padding: 1rem; background: #e8f5e9; border-radius: 10px;">
                <div style="font-size: 2rem; font-weight: bold; color: #388e3c;"><?php echo $stats['passed']; ?></div>
                <div>Passed</div>
            </div>
            <div style="text-align: center; padding: 1rem; background: #ffebee; border-radius: 10px;">
                <div style="font-size: 2rem; font-weight: bold; color: #d32f2f;"><?php echo $stats['failed']; ?></div>
                <div>Failed</div>
            </div>
            <div style="text-align: center; padding: 1rem; background: #fff3e0; border-radius: 10px;">
                <div style="font-size: 2rem; font-weight: bold; color: #f57c00;"><?php echo $pass_rate; ?>%</div>
                <div>Pass Rate</div>
            </div>
        </div>
    </div>
    
    <a href="calibration.php" class="btn btn-primary">Back to Calibration List</a>
</div>

<?php include '../includes/footer.php'; ?>
