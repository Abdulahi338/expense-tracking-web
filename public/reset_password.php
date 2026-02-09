<?php
// public/reset_password.php
session_start();
include_once '../config/db.php';
include_once '../src/functions.php';

if (isset($_GET['token'])) {
    $token = $_GET['token']; // Get raw token for validation

    // Validate token against the dedicated table
    // We remove the NOW() check temporarily for debugging or use a very long expiry
    $query = "SELECT * FROM password_resets WHERE token = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($reset = mysqli_fetch_assoc($result)) {
        // Now check if it's expired
        $expiry = strtotime($reset['expires_at']);
        $now = time();

        if ($now > $expiry) {
            $error_msg = "This reset link has expired. (DB: " . $reset['expires_at'] . " | Current: " . date("Y-m-d H:i:s") . ")";
        }
        else {
            // Token is valid!
            if (isset($_POST['update_password'])) {
                $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $email = $reset['email'];

                // Update user password
                $update_query = "UPDATE users SET password = ? WHERE email = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "ss", $new_password, $email);
                mysqli_stmt_execute($stmt);

                // Clean up: Delete used token
                $delete_query = "DELETE FROM password_resets WHERE email = ?";
                $stmt = mysqli_prepare($conn, $delete_query);
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);

                header("Location: login.php?reset=success");
                exit();
            }
        }
    }
    else {
        $error_msg = "Invalid token. No matching record found in database for: " . htmlspecialchars($token);
    }
}
else {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f4f7f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .reset-card { width: 100%; max-width: 400px; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="reset-card">
        <h3 class="text-center mb-4">Set New Password</h3>
        
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger text-center">
                <?php echo $error_msg; ?>
                <br>
                <a href="forgot_password.php" class="btn btn-sm btn-outline-danger mt-2">Request New Link</a>
            </div>
        <?php
else: ?>
            <form action="reset_password.php?token=<?php echo $token; ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-primary-gradient w-100">Update Password</button>
            </form>
        <?php
endif; ?>
    </div>

</body>
</html>
