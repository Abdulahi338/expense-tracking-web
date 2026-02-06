<?php require_once dirname(__DIR__, 2) . '/src/init.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Expense Tracker'; ?> | Finance Manager</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg border-bottom sticky-top bg-white">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="/">
            <i class="fas fa-wallet me-2"></i>ExpenseTrack
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/transactions/index.php">Transactions</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/auth/profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php
else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary-gradient btn-sm px-4 rounded-pill" href="/auth/register.php">Get Started</a>
                    </li>
                <?php
endif; ?>
                
                <li class="nav-item ms-lg-3">
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" id="themeToggle">
                        <label class="form-check-label" for="themeToggle"><i class="fas fa-moon"></i></label>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $_SESSION['flash_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php
endif; ?>
