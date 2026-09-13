<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$current_admin_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | 1001Media</title>
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body>

    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-logo">
            <h2>1001Media</h2>
            <span>Admin Dashboard</span>
        </div>
        <ul class="admin-nav">
            <li class="admin-nav-item <?php echo $current_admin_page === 'index.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/index.php">
                    <i class="fa-solid fa-gauge"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $current_admin_page === 'posts.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/posts.php">
                    <i class="fa-solid fa-newspaper"></i>
                    <span>Manage Posts</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $current_admin_page === 'categories.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/categories.php">
                    <i class="fa-solid fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $current_admin_page === 'messages.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/messages.php">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Messages</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $current_admin_page === 'subscribers.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/subscribers.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Subscribers</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $current_admin_page === 'settings.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/settings.php">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="admin-nav-item" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem;">
                <a href="<?php echo BASE_URL; ?>" target="_blank">
                    <i class="fa-solid fa-globe"></i>
                    <span>Visit Live Site</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?php echo BASE_URL; ?>admin/logout.php" style="color: #fda4af;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Log Out</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content wrapper -->
    <div class="admin-main">
        <header class="admin-header">
            <h1>
                <?php
                if ($current_admin_page === 'index.php') echo 'Dashboard Overview';
                elseif ($current_admin_page === 'posts.php') echo 'Post Management';
                elseif ($current_admin_page === 'categories.php') echo 'Category Management';
                elseif ($current_admin_page === 'messages.php') echo 'Received Messages';
                elseif ($current_admin_page === 'subscribers.php') echo 'Newsletter Subscribers';
                elseif ($current_admin_page === 'settings.php') echo 'Settings & Scripts';
                ?>
            </h1>
            <div class="admin-user-profile">
                <div class="admin-user-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                </div>
                <span>Welcome, <?php echo e($_SESSION['admin_username'] ?? 'Admin'); ?></span>
            </div>
        </header>

        <div class="admin-container">
