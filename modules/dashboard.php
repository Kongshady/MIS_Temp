<?php
/**
 * Dashboard - Clinical Laboratory Management System
 * Shows role-specific overview and quick actions
 */

require_once '../db_connection.php';
require_once '../includes/auth.php';
require_login();

$page_title = 'Dashboard';
include '../includes/header.php';

$user_id = get_user_id();
$user_name = get_user_name();
$user_role = get_user_role();
?>

<div class="dashboard-container" style="padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <div>
            <h1 style="margin: 0;">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p class="text-muted" style="margin: 0.5rem 0 0 0;">Role: <strong><?php echo htmlspecialchars($user_role); ?></strong></p>
        </div>
        <a href="../logout.php" class="btn btn-danger" style="padding: 0.5rem 1.5rem; background: #dc3545; color: white; text-decoration: none; border-radius: 5px;">Logout</a>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
        
        <?php if (has_permission('patients.view')): ?>
        <div class="card" style="padding: 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
            <h3 style="margin: 0 0 0.5rem 0;">Patients</h3>
            <?php
            $patient_count = $conn->query("SELECT COUNT(*) as count FROM patient WHERE status_code = 1")->fetch_assoc()['count'];
            ?>
            <p style="font-size: 2rem; margin: 0; font-weight: bold;"><?php echo $patient_count; ?></p>
            <a href="patients.php" style="color: white; text-decoration: underline; margin-top: 1rem; display: inline-block;">View Patients →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('inventory.view')): ?>
        <div class="card" style="padding: 1.5rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 10px;">
            <h3 style="margin: 0 0 0.5rem 0;">Inventory</h3>
            <?php
            $item_count = $conn->query("SELECT COUNT(*) as count FROM item WHERE status_code = 1")->fetch_assoc()['count'];
            ?>
            <p style="font-size: 2rem; margin: 0; font-weight: bold;"><?php echo $item_count; ?></p>
            <a href="inventory.php" style="color: white; text-decoration: underline; margin-top: 1rem; display: inline-block;">Manage Inventory →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('equipment.view')): ?>
        <div class="card" style="padding: 1.5rem; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 10px;">
            <h3 style="margin: 0 0 0.5rem 0;">Equipment</h3>
            <?php
            $equipment_count = $conn->query("SELECT COUNT(*) as count FROM equipment WHERE status = 'operational'")->fetch_assoc()['count'];
            ?>
            <p style="font-size: 2rem; margin: 0; font-weight: bold;"><?php echo $equipment_count; ?></p>
            <a href="equipment.php" style="color: white; text-decoration: underline; margin-top: 1rem; display: inline-block;">View Equipment →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('transactions.view')): ?>
        <div class="card" style="padding: 1.5rem; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 10px;">
            <h3 style="margin: 0 0 0.5rem 0;">Transactions</h3>
            <?php
            $transaction_count = $conn->query("SELECT COUNT(*) as count FROM transaction WHERE status_code = 1")->fetch_assoc()['count'];
            ?>
            <p style="font-size: 2rem; margin: 0; font-weight: bold;"><?php echo $transaction_count; ?></p>
            <a href="transactions.php" style="color: white; text-decoration: underline; margin-top: 1rem; display: inline-block;">View Transactions →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('tests.manage')): ?>
        <div class="card" style="padding: 1.5rem; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border-radius: 10px;">
            <h3 style="margin: 0 0 0.5rem 0;">Tests</h3>
            <?php
            $test_count = $conn->query("SELECT COUNT(*) as count FROM test")->fetch_assoc()['count'];
            ?>
            <p style="font-size: 2rem; margin: 0; font-weight: bold;"><?php echo $test_count; ?></p>
            <a href="tests.php" style="color: white; text-decoration: underline; margin-top: 1rem; display: inline-block;">Manage Tests →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('users.manage')): ?>
        <div class="card" style="padding: 1.5rem; background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white; border-radius: 10px;">
            <h3 style="margin: 0 0 0.5rem 0;">Employees</h3>
            <?php
            $employee_count = $conn->query("SELECT COUNT(*) as count FROM employee WHERE status_code = 1")->fetch_assoc()['count'];
            ?>
            <p style="font-size: 2rem; margin: 0; font-weight: bold;"><?php echo $employee_count; ?></p>
            <a href="employees.php" style="color: white; text-decoration: underline; margin-top: 1rem; display: inline-block;">Manage Employees →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('sections.manage')): ?>
        <div class="card" style="padding: 1.5rem; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; border-radius: 10px;">
            <h3 style="margin: 0 0 0.5rem 0;">Sections</h3>
            <?php
            $section_count = $conn->query("SELECT COUNT(*) as count FROM section")->fetch_assoc()['count'];
            ?>
            <p style="font-size: 2rem; margin: 0; font-weight: bold;"><?php echo $section_count; ?></p>
            <a href="sections.php" style="color: #333; text-decoration: underline; margin-top: 1rem; display: inline-block;">Manage Sections →</a>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (has_permission('logs.view')): ?>
    <div class="card" style="margin-top: 2rem; padding: 1.5rem; border-radius: 10px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0;">Recent Activity</h2>
        <?php
        $recent_logs = $conn->query("
            SELECT al.*, e.firstname, e.lastname, e.username
            FROM activity_log al
            LEFT JOIN employee e ON al.employee_id = e.employee_id
            ORDER BY al.datetime_added DESC
            LIMIT 10
        ");
        
        if ($recent_logs && $recent_logs->num_rows > 0):
        ?>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">User</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Activity</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Date & Time</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($log = $recent_logs->fetch_assoc()): ?>
                <tr>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <?php echo htmlspecialchars($log['firstname'] . ' ' . $log['lastname']); ?>
                        <small style="color: #6c757d;">(<?php echo htmlspecialchars($log['username']); ?>)</small>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <?php echo htmlspecialchars($log['description']); ?>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <?php echo date('M d, Y h:i A', strtotime($log['datetime_added'])); ?>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <?php if ($log['status_code'] == 1): ?>
                            <span style="padding: 0.25rem 0.5rem; background: #d4edda; color: #155724; border-radius: 4px; font-size: 0.875rem;">Success</span>
                        <?php else: ?>
                            <span style="padding: 0.25rem 0.5rem; background: #f8d7da; color: #721c24; border-radius: 4px; font-size: 0.875rem;">Failed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="logs.php" style="margin-top: 1rem; display: inline-block;">View All Logs →</a>
        <?php else: ?>
        <p>No recent activity.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="card" style="margin-top: 2rem; padding: 1.5rem; border-radius: 10px; background: #f8f9fa;">
        <h3>Quick Actions</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
            <?php if (has_permission('patients.add_internal') || has_permission('patients.add_walkin')): ?>
            <a href="patients.php" class="btn btn-primary">Add Patient</a>
            <?php endif; ?>
            
            <?php if (has_permission('inventory.stock_in')): ?>
            <a href="inventory.php" class="btn btn-primary">Stock In</a>
            <?php endif; ?>
            
            <?php if (has_permission('equipment.manage')): ?>
            <a href="equipment.php" class="btn btn-primary">Add Equipment</a>
            <?php endif; ?>
            
            <?php if (has_permission('transactions.create')): ?>
            <a href="transactions.php" class="btn btn-primary">New Transaction</a>
            <?php endif; ?>
            
            <?php if (has_permission('reports.generate')): ?>
            <a href="inventory_reports.php" class="btn btn-success">Generate Report</a>
            <?php endif; ?>
            
            <?php if (has_permission('users.manage')): ?>
            <a href="employees.php" class="btn btn-primary">Manage Employees</a>
            <?php endif; ?>
            
            <?php if (has_permission('tests.manage')): ?>
            <a href="tests.php" class="btn btn-primary">Manage Tests</a>
            <?php endif; ?>
            
            <?php if (has_permission('sections.manage')): ?>
            <a href="sections.php" class="btn btn-primary">Manage Sections</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.dashboard-container h1 {
    color: #333;
    margin-bottom: 0.5rem;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}
</style>

<?php include '../includes/footer.php'; ?>
