<?php
// public/reset_password.php
session_start();
include_once '../config/db.php';
include_once '../src/functions.php';

if (isset($_GET['token'])) {
    $token = sanitize($_GET['token']);

    // Validate token
    $query = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($reset = mysqli_fetch_assoc($result)) {
        if (isset($_POST['update_password'])) {
            $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $email = $reset['email'];

            // Update user password
            $update_query = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ss", $new_password, $email);
            mysqli_stmt_execute($stmt);

            // Delete token
            $delete_query = "DELETE FROM password_resets WHERE email = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            header("Location: login.php?reset=success");
            exit();
        }
    }
    else {
        die("Invalid or expired token.");
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
        
        <form action="reset_password.php?token=<?php echo $token; ?>" method="POST">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="update_password" class="btn btn-primary-gradient w-100">Update Password</button>
        </form>
    </div>

</body>
</html>
