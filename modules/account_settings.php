<?php
require_once '../db_connection.php';
$page_title = 'Account Settings';
include '../includes/header.php';

// Get current user info
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? '';

if (!$user_id) {
    header('Location: ../login.php');
    exit;
}

// Handle password reset
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate new password
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> All fields are required!</div>';
        } elseif ($new_password !== $confirm_password) {
            $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> New passwords do not match!</div>';
        } elseif (strlen($new_password) < 6) {
            $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> New password must be at least 6 characters long!</div>';
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM employee WHERE employee_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result && password_verify($current_password, $result['password'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE employee SET password = ? WHERE employee_id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    log_activity($conn, $user_id, "Password changed for user: $username");
                    $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Password updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error updating password!</div>';
                }
            } else {
                $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Current password is incorrect!</div>';
            }
        }
    }
}

// Get user details
$user_query = $conn->prepare("SELECT e.*, s.label as section_name, r.role_name 
                               FROM employee e 
                               LEFT JOIN section s ON e.section_id = s.section_id 
                               LEFT JOIN roles r ON e.role_id = r.role_id 
                               WHERE e.employee_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-cog"></i> Account Settings</h2>
        </div>
        
        <?php echo $message; ?>
        
        <!-- User Information -->
        <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem; color: #374151;">Profile Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div>
                    <label style="font-size: 0.875rem; color: #6b7280; display: block; margin-bottom: 0.25rem;">Full Name</label>
                    <p style="font-weight: 500; color: #111827; margin: 0;">
                        <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname']); ?>
                    </p>
                </div>
                <div>
                    <label style="font-size: 0.875rem; color: #6b7280; display: block; margin-bottom: 0.25rem;">Username</label>
                    <p style="font-weight: 500; color: #111827; margin: 0;">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </p>
                </div>
                <div>
                    <label style="font-size: 0.875rem; color: #6b7280; display: block; margin-bottom: 0.25rem;">Section</label>
                    <p style="font-weight: 500; color: #111827; margin: 0;">
                        <?php echo htmlspecialchars($user['section_name'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div>
                    <label style="font-size: 0.875rem; color: #6b7280; display: block; margin-bottom: 0.25rem;">Position</label>
                    <p style="font-weight: 500; color: #111827; margin: 0;">
                        <?php echo htmlspecialchars($user['position'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div>
                    <label style="font-size: 0.875rem; color: #6b7280; display: block; margin-bottom: 0.25rem;">Role</label>
                    <p style="font-weight: 500; color: #111827; margin: 0;">
                        <span class="badge badge-info"><?php echo htmlspecialchars($user['role_name'] ?? 'No Role'); ?></span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Change Password Form -->
        <div style="max-width: 600px;">
            <h3 style="margin-bottom: 1rem; color: #374151;"><i class="fas fa-lock"></i> Change Password</h3>
            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                
                <div class="form-group">
                    <label>Current Password *</label>
                    <input type="password" name="current_password" class="form-control" required placeholder="Enter your current password">
                </div>
                
                <div class="form-group">
                    <label>New Password *</label>
                    <input type="password" name="new_password" class="form-control" required placeholder="Enter new password (min. 6 characters)">
                    <small class="form-text text-muted">Must be at least 6 characters long</small>
                </div>
                
                <div class="form-group">
                    <label>Confirm New Password *</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter new password">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
