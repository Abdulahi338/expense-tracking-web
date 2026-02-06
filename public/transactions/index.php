<?php
require_once dirname(__DIR__, 2) . '/src/init.php';
require_once ROOT_PATH . '/src/Transactions/Transaction.php';

use App\Transactions\Transaction;
use App\Core\Security;

requireLogin();

$pageTitle = 'All Transactions';
$userId = $_SESSION['user_id'];
$transObj = new Transaction($pdo);
$transactions = $transObj->getAllByUser($userId);

require_once ROOT_PATH . '/public/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Transaction History</h2>
    <a href="/transactions/create.php" class="btn btn-primary-gradient rounded-pill px-4">
        <i class="fas fa-plus me-1"></i> Add Transaction
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4">Date</th>
                        <th class="border-0">Category</th>
                        <th class="border-0">Description</th>
                        <th class="border-0">Type</th>
                        <th class="border-0">Amount</th>
                        <th class="border-0 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-search-dollar fa-3x mb-3 opacity-25"></i>
                                <p>No transactions found matching your criteria.</p>
                            </td>
                        </tr>
                    <?php
else: ?>
                        <?php foreach ($transactions as $trans): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="d-block fw-bold"><?php echo date('d M Y', strtotime($trans['transaction_date'])); ?></span>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($trans['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas <?php echo $trans['type'] === 'income' ? 'fa-wallet text-success' : 'fa-shopping-cart text-danger'; ?> small"></i>
                                        </div>
                                        <?php echo Security::sanitize($trans['category_name']); ?>
                                    </div>
                                </td>
                                <td><span class="text-muted"><?php echo Security::sanitize($trans['description'] ?: '-'); ?></span></td>
                                <td>
                                    <span class="badge rounded-pill <?php echo $trans['type'] === 'income' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst($trans['type']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold <?php echo $trans['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo($trans['type'] === 'income' ? '+' : '-') . '$' . number_format($trans['amount'], 2); ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="/transactions/edit.php?id=<?php echo $trans['id']; ?>"><i class="fas fa-edit me-2"></i> Edit</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="/transactions/delete.php?id=<?php echo $trans['id']; ?>" onclick="return confirm('Are you sure you want to delete this transaction?')"><i class="fas fa-trash me-2"></i> Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php
    endforeach; ?>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/public/includes/footer.php'; ?>
