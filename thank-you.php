<?php
$page_title = "Thank You";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 8rem 0; text-align: center; font-family: var(--font-body); max-width: 600px; margin: 0 auto;">
    <div style="background-color: var(--bg-white); padding: 4rem 3rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-lg);">
        <div style="color: #10b981; font-size: 5rem; margin-bottom: 1.5rem; line-height: 1; display: inline-block;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary-color); font-weight: 800;">Thank You!</h1>
        <p style="font-size: 1.1rem; line-height: 1.6; color: var(--text-muted); margin-bottom: 2.5rem;">Your request has been successfully captured. One of our digital marketing strategists will be in touch with you shortly.</p>
        
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="<?php echo BASE_URL; ?>" class="btn" style="background-color: var(--primary-light); color: #fff; padding: 0.9rem 2rem; border-radius: var(--radius-sm); font-weight: 700; transition: var(--transition); display: inline-block; font-size: 0.95rem;">Back to Home Page</a>
            <a href="<?php echo BASE_URL; ?>blog.php" class="btn" style="background-color: var(--bg-light); color: var(--text-dark); padding: 0.9rem 2rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-weight: 700; transition: var(--transition); display: inline-block; font-size: 0.95rem;">Read Our Blog</a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
