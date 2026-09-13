<?php
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

// Flash messages from query parameters
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') {
        $success = 'Category created successfully!';
    } elseif ($_GET['msg'] === 'updated') {
        $success = 'Category updated successfully!';
    } elseif ($_GET['msg'] === 'deleted') {
        $success = 'Category deleted successfully! Associated posts are now uncategorized.';
    }
}

// ADD CATEGORY SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $custom_slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $slug = !empty($custom_slug) ? slugify($custom_slug) : slugify($name);

    if (empty($name)) {
        $error = 'Category name cannot be empty.';
    } else {
        try {
            // Check if name or slug already exists
            $chk = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = :slug OR name = :name");
            $chk->execute(['slug' => $slug, 'name' => $name]);
            if ($chk->fetchColumn() > 0) {
                $error = 'A category with this name or slug already exists.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO categories (name, slug, description) 
                    VALUES (:name, :slug, :description)
                ");
                $stmt->execute([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description
                ]);
                header('Location: ' . BASE_URL . 'admin/categories.php?msg=added');
                exit;
            }
        } catch (\PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// EDIT CATEGORY SUBMISSION (Handles both Side-Panel and Modal Edit forms)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['edit_category']) || isset($_POST['modal_edit_category'])) && $id > 0) {
    $name = trim($_POST['name'] ?? '');
    $custom_slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $slug = !empty($custom_slug) ? slugify($custom_slug) : slugify($name);

    if (empty($name)) {
        $error = 'Category name cannot be empty.';
    } else {
        try {
            // Check if name or slug already exists for another category
            $chk = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE (slug = :slug OR name = :name) AND id != :id");
            $chk->execute(['slug' => $slug, 'name' => $name, 'id' => $id]);
            if ($chk->fetchColumn() > 0) {
                $error = 'Another category with this name or slug already exists.';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE categories 
                    SET name = :name, slug = :slug, description = :description 
                    WHERE id = :id
                ");
                $stmt->execute([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'id' => $id
                ]);
                header('Location: ' . BASE_URL . 'admin/categories.php?msg=updated');
                exit;
            }
        } catch (\PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// DELETE CATEGORY ACTION
if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header('Location: ' . BASE_URL . 'admin/categories.php?msg=deleted');
        exit;
    } catch (\PDOException $e) {
        $error = 'Error deleting category: ' . $e->getMessage();
        $action = 'list';
    }
}

// Load category if we are editing in side-panel
$edit_category = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $edit_category = $stmt->fetch();
        if (!$edit_category) {
            $error = 'Category not found.';
            $action = 'list';
        }
    } catch (\PDOException $e) {
        $error = 'Error loading category: ' . $e->getMessage();
    }
}

// Fetch all categories with post counts
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as post_count 
        FROM categories c 
        LEFT JOIN posts p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (\PDOException $e) {
    $categories = [];
    $error = 'Error loading categories: ' . $e->getMessage();
}
?>

