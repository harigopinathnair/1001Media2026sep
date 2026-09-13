<?php
require_once __DIR__ . '/../config.php';

// Require admin login
requireAdmin();

$success = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// HANDLE CSV EXPORT
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        $stmt = $pdo->query("SELECT name, email FROM subscribers ORDER BY created_at DESC");
        $subscribers = $stmt->fetchAll();

        // Clear any previous output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set Headers for CSV Download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=subscribers_export_' . date('Y-m-d') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // Output CSV Header using Pipe (|) delimiter
        fputcsv($output, ['Name', 'Email ID'], '|');

        // Output CSV Data rows
        foreach ($subscribers as $sub) {
            fputcsv($output, [$sub['name'], $sub['email']], '|');
        }

        fclose($output);
        exit;
    } catch (\PDOException $e) {
        $error = 'Failed to export subscribers: ' . $e->getMessage();
    }
}

// HANDLE DELETE
if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM subscribers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $success = 'Subscriber removed successfully!';
    } catch (\PDOException $e) {
        $error = 'Error deleting subscriber: ' . $e->getMessage();
    }
    $action = 'list';
}

// Fetch all subscribers for display
try {
    $stmt = $pdo->query("SELECT * FROM subscribers ORDER BY created_at DESC");
    $subscribers = $stmt->fetchAll();
} catch (\PDOException $e) {
    $subscribers = [];
    $error = 'Error loading subscribers: ' . $e->getMessage();
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
        <h2>Newsletter Subscribers (<?php echo count($subscribers); ?> total)</h2>
        <div style="display: flex; gap: 1rem;">
            <a href="?export=csv" class="btn-admin" style="background-color: var(--admin-success);">
                <i class="fa-solid fa-file-csv"></i> Export CSV (Name|Email)
            </a>
        </div>
    </div>
    <div class="panel-body" style="padding: 0;">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Subscription Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subscribers)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748b; padding: 2.5rem;">No subscribers found yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subscribers as $sub): ?>
                            <tr>
                                <td><?php echo $sub['id']; ?></td>
                                <td style="font-weight: 600; color: var(--admin-primary);"><?php echo e($sub['name']); ?></td>
                                <td><a href="mailto:<?php echo e($sub['email']); ?>" style="color: inherit; text-decoration: none;"><?php echo e($sub['email']); ?></a></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($sub['created_at'])); ?></td>
                                <td style="text-align: right;">
                                    <a href="?action=delete&id=<?php echo $sub['id']; ?>" class="btn-action delete" title="Delete Subscriber" onclick="return confirm('Are you sure you want to remove this subscriber?');"><i class="fa-regular fa-trash-can"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
