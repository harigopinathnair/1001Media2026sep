<?php
require_once __DIR__ . '/../config.php';

// Require admin login
requireAdmin();

$success = '';
$error = '';

// Handle Settings Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings_to_update = $_POST['settings'] ?? [];
    
    // File uploads directory
    $upload_dir = __DIR__ . '/../assets/uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Process Logo file upload
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['logo_file']['tmp_name'];
        $file_name = 'logo_' . time() . '_' . basename($_FILES['logo_file']['name']);
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $settings_to_update['logo_url'] = BASE_URL . 'assets/uploads/' . $file_name;
        }
    }
    
    // Process Favicon file upload
    if (isset($_FILES['favicon_file']) && $_FILES['favicon_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['favicon_file']['tmp_name'];
        $file_name = 'favicon_' . time() . '_' . basename($_FILES['favicon_file']['name']);
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $settings_to_update['favicon_url'] = BASE_URL . 'assets/uploads/' . $file_name;
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE settings SET value = :value WHERE name = :name");
        
        foreach ($settings_to_update as $name => $value) {
            $stmt->execute([
                'value' => $value,
                'name' => $name
            ]);
        }
        
        $pdo->commit();
        $success = 'Settings updated successfully!';
    } catch (\PDOException $e) {
        $pdo->rollBack();
        $error = 'Failed to save settings: ' . $e->getMessage();
    }
}

// Fetch current settings values
try {
    $stmt = $pdo->query("SELECT * FROM settings");
    $current_settings = [];
    while ($row = $stmt->fetch()) {
        $current_settings[$row['name']] = $row['value'];
    }
} catch (\PDOException $e) {
    $current_settings = [];
    $error = 'Error loading settings: ' . $e->getMessage();
}

// Load header
require_once __DIR__ . '/includes/header.php';
?>

<?php if (!empty($success)): ?>
    <div class="badge badge-success" style="display: block; padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius);">
        <i class="fa-solid fa-circle-check"></i> <?php echo e($success); ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="badge badge-danger" style="display: block; padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius);">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo e($error); ?>
    </div>
<?php endif; ?>

<div class="panel">
    <div class="panel-header">
        <h2>Global Website Settings & Custom Scripts</h2>
    </div>
    <div class="panel-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <!-- Branding assets -->
            <h3 style="font-size: 1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.5rem; margin-bottom: 1.25rem; color: var(--admin-primary);"><i class="fa-solid fa-image"></i> Brand Assets</h3>
            
            <div class="admin-form-group">
                <label for="logo_url">Logo URL / Image Upload</label>
                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
                    <?php if (!empty($current_settings['logo_url'] ?? '')): ?>
                        <img src="<?php echo e($current_settings['logo_url']); ?>" alt="Logo Preview" style="max-height: 48px; border: 1px solid var(--admin-border); border-radius: 4px; padding: 4px; background-color: #f8fafc;">
                    <?php endif; ?>
                    <input type="file" id="logo_file" name="logo_file" class="admin-form-control" accept="image/*" style="flex: 1;">
                </div>
                <input type="text" id="logo_url" name="settings[logo_url]" class="admin-form-control" value="<?php echo e($current_settings['logo_url'] ?? ''); ?>" placeholder="Or enter external Logo URL link.">
            </div>

            <div class="admin-form-group" style="margin-bottom: 2.5rem;">
                <label for="favicon_url">Favicon URL / Icon Upload</label>
                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
                    <?php if (!empty($current_settings['favicon_url'] ?? '')): ?>
                        <img src="<?php echo e($current_settings['favicon_url']); ?>" alt="Favicon Preview" style="width: 32px; height: 32px; border: 1px solid var(--admin-border); border-radius: 4px; padding: 4px; background-color: #f8fafc;">
                    <?php endif; ?>
                    <input type="file" id="favicon_file" name="favicon_file" class="admin-form-control" accept="image/*,image/x-icon" style="flex: 1;">
                </div>
                <input type="text" id="favicon_url" name="settings[favicon_url]" class="admin-form-control" value="<?php echo e($current_settings['favicon_url'] ?? ''); ?>" placeholder="Or enter external Favicon URL link.">
            </div>

            <!-- Script injections -->
            <h3 style="font-size: 1.1rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.5rem; margin-bottom: 1.25rem; color: var(--admin-primary);"><i class="fa-solid fa-code"></i> Custom Scripts Integration</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin-top: -0.75rem; margin-bottom: 1.5rem;">Injected directly into page source code. Use these slots for Google Analytics, Meta Pixel, Search Console meta verification tags, custom CSS, or custom Javascript.</p>

            <div class="admin-form-group">
                <label for="header_code">Header Script Injection (inside &lt;head&gt;)</label>
                <textarea id="header_code" name="settings[header_code]" class="admin-form-control" rows="6" style="font-family: monospace; font-size: 0.85rem;" placeholder="e.g., <script async src='https://www.googletagmanager.com/gtag/js?id=UA-XXXXX'></script>..."><?php echo $current_settings['header_code'] ?? ''; ?></textarea>
            </div>

            <div class="admin-form-group">
                <label for="body_code">Body Script Injection (right after opening &lt;body&gt;)</label>
                <textarea id="body_code" name="settings[body_code]" class="admin-form-control" rows="6" style="font-family: monospace; font-size: 0.85rem;" placeholder="e.g., Google Tag Manager (noscript) tag, tracking pixels..."><?php echo $current_settings['body_code'] ?? ''; ?></textarea>
            </div>

            <div class="admin-form-group" style="margin-bottom: 2rem;">
                <label for="footer_code">Footer Script Injection (right before closing &lt;/body&gt;)</label>
                <textarea id="footer_code" name="settings[footer_code]" class="admin-form-control" rows="6" style="font-family: monospace; font-size: 0.85rem;" placeholder="e.g., Live chat scripts, custom analytics tracking event listeners..."><?php echo $current_settings['footer_code'] ?? ''; ?></textarea>
            </div>

            <button type="submit" class="btn-admin" style="padding: 0.75rem 1.5rem;">
                <i class="fa-solid fa-floppy-disk"></i> Save Settings & Scripts
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
