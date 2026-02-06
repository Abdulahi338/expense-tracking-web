<?php
/**
 * Reports Index
 * Expense Tracking System
 */

// Include configuration and require login
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

requireLogin();

$userId = getCurrentUserId();
$pageTitle = 'Reports';

// Get date range parameters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('Y-m');

// Get summary for date range
$stmt = $pdo->prepare("SELECT 
    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
    COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expenses,
    COUNT(CASE WHEN type = 'income' THEN 1 END) as income_count,
    COUNT(CASE WHEN type = 'expense' THEN 1 END) as expense_count
FROM transactions 
WHERE user_id = ? AND transaction_date BETWEEN ? AND ?");
$stmt->execute([$userId, $startDate, $endDate]);
$summary = $stmt->fetch();

$balance = $summary['total_income'] - $summary['total_expenses'];

// Get income by category
$stmt = $pdo->prepare("SELECT c.name, c.color, c.icon, COALESCE(SUM(t.amount), 0) as total 
                        FROM categories c 
                        LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ? AND t.type = 'income' AND t.transaction_date BETWEEN ? AND ?
                        WHERE c.type = 'income' 
                        GROUP BY c.id 
                        HAVING total > 0 
                        ORDER BY total DESC");
$stmt->execute([$userId, $startDate, $endDate]);
$incomeByCategory = $stmt->fetchAll();

// Get expenses by category
$stmt = $pdo->prepare("SELECT c.name, c.color, c.icon, COALESCE(SUM(t.amount), 0) as total 
                        FROM categories c 
                        LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ? AND t.type = 'expense' AND t.transaction_date BETWEEN ? AND ?
                        WHERE c.type = 'expense' 
                        GROUP BY c.id 
                        HAVING total > 0 
                        ORDER BY total DESC");
$stmt->execute([$userId, $startDate, $endDate]);
$expenseByCategory = $stmt->fetchAll();

// Get monthly trend data
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    $monthName = date('F Y', strtotime("-$i months"));
    
    $stmt = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
    FROM transactions 
    WHERE user_id = ? AND transaction_date BETWEEN ? AND ?");
    $stmt->execute([$userId, $monthStart, $monthEnd]);
    $data = $stmt->fetch();
    
    $monthlyData[] = [
        'month' => $monthName,
        'income' => $data['income'],
        'expense' => $data['expense']
    ];
}

// Get daily trend for current month
$dailyData = [];
$daysInMonth = date('t');
for ($i = 1; $i <= $daysInMonth; $i++) {
    $day = sprintf('%02d', $i);
    $date = date('Y-m-') . $day;
    
    $stmt = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
    FROM transactions 
    WHERE user_id = ? AND transaction_date = ?");
    $stmt->execute([$userId, $date]);
    $data = $stmt->fetch();
    
    $dailyData[] = [
        'date' => date('M d', strtotime($date)),
        'income' => $data['income'],
        'expense' => $data['expense']
    ];
}

// Get top transactions
$stmt = $pdo->prepare("SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color 
                        FROM transactions t 
                        LEFT JOIN categories c ON t.category_id = c.id 
                        WHERE t.user_id = ? AND t.transaction_date BETWEEN ? AND ?
                        ORDER BY t.amount DESC 
                        LIMIT 10");
$stmt->execute([$userId, $startDate, $endDate]);
$topTransactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo APP_NAME; ?></title>
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
                <h1 class="h3 mb-0"><i class="bi bi-bar-chart-line me-2"></i>Reports & Analytics</h1>
                <p class="text-muted mb-0">Track your financial performance</p>
            </div>
            
            <!-- Date Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group w-100" role="group">
                                <a href="?start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary <?php echo $startDate === date('Y-m-01') ? 'active' : ''; ?>">This Month</a>
                                <a href="?start_date=<?php echo date('Y-01-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary <?php echo $startDate === date('Y-01-01') ? 'active' : ''; ?>">This Year</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card summary-card income">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="card-subtitle mb-1">Total Income</p>
                                    <h4 class="card-title mb-0"><?php echo CURRENCY_SYMBOL . number_format($summary['total_income'], 2); ?></h4>
                                    <small class="text-muted"><?php echo $summary['income_count']; ?> transactions</small>
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
                                    <h4 class="card-title mb-0"><?php echo CURRENCY_SYMBOL . number_format($summary['total_expenses'], 2); ?></h4>
                                    <small class="text-muted"><?php echo $summary['expense_count']; ?> transactions</small>
                                </div>
                                <div class="icon-circle bg-danger bg-opacity-10">
                                    <i class="bi bi-arrow-up-circle text-danger fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card summary-card <?php echo $balance >= 0 ? 'balance' : 'negative'; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="card-subtitle mb-1">Net Balance</p>
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
                            <h5 class="card-title mb-0"><i class="bi bi-bar-chart-line me-2"></i>Income vs Expenses Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-pie-chart me-2"></i>Income Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($incomeByCategory)): ?>
                                <p class="text-muted text-center">No income data for this period</p>
                            <?php else: ?>
                                <canvas id="incomeChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-pie-chart me-2"></i>Expense Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($expenseByCategory)): ?>
                                <p class="text-muted text-center">No expense data for this period</p>
                            <?php else: ?>
                                <canvas id="expenseChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-arrow-up-right-circle me-2"></i>Top Transactions</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($topTransactions)): ?>
                                <p class="text-muted text-center py-4">No transactions for this period</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Category</th>
                                                <th>Date</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topTransactions as $tx): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="category-icon me-2" style="background-color: <?php echo $tx['category_color']; ?>20; color: <?php echo $tx['category_color']; ?>;">
                                                                <i class="<?php echo $tx['category_icon']; ?>"></i>
                                                            </div>
                                                            <?php echo htmlspecialchars($tx['category_name']); ?>
                                                        </div>
                                                    </td>
                                                    <td><?php echo date(DISPLAY_DATE_FORMAT, strtotime($tx['transaction_date'])); ?></td>
                                                    <td class="text-end fw-bold <?php echo $tx['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo $tx['type'] === 'income' ? '+' : '-'; ?><?php echo CURRENCY_SYMBOL . number_format($tx['amount'], 2); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Include Footer -->
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
    
    <!-- Chart Configurations -->
    <script>
    // Monthly Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
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
    
    <?php if (!empty($incomeByCategory)): ?>
    // Income Pie Chart
    const incomeCtx = document.getElementById('incomeChart').getContext('2d');
    new Chart(incomeCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($incomeByCategory, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($incomeByCategory, 'total')); ?>,
                backgroundColor: <?php echo json_encode(array_column($incomeByCategory, 'color')); ?>,
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
    <?php endif; ?>
    
    <?php if (!empty($expenseByCategory)): ?>
    // Expense Pie Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($expenseByCategory, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($expenseByCategory, 'total')); ?>,
                backgroundColor: <?php echo json_encode(array_column($expenseByCategory, 'color')); ?>,
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
    <?php endif; ?>
    </script>
</body>
</html>

