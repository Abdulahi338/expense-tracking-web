<?php
/**
 * Edit Transaction
 * Expense Tracking System
 */

// Include configuration and require login
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

requireLogin();

$error = '';
$success = '';
$transactionId = $_GET['id'] ?? 0;

// Validate transaction ID
if (empty($transactionId) || !is_numeric($transactionId)) {
    setFlash('error', 'Invalid transaction ID.');
    header('Location: index.php');
    exit;
}

// Get transaction
$stmt = $pdo->prepare("SELECT t.*, c.type as category_type FROM transactions t LEFT JOIN categories c ON t.category_id = c.id WHERE t.id = ? AND t.user_id = ?");
$stmt->execute([$transactionId, getCurrentUserId()]);
$transaction = $stmt->fetch();

if (!$transaction) {
    setFlash('error', 'Transaction not found.');
    header('Location: index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $categoryId = $_POST['category_id'] ?? '';
        $amount = $_POST['amount'] ?? '';
        $description = $_POST['description'] ?? '';
        $transactionDate = $_POST['transaction_date'] ?? '';
        
        // Validate inputs
        if (empty($categoryId) || !is_numeric($categoryId)) {
            $error = 'Please select a category.';
        } elseif (empty($amount) || !is_numeric($amount) || $amount <= 0) {
            $error = 'Please enter a valid amount greater than 0.';
        } elseif (empty($transactionDate) || !strtotime($transactionDate)) {
            $error = 'Please select a valid date.';
        } else {
            // Update transaction
            $stmt = $pdo->prepare("UPDATE transactions SET category_id = ?, amount = ?, description = ?, transaction_date = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([
                $categoryId,
                $amount,
                $description,
                $transactionDate,
                $transactionId,
                getCurrentUserId()
            ]);
            
            setFlash('success', 'Transaction updated successfully!');
            header('Location: index.php');
            exit;
        }
    }
}

// Get categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY name");
$stmt->execute([getCurrentUserId()]);
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction - <?php echo APP_NAME; ?></title>
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
                        <h1 class="h3 mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Transaction</h1>
                        <p class="text-muted mb-0">Update transaction details</p>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Transactions
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="editTransactionForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <div class="form-control bg-light">
                                    <i class="bi bi-<?php echo $transaction['type'] === 'income' ? 'arrow-down-circle text-success' : 'arrow-up-circle text-danger'; ?> me-2"></i>
                                    <span class="text-capitalize"><?php echo $transaction['type']; ?></span>
                                </div>
                                <input type="hidden" name="type" value="<?php echo $transaction['type']; ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($transaction['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo CURRENCY_SYMBOL; ?></span>
                                    <input type="number" class="form-control" id="amount" name="amount" 
                                           step="0.01" min="0.01" placeholder="0.00" 
                                           value="<?php echo htmlspecialchars($transaction['amount']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="transaction_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="transaction_date" name="transaction_date" 
                                       value="<?php echo htmlspecialchars($transaction['transaction_date']); ?>" required>
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="2" placeholder="Optional description"><?php echo htmlspecialchars($transaction['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Update Transaction
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Include Footer -->
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>

