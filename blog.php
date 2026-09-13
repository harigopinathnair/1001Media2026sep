<?php
$page_title = "Articles & Blog";
require_once __DIR__ . '/includes/header.php';

// Inputs & Default Initialization
$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';
$categories = [];
$posts = [];
$heading = "Articles & Blog";

// Build Query
try {
    $sql = "
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'published'
    ";
    
    $params = [];
    
    if (!empty($category_filter)) {
        $sql .= " AND c.slug = :category_slug";
        $params['category_slug'] = $category_filter;
    }
    
    if (!empty($search_query)) {
        $sql .= " AND (p.title LIKE :search OR p.content LIKE :search OR p.summary LIKE :search)";
        $params['search'] = '%' . $search_query . '%';
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    // Fetch Categories for Filter Pills
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
    
    // Set Page Heading Title
    $heading = "All Articles";
    if (!empty($category_filter)) {
        // Find category name
        foreach ($categories as $cat) {
            if ($cat['slug'] === $category_filter) {
                $heading = "Articles in " . $cat['name'];
                break;
            }
        }
    } elseif (!empty($search_query)) {
        $heading = "Search Results for \"" . e($search_query) . "\"";
    }

} catch (\PDOException $e) {
    $db_error = true;
}
?>

<div class="container" style="padding: 4rem 0;">
    <!-- Page Header -->
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;"><?php echo e($heading); ?></h1>
        <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">Explore the latest tips, scientific findings, and recipes from the experts in natural healthy living.</p>
    </div>

    <!-- Filter Bar -->
    <div style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Category Pills -->
        <ul class="category-pills" style="margin: 0; justify-content: center;">
            <li class="category-pill <?php echo empty($category_filter) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>blog.php<?php echo !empty($search_query) ? '?search=' . urlencode($search_query) : ''; ?>">All Topics</a>
            </li>
            <?php foreach ($categories as $cat): ?>
                <li class="category-pill <?php echo $category_filter === $cat['slug'] ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>blog.php?category=<?php echo e($cat['slug']); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                        <?php echo e($cat['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Inline Search inside Blog Page -->
        <form action="<?php echo BASE_URL; ?>blog.php" method="GET" class="search-box" style="width: 100%; max-width: 400px;">
            <?php if (!empty($category_filter)): ?>
                <input type="hidden" name="category" value="<?php echo e($category_filter); ?>">
            <?php endif; ?>
            <input type="text" name="search" placeholder="Search within category..." value="<?php echo e($search_query); ?>" style="padding: 0.8rem 1.2rem; font-size: 0.95rem;">
            <button type="submit" style="padding: 0 1.2rem; font-size: 0.9rem;">Search</button>
        </form>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger">Error querying the database. Please try again.</div>
    <?php else: ?>
        <!-- Articles Grid -->
        <?php if (empty($posts)): ?>
            <div style="text-align: center; padding: 4rem 0;">
                <div style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <h3>No articles found</h3>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">We couldn't find any articles matching your search filters. Try modifying your search term.</p>
                <a href="<?php echo BASE_URL; ?>blog.php" class="btn" style="margin-top: 1.5rem;">Reset Filters</a>
            </div>
        <?php else: ?>
            <div class="posts-grid" style="padding: 0;">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <div class="card-img-wrap">
                            <span class="category-badge"><?php echo e($post['category_name']); ?></span>
                            <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($post['slug']); ?>">
                                <img src="<?php echo BASE_URL . e($post['image_url']); ?>" alt="<?php echo e($post['title']); ?>" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80'">
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="card-meta">
                                <span><i class="fa-regular fa-calendar-days"></i> <?php echo formatDate($post['created_at']); ?></span>
                                <span><i class="fa-regular fa-eye"></i> <?php echo e($post['views']); ?> reads</span>
                            </div>
                            <h3 class="card-title">
                                <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($post['slug']); ?>"><?php echo e($post['title']); ?></a>
                            </h3>
                            <p class="card-excerpt"><?php echo e($post['summary']); ?></p>
                            <a href="<?php echo BASE_URL; ?>post.php?slug=<?php echo e($post['slug']); ?>" class="read-more">Read Article <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
