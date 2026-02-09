<?php
// public/forgot_password.php
session_start();
include_once '../config/db.php';
include_once '../src/functions.php';

if (isset($_POST['reset_request'])) {
    $email = sanitize($_POST['email']);

    // Check if email exists
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+24 hours")); // Long expiry for development

        // Delete any old tokens for this email first
        $clear_query = "DELETE FROM password_resets WHERE email = ?";
        $stmt = mysqli_prepare($conn, $clear_query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        // Store the token in the dedicated table
        $insert_query = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "sss", $email, $token, $expires);
        mysqli_stmt_execute($stmt);

        $reset_link = "http://localhost/expense-tracking/expense-tracking-web/public/reset_password.php?token=" . $token;

        // Display the link directly for local development (PHPMailer bypassed)
        $msg = "A reset link has been generated! (Develop Mode): <a href='$reset_link' class='fw-bold'>Click Here to Reset Password</a>";
    }
    else {
        $error = "No user found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f4f7f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .reset-card { width: 100%; max-width: 400px; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="reset-card">
        <h3 class="text-center mb-4">Reset Password</h3>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php
endif; ?>
        
        <?php if (isset($msg)): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php
endif; ?>

        <form action="forgot_password.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" name="reset_request" class="btn btn-primary-gradient w-100 mb-3">Send Reset Link</button>
            <div class="text-center">
                <a href="login.php" class="text-decoration-none">Back to Login</a>
            </div>
        </form>
    </div>

</body>
</html>
