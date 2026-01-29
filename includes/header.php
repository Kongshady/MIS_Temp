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
            <a href="/Proto/MIS_Temp/logout.php" class="nav-item logout-link">
                <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </aside>
    <div class="main-content">
