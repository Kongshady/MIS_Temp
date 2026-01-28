<?php
/**
 * Logout Page - Clinical Laboratory Management System
 * Logs out the current user and redirects to login page
 */

session_start();
require_once 'db_connection.php';

// Log logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $log_stmt = $conn->prepare("INSERT INTO activity_log (employee_id, description, datetime_added, status_code) VALUES (?, ?, NOW(), 1)");
    if ($log_stmt) {
        $description = 'User logged out';
        $log_stmt->bind_param("is", $user_id, $description);
        $log_stmt->execute();
        $log_stmt->close();
    }
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: /mis_project/login.php');
exit();
?>
