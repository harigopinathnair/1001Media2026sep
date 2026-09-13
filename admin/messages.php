<?php
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// MARK AS READ/UNREAD AND FETCH MESSAGE DETAILS IF SINGLE VIEWED
$viewed_message = null;
if ($id > 0 && $action === 'view') {
    try {
        // Mark as read
        $updateStmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :id");
        $updateStmt->execute(['id' => $id]);

        // Fetch single message
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $viewed_message = $stmt->fetch();
        
        if (!$viewed_message) {
            $error = 'Message not found.';
            $action = 'list';
        }
    } catch (\PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
        $action = 'list';
    }
}

// DELETE MESSAGE ACTION
if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $success = 'Message deleted successfully!';
    } catch (\PDOException $e) {
        $error = 'Error deleting message: ' . $e->getMessage();
    }
    $action = 'list';
}

// Fetch all messages
try {
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll();
} catch (\PDOException $e) {
    $messages = [];
    $error = 'Error loading messages: ' . $e->getMessage();
}
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

<div style="display: grid; grid-template-columns: <?php echo $action === 'view' ? '1fr 1fr' : '1fr'; ?>; gap: 2rem; align-items: start;">
    
    <!-- Messages Inbox Panel -->
    <div class="panel">
        <div class="panel-header">
            <h2>Inbox Messages</h2>
        </div>
        <div class="panel-body" style="padding: 0;">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Received At</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #64748b; padding: 2.5rem;">Your inbox is empty. No messages received yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <tr style="<?php echo $msg['is_read'] == 0 ? 'font-weight: 700; background-color: rgba(16, 185, 129, 0.02);' : ''; ?>">
                                    <td><?php echo e($msg['name']); ?></td>
                                    <td><?php echo e($msg['email']); ?></td>
                                    <td>
                                        <a href="?action=view&id=<?php echo $msg['id']; ?>" style="color: inherit; text-decoration: none;">
                                            <?php echo e(!empty($msg['subject']) ? $msg['subject'] : '(No Subject)'); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $msg['is_read'] == 1 ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $msg['is_read'] == 1 ? 'Read' : 'Unread'; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <a href="?action=view&id=<?php echo $msg['id']; ?>" class="btn-action view" title="View Message"><i class="fa-regular fa-eye"></i></a>
                                        <a href="?action=delete&id=<?php echo $msg['id']; ?>" class="btn-action delete" title="Delete Message" onclick="return confirm('Are you sure you want to delete this message?');"><i class="fa-regular fa-trash-can"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Message Detail View (only shown when action=view) -->
    <?php if ($action === 'view' && $viewed_message): ?>
        <div class="panel">
            <div class="panel-header">
                <h2>Message Details</h2>
                <a href="?action=list" class="btn-admin" style="background-color: #64748b; padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                    Close Detail
                </a>
            </div>
            <div class="panel-body">
                <div class="message-detail-wrap">
                    <div class="message-detail-meta">
                        <div>
                            <strong>From:</strong> <?php echo e($viewed_message['name']); ?> (<a href="mailto:<?php echo e($viewed_message['email']); ?>" style="color: var(--admin-primary); font-weight: 600;"><?php echo e($viewed_message['email']); ?></a>)<br>
                            <strong>Subject:</strong> <?php echo e(!empty($viewed_message['subject']) ? $viewed_message['subject'] : '(No Subject)'); ?>
                        </div>
                        <div style="text-align: right; color: #64748b; font-size: 0.85rem;">
                            Received:<br>
                            <?php echo date('M d, Y h:i A', strtotime($viewed_message['created_at'])); ?>
                        </div>
                    </div>
                    <div class="message-detail-body">
                        <?php echo e($viewed_message['message']); ?>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <a href="mailto:<?php echo e($viewed_message['email']); ?>?subject=Re: <?php echo urlencode($viewed_message['subject']); ?>" class="btn-admin">
                        <i class="fa-solid fa-reply"></i> Reply by Email
                    </a>
                    <a href="?action=delete&id=<?php echo $viewed_message['id']; ?>" class="btn-admin btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">
                        <i class="fa-regular fa-trash-can"></i> Delete Message
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
