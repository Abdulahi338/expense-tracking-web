<?php
// public/verify_otp.php
include_once '../src/auth_logic.php';

if (!isset($_SESSION['pending_email'])) {
    header("Location: signup.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f4f7f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .otp-card { width: 100%; max-width: 400px; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="otp-card text-center">
        <h3>Verify Email</h3>
        <p class="text-muted">Enter the 6-digit OTP sent to your email.</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php
endif; ?>

        <form action="verify_otp.php" method="POST">
            <div class="mb-3">
                <input type="text" name="otp" class="form-control text-center fs-4" maxlength="6" placeholder="000000" required>
            </div>
            <button type="submit" name="verify_otp" class="btn btn-primary-gradient w-100 py-2">Verify OTP</button>
        </form>
        
        <div class="mt-3">
            <a href="signup.php" class="text-decoration-none">Back to Signup</a>
        </div>
    </div>

</body>
</html>
