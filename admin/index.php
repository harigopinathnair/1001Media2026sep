<?php
require_once __DIR__ . '/includes/header.php';

try {
    // Get Stats
    $total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $total_messages = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    $unread_messages = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

    // Get Recent Posts
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ");
    $recent_posts = $stmt->fetchAll();

    // Get Unread Messages
    $stmt = $pdo->query("
        SELECT * FROM contact_messages 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_messages = $stmt->fetchAll();

} catch (\PDOException $e) {
    echo '<div class="badge badge-danger">Database Error: ' . e($e->getMessage()) . '</div>';
    $recent_posts = [];
    $recent_messages = [];
}
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Posts</h3>
            <p><?php echo $total_posts; ?></p>
        </div>
        <div class="stat-icon posts">
            <i class="fa-solid fa-newspaper"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Categories</h3>
            <p><?php echo $total_categories; ?></p>
        </div>
        <div class="stat-icon categories">
            <i class="fa-solid fa-tags"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Messages</h3>
            <p><?php echo $total_messages; ?> <span style="font-size: 0.8rem; font-weight: 500; color: var(--admin-success);">(<?php echo $unread_messages; ?> unread)</span></p>
        </div>
        <div class="stat-icon messages">
            <i class="fa-solid fa-envelope"></i>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 2rem;">
    <!-- Recent Posts Panel -->
    <div class="panel">
        <div class="panel-header">
            <h2>Recent Blog Articles</h2>
            <a href="<?php echo BASE_URL; ?>admin/posts.php?action=add" class="btn-admin" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                <i class="fa-solid fa-plus"></i> New Post
            </a>
        </div>
        <div class="panel-body" style="padding: 0;">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_posts)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #64748b;">No posts created yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_posts as $post): ?>
                                <tr>
                                    <td style="font-weight: 600;">
                                        <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($post['slug']); ?>" target="_blank" style="color: inherit; text-decoration: none;">
                                            <?php echo e($post['title']); ?>
                                        </a>
                                        <?php if ($post['is_featured']): ?>
                                            <span class="badge badge-info" style="font-size: 0.6rem; padding: 0.1rem 0.3rem; margin-left: 0.3rem;">Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($post['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $post['status'] === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo e($post['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $post['views']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Messages Panel -->
    <div class="panel">
        <div class="panel-header">
            <h2>Recent Messages</h2>
            <a href="<?php echo BASE_URL; ?>admin/messages.php" style="font-size: 0.85rem; font-weight: 700; color: var(--admin-primary); text-decoration: none;">View All Inbox</a>
        </div>
        <div class="panel-body" style="padding: 0;">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_messages)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #64748b;">No messages received.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_messages as $msg): ?>
                                <tr style="<?php echo $msg['is_read'] == 0 ? 'font-weight: 700; background-color: rgba(16, 185, 129, 0.02);' : ''; ?>">
                                    <td><?php echo e($msg['name']); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>admin/messages.php?id=<?php echo $msg['id']; ?>" style="color: inherit; text-decoration: none;">
                                            <?php echo e(!empty($msg['subject']) ? $msg['subject'] : '(No Subject)'); ?>
                                        </a>
                                    </td>
                                    <td style="font-size: 0.8rem; color: #64748b;"><?php echo date('M d', strtotime($msg['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
