<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define Constants
// Calculate relative path from executing script to config.php directory
$root_dir = str_replace('\\', '/', realpath(__DIR__));
$script_path = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
$relative_path = '';

if ($script_path && strpos($script_path, $root_dir) === 0) {
    $relative_sub_path = substr($script_path, strlen($root_dir));
    $levels = substr_count(trim($relative_sub_path, '/'), '/');
    if ($levels > 0) {
        $relative_path = str_repeat('../', $levels);
    } else {
        $relative_path = './';
    }
} else {
    $relative_path = './';
}
define('SITE_NAME', '1001Media');
define('BASE_URL', $relative_path);

// Include Database connection
require_once __DIR__ . '/includes/db.php';

/**
 * Sanitize HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Simple slugify helper
 */
function slugify($text) {
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // trim
    $text = trim($text, '-');
    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

/**
 * Format date for display
 */
function formatDate($dateString) {
    return date('M d, Y', strtotime($dateString));
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}

/**
 * Get setting value by name (cached statically)
 */
function getSetting($name, $default = '') {
    global $pdo;
    static $settings = null;
    
    if ($settings === null) {
        $settings = [];
        try {
            $stmt = $pdo->query("SELECT name, value FROM settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['name']] = $row['value'];
            }
        } catch (\PDOException $e) {
            // Silently fail if table doesn't exist yet
        }
    }
    
    return $settings[$name] ?? $default;
}
?>
