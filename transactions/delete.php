<?php
/**
 * Delete Transaction Handler
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

// Check for confirmation
if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
    // Verify transaction belongs to user
    $stmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transactionId, getCurrentUserId()]);
    
    if (!$stmt->fetch()) {
        setFlash('error', 'Transaction not found.');
        header('Location: index.php');
        exit;
    }
    
    // Delete transaction
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transactionId, getCurrentUserId()]);
    
    setFlash('success', 'Transaction deleted successfully.');
    header('Location: index.php');
    exit;
}

// Show confirmation page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Transaction - <?php echo APP_NAME; ?></title>
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
                <h1 class="h3 mb-0"><i class="bi bi-trash text-danger me-2"></i>Delete Transaction</h1>
            </div>
            
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">Are you sure?</h4>
                    <p class="text-muted mb-4">This action cannot be undone. This will permanently delete this transaction.</p>
                    
                    <a href="delete.php?id=<?php echo $transactionId; ?>&confirm=1" class="btn btn-danger me-2">
                        <i class="bi bi-trash me-1"></i>Yes, Delete It
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Include Footer -->
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>

