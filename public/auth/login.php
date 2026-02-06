<?php
require_once dirname(__DIR__, 2) . '/src/init.php';
require_once ROOT_PATH . '/src/Auth/User.php';

use App\Core\Security;
use App\Auth\User;

$pageTitle = 'Login';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit();
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    }
    else {
        $userObj = new User($pdo);
        $result = $userObj->login($email, $password);

        if ($result['status']) {
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['email'] = $result['user']['email'];

            Security::redirect('/dashboard.php', 'Welcome back, ' . $result['user']['username'] . '!', 'success');
        }
        else {
            if (isset($result['unverified'])) {
                $_SESSION['verify_email'] = $email;
                Security::redirect('/auth/verify.php', $result['message'], 'warning');
            }
            $error = $result['message'];
        }
    }
}

require_once ROOT_PATH . '/public/includes/header.php';
?>

<div class="auth-container">
    <div class="card shadow">
        <div class="card-body p-4">
            <h2 class="text-center mb-4">Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?php echo $error; ?></div>
            <?php
endif; ?>

            <form action="/auth/login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo isset($email) ?Security::sanitize($email) : ''; ?>" required autofocus>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Password</label>
                        <a href="/auth/forgot-password.php" class="small">Forgot Password?</a>
                    </div>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary-gradient w-100 py-2 rounded-pill shadow-sm">Login</button>
            </form>
            
            <div class="text-center mt-4">
                <p class="mb-0">Don't have an account? <a href="/auth/register.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/public/includes/footer.php'; ?>
