<?php
// src/auth_logic.php
// This file handles the logic for user login, signup, and logout.

session_start();
include_once '../config/db.php';
include_once 'functions.php';

// --- Signup Logic ---
if (isset($_POST['signup'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $otp = rand(100000, 999999); // 6-digit OTP

    // Check if email already exists
    $check_query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $error = "Email already registered!";
    }
    else {
        // Insert new user
        $insert_query = "INSERT INTO users (username, email, password, otp) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password, $otp);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['pending_email'] = $email;

            // Here you would normally send the email using PHPMailer.
            // Example: send_otp_email($email, $otp);

            header("Location: verify_otp.php");
            exit();
        }
        else {
            $error = "Registration failed: " . mysqli_error($conn);
        }
    }
}

// --- Login Logic ---
if (isset($_POST['login'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if ($user['is_verified'] == 0) {
            $error = "Please verify your email first.";
        }
        elseif (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            }
            else {
                header("Location: dashboard.php");
            }
            exit();
        }
        else {
            $error = "Invalid password!";
        }
    }
    else {
        $error = "No user found with this email.";
    }
}

// --- OTP Verification Logic ---
if (isset($_POST['verify_otp'])) {
    $email = $_SESSION['pending_email'];
    $user_otp = sanitize($_POST['otp']);

    $query = "SELECT * FROM users WHERE email = ? AND otp = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $email, $user_otp);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Update user as verified
        $update_query = "UPDATE users SET is_verified = 1, otp = NULL WHERE email = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "s", $email);
        mysqli_stmt_execute($update_stmt);

        unset($_SESSION['pending_email']);
        header("Location: login.php?verified=1");
        exit();
    }
    else {
        $error = "Invalid OTP!";
    }
}
?>
