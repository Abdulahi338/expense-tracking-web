<?php

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

?>

<div class="row min-vh-75 align-items-center">
    <div class="col-lg-6">
        <h1 class="display-4 fw-bold">Master Your Finances with <span class="text-primary">ExpenseTrack</span></h1>
        <p class="lead mb-4">The simple, smart, and secure way to track your income and expenses. Visualize your spending habits and save more every month.</p>
        
        <?php if (!isLoggedIn()): ?>
            <div class="d-grid gap-3 d-md-flex">
                <a href="/auth/register.php" class="btn btn-primary-gradient btn-lg px-5 rounded-pill shadow">Start for Free</a>
                <a href="/auth/login.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill">Login Now</a>
            </div>
        <?php
else: ?>
            <a href="/dashboard.php" class="btn btn-primary-gradient btn-lg px-5 rounded-pill shadow">Go to Dashboard</a>
        <?php
endif; ?>
    </div>
    <div class="col-lg-6 text-center">
        <img src="https://img.freepik.com/free-vector/personal-finance-concept-illustration_114360-5471.jpg" alt="Finance Illustration" class="img-fluid rounded" style="max-height: 450px;">
    </div>
</div>

<hr class="my-5">

<div class="row text-center g-4">
    <div class="col-md-4">
        <div class="p-3">
            <div class="feature-icon bg-primary bg-gradient text-white rounded-3 mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Secure Tracking</h3>
            <p>Your data is protected with best-in-class security practices and secure authentication.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3">
            <div class="feature-icon bg-success bg-gradient text-white rounded-3 mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i class="fas fa-chart-pie"></i>
            </div>
            <h3>Visual Insights</h3>
            <p>Get clear charts and reports to understand exactly where your money is going.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3">
            <div class="feature-icon bg-info bg-gradient text-white rounded-3 mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h3>Universal Access</h3>
            <p>Responsive design works perfectly on your desktop, tablet, and mobile phone.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
