<?php
/**
 * Signup Page
 * Expense Tracking System
 */

// Start session and include configuration
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: ../dashboard/index.php');
    exit;
}

$error = '';
$success = '';

// Process signup form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all fields.';
        } elseif (strlen($name) < 2 || strlen($name) > MAX_NAME_LENGTH) {
            $error = 'Name must be between 2 and ' . MAX_NAME_LENGTH . ' characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < MIN_PASSWORD_LENGTH) {
            $error = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                if ($existingUser['is_verified'] == 0) {
                    // User exists but not verified - resend OTP
                    $otp = str_pad(random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
                    $stmt = $pdo->prepare("UPDATE users SET verification_code = ?, name = ?, password = ? WHERE id = ?");
                    $stmt->execute([$otp, $name, password_hash($password, PASSWORD_DEFAULT), $existingUser['id']]);
                    
                    $_SESSION['pending_user_id'] = $existingUser['id'];
                    $_SESSION['otp_resent'] = true;
                    header('Location: verify-otp.php');
                    exit;
                } else {
                    $error = 'This email is already registered. Please login instead.';
                }
            } else {
                // Create new user
                $otp = str_pad(random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_code) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $passwordHash, $otp]);
                
                $userId = $pdo->lastInsertId();
                
                // Store pending user ID in session
                $_SESSION['pending_user_id'] = $userId;
                $_SESSION['otp_resent'] = false;
                
                // In production, send email here with $otp
                // For demo purposes, we'll show it (remove in production)
                $_SESSION['demo_otp'] = $otp;
                
                header('Location: verify-otp.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo APP_NAME; ?></title>
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
                    <div class="card-header bg-success text-white text-center py-4">
                        <h3 class="mb-0"><i class="bi bi-wallet2 me-2"></i><?php echo APP_NAME; ?></h3>
                        <p class="small mb-0">Create your account</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="signupForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           placeholder="Enter your full name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Enter your email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Create a password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Must be at least <?php echo MIN_PASSWORD_LENGTH; ?> characters.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirm your password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-person-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Sign In</a></p>
                        </div>
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