<?php if (!empty($success)): ?>
    <div class="badge badge-success" style="display: block; padding: 1rem 1.25rem; margin-bottom: 1.5rem; border-radius: var(--radius); font-size: 0.95rem;">
        <i class="fa-solid fa-circle-check"></i> <?php echo e($success); ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="badge badge-danger" style="display: block; padding: 1rem 1.25rem; margin-bottom: 1.5rem; border-radius: var(--radius); font-size: 0.95rem;">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo e($error); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 2rem; align-items: start;">
    <!-- Categories List Panel -->
    <div class="panel">
        <div class="panel-header">
            <div>
                <h2>Categories Directory</h2>
                <span style="font-size: 0.8rem; color: #64748b;">Manage blog topics, edit names, slugs, and descriptions</span>
            </div>
            <span class="badge badge-info" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                <?php echo count($categories); ?> Categories
            </span>
        </div>
        <div class="panel-body" style="padding: 0;">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Articles</th>
                            <th style="text-align: right; min-width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b; padding: 2.5rem;">No categories found. Create your first category using the form.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr class="<?php echo ($edit_category && $edit_category['id'] == $cat['id']) ? 'editing-row' : ''; ?>" id="cat-row-<?php echo $cat['id']; ?>">
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>blog.php?category=<?php echo e($cat['slug']); ?>" target="_blank" style="font-weight: 600; color: var(--admin-primary); text-decoration: none;" title="View public articles in this category">
                                            <?php echo e($cat['name']); ?>
                                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem; color: #94a3b8; margin-left: 3px;"></i>
                                        </a>
                                        <?php if ($edit_category && $edit_category['id'] == $cat['id']): ?>
                                            <span class="badge badge-warning" style="font-size: 0.65rem; padding: 0.15rem 0.4rem; margin-left: 0.4rem;">Editing</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-family: monospace; font-size: 0.82rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569;">
                                            <?php echo e($cat['slug']); ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #64748b; max-width: 220px;">
                                        <?php echo !empty($cat['description']) ? e($cat['description']) : '<em style="color:#94a3b8;">No description</em>'; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info" style="font-size: 0.75rem;">
                                            <?php echo (int)$cat['post_count']; ?> <?php echo $cat['post_count'] == 1 ? 'post' : 'posts'; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <!-- Quick Edit Modal Button -->
                                        <button type="button" 
                                                class="btn-action quick-edit" 
                                                title="Quick Edit Category Name & Details"
                                                onclick="openQuickEdit(<?php echo (int)$cat['id']; ?>, '<?php echo addslashes(e($cat['name'])); ?>', '<?php echo addslashes(e($cat['slug'])); ?>', '<?php echo addslashes(e($cat['description'] ?? '')); ?>')"
                                                style="margin-right: 0.25rem; border: none; cursor: pointer;">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <!-- Side Panel Edit Link -->
                                        <a href="?action=edit&id=<?php echo $cat['id']; ?>" class="btn-action edit" title="Edit in Side Panel" style="margin-right: 0.25rem;">
                                            <i class="fa-solid fa-sliders"></i>
                                        </a>

                                        <!-- Delete Link -->
                                        <a href="?action=delete&id=<?php echo $cat['id']; ?>" class="btn-action delete" title="Delete Category" onclick="return confirm('Are you sure you want to delete category \'<?php echo addslashes(e($cat['name'])); ?>\'? Associated posts will become uncategorized.');">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create / Edit Category Side Panel -->
    <div class="panel" style="<?php echo $edit_category ? 'border-color: var(--admin-primary); box-shadow: 0 4px 14px rgba(26, 67, 49, 0.12);' : ''; ?>">
        <div class="panel-header" style="<?php echo $edit_category ? 'background-color: #ecfdf5;' : ''; ?>">
            <div>
                <h2><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h2>
                <?php if ($edit_category): ?>
                    <span style="font-size: 0.8rem; color: var(--admin-primary); font-weight: 500;">Currently editing: <strong><?php echo e($edit_category['name']); ?></strong></span>
                <?php endif; ?>
            </div>
            <?php if ($edit_category): ?>
                <a href="categories.php" class="btn-admin" style="font-size: 0.8rem; padding: 4px 10px; background-color: #64748b; text-decoration: none;">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </a>
            <?php endif; ?>
        </div>
        <div class="panel-body">
            <form action="categories.php" method="POST">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$edit_category['id']; ?>">
                <?php endif; ?>

                <div class="admin-form-group">
                    <label for="side_name">Category Name *</label>
                    <input type="text" 
                           id="side_name" 
                           name="name" 
                           class="admin-form-control" 
                           placeholder="e.g., Healthy Smoothies & Juices" 
                           value="<?php echo e($edit_category['name'] ?? ''); ?>" 
                           oninput="updateSlugPreview(this.value, 'side_slug_preview')"
                           required>
                    <span class="slug-preview" id="side_slug_preview">
                        Slug: <?php echo !empty($edit_category['slug']) ? e($edit_category['slug']) : '<em>(auto-generated)</em>'; ?>
                    </span>
                </div>

                <div class="admin-form-group">
                    <label for="side_slug">Custom Slug (Optional)</label>
                    <input type="text" 
                           id="side_slug" 
                           name="slug" 
                           class="admin-form-control" 
                           placeholder="Leave blank to auto-generate from name" 
                           value="<?php echo e($edit_category['slug'] ?? ''); ?>">
                </div>

                <div class="admin-form-group">
                    <label for="side_description">Description</label>
                    <textarea id="side_description" 
                              name="description" 
                              class="admin-form-control" 
                              rows="4" 
                              placeholder="Brief description about the topics grouped under this category..."><?php echo e($edit_category['description'] ?? ''); ?></textarea>
                </div>

                <?php if ($edit_category): ?>
                    <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                        <button type="submit" name="edit_category" class="btn-admin" style="flex: 1; justify-content: center;">
                            <i class="fa-solid fa-floppy-disk"></i> Update Category
                        </button>
                        <a href="categories.php" class="btn-admin" style="background-color: #64748b; text-decoration: none; justify-content: center;">
                            Cancel
                        </a>
                    </div>
                <?php else: ?>
                    <button type="submit" name="add_category" class="btn-admin" style="width: 100%; justify-content: center; margin-top: 1.5rem;">
                        <i class="fa-solid fa-plus"></i> Add Category
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- Quick Edit Modal -->
<div class="admin-modal-backdrop" id="quickEditModal" onclick="handleBackdropClick(event)">
    <div class="admin-modal" role="dialog" aria-modal="true">
        <div class="admin-modal-header">
            <h3><i class="fa-regular fa-pen-to-square"></i> Quick Edit Category</h3>
            <button type="button" class="admin-modal-close" onclick="closeQuickEdit()" title="Close">&times;</button>
        </div>
        <form action="categories.php" method="POST" id="quickEditForm">
            <input type="hidden" name="id" id="modal_cat_id" value="0">
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label for="modal_cat_name">Category Name *</label>
                    <input type="text" 
                           id="modal_cat_name" 
                           name="name" 
                           class="admin-form-control" 
                           required 
                           placeholder="e.g., SEO & Growth Marketing"
                           oninput="updateSlugPreview(this.value, 'modal_slug_preview')">
                    <span class="slug-preview" id="modal_slug_preview">Slug: <em>(auto-generated)</em></span>
                </div>

                <div class="admin-form-group">
                    <label for="modal_cat_slug">Category Slug (Optional)</label>
                    <input type="text" 
                           id="modal_cat_slug" 
                           name="slug" 
                           class="admin-form-control" 
                           placeholder="Leave empty to auto-generate">
                </div>

                <div class="admin-form-group" style="margin-bottom: 0;">
                    <label for="modal_cat_description">Description</label>
                    <textarea id="modal_cat_description" 
                              name="description" 
                              class="admin-form-control" 
                              rows="3" 
                              placeholder="Brief summary of articles in this category..."></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin" style="background-color: #64748b;" onclick="closeQuickEdit()">Cancel</button>
                <button type="submit" name="modal_edit_category" class="btn-admin">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function slugifyJs(text) {
    return text.toString().toLowerCase().trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function updateSlugPreview(val, previewId) {
    const previewEl = document.getElementById(previewId);
    if (!previewEl) return;
    const slug = slugifyJs(val);
    previewEl.innerHTML = 'Slug: ' + (slug ? '<code>' + slug + '</code>' : '<em>(auto-generated)</em>');
}

function openQuickEdit(id, name, slug, description) {
    document.getElementById('modal_cat_id').value = id;
    document.getElementById('modal_cat_name').value = name;
    document.getElementById('modal_cat_slug').value = slug;
    document.getElementById('modal_cat_description').value = description;
    
    updateSlugPreview(name, 'modal_slug_preview');
    
    const modal = document.getElementById('quickEditModal');
    modal.classList.add('show');
    document.getElementById('modal_cat_name').focus();
}

function closeQuickEdit() {
    const modal = document.getElementById('quickEditModal');
    modal.classList.remove('show');
}

function handleBackdropClick(e) {
    if (e.target.id === 'quickEditModal') {
        closeQuickEdit();
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQuickEdit();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
