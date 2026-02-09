<?php
// public/expenses.php
session_start();
include_once '../config/db.php';
include_once '../src/functions.php';

check_login();
$user_id = $_SESSION['user_id'];

// --- Handle Add Expense ---
if (isset($_POST['add_expense'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF validation failed");
    }

    $amount = sanitize($_POST['amount']);
    $category = sanitize($_POST['category']);
    $date = sanitize($_POST['date']);
    $description = sanitize($_POST['description']);

    // --- Balance Validation Logic ---
    // Calculate current Total Income and Total Expenses to find available balance
    $balance_query = "SELECT 
        (SELECT IFNULL(SUM(amount), 0) FROM income WHERE user_id = $user_id) as total_income,
        (SELECT IFNULL(SUM(amount), 0) FROM expenses WHERE user_id = $user_id) as total_expenses";

    $balance_res = mysqli_query($conn, $balance_query);
    $balance_row = mysqli_fetch_assoc($balance_res);

    $total_income = $balance_row['total_income'];
    $total_expenses = $balance_row['total_expenses'];
    $available_balance = $total_income - $total_expenses;

    // Strict Denial Check
    if ($amount > $available_balance) {
        $error = "Transaction Denied! You only have " . format_currency($available_balance) . " available, but tried to spend " . format_currency($amount) . ".";
        $error_type = "balance_denied";
    }
    else {
        $query = "INSERT INTO expenses (user_id, amount, category, date, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "idsss", $user_id, $amount, $category, $date, $description);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: expenses.php?success=1");
            exit();
        }
        else {
            $error = "Error adding expense: " . mysqli_error($conn);
        }
    }
}

// --- Handle Delete Expense ---
if (isset($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    $query = "DELETE FROM expenses WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($stmt);
    header("Location: expenses.php");
}

// Get all expenses for this user
$expense_query = "SELECT * FROM expenses WHERE user_id = $user_id ORDER BY date DESC";
$expense_result = mysqli_query($conn, $expense_query);

// Get available expense categories created by admin
$cat_query = "SELECT * FROM categories WHERE type = 'expense' ORDER BY name ASC";
$cat_result = mysqli_query($conn, $cat_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Expenses - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include_once '../src/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-gradient shadow-sm">
            <h1>Manage Expenses</h1>
            <p>Control your spending</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert mt-3 text-white border-0 shadow-sm <?php echo(isset($error_type) && $error_type == 'balance_denied') ? 'btn-primary-gradient' : 'bg-danger'; ?>" role="alert">
                <i class="bi <?php echo(isset($error_type) && $error_type == 'balance_denied') ? 'bi-exclamation-triangle-fill' : 'bi-x-circle-fill'; ?> me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php
endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success mt-3 border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                Expense added successfully!
            </div>
        <?php
endif; ?>

        <!-- Add Expense Form -->
        <div class="card p-4 shadow-sm mb-4">
            <h5>Add New Expense</h5>
            <form action="expenses.php" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="col-md-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo isset($_POST['amount']) ? sanitize($_POST['amount']) : ''; ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php
mysqli_data_seek($cat_result, 0);
while ($cat = mysqli_fetch_assoc($cat_result)): ?>
                            <option value="<?php echo sanitize($cat['name']); ?>" <?php echo(isset($_POST['category']) && $_POST['category'] == $cat['name']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($cat['name']); ?>
                            </option>
                        <?php
endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo isset($_POST['date']) ? sanitize($_POST['date']) : date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="<?php echo isset($_POST['description']) ? sanitize($_POST['description']) : ''; ?>">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" name="add_expense" class="btn btn-primary-gradient">Add Expense</button>
                </div>
            </form>
        </div>

        <!-- Expenses List -->
        <div class="card shadow-sm p-4">
            <h5>Expense History</h5>
            <div class="table-responsive">
                <table class="table table-hover mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($expense_result)): ?>
                        <tr>
                            <td><?php echo $row['date']; ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo sanitize($row['category']); ?></span></td>
                            <td class="text-danger fw-bold"><?php echo format_currency($row['amount']); ?></td>
                            <td><?php echo sanitize($row['description']); ?></td>
                            <td>
                                <a href="expenses.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
