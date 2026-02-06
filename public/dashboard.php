<?php
require_once __DIR__ . '/../src/init.php';
require_once ROOT_PATH . '/src/Transactions/Transaction.php';
require_once ROOT_PATH . '/src/Categories/Category.php';

use App\Transactions\Transaction;
use App\Core\Security;

requireLogin();

$pageTitle = 'Dashboard';
$userId = $_SESSION['user_id'];
$transObj = new Transaction($pdo);

$summary = $transObj->getSummary($userId);
$recentTransactions = array_slice($transObj->getAllByUser($userId), 0, 5);

// Fetch chart data
$categoryData = $transObj->getCategoryDistribution($userId);
$trendData = $transObj->getMonthlyTrends($userId);

// Prepare JSON for JS
$catLabels = json_encode(array_column($categoryData, 'name'));
$catTotals = json_encode(array_column($categoryData, 'total'));

$trendLabels = json_encode(array_column($trendData, 'month'));
$trendIncome = json_encode(array_column($trendData, 'income'));
$trendExpense = json_encode(array_column($trendData, 'expense'));

require_once __DIR__ . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card summary-card border-0 bg-primary-gradient text-white h-100 shadow-sm">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2 opacity-75">Current Balance</h6>
                        <h2 class="display-6 fw-bold mb-0">$<?php echo number_format($summary['balance'], 2); ?></h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card summary-card border-0 bg-success text-white h-100 shadow-sm">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2 opacity-75">Total Income</h6>
                        <h2 class="display-6 fw-bold mb-0">$<?php echo number_format($summary['income'], 2); ?></h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-arrow-alt-circle-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card summary-card border-0 bg-danger text-white h-100 shadow-sm">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2 opacity-75">Total Expenses</h6>
                        <h2 class="display-6 fw-bold mb-0">$<?php echo number_format($summary['expense'], 2); ?></h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-arrow-alt-circle-down"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Reports/Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Spending Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="trendsChart" style="min-height: 250px;"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Category Distribution -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 py-3 text-center">
                <h5 class="mb-0">Spending by Category</h5>
            </div>
            <div class="card-body">
                <?php if (empty($categoryData)): ?>
                    <div class="text-center py-5 text-muted">No data to display</div>
                    <canvas id="categoryChart" style="display:none"></canvas>
                <?php
else: ?>
                    <canvas id="categoryChart"></canvas>
                <?php
endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Transactions</h5>
                <a href="/transactions/create.php" class="btn btn-primary-gradient btn-sm rounded-pill px-3">
                    <i class="fas fa-plus me-1"></i> Add Transaction
                </a>
            </div>
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
                        <?php if (empty($recentTransactions)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No transactions found yet. Start by adding one!</td>
                            </tr>
                        <?php
else: ?>
                            <?php foreach ($recentTransactions as $trans): ?>
                                <tr>
                                    <td class="ps-4"><?php echo date('M d, Y', strtotime($trans['transaction_date'])); ?></td>
                                    <td><?php echo Security::sanitize($trans['category_name']); ?></td>
                                    <td><small class="text-muted"><?php echo Security::sanitize($trans['description']); ?></small></td>
                                    <td>
                                        <span class="badge rounded-pill <?php echo $trans['type'] === 'income' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($trans['type']); ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold <?php echo $trans['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo($trans['type'] === 'income' ? '+' : '-') . '$' . number_format($trans['amount'], 2); ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="/transactions/edit.php?id=<?php echo $trans['id']; ?>" class="btn btn-sm btn-outline-info me-1"><i class="fas fa-edit"></i></a>
                                        <a href="/transactions/delete.php?id=<?php echo $trans['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php
    endforeach; ?>
                        <?php
endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($recentTransactions)): ?>
                <div class="card-footer bg-white border-0 text-center py-3">
                    <a href="/transactions/index.php" class="text-primary text-decoration-none small fw-bold">View All Transactions <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            <?php
endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: <?php echo $trendLabels; ?>,
            datasets: [{
                label: 'Income',
                data: <?php echo $trendIncome; ?>,
                borderColor: '#198754',
                tension: 0.4,
                fill: false
            }, {
                label: 'Expenses',
                data: <?php echo $trendExpense; ?>,
                borderColor: '#dc3545',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Category Chart
    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?php echo $catLabels; ?>,
                datasets: [{
                    data: <?php echo $catTotals; ?>,
                    backgroundColor: ['#007BFF', '#dc3545', '#ffc107', '#17a2b8', '#6610f2', '#fd7e14', '#20c997', '#0dcaf0']
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
