<?php
/**
 * Reset Password Page
 * Expense Tracking System
 */

// Start session and include configuration
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

$error = '';
$success = '';

// Validate token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    $error = 'Invalid reset link. Please request a new password reset.';
}

$userId = null;

// Check token validity
if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $error = 'This reset link has expired or is invalid. Please request a new one.';
    } else {
        $userId = $user['id'];
    }
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all password fields.';
        } elseif (strlen($password) < MIN_PASSWORD_LENGTH) {
            $error = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Update password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
            $stmt->execute([$passwordHash, $userId]);
            
            $success = 'Password reset successfully! You can now login with your new password.';
            
            // Clear demo token
            unset($_SESSION['demo_reset_token']);
            unset($_SESSION['demo_reset_email']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card shadow-lg border-0 rounded-lg mt-5">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0"><i class="bi bi-key-fill me-2"></i>Reset Password</h3>
                        <p class="small mb-0">Enter your new password</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error && !$userId): ?>
                            <div class="alert alert-danger text-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                            </div>
                            <div class="text-center">
                                <a href="forgot-password.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left me-1"></i>Request New Reset Link
                                </a>
                            </div>
                        <?php elseif ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="btn btn-success btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Proceed to Login
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="resetForm">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Create a new password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Must be at least <?php echo MIN_PASSWORD_LENGTH; ?> characters.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Confirm your new password" required>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle me-2"></i>Reset Password
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <p class="mb-0"><a href="login.php" class="text-decoration-none fw-bold"><i class="bi bi-arrow-left me-1"></i>Back to Login</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/app.js"></script>
</body>
</html>

