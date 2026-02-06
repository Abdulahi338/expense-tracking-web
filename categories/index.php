<?php
/**
 * Categories Index
 * Expense Tracking System
 */

// Include configuration and require login
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

requireLogin();

$userId = getCurrentUserId();
$pageTitle = 'Categories';
$error = '';
$success = '';

// Process add/edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? '';
        $color = $_POST['color'] ?? '#3788d8';
        $icon = $_POST['icon'] ?? 'bi-tag';
        $categoryId = $_POST['category_id'] ?? 0;
        
        if (empty($name)) {
            $error = 'Category name is required.';
        } elseif (!in_array($type, ['income', 'expense'])) {
            $error = 'Invalid category type.';
        } else {
            if ($categoryId > 0) {
                // Update existing category
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, type = ?, color = ?, icon = ? WHERE id = ? AND (user_id = ? OR is_default = 1)");
                $stmt->execute([$name, $type, $color, $icon, $categoryId, $userId]);
                setFlash('success', 'Category updated successfully!');
            } else {
                // Add new category
                $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, color, icon) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $name, $type, $color, $icon]);
                setFlash('success', 'Category added successfully!');
            }
            header('Location: index.php');
            exit;
        }
    }
}

// Delete category
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    $stmt->execute([$deleteId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        setFlash('success', 'Category deleted successfully!');
    } else {
        setFlash('error', 'Cannot delete this category.');
    }
    header('Location: index.php');
    exit;
}

// Get categories
$stmt = $pdo->prepare("SELECT c.*, COUNT(t.id) as transaction_count 
                        FROM categories c 
                        LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ? 
                        WHERE c.user_id IS NULL OR c.user_id = ? 
                        GROUP BY c.id 
                        ORDER BY c.type, c.name");
$stmt->execute([$userId, $userId]);
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include_once __DIR__ . '/../components/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0"><i class="bi bi-tags me-2"></i>Categories</h1>
                        <p class="text-muted mb-0">Manage your transaction categories</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <i class="bi bi-plus-circle me-1"></i>Add Category
                    </button>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <?php foreach (['income', 'expense'] as $type): ?>
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-<?php echo $type === 'income' ? 'arrow-down-circle text-success' : 'arrow-up-circle text-danger'; ?> me-2"></i>
                                    <?php echo ucfirst($type); ?> Categories
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php 
                                    $typeCategories = array_filter($categories, fn($c) => $c['type'] === $type);
                                    if (empty($typeCategories)): 
                                    ?>
                                        <div class="list-group-item text-center py-4 text-muted">
                                            No <?php echo $type; ?> categories yet.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($typeCategories as $cat): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="category-icon me-3" style="background-color: <?php echo $cat['color']; ?>20; color: <?php echo $cat['color']; ?>;">
                                                            <i class="<?php echo $cat['icon']; ?>"></i>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                                            <?php if ($cat['is_default']): ?>
                                                                <span class="badge bg-secondary ms-1">Default</span>
                                                            <?php endif; ?>
                                                            <small class="text-muted d-block"><?php echo $cat['transaction_count']; ?> transactions</small>
                                                        </div>
                                                    </div>
                                                    <?php if ($cat['user_id'] == $userId): ?>
                                                        <div>
                                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                                    onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>', '<?php echo $cat['type']; ?>', '<?php echo $cat['color']; ?>', '<?php echo $cat['icon']; ?>')">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?');">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    
    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="categoryModalTitle"><i class="bi bi-plus-circle me-2"></i>Add Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="categoryForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="category_id" id="category_id" value="0">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="e.g., Groceries" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="catTypeIncome" value="income">
                                <label class="btn btn-outline-success" for="catTypeIncome">Income</label>
                                <input type="radio" class="btn-check" name="type" id="catTypeExpense" value="expense">
                                <label class="btn btn-outline-danger" for="catTypeExpense">Expense</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="#3788d8">
                        </div>
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon Class (Bootstrap Icons)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag" id="iconPreview"></i></span>
                                <input type="text" class="form-control" id="icon" name="icon" placeholder="bi-tag" value="bi-tag">
                            </div>
                            <div class="form-text">Enter Bootstrap Icons class name (e.g., bi-cart, bi-house, bi-car)</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Include Footer -->
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
    
    <script>
    function editCategory(id, name, type, color, icon) {
        document.getElementById('categoryModalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Category';
        document.getElementById('category_id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('color').value = color;
        document.getElementById('icon').value = icon;
        document.getElementById('iconPreview').className = icon;
        
        if (type === 'income') {
            document.getElementById('catTypeIncome').checked = true;
        } else {
            document.getElementById('catTypeExpense').checked = true;
        }
        
        new bootstrap.Modal(document.getElementById('categoryModal')).show();
    }
    
    document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('categoryModalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Category';
        document.getElementById('categoryForm').reset();
        document.getElementById('category_id').value = 0;
        document.getElementById('iconPreview').className = 'bi bi-tag';
    });
    
    document.getElementById('icon').addEventListener('input', function() {
        document.getElementById('iconPreview').className = this.value || 'bi bi-tag';
    });
    </script>
</body>
</html>

