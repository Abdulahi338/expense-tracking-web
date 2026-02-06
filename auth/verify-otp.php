<?php
/**
 * OTP Verification Page
 * Expense Tracking System
 */

// Start session and include configuration
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

// Check if pending user exists
if (!isset($_SESSION['pending_user_id'])) {
    header('Location: signup.php');
    exit;
}

$error = '';
$success = '';

// Get user info
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['pending_user_id']]);
$pendingUser = $stmt->fetch();

if (!$pendingUser) {
    unset($_SESSION['pending_user_id']);
    header('Location: signup.php');
    exit;
}

// Process OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $otp = $_POST['otp'] ?? '';
        
        // Validate OTP
        if (empty($otp) || strlen($otp) !== OTP_LENGTH || !ctype_digit($otp)) {
            $error = 'Please enter a valid ' . OTP_LENGTH . '-digit OTP.';
        } else {
            // Verify OTP
            $stmt = $pdo->prepare("SELECT id, verification_code FROM users WHERE id = ? AND verification_code = ?");
            $stmt->execute([$_SESSION['pending_user_id'], $otp]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Mark user as verified
                $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
                $stmt->execute([$_SESSION['pending_user_id']]);
                
                // Auto-login user
                $_SESSION['user_id'] = $_SESSION['pending_user_id'];
                $_SESSION['user_name'] = $pendingUser['name'];
                $_SESSION['user_email'] = $pendingUser['email'];
                
                unset($_SESSION['pending_user_id']);
                unset($_SESSION['demo_otp']);
                
                setFlash('success', 'Account verified successfully! Welcome to ' . APP_NAME . '.');
                header('Location: ../dashboard/index.php');
                exit;
            } else {
                $error = 'Invalid OTP. Please try again.';
            }
        }
    }
}

// Resend OTP
if (isset($_POST['resend'])) {
    $otp = str_pad(random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
    $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE id = ?");
    $stmt->execute([$otp, $_SESSION['pending_user_id']]);
    
    // In production, send email here
    $_SESSION['demo_otp'] = $otp;
    
    $success = 'OTP sent successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - <?php echo APP_NAME; ?></title>
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
                        <h3 class="mb-0"><i class="bi bi-shield-check me-2"></i>Email Verification</h3>
                        <p class="small mb-0">Enter the OTP sent to your email</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['demo_otp'])): ?>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <strong>Demo Mode:</strong> Your OTP is: <strong><?php echo $_SESSION['demo_otp']; ?></strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="bi bi-envelope-check text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <p class="text-muted">
                                We've sent a <?php echo OTP_LENGTH; ?>-digit verification code to<br>
                                <strong><?php echo htmlspecialchars($pendingUser['email']); ?></strong>
                            </p>
                        </div>
                        
                        <form method="POST" action="" id="otpForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-4">
                                <label for="otp" class="form-label">Enter OTP</label>
                                <div class="otp-inputs d-flex gap-2 justify-content-center">
                                    <?php for ($i = 0; $i < OTP_LENGTH; $i++): ?>
                                        <input type="text" class="form-control otp-digit text-center" 
                                               name="otp_digits[]" maxlength="1" pattern="[0-9]" 
                                               inputmode="numeric" style="width: 45px;" required>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="otp" id="otpValue">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Verify OTP
                                </button>
                            </div>
                        </form>
                        
                        <form method="POST" action="" class="mt-3">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="resend" value="1">
                            <div class="text-center">
                                <p class="mb-2">Didn't receive the code?</p>
                                <button type="submit" class="btn btn-link p-0">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Resend OTP
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/app.js"></script>
    <script>
        // OTP input handling
        document.addEventListener('DOMContentLoaded', function() {
            const otpInputs = document.querySelectorAll('.otp-digit');
            const otpForm = document.getElementById('otpForm');
            const otpValue = document.getElementById('otpValue');
            
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    const value = this.value;
                    
                    if (value.length > 0) {
                        if (index < otpInputs.length - 1) {
                            otpInputs[index + 1].focus();
                        }
                    }
                    
                    // Update hidden field
                    updateOtpValue();
                });
                
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                });
                
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = e.clipboardData.getData('text');
                    const digits = paste.replace(/\D/g, '').split('').slice(0, <?php echo OTP_LENGTH; ?>);
                    
                    digits.forEach((digit, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = digit;
                            if (i < otpInputs.length - 1) {
                                otpInputs[i + 1].focus();
                            }
                        }
                    });
                    
                    updateOtpValue();
                });
            });
            
            function updateOtpValue() {
                otpValue.value = Array.from(otpInputs).map(input => input.value).join('');
            }
        });
    </script>
</body>
</html>

