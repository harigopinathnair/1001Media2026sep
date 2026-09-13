<?php
require_once __DIR__ . '/../config.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME . ' - Digital Marketing Agency & SEO Experts'; ?></title>
    <?php if (!empty($meta_description)): ?>
    <meta name="description" content="<?php echo e($meta_description); ?>">
    <?php endif; ?>
    <?php if (!empty($meta_keywords)): ?>
    <meta name="keywords" content="<?php echo e($meta_keywords); ?>">
    <?php endif; ?>
    <?php if (!empty(getSetting('favicon_url'))): ?>
    <link rel="shortcut icon" href="<?php echo e(getSetting('favicon_url')); ?>" type="image/x-icon">
    <?php endif; ?>
    <!-- Fonts and Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <?php echo getSetting('header_code'); ?>
</head>
<body>
<?php echo getSetting('body_code'); ?>

<header>
    <div class="container header-container">
        <a href="<?php echo BASE_URL; ?>" class="logo">
            <?php if (!empty(getSetting('logo_url'))): ?>
                <img src="<?php echo e(getSetting('logo_url')); ?>" alt="1001Media Logo" style="max-height: 48px; border-radius: 4px;">
            <?php else: ?>
                <div class="logo-icon" style="color: var(--primary-light); font-size: 2rem;">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <span class="logo-text">1001Media</span>
                    <span class="logo-tagline">Digital Marketing & SEO</span>
                </div>
            <?php endif; ?>
        </a>

        <button class="menu-toggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <ul class="nav-links">
            <li><a href="<?php echo BASE_URL; ?>" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo BASE_URL; ?>blog.php" class="<?php echo $current_page === 'blog.php' || $current_page === 'post.php' ? 'active' : ''; ?>">Blog</a></li>
            <li><a href="<?php echo BASE_URL; ?>contact.php" class="<?php echo $current_page === 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
            <?php if (isAdminLoggedIn()): ?>
                <li><a href="<?php echo BASE_URL; ?>admin/" class="nav-btn" style="background-color: var(--accent-color); color: var(--primary-color) !important;"><i class="fa-solid fa-gauge"></i> Admin Portal</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/logout.php" style="color: var(--admin-danger);"><i class="fa-solid fa-sign-out-alt"></i></a></li>
            <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>admin/login.php" class="nav-btn">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            menuToggle.classList.toggle('active');
            
            // Hamburger animation
            const spans = menuToggle.querySelectorAll('span');
            if (menuToggle.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    }
});
</script>
