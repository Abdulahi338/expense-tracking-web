<?php
// public/income.php
session_start();
include_once '../config/db.php';
include_once '../src/functions.php';

check_login();
$user_id = $_SESSION['user_id'];

// --- Handle Add Income ---
if (isset($_POST['add_income'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF validation failed");
    }

    $amount = sanitize($_POST['amount']);
    $source = sanitize($_POST['source']);
    $date = sanitize($_POST['date']);
    $description = sanitize($_POST['description']);

    $query = "INSERT INTO income (user_id, amount, source, date, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "idsss", $user_id, $amount, $source, $date, $description);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: income.php?success=1");
    }
    else {
        $error = "Error adding income: " . mysqli_error($conn);
    }
}

// --- Handle Delete Income ---
if (isset($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    $query = "DELETE FROM income WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($stmt);
    header("Location: income.php");
}

// Get all income for this user
$income_query = "SELECT * FROM income WHERE user_id = $user_id ORDER BY date DESC";
$income_result = mysqli_query($conn, $income_query);

// Get available income categories created by admin
$cat_query = "SELECT * FROM categories WHERE type = 'income' ORDER BY name ASC";
$cat_result = mysqli_query($conn, $cat_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Income - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include_once '../src/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-gradient">
            <h1>Manage Income</h1>
            <p>Track your sources of wealth</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
        <?php
endif; ?>

        <!-- Add Income Form -->
        <div class="card p-4 shadow-sm mb-4">
            <h5>Add New Income</h5>
            <form action="income.php" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="col-md-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo isset($_POST['amount']) ? sanitize($_POST['amount']) : ''; ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Source / Category</label>
                    <select name="source" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php
// Reset pointer for re-use if needed or just use the result
mysqli_data_seek($cat_result, 0);
while ($cat = mysqli_fetch_assoc($cat_result)): ?>
                            <option value="<?php echo sanitize($cat['name']); ?>" <?php echo(isset($_POST['source']) && $_POST['source'] == $cat['name']) ? 'selected' : ''; ?>>
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
                    <button type="submit" name="add_income" class="btn btn-primary-gradient">Add Income</button>
                </div>
            </form>
        </div>

        <!-- Income List -->
        <div class="card shadow-sm p-4">
            <h5>Income History</h5>
            <div class="table-responsive">
                <table class="table table-hover mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Source</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($income_result)): ?>
                        <tr>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo sanitize($row['source']); ?></td>
                            <td class="text-success fw-bold"><?php echo format_currency($row['amount']); ?></td>
                            <td><?php echo sanitize($row['description']); ?></td>
                            <td>
                                <a href="income.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
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

    <script>
        // Dark Mode support
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>
