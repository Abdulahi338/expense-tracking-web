<?php
$pageTitle = '404 - Not Found';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row min-vh-75 align-items-center justify-content-center text-center">
    <div class="col-md-6">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle text-warning display-1"></i>
        </div>
        <h1 class="display-4 fw-bold">404</h1>
        <h2 class="mb-4">Oops! Page Not Found</h2>
        <p class="lead mb-5 text-muted">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        <a href="/" class="btn btn-primary-gradient px-5 py-3 rounded-pill shadow">
            <i class="fas fa-home me-2"></i> Back to Homepage
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
