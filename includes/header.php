<?php
// Include authentication functions
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Clinical Laboratory Management System'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Kit -->
    <script src="https://kit.fontawesome.com/e4a261e8da.js" crossorigin="anonymous"></script>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fa-solid fa-hospital"></i> Clinical Lab</h2>
        </div>
        <nav class="sidebar-nav">
            <?php
            // Get accessible menu items based on user permissions
            $menu_items = get_accessible_menu();
            foreach ($menu_items as $item):
            ?>
            <a href="<?php echo htmlspecialchars($item['url']); ?>" class="nav-item">
                <span class="nav-icon"><?php echo $item['icon']; ?></span>
                <span class="nav-text"><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile-dropdown">
                <button class="user-profile-btn" onclick="toggleUserMenu()">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($_SESSION['role_name'] ?? 'Employee'); ?></span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </button>
                
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <a href="account_settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Account Settings</span>
                        <span class="shortcut">⌘S</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="../logout.php" class="dropdown-item logout">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Log out</span>
                        <span class="shortcut">⇧⌘Q</span>
                    </a>
                </div>
            </div>
        </div>
    </aside>
    <div class="main-content">
        
    <script>
    // Toggle user dropdown menu
    function toggleUserMenu() {
        const dropdown = document.getElementById('userDropdownMenu');
        const parent = dropdown.parentElement;
        dropdown.classList.toggle('show');
        parent.classList.toggle('active');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userDropdown = document.querySelector('.user-profile-dropdown');
        const dropdownMenu = document.getElementById('userDropdownMenu');
        
        if (userDropdown && !userDropdown.contains(event.target)) {
            dropdownMenu.classList.remove('show');
            userDropdown.classList.remove('active');
        }
    });
    </script>
