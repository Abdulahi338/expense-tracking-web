<?php
require_once dirname(__DIR__, 2) . '/src/init.php';
require_once ROOT_PATH . '/src/Auth/User.php';

use App\Core\Security;
use App\Auth\User;

$pageTitle = 'Register';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if (empty($errors)) {
        $userObj = new User($pdo);
        $result = $userObj->register($username, $email, $password);

        if ($result['status']) {
            // Store email in session for OTP verification
            $_SESSION['verify_email'] = $email;
            // In a real app, send email here with $result['otp']
            // For now, we'll simulate it for the demo
            $_SESSION['demo_otp'] = $result['otp'];

            Security::redirect('/auth/verify.php', 'Registration successful! Please enter the 6-digit OTP sent to your email.', 'success');
        }
        else {
            $errors[] = $result['message'];
        }
    }
}

require_once ROOT_PATH . '/public/includes/header.php';
?>

<div class="auth-container">
    <div class="card shadow">
        <div class="card-body p-4">
            <h2 class="text-center mb-4">Create Account</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo Security::sanitize($error); ?></li>
                        <?php
    endforeach; ?>
                    </ul>
                </div>
            <?php
endif; ?>

            <form action="/auth/register.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo isset($username) ?Security::sanitize($username) : ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo isset($email) ?Security::sanitize($email) : ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-text">Minimum 8 characters.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary-gradient w-100 py-2 rounded-pill shadow-sm">Sign Up</button>
            </form>
            
            <div class="text-center mt-4">
                <p class="mb-0">Already have an account? <a href="/auth/login.php">Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/public/includes/footer.php'; ?>
