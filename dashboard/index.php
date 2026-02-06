<?php
/**
 * Dashboard Index
 * Expense Tracking System
 */

// Include configuration and require login
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

requireLogin();

$userId = getCurrentUserId();
$pageTitle = 'Dashboard';

// Get summary data
$currentMonth = date('Y-m');

// Total Income (current month)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'income' AND DATE_FORMAT(transaction_date, '%Y-%m') = ?");
$stmt->execute([$userId, $currentMonth]);
$totalIncome = $stmt->fetch()['total'];

// Total Expenses (current month)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'expense' AND DATE_FORMAT(transaction_date, '%Y-%m') = ?");
$stmt->execute([$userId, $currentMonth]);
$totalExpense = $stmt->fetch()['total'];

// Current Balance
$balance = $totalIncome - $totalExpense;

// Recent Transactions
$stmt = $pdo->prepare("SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color 
                        FROM transactions t 
                        LEFT JOIN categories c ON t.category_id = c.id 
                        WHERE t.user_id = ? 
                        ORDER BY t.transaction_date DESC, t.id DESC 
                        LIMIT 5");
$stmt->execute([$userId]);
$recentTransactions = $stmt->fetchAll();

// Category breakdown for pie chart
$stmt = $pdo->prepare("SELECT c.name, c.color, COALESCE(SUM(t.amount), 0) as total 
                       FROM categories c 
                       LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ? AND t.type = 'expense' AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ? 
                       WHERE c.type = 'expense' 
                       GROUP BY c.id 
                       HAVING total > 0 
                       ORDER BY total DESC 
                       LIMIT 6");
$stmt->execute([$userId, $currentMonth]);
$categoryData = $stmt->fetchAll();

// Monthly trend data (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthName = date('M Y', strtotime("-$i months"));
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                                  COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
                           FROM transactions 
                           WHERE user_id = ? AND DATE_FORMAT(transaction_date, '%Y-%m') = ?");
    $stmt->execute([$userId, $month]);
    $data = $stmt->fetch();
    
    $monthlyData[] = [
        'month' => $monthName,
        'income' => $data['income'],
        'expense' => $data['expense']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <h1 class="h3 mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
                <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>
            
            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card summary-card income">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="card-subtitle mb-1">Total Income</p>
                                    <h4 class="card-title mb-0"><?php echo CURRENCY_SYMBOL . number_format($totalIncome, 2); ?></h4>
                                </div>
                                <div class="icon-circle bg-success bg-opacity-10">
                                    <i class="bi bi-arrow-down-circle text-success fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card summary-card expense">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="card-subtitle mb-1">Total Expenses</p>
                                    <h4 class="card-title mb-0"><?php echo CURRENCY_SYMBOL . number_format($totalExpense, 2); ?></h4>
                                </div>
                                <div class="icon-circle bg-danger bg-opacity-10">
                                    <i class="bi bi-arrow-up-circle text-danger fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card summary-card balance <?php echo $balance >= 0 ? 'positive' : 'negative'; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="card-subtitle mb-1">Current Balance</p>
                                    <h4 class="card-title mb-0"><?php echo CURRENCY_SYMBOL . number_format($balance, 2); ?></h4>
                                </div>
                                <div class="icon-circle <?php echo $balance >= 0 ? 'bg-primary' : 'bg-danger'; ?> bg-opacity-10">
                                    <i class="bi bi-wallet2 <?php echo $balance >= 0 ? 'text-primary' : 'text-danger'; ?> fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-bar-chart-line me-2"></i>Monthly Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-pie-chart me-2"></i>Expenses by Category</h5>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
                    <a href="<?php echo APP_URL; ?>/transactions/index.php" class="btn btn-sm btn-outline-primary">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentTransactions)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No transactions yet</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                                <i class="bi bi-plus-circle me-1"></i>Add Your First Transaction
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
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $tx): ?>
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
                                                <a href="<?php echo APP_URL; ?>/transactions/edit.php?id=<?php echo $tx['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Include Footer for Modal -->
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
    
    <!-- Chart Configurations -->
    <script>
    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($monthlyData, 'month')); ?>,
            datasets: [
                {
                    label: 'Income',
                    data: <?php echo json_encode(array_column($monthlyData, 'income')); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Expenses',
                    data: <?php echo json_encode(array_column($monthlyData, 'expense')); ?>,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '<?php echo CURRENCY_SYMBOL; ?>' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });
    
    // Category Pie Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($categoryData, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($categoryData, 'total')); ?>,
                backgroundColor: <?php echo json_encode(array_column($categoryData, 'color')); ?>,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>

