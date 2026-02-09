<?php
// public/dashboard.php
session_start();
include_once '../config/db.php';
include_once '../src/functions.php';

// Protect the page
check_login();

$user_id = $_SESSION['user_id'];

// Get total income
$income_query = "SELECT SUM(amount) AS total_income FROM income WHERE user_id = $user_id";
$income_res = mysqli_query($conn, $income_query);
$income_row = mysqli_fetch_assoc($income_res);
$total_income = $income_row['total_income'] ?? 0;

// Get total expenses
$expense_query = "SELECT SUM(amount) AS total_expenses FROM expenses WHERE user_id = $user_id";
$expense_res = mysqli_query($conn, $expense_query);
$expense_row = mysqli_fetch_assoc($expense_res);
$total_expenses = $expense_row['total_expenses'] ?? 0;

// Calculate balance
$balance = $total_income - $total_expenses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Expense Tracker</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include_once '../src/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-gradient d-flex justify-content-between align-items-center">
            <h1>Welcome, <?php echo sanitize($_SESSION['username']); ?>!</h1>
            <p class="mb-0"><?php echo date('l, jS F Y'); ?></p>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'unauthorized'): ?>
            <div class="alert alert-danger mt-3 shadow-sm border-0">
                <i class="bi bi-shield-slash me-2"></i> **Permission Denied!** You do not have administrator privileges to access that page.
            </div>
        <?php
endif; ?>

        <!-- Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm">
                    <h5>Total Balance</h5>
                    <h2 class="text-primary fw-bold"><?php echo format_currency($balance); ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm border-success">
                    <h5 class="text-success">Total Income</h5>
                    <h2 class="text-success fw-bold"><?php echo format_currency($total_income); ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm border-danger">
                    <h5 class="text-danger">Total Expenses</h5>
                    <h2 class="text-danger fw-bold"><?php echo format_currency($total_expenses); ?></h2>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h5 class="mb-4">Income vs Expenses (Pie Chart)</h5>
                    <div style="height: 300px;">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h5 class="mb-4">Monthly Trends (Line Chart)</h5>
                    <div style="height: 300px;">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js and Dashboard Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data for Pie Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Income', 'Expenses'],
                datasets: [{
                    data: [<?php echo $total_income; ?>, <?php echo $total_expenses; ?>],
                    backgroundColor: ['#28a745', '#dc3545'],
                }]
            },
            options: { maintainAspectRatio: false }
        });

        // Placeholder Data for Line Chart (Simplified for student project)
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Expenses',
                    data: [500, 700, 400, 600, 800, 550],
                    borderColor: '#007BFF',
                    fill: false
                }]
            },
            options: { maintainAspectRatio: false }
        });
    </script>
</body>
</html>
