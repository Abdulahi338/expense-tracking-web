<?php
require_once dirname(__DIR__, 2) . '/src/init.php';
require_once ROOT_PATH . '/src/Auth/User.php';

use App\Core\Security;
use App\Auth\User;

$pageTitle = 'Forgot Password';

$message = null;
$type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $email = trim($_POST['email']);
    $userObj = new User($pdo);
    $token = $userObj->generateResetToken($email);

    if ($token) {
        // In a real app, send email here
        $message = "A reset link has been sent to your email. (Demo: /auth/reset-password.php?token=$token)";
    }
    else {
        $message = "If that email exists in our system, you will receive a reset link shortly.";
    }
}

require_once ROOT_PATH . '/public/includes/header.php';
?>

<div class="auth-container">
    <div class="card shadow">
        <div class="card-body p-4 text-center">
            <h2 class="mb-3">Reset Password</h2>
            <p class="text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>
            
            <?php if ($message): ?>
                <div class="alert alert-info py-2 small"><?php echo $message; ?></div>
            <?php
endif; ?>

            <form action="/auth/forgot-password.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                
                <div class="mb-4">
                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary-gradient w-100 py-2 rounded-pill shadow-sm">Send Reset Link</button>
            </form>
            
            <div class="mt-4">
                <a href="/auth/login.php" class="text-secondary small">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/public/includes/footer.php'; ?>
