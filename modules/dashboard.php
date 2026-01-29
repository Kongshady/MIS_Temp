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
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <?php if (has_permission('patients.add_internal') || has_permission('patients.add_walkin')): ?>
            <a href="patients.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; background: #e2e8f0; color: #2d3748; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">Add Patient</a>
            <?php endif; ?>
            
            <?php if (has_permission('inventory.stock_in')): ?>
            <a href="inventory.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; background: #e2e8f0; color: #2d3748; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">Stock In</a>
            <?php endif; ?>
            
            <?php if (has_permission('equipment.manage')): ?>
            <a href="equipment.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; background: #e2e8f0; color: #2d3748; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">Add Equipment</a>
            <?php endif; ?>
            
            <?php if (has_permission('transactions.create')): ?>
            <a href="transactions.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; background: #e2e8f0; color: #2d3748; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">New Transaction</a>
            <?php endif; ?>
            
            <?php if (has_permission('reports.generate')): ?>
            <a href="inventory_reports.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; background: #e2e8f0; color: #2d3748; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">Generate Report</a>
            <?php endif; ?>
            
            <?php if (has_permission('users.manage')): ?>
            <a href="employees.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; background: #e2e8f0; color: #2d3748; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">Manage Employees</a>
            <?php endif; ?>
            
            <?php if (has_permission('tests.manage')): ?>
            <a href="tests.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; background: #e2e8f0; color: #2d3748; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">Manage Tests</a>
            <?php endif; ?>
            
            <?php if (has_permission('sections.manage')): ?>
            <a href="sections.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; background: #e2e8f0; color: #2d3748; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">Manage Sections</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
        
        <?php if (has_permission('patients.view')): ?>
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
            <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Patients</div>
            <?php
            $patient_count = $conn->query("SELECT COUNT(*) as count FROM patient WHERE status_code = 1")->fetch_assoc()['count'];
            // Check if datetime_added column exists in patient table
            $patient_cols = $conn->query("SHOW COLUMNS FROM patient LIKE 'datetime_added'");
            $patient_today = ($patient_cols && $patient_cols->num_rows > 0) ? 
                $conn->query("SELECT COUNT(*) as count FROM patient WHERE status_code = 1 AND DATE(datetime_added) = CURDATE()")->fetch_assoc()['count'] : 0;
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #4a5568;"><?php echo $patient_count; ?></div>
                <div style="color: #5a67d8; font-size: 1.25rem; font-weight: 600;">
                    <i class="fas fa-user-plus" style="font-size: 0.9rem;"></i> <?php echo $patient_today; ?>
                </div>
            </div>
            <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                Added today: <?php echo $patient_today; ?> patients
            </div>
            <a href="patients.php" style="color: #5a67d8; text-decoration: none; margin-top: 1rem; display: inline-block; font-weight: 500;">View Patients →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('inventory.view')): ?>
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
            <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Inventory</div>
            <?php
            $item_count = $conn->query("SELECT COUNT(*) as count FROM item WHERE status_code = 1")->fetch_assoc()['count'];
            $item_today = $conn->query("SELECT COUNT(DISTINCT item_id) as count FROM stock_in WHERE DATE(datetime_added) = CURDATE()")->fetch_assoc()['count'];
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #4a5568;"><?php echo $item_count; ?></div>
                <div style="color: #48bb78; font-size: 1.25rem; font-weight: 600;">
                    <i class="fas fa-arrow-up" style="font-size: 0.9rem;"></i> <?php echo $item_today; ?>
                </div>
            </div>
            <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                Added today: <?php echo $item_today; ?> items
            </div>
            <a href="inventory.php" style="color: #5a67d8; text-decoration: none; margin-top: 1rem; display: inline-block; font-weight: 500;">Manage Inventory →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('equipment.view')): ?>
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
            <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Equipment</div>
            <?php
            $equipment_count = $conn->query("SELECT COUNT(*) as count FROM equipment WHERE status = 'operational'")->fetch_assoc()['count'];
            // Check if datetime_added column exists in equipment table
            $equipment_cols = $conn->query("SHOW COLUMNS FROM equipment LIKE 'datetime_added'");
            $equipment_today = ($equipment_cols && $equipment_cols->num_rows > 0) ? 
                $conn->query("SELECT COUNT(*) as count FROM equipment WHERE DATE(datetime_added) = CURDATE()")->fetch_assoc()['count'] : 0;
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #4a5568;"><?php echo $equipment_count; ?></div>
                <div style="color: #5a67d8; font-size: 1.25rem; font-weight: 600;">
                    <i class="fas fa-tools" style="font-size: 0.9rem;"></i> <?php echo $equipment_today; ?>
                </div>
            </div>
            <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                Added today: <?php echo $equipment_today; ?> items
            </div>
            <a href="equipment.php" style="color: #5a67d8; text-decoration: none; margin-top: 1rem; display: inline-block; font-weight: 500;">View Equipment →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('transactions.view')): ?>
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
            <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Transactions</div>
            <?php
            $transaction_count = $conn->query("SELECT COUNT(*) as count FROM transaction WHERE status_code = 1")->fetch_assoc()['count'];
            // Check if datetime_added column exists in transaction table
            $transaction_cols = $conn->query("SHOW COLUMNS FROM transaction LIKE 'datetime_added'");
            $transaction_today = ($transaction_cols && $transaction_cols->num_rows > 0) ? 
                $conn->query("SELECT COUNT(*) as count FROM transaction WHERE DATE(datetime_added) = CURDATE()")->fetch_assoc()['count'] : 0;
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #4a5568;"><?php echo $transaction_count; ?></div>
                <div style="color: #5a67d8; font-size: 1.25rem; font-weight: 600;">
                    <i class="fas fa-receipt" style="font-size: 0.9rem;"></i> <?php echo $transaction_today; ?>
                </div>
            </div>
            <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                Added today: <?php echo $transaction_today; ?> transactions
            </div>
            <a href="transactions.php" style="color: #5a67d8; text-decoration: none; margin-top: 1rem; display: inline-block; font-weight: 500;">View Transactions →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('tests.manage')): ?>
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
            <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Tests</div>
            <?php
            $test_count = $conn->query("SELECT COUNT(*) as count FROM test")->fetch_assoc()['count'];
            // Check if datetime_added column exists in test table
            $test_cols = $conn->query("SHOW COLUMNS FROM test LIKE 'datetime_added'");
            $test_today = ($test_cols && $test_cols->num_rows > 0) ? 
                $conn->query("SELECT COUNT(*) as count FROM test WHERE DATE(datetime_added) = CURDATE()")->fetch_assoc()['count'] : 0;
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #4a5568;"><?php echo $test_count; ?></div>
                <div style="color: #5a67d8; font-size: 1.25rem; font-weight: 600;">
                    <i class="fas fa-flask" style="font-size: 0.9rem;"></i> <?php echo $test_today; ?>
                </div>
            </div>
            <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                Added today: <?php echo $test_today; ?> tests
            </div>
            <a href="tests.php" style="color: #5a67d8; text-decoration: none; margin-top: 1rem; display: inline-block; font-weight: 500;">Manage Tests →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('users.manage')): ?>
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
            <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Employees</div>
            <?php
            $employee_count = $conn->query("SELECT COUNT(*) as count FROM employee WHERE status_code = 1")->fetch_assoc()['count'];
            // Check if datetime_added column exists in employee table
            $employee_cols = $conn->query("SHOW COLUMNS FROM employee LIKE 'datetime_added'");
            $employee_today = ($employee_cols && $employee_cols->num_rows > 0) ? 
                $conn->query("SELECT COUNT(*) as count FROM employee WHERE DATE(datetime_added) = CURDATE()")->fetch_assoc()['count'] : 0;
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #4a5568;"><?php echo $employee_count; ?></div>
                <div style="color: #5a67d8; font-size: 1.25rem; font-weight: 600;">
                    <i class="fas fa-user-tie" style="font-size: 0.9rem;"></i> <?php echo $employee_today; ?>
                </div>
            </div>
            <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                Added today: <?php echo $employee_today; ?> employees
            </div>
            <a href="employees.php" style="color: #5a67d8; text-decoration: none; margin-top: 1rem; display: inline-block; font-weight: 500;">Manage Employees →</a>
        </div>
        <?php endif; ?>
        
        <?php if (has_permission('sections.manage')): ?>
        <div class="card" style="padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
            <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Sections</div>
            <?php
            $section_count = $conn->query("SELECT COUNT(*) as count FROM section")->fetch_assoc()['count'];
            // Check if datetime_added column exists in section table
            $section_cols = $conn->query("SHOW COLUMNS FROM section LIKE 'datetime_added'");
            $section_today = ($section_cols && $section_cols->num_rows > 0) ? 
                $conn->query("SELECT COUNT(*) as count FROM section WHERE DATE(datetime_added) = CURDATE()")->fetch_assoc()['count'] : 0;
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #4a5568;"><?php echo $section_count; ?></div>
                <div style="color: #5a67d8; font-size: 1.25rem; font-weight: 600;">
                    <i class="fas fa-sitemap" style="font-size: 0.9rem;"></i> <?php echo $section_today; ?>
                </div>
            </div>
            <div style="color: #999; font-size: 0.75rem; margin-top: 0.5rem;">
                Added today: <?php echo $section_today; ?> sections
            </div>
            <a href="sections.php" style="color: #5a67d8; text-decoration: none; margin-top: 1rem; display: inline-block; font-weight: 500;">Manage Sections →</a>
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

.btn-secondary {
    background: #e2e8f0;
    color: #2d3748;
}

.btn-secondary:hover {
    background: #cbd5e0;
}
</style>

<?php include '../includes/footer.php'; ?>
