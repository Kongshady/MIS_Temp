<?php
/**
 * Logout Page - Clinical Laboratory Management System
 * Logs out the current user and redirects to login page
 */

session_start();
require_once 'db_connection.php';
require_once 'includes/auth.php';

// Log logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    log_activity($conn, $user_id, 'User logged out', 1);
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: /mis_project/login.php');
exit();
?>
