<?php
/**
 * Transactions Index
 * Expense Tracking System
 */

// Include configuration and require login
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

requireLogin();

$userId = getCurrentUserId();
$pageTitle = 'Transactions';

// Get filter parameters
$type = $_GET['type'] ?? 'all';
$categoryId = $_GET['category'] ?? '';
$month = $_GET['month'] ?? date('Y-m');
$search = $_GET['search'] ?? '';

// Build query
$where = "WHERE t.user_id = ?";
$params = [$userId];

if ($type !== 'all') {
    $where .= " AND t.type = ?";
    $params[] = $type;
}

if (!empty($categoryId)) {
    $where .= " AND t.category_id = ?";
    $params[] = $categoryId;
}

if (!empty($month)) {
    $where .= " AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?";
    $params[] = $month;
}

if (!empty($search)) {
    $where .= " AND (t.description LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$itemsPerPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $itemsPerPage;

// Get total count
$countSql = "SELECT COUNT(*) FROM transactions t LEFT JOIN categories c ON t.category_id = c.id $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalItems = $stmt->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

// Get transactions
$sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color 
        FROM transactions t 
        LEFT JOIN categories c ON t.category_id = c.id 
        $where 
        ORDER BY t.transaction_date DESC, t.id DESC 
        LIMIT $itemsPerPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Get categories for filter
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY name");
$stmt->execute([$userId]);
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - <?php echo APP_NAME; ?></title>
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
                        <h1 class="h3 mb-0"><i class="bi bi-cash-stack me-2"></i>Transactions</h1>
                        <p class="text-muted mb-0">Manage your income and expenses</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                        <i class="bi bi-plus-circle me-1"></i>Add Transaction
                    </button>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-2">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>All Types</option>
                                <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Income</option>
                                <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId === (string)$cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="month" class="form-label">Month</label>
                            <input type="month" class="form-control" id="month" name="month" value="<?php echo $month; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Search description..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-grid w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel me-1"></i>Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Transactions Table -->
            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No transactions found</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                                <i class="bi bi-plus-circle me-1"></i>Add Transaction
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $tx): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="category-icon me-2" style="background-color: <?php echo $tx['category_color']; ?>20; color: <?php echo $tx['category_color']; ?>;">
                                                        <i class="<?php echo $tx['category_icon']; ?>"></i>
                                                    </div>
                                                    <?php echo htmlspecialchars($tx['category_name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($tx['description'] ?? '-'); ?></td>
                                            <td><?php echo date(DISPLAY_DATE_FORMAT, strtotime($tx['transaction_date'])); ?></td>
                                            <td class="text-end fw-bold <?php echo $tx['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $tx['type'] === 'income' ? '+' : '-'; ?><?php echo CURRENCY_SYMBOL . number_format($tx['amount'], 2); ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="edit.php?id=<?php echo $tx['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDelete(<?php echo $tx['id']; ?>)" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo $type; ?>&category=<?php echo $categoryId; ?>&month=<?php echo $month; ?>&search=<?php echo urlencode($search); ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $type; ?>&category=<?php echo $categoryId; ?>&month=<?php echo $month; ?>&search=<?php echo urlencode($search); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo $type; ?>&category=<?php echo $categoryId; ?>&month=<?php echo $month; ?>&search=<?php echo urlencode($search); ?>">
                                                    <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Include Footer for Modal -->
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
    
    <script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Transaction',
            text: 'Are you sure you want to delete this transaction? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash me-1"></i>Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'delete.php?id=' + id + '&confirm=1';
            }
        });
    }
    </script>
</body>
</html>

