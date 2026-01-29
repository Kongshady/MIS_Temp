<?php
/**
 * ============================================================================
 * Authentication & Authorization Helper
 * Clinical Laboratory Management System - RBAC Implementation
 * ============================================================================
 * 
 * This file provides core authentication and authorization functions.
 * Include this at the top of protected pages.
 * 
 * Usage:
 *   require_once '../includes/auth.php';
 *   require_login();
 *   require_permission('users.manage');
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Log activity to database
 * @param object $conn Database connection
 * @param int $employee_id Employee ID performing the action
 * @param string $description Activity description
 * @param int $status_code 1 for success, 0 for failure/error
 */
function log_activity($conn, $employee_id, $description, $status_code = 1) {
    if (!$conn) {
        return false;
    }
    
    $stmt = $conn->prepare("INSERT INTO activity_log (employee_id, description, datetime_added, status_code) VALUES (?, ?, NOW(), ?)");
    if ($stmt) {
        $stmt->bind_param("isi", $employee_id, $description, $status_code);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    return false;
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role_id']);
}

/**
 * Require user to be logged in
 * Redirects to login page if not authenticated
 * @param string $redirect_to Optional URL to redirect after login
 */
function require_login($redirect_to = null) {
    if (!is_logged_in()) {
        // Store intended destination
        if ($redirect_to) {
            $_SESSION['redirect_after_login'] = $redirect_to;
        } else {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        }
        
        // Redirect to login
        header('Location: /mis_project/login.php');
        exit();
    }
}

/**
 * Check if current user has a specific permission
 * @param string $permission_key Permission key (e.g., 'users.manage')
 * @return bool
 */
function has_permission($permission_key) {
    // Not logged in = no permissions
    if (!is_logged_in()) {
        return false;
    }
    
    // Check if permissions array exists in session
    if (!isset($_SESSION['permissions']) || !is_array($_SESSION['permissions'])) {
        return false;
    }
    
    // Check if user has the permission
    return in_array($permission_key, $_SESSION['permissions']);
}

/**
 * Check if user has ANY of the given permissions (OR logic)
 * @param array $permission_keys Array of permission keys
 * @return bool
 */
function has_any_permission($permission_keys) {
    if (!is_array($permission_keys)) {
        $permission_keys = [$permission_keys];
    }
    
    foreach ($permission_keys as $key) {
        if (has_permission($key)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if user has ALL of the given permissions (AND logic)
 * @param array $permission_keys Array of permission keys
 * @return bool
 */
function has_all_permissions($permission_keys) {
    if (!is_array($permission_keys)) {
        $permission_keys = [$permission_keys];
    }
    
    foreach ($permission_keys as $key) {
        if (!has_permission($key)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Require user to have a specific permission
 * Shows access denied error if user lacks permission
 * @param string $permission_key Permission key
 */
function require_permission($permission_key) {
    require_login();
    
    if (!has_permission($permission_key)) {
        // Log unauthorized access attempt
        log_unauthorized_access($permission_key);
        
        // Show access denied page
        show_access_denied($permission_key);
        exit();
    }
}

/**
 * Require user to have ANY of the given permissions
 * @param array $permission_keys Array of permission keys
 */
function require_any_permission($permission_keys) {
    require_login();
    
    if (!has_any_permission($permission_keys)) {
        log_unauthorized_access(implode(' OR ', $permission_keys));
        show_access_denied(implode(', ', $permission_keys));
        exit();
    }
}

/**
 * Get current user's role name
 * @return string|null
 */
function get_user_role() {
    return $_SESSION['role_name'] ?? null;
}

/**
 * Get current user ID
 * @return int|null
 */
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user's full name
 * @return string
 */
function get_user_name() {
    if (isset($_SESSION['user_firstname']) && isset($_SESSION['user_lastname'])) {
        return $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'];
    }
    return 'Unknown User';
}

/**
 * Check if current user is a specific role
 * @param string $role_name Role name to check
 * @return bool
 */
function is_role($role_name) {
    return get_user_role() === $role_name;
}

/**
 * Log unauthorized access attempt
 * @param string $permission_key
 */
function log_unauthorized_access($permission_key) {
    // Only log if database connection is available
    if (!isset($GLOBALS['conn'])) {
        return;
    }
    
    $conn = $GLOBALS['conn'];
    $user_id = get_user_id() ?? 0;
    $request_uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
    
    $stmt = $conn->prepare("INSERT INTO activity_log (employee_id, description, datetime_added, status_code) VALUES (?, ?, NOW(), 0)");
    if ($stmt) {
        $description = "Unauthorized access: $permission_key from $request_uri";
        $stmt->bind_param("is", $user_id, $description);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Show access denied page
 * @param string $permission_key
 */
function show_access_denied($permission_key) {
    $role = get_user_role();
    $user_name = get_user_name();
    
    // If headers not sent, send 403
    if (!headers_sent()) {
        http_response_code(403);
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - Clinical Laboratory Management System</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .access-denied-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                padding: 3rem;
                max-width: 600px;
                text-align: center;
            }
            .icon-container {
                font-size: 80px;
                color: #e74c3c;
                margin-bottom: 1rem;
            }
            h1 {
                color: #2c3e50;
                font-size: 2rem;
                margin-bottom: 1rem;
            }
            .error-code {
                color: #e74c3c;
                font-size: 1.2rem;
                font-weight: bold;
                margin-bottom: 1.5rem;
            }
            .message {
                color: #555;
                line-height: 1.6;
                margin-bottom: 1.5rem;
            }
            .info-box {
                background: #f8f9fa;
                border-left: 4px solid #667eea;
                padding: 1rem;
                margin: 1.5rem 0;
                text-align: left;
            }
            .info-box strong {
                color: #2c3e50;
                display: block;
                margin-bottom: 0.5rem;
            }
            .info-item {
                color: #555;
                font-size: 0.9rem;
                margin: 0.3rem 0;
            }
            .buttons {
                display: flex;
                gap: 1rem;
                justify-content: center;
                margin-top: 2rem;
            }
            .btn {
                padding: 0.8rem 2rem;
                border: none;
                border-radius: 5px;
                font-size: 1rem;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.3s;
            }
            .btn-primary {
                background: #667eea;
                color: white;
            }
            .btn-primary:hover {
                background: #5568d3;
            }
            .btn-secondary {
                background: #95a5a6;
                color: white;
            }
            .btn-secondary:hover {
                background: #7f8c8d;
            }
        </style>
    </head>
    <body>
        <div class="access-denied-container">
            <div class="icon-container"><i class="fas fa-ban"></i></div>
            <h1>Access Denied</h1>
            <div class="error-code">Error 403 - Forbidden</div>
            <p class="message">
                You do not have permission to access this resource.
                This action requires specific authorization that your current role does not have.
            </p>
            
            <div class="info-box">
                <strong>Access Information:</strong>
                <div class="info-item"><strong>User:</strong> <?php echo htmlspecialchars($user_name); ?></div>
                <div class="info-item"><strong>Role:</strong> <?php echo htmlspecialchars($role ?? 'No Role Assigned'); ?></div>
                <div class="info-item"><strong>Required Permission:</strong> <?php echo htmlspecialchars($permission_key); ?></div>
                <div class="info-item"><strong>Time:</strong> <?php echo date('F j, Y, g:i a'); ?></div>
            </div>
            
            <p class="message" style="font-size: 0.9rem; color: #777;">
                If you believe you should have access to this resource, please contact your system administrator 
                or the laboratory manager to request the appropriate permissions.
            </p>
            
            <div class="buttons">
                <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
                <a href="mis_project/modules/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Load user permissions from database into session
 * Call this after successful login
 * @param object $conn Database connection
 * @param int $user_id Employee ID
 * @return bool Success status
 */
function load_user_permissions($conn, $user_id) {
    // Get user role and details
    $stmt = $conn->prepare("
        SELECT e.employee_id, e.firstname, e.lastname, e.position, e.role_id,
               r.role_name, r.display_name as role_display
        FROM employee e
        LEFT JOIN roles r ON e.role_id = r.role_id
        WHERE e.employee_id = ? AND e.status_code = 1
    ");
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        return false;
    }
    
    // Store user info in session
    $_SESSION['user_id'] = $user['employee_id'];
    $_SESSION['user_firstname'] = $user['firstname'];
    $_SESSION['user_lastname'] = $user['lastname'];
    $_SESSION['user_position'] = $user['position'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'];
    $_SESSION['role_display'] = $user['role_display'];
    
    // Load permissions for this role
    if ($user['role_id']) {
        $stmt = $conn->prepare("
            SELECT p.permission_key
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.permission_id
            WHERE rp.role_id = ?
        ");
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $user['role_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission_key'];
        }
        $stmt->close();
        
        $_SESSION['permissions'] = $permissions;
    } else {
        $_SESSION['permissions'] = [];
    }
    
    return true;
}

/**
 * Clear all session data and logout user
 */
function logout_user() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login
    header('Location: /mis_project/login.php');
    exit();
}

/**
 * Generate permission-based navigation menu
 * @return array Menu items the user has access to
 */
function get_accessible_menu() {
    $all_menu_items = [
        [
            'label' => 'Dashboard',
            'url' => 'dashboard.php',
            'icon' => '<i class="fa-solid fa-gauge"></i>',
            'permission' => null // Everyone can access
        ],
        [
            'label' => 'Patients',
            'url' => 'patients.php',
            'icon' => '<i class="fa-solid fa-users"></i>',
            'permission' => 'patients.view'
        ],
        [
            'label' => 'Physicians',
            'url' => 'physicians.php',
            'icon' => '<i class="fa-solid fa-user-doctor"></i>',
            'permission' => 'physicians.view'
        ],
        [
            'label' => 'Lab Results',
            'url' => 'lab_results.php',
            'icon' => '<i class="fa-solid fa-microscope"></i>',
            'permission' => 'lab_results.view'
        ],
        [
            'label' => 'Tests',
            'url' => 'tests.php',
            'icon' => '<i class="fa-solid fa-flask"></i>',
            'permission' => 'tests.manage'
        ],
        [
            'label' => 'Sections',
            'url' => 'sections.php',
            'icon' => '<i class="fa-solid fa-building"></i>',
            'permission' => 'sections.manage'
        ],
        [
            'label' => 'Employees',
            'url' => 'employees.php',
            'icon' => '<i class="fa-solid fa-user-tie"></i>',
            'permission' => 'employees.view'
        ],
        [
            'label' => 'Transactions',
            'url' => 'transactions.php',
            'icon' => '<i class="fa-solid fa-file-invoice"></i>',
            'permission' => 'transactions.view'
        ],
        [
            'label' => 'Items',
            'url' => 'items.php',
            'icon' => '<i class="fa-solid fa-box"></i>',
            'permission' => 'items.view'
        ],
        [
            'label' => 'Inventory',
            'url' => 'inventory.php',
            'icon' => '<i class="fa-solid fa-warehouse"></i>',
            'permission' => 'inventory.view'
        ],
        [
            'label' => 'Equipment',
            'url' => 'equipment.php',
            'icon' => '<i class="fa-solid fa-wrench"></i>',
            'permission' => 'equipment.view'
        ],
        [
            'label' => 'Calibration',
            'url' => 'calibration.php',
            'icon' => '<i class="fa-solid fa-scale-balanced"></i>',
            'permission' => 'calibration.view'
        ],
        [
            'label' => 'Certificates',
            'url' => 'certificates.php',
            'icon' => '<i class="fa-solid fa-certificate"></i>',
            'permission' => 'certificates.view'
        ],
        [
            'label' => 'Reports',
            'url' => '../modules/compliance_reports.php',
            'icon' => '<i class="fa-solid fa-chart-line"></i>',
            'permission' => 'reports.view'
        ],
        [
            'label' => 'Activity Logs',
            'url' => 'logs.php',
            'icon' => '<i class="fa-solid fa-clipboard-list"></i>',
            'permission' => 'logs.view'
        ],
    ];
    
    $accessible_menu = [];
    foreach ($all_menu_items as $item) {
        // If no permission required, or user has permission
        if ($item['permission'] === null || has_permission($item['permission'])) {
            $accessible_menu[] = $item;
        }
    }
    
    return $accessible_menu;
}
