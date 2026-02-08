<?php
// public/reports.php
session_start();
include_once '../config/db.php';
include_once '../src/functions.php';

check_login();
$user_id = $_SESSION['user_id'];

// Get data for report
$income_query = "SELECT source as label, SUM(amount) as value FROM income WHERE user_id = $user_id GROUP BY source";
$income_data = mysqli_query($conn, $income_query);

$expense_query = "SELECT category as label, SUM(amount) as value FROM expenses WHERE user_id = $user_id GROUP BY category";
$expense_data = mysqli_query($conn, $expense_query);

$income_labels = [];
$income_values = [];
while ($row = mysqli_fetch_assoc($income_data)) {
    $income_labels[] = $row['label'];
    $income_values[] = $row['value'];
}

$expense_labels = [];
$expense_values = [];
while ($row = mysqli_fetch_assoc($expense_data)) {
    $expense_labels[] = $row['label'];
    $expense_values[] = $row['value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include_once '../src/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header-gradient">
            <h1>Financial Reports</h1>
            <p>Detailed breakdown of your finances</p>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h5>Income Sources</h5>
                    <canvas id="incomeReportChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h5>Expense Categories</h5>
                    <canvas id="expenseReportChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Income Chart
        new Chart(document.getElementById('incomeReportChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($income_labels); ?>,
                datasets: [{
                    label: 'Income ($)',
                    data: <?php echo json_encode($income_values); ?>,
                    backgroundColor: '#28a745'
                }]
            }
        });

        // Expense Chart
        new Chart(document.getElementById('expenseReportChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($expense_labels); ?>,
                datasets: [{
                    label: 'Expenses ($)',
                    data: <?php echo json_encode($expense_values); ?>,
                    backgroundColor: '#dc3545'
                }]
            }
        });

        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>
