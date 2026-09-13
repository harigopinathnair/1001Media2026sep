<?php
require_once __DIR__ . '/config.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ' . BASE_URL . 'blog.php');
    exit;
}

try {
    // Fetch Post Details
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.slug = :slug AND p.status = 'published'
        LIMIT 1
    ");
    $stmt->execute(['slug' => $slug]);
    $post = $stmt->fetch();

    if (!$post) {
        // Fallback: If logged-in admin, allow viewing draft posts
        if (isAdminLoggedIn()) {
            $stmt = $pdo->prepare("
                SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.slug = :slug
                LIMIT 1
            ");
            $stmt->execute(['slug' => $slug]);
            $post = $stmt->fetch();
        }
        
        if (!$post) {
            header('HTTP/1.1 404 Not Found');
            $page_title = "Post Not Found";
            require_once __DIR__ . '/includes/header.php';
            echo '<div class="container" style="padding: 6rem 0; text-align: center;">';
            echo '<h1>404 - Article Not Found</h1>';
            echo '<p style="margin: 1rem 0 2rem 0; color: var(--text-muted);">The article you are looking for does not exist or has been removed.</p>';
            echo '<a href="' . BASE_URL . 'blog.php" class="btn">Back to Blog</a>';
            echo '</div>';
            require_once __DIR__ . '/includes/footer.php';
            exit;
        }
    }

    // Increment View Count (only if it's a published post being viewed by a normal user)
    if ($post['status'] === 'published' && !isAdminLoggedIn()) {
        $updateStmt = $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
        $updateStmt->execute(['id' => $post['id']]);
        // Update local array so views count is current on this page load
        $post['views'] += 1;
    }

    // Fetch Related Posts
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'published' AND p.category_id = :category_id AND p.id != :post_id 
        ORDER BY p.created_at DESC 
        LIMIT 3
    ");
    $stmt->execute([
        'category_id' => $post['category_id'],
        'post_id' => $post['id']
    ]);
    $related_posts = $stmt->fetchAll();

    $page_title = !empty($post['meta_title']) ? $post['meta_title'] : $post['title'];
    $meta_description = !empty($post['meta_description']) ? $post['meta_description'] : $post['summary'];
    $meta_keywords = !empty($post['meta_keywords']) ? $post['meta_keywords'] : '';
    require_once __DIR__ . '/includes/header.php';

} catch (\PDOException $e) {
    header('Location: ' . BASE_URL . 'blog.php');
    exit;
}
?>

<article style="padding-bottom: 5rem;">
    <!-- Post Header -->
    <header class="post-detail-header">
        <?php if ($post['status'] === 'draft'): ?>
            <span class="badge badge-warning" style="margin-bottom: 1rem; padding: 0.5rem 1rem;">DRAFT PREVIEW</span>
        <?php endif; ?>
        <p class="post-detail-category">
            <a href="<?php echo BASE_URL; ?>blog.php?category=<?php echo e($post['category_slug']); ?>">
                <?php echo e($post['category_name']); ?>
            </a>
        </p>
        <h1 class="post-detail-title"><?php echo e($post['title']); ?></h1>
        <div class="post-detail-meta">
            <span><i class="fa-regular fa-calendar-days"></i> Published on <?php echo formatDate($post['created_at']); ?></span>
            <span><i class="fa-regular fa-eye"></i> <?php echo e($post['views']); ?> reads</span>
            <?php if (isAdminLoggedIn()): ?>
                <span><a href="<?php echo BASE_URL; ?>admin/posts.php?action=edit&id=<?php echo $post['id']; ?>" style="color: var(--accent-dark); font-weight: 700;"><i class="fa-regular fa-pen-to-square"></i> Edit Post</a></span>
            <?php endif; ?>
        </div>
    </header>

    <!-- Post Featured Image -->
    <div class="post-detail-hero">
        <img src="<?php echo BASE_URL . e($post['image_url']); ?>" alt="<?php echo e($post['title']); ?>" onerror="this.src='https://images.unsplash.com/photo-1592417817098-8f3d6eb19675?auto=format&fit=crop&w=1200&q=80'">
    </div>

    <!-- Post Content Body -->
    <div class="post-detail-body">
        <?php 
        // Display html correctly, allowing specific markdown or tags inserted via editor
        echo $post['content']; 
        ?>
        
        <!-- Social Sharing Mock -->
        <div style="border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.5rem 0; margin-top: 4rem; display: flex; align-items: center; justify-content: space-between;">
            <span style="font-weight: 700; color: var(--primary-color);">Share this article:</span>
            <div style="display: flex; gap: 1rem; font-size: 1.2rem;">
                <a href="#" onclick="event.preventDefault(); alert('Sharing on Twitter/X...')" style="color: #1da1f2;"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#" onclick="event.preventDefault(); alert('Sharing on Facebook...')" style="color: #1877f2;"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" onclick="event.preventDefault(); alert('Sharing on Pinterest...')" style="color: #bd081c;"><i class="fa-brands fa-pinterest-p"></i></a>
                <a href="#" onclick="event.preventDefault(); alert('Copied article link to clipboard!')" style="color: var(--text-muted);"><i class="fa-solid fa-link"></i></a>
            </div>
        </div>
    </div>
</article>

<!-- Related Articles section -->
<?php if (!empty($related_posts)): ?>
    <section style="background-color: #fff; border-top: 1px solid var(--border-color); padding: 5rem 0;">
        <div class="container">
            <div class="section-title-wrap">
                <h2 class="section-title">Related Articles</h2>
            </div>
            <div class="posts-grid" style="padding: 0;">
                <?php foreach ($related_posts as $rel): ?>
                    <article class="post-card">
                        <div class="card-img-wrap">
                            <span class="category-badge"><?php echo e($rel['category_name']); ?></span>
                            <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($rel['slug']); ?>">
                                <img src="<?php echo BASE_URL . e($rel['image_url']); ?>" alt="<?php echo e($rel['title']); ?>" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80'">
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="card-meta">
                                <span><i class="fa-regular fa-calendar-days"></i> <?php echo formatDate($rel['created_at']); ?></span>
                            </div>
                            <h3 class="card-title">
                                <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($rel['slug']); ?>"><?php echo e($rel['title']); ?></a>
                            </h3>
                            <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($rel['slug']); ?>" class="read-more">Read Article <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
