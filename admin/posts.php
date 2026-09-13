<?php
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$success = '';
$error = '';

// Make sure upload directory exists
$upload_dir = __DIR__ . '/../assets/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Fetch categories for form selects
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error = 'Error loading categories.';
}

// HANDLE FORM SUBMISSIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $title = trim($_POST['title'] ?? '');
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] === 'published' ? 'published' : 'draft';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords = trim($_POST['meta_keywords'] ?? '');
        
        // Generate slug from title
        $slug = slugify($title);

        if (empty($title) || empty($content) || empty($summary)) {
            $error = 'Please fill in Title, Summary, and Content.';
        } else {
            // Handle File Upload
            $image_url = $_POST['existing_image'] ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80'; // default if none
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['image']['tmp_name'];
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                
                // Allow only images
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $file_info = getimagesize($file_tmp);
                
                if ($file_info && in_array($file_info['mime'], $allowed_types)) {
                    if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                        $image_url = 'assets/uploads/' . $file_name;
                    } else {
                        $error = 'Failed to upload image. Using default or existing image.';
                    }
                } else {
                    $error = 'Invalid image file format. Allowed formats: JPG, PNG, WEBP, GIF.';
                }
            }

            if (empty($error)) {
                try {
                    if ($action === 'add') {
                        // Check if slug exists, append random if it does
                        $chk = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE slug = :slug");
                        $chk->execute(['slug' => $slug]);
                        if ($chk->fetchColumn() > 0) {
                            $slug .= '-' . rand(100, 999);
                        }

                        $stmt = $pdo->prepare("
                            INSERT INTO posts (category_id, title, slug, content, summary, image_url, status, is_featured, meta_title, meta_description, meta_keywords) 
                            VALUES (:category_id, :title, :slug, :content, :summary, :image_url, :status, :is_featured, :meta_title, :meta_description, :meta_keywords)
                        ");
                        $stmt->execute([
                            'category_id' => $category_id,
                            'title' => $title,
                            'slug' => $slug,
                            'content' => $content,
                            'summary' => $summary,
                            'image_url' => $image_url,
                            'status' => $status,
                            'is_featured' => $is_featured,
                            'meta_title' => $meta_title,
                            'meta_description' => $meta_description,
                            'meta_keywords' => $meta_keywords
                        ]);
                        $success = 'Post created successfully!';
                        $action = 'list'; // redirect back
                    } else {
                        // Edit action
                        // Check if slug exists on another post
                        $chk = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE slug = :slug AND id != :id");
                        $chk->execute(['slug' => $slug, 'id' => $id]);
                        if ($chk->fetchColumn() > 0) {
                            $slug .= '-' . rand(100, 999);
                        }

                        $stmt = $pdo->prepare("
                            UPDATE posts 
                            SET category_id = :category_id, title = :title, slug = :slug, content = :content, 
                                summary = :summary, image_url = :image_url, status = :status, is_featured = :is_featured,
                                meta_title = :meta_title, meta_description = :meta_description, meta_keywords = :meta_keywords
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'category_id' => $category_id,
                            'title' => $title,
                            'slug' => $slug,
                            'content' => $content,
                            'summary' => $summary,
                            'image_url' => $image_url,
                            'status' => $status,
                            'is_featured' => $is_featured,
                            'meta_title' => $meta_title,
                            'meta_description' => $meta_description,
                            'meta_keywords' => $meta_keywords,
                            'id' => $id
                        ]);
                        $success = 'Post updated successfully!';
                        $action = 'list';
                    }
                } catch (\PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

// HANDLE DELETIONS
if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $success = 'Post deleted successfully!';
    } catch (\PDOException $e) {
        $error = 'Error deleting post: ' . $e->getMessage();
    }
    $action = 'list';
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

<!-- LIST ACTION -->
<?php if ($action === 'list'): ?>
    <?php
    try {
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name 
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC
        ");
        $posts = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $posts = [];
    }
    ?>
    
    <div class="panel">
        <div class="panel-header">
            <h2>All Articles</h2>
            <a href="?action=add" class="btn-admin">
                <i class="fa-solid fa-plus"></i> Write New Post
            </a>
        </div>
        <div class="panel-body" style="padding: 0;">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Published Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #64748b; padding: 2rem;">No posts found. Write your first article today!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo BASE_URL . e($post['image_url']); ?>" alt="Thumbnail" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=60&q=80'">
                                    </td>
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
                                    <td><?php echo formatDate($post['created_at']); ?></td>
                                    <td style="text-align: right;">
                                        <a href="?action=edit&id=<?php echo $post['id']; ?>" class="btn-action edit" title="Edit Post"><i class="fa-regular fa-pen-to-square"></i></a>
                                        <a href="?action=delete&id=<?php echo $post['id']; ?>" class="btn-action delete" title="Delete Post" onclick="return confirm('Are you sure you want to delete this post?');"><i class="fa-regular fa-trash-can"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- ADD / EDIT ACTIONS -->
<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <?php
    $post = [
        'title' => '',
        'category_id' => '',
        'summary' => '',
        'content' => '',
        'status' => 'draft',
        'is_featured' => 0,
        'image_url' => '',
        'meta_title' => '',
        'meta_description' => '',
        'meta_keywords' => ''
    ];

    if ($action === 'edit' && $id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $fetched_post = $stmt->fetch();
            if ($fetched_post) {
                $post = $fetched_post;
            }
        } catch (\PDOException $e) {
            $error = 'Error fetching post details.';
        }
    }
    ?>

    <!-- CKEditor CDN -->
    <script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>

    <div class="panel">
        <div class="panel-header">
            <h2><?php echo $action === 'add' ? 'Create New Post' : 'Edit Post: ' . e($post['title']); ?></h2>
            <a href="?action=list" class="btn-admin" style="background-color: #64748b;">
                <i class="fa-solid fa-arrow-left"></i> Back to List
            </a>
        </div>
        <div class="panel-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="existing_image" value="<?php echo e($post['image_url']); ?>">
                
                <div class="admin-form-group">
                    <label for="title">Article Title *</label>
                    <input type="text" id="title" name="title" class="admin-form-control" value="<?php echo e($post['title']); ?>" required placeholder="e.g., Moringa Tea Recipe for High Energy">
                </div>

                <div class="grid-2">
                    <div class="admin-form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label for="category_id" style="margin-bottom: 0;">Category</label>
                            <a href="categories.php" target="_blank" style="font-size: 0.78rem; font-weight: 600; color: var(--admin-primary); text-decoration: none;">
                                <i class="fa-regular fa-pen-to-square"></i> Edit Categories
                            </a>
                        </div>
                        <select id="category_id" name="category_id" class="admin-form-control">
                            <option value="">-- Uncategorized --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $post['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label for="status">Publish Status</label>
                        <select id="status" name="status" class="admin-form-control">
                            <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="image">Featured Image</label>
                    <?php if (!empty($post['image_url'])): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <span style="font-size: 0.8rem; color: #64748b;">Current image:</span><br>
                            <img src="<?php echo BASE_URL . e($post['image_url']); ?>" alt="Current Image" style="width: 150px; border-radius: 4px; border: 1px solid var(--admin-border);">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" class="admin-form-control" accept="image/*">
                    <span style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: block;">Select a JPG, PNG, WEBP, or GIF image to represent this article on the grids.</span>
                </div>

                <div class="admin-form-group">
                    <label for="summary">Brief Summary / Excerpt *</label>
                    <textarea id="summary" name="summary" class="admin-form-control" rows="3" required placeholder="A short 1-2 sentence description shown in article listings..."><?php echo e($post['summary']); ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="content">Full Content Body *</label>
                    <textarea id="content" name="content" required><?php echo $post['content']; ?></textarea>
                    <script>
                        CKEDITOR.replace('content', {
                            height: 400
                        });
                    </script>
                </div>

                <div class="admin-form-group" style="margin-top: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo $post['is_featured'] == 1 ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="is_featured" style="margin-bottom: 0; cursor: pointer; font-weight: 600;">Feature this post on the Homepage Hero/Top banner</label>
                </div>

                <div style="margin-top: 2rem; border-top: 1px solid var(--admin-border); padding-top: 1.5rem;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--admin-primary);"><i class="fa-solid fa-search"></i> SEO Meta Information (Optional)</h3>
                    
                    <div class="admin-form-group">
                        <label for="meta_title">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title" class="admin-form-control" value="<?php echo e($post['meta_title'] ?? ''); ?>" placeholder="Custom browser tab title. Defaults to Article Title if empty.">
                    </div>

                    <div class="admin-form-group">
                        <label for="meta_description">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="admin-form-control" rows="2" placeholder="Search engine snippet description. Defaults to Summary if empty."><?php echo e($post['meta_description'] ?? ''); ?></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label for="meta_keywords">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" class="admin-form-control" value="<?php echo e($post['meta_keywords'] ?? ''); ?>" placeholder="e.g., moringa, superfood, health benefits (comma separated)">
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn-admin">
                        <i class="fa-solid fa-floppy-disk"></i> Save Article
                    </button>
                    <a href="?action=list" class="btn-admin" style="background-color: #64748b;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
