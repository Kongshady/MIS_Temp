<?php
/**
 * ============================================================================
 * Login Page - Clinical Laboratory Management System
 * With RBAC Integration
 * ============================================================================
 */

session_start();
require_once 'db_connection.php';
require_once 'includes/auth.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: /mis_project/modules/dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Query user from database
        $stmt = $conn->prepare("
            SELECT e.employee_id, e.username, e.password_hash, e.firstname, e.lastname, 
                   e.position, e.role_id, e.status_code,
                   r.role_name, r.display_name as role_display
            FROM employee e
            LEFT JOIN roles r ON e.role_id = r.role_id
            WHERE e.username = ?
        ");
        
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if ($user) {
                // Check if account is active
                if ($user['status_code'] != 1) {
                    $error = 'Your account is inactive. Please contact the administrator.';
                } 
                // Check if user has a role assigned
                elseif (!$user['role_id']) {
                    $error = 'No role assigned to your account. Please contact the administrator.';
                }
                // Verify password
                elseif (password_verify($password, $user['password_hash'])) {
                    // Successful login - load permissions
                    if (load_user_permissions($conn, $user['employee_id'])) {
                        // Log successful login
                        log_activity($conn, $user['employee_id'], 'User logged in successfully', 1);
                        
                        // Redirect to intended page or dashboard
                        $redirect = $_SESSION['redirect_after_login'] ?? '/mis_project/modules/dashboard.php';
                        unset($_SESSION['redirect_after_login']);
                        header('Location: ' . $redirect);
                        exit();
                    } else {
                        $error = 'Failed to load user permissions. Please try again.';
                    }
                } else {
                    // Invalid password - log failed attempt
                    if (isset($user['employee_id'])) {
                        log_activity($conn, $user['employee_id'], 'Failed login attempt - incorrect password', 0);
                    }
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Database error. Please try again later.';
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    logout_user();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clinical Laboratory Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        
        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 3rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-left h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .login-left p {
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .login-left .icon {
            font-size: 80px;
            margin-bottom: 2rem;
        }
        
        .login-right {
            padding: 3rem;
        }
        
        .login-right h2 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .login-right p {
            color: #7f8c8d;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #e74c3c;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #27ae60;
        }
        
        .features {
            margin-top: 2rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            gap: 0.8rem;
        }
        
        .feature-item .icon {
            font-size: 24px;
        }
        
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }
            
            .login-left {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="icon">🔬</div>
            <h1>Clinical Laboratory Management System</h1>
            <p>Secure, efficient, and comprehensive laboratory information system with role-based access control.</p>
            
            <div class="features">
                <div class="feature-item">
                    <span class="icon">✅</span>
                    <span>Patient & Result Management</span>
                </div>
                <div class="feature-item">
                    <span class="icon">✅</span>
                    <span>Inventory & Equipment Tracking</span>
                </div>
                <div class="feature-item">
                    <span class="icon">✅</span>
                    <span>Automated Certificate Generation</span>
                </div>
                <div class="feature-item">
                    <span class="icon">✅</span>
                    <span>Activity Logging & Compliance</span>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <h2>Welcome Back</h2>
            <p>Please login to access the system</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>✅ Success:</strong> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-success">
                    You have been logged out successfully.
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" name="login" class="btn-login">
                    🔐 Login to System
                </button>
            </form>
            
            <p style="margin-top: 2rem; text-align: center; font-size: 0.85rem; color: #95a5a6;">
                © 2026 Clinical Laboratory Management System<br>
                All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
