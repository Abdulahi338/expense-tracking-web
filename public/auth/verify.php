<?php
require_once dirname(__DIR__, 2) . '/src/init.php';
require_once ROOT_PATH . '/src/Auth/User.php';

use App\Core\Security;
use App\Auth\User;

$pageTitle = 'Verify Email';

if (!isset($_SESSION['verify_email'])) {
    header('Location: /auth/register.php');
    exit();
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $otp = trim($_POST['otp']);
    $userObj = new User($pdo);

    if ($userObj->verifyOTP($_SESSION['verify_email'], $otp)) {
        unset($_SESSION['verify_email'], $_SESSION['demo_otp']);
        Security::redirect('/auth/login.php', 'Email verified successfully! You can now login.', 'success');
    }
    else {
        $error = "Invalid or expired OTP code.";
    }
}

require_once ROOT_PATH . '/public/includes/header.php';
?>

<div class="auth-container">
    <div class="card shadow">
        <div class="card-body p-4 text-center">
            <h2 class="mb-3">Verify Your Email</h2>
            <p class="text-muted">Enter the 6-digit code sent to <strong><?php echo Security::sanitize($_SESSION['verify_email']); ?></strong></p>
            
            <?php if (isset($_SESSION['demo_otp'])): ?>
                <div class="alert alert-info py-2">
                    <small><i class="fas fa-info-circle me-1"></i> Demo Mode: Your OTP is <strong><?php echo $_SESSION['demo_otp']; ?></strong></small>
                </div>
            <?php
endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?php echo $error; ?></div>
            <?php
endif; ?>

            <form action="/auth/verify.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                
                <div class="mb-4">
                    <input type="text" name="otp" class="form-control otp-input" maxlength="6" placeholder="000000" pattern="\d{6}" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary-gradient w-100 py-2 rounded-pill shadow-sm">Verify OTP</button>
            </form>
            
            <div class="mt-4">
                <p class="mb-0">Didn't receive code? <a href="#">Resend Code</a></p>
                <a href="/auth/register.php" class="small text-secondary">Back to Register</a>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/public/includes/footer.php'; ?>
