<?php
// public/index.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Personal Finance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background: var(--primary-gradient);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">FinanceTracker</a>
            <div class="d-flex">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
                    <a href="logout.php" class="btn btn-light">Logout</a>
                <?php
else: ?>
                    <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="signup.php" class="btn btn-light">Sign Up</a>
                <?php
endif; ?>
            </div>
        </div>
    </nav>

    <header class="py-5 text-center text-white header-gradient shadow-lg">
        <div class="container py-5">
            <h1 class="display-4 fw-bold">Master Your Finances</h1>
            <p class="lead mb-4">A simple, student-friendly way to track your income and expenses.</p>
            <a href="signup.php" class="btn btn-light btn-lg px-5 shadow">Get Started for Free</a>
        </div>
    </header>

    <section class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-6">
                    <div class="p-4 bg-white rounded shadow-sm">
                        <i class="bi bi-shield-check fs-1 text-primary"></i>
                        <h4 class="mt-3">Secure</h4>
                        <p class="text-muted">Password hashing and SQL injection protection.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 bg-white rounded shadow-sm">
                        <i class="bi bi-graph-up-arrow fs-1 text-primary"></i>
                        <h4 class="mt-3">Visual Insights</h4>
                        <p class="text-muted">Interactive charts to see your spending habits.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 bg-white rounded shadow-sm">
                        <i class="bi bi-moon-stars fs-1 text-primary"></i>
                        <h4 class="mt-3">Dark Mode</h4>
                        <p class="text-muted">Easy on the eyes, day or night.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-4 bg-white border-top text-center mt-5">
        <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> FinanceTracker Project</p>
    </footer>

</body>
</html>
