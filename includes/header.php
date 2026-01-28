<?php
// Include authentication functions
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Clinical Laboratory Management System'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🏥 Clinical Lab</h2>
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
            <a href="/mis_project/logout.php" class="nav-item logout-link">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </aside>
    <div class="main-content">
