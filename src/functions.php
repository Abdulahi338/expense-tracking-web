<?php
// src/functions.php
// This file contains reusable functions for security and general tasks.

// Secure the output to prevent XSS (Cross-Site Scripting)
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Generate CSRF Token for form security
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Check CSRF Token
function verify_csrf_token($token)
{
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

// Check if user is logged in
function check_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Check if user is an admin
function check_admin()
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Redirect to standard dashboard with an error message
        header("Location: dashboard.php?error=unauthorized");
        exit();
    }
}

// Format currency
function format_currency($amount)
{
    return "$" . number_format($amount, 2);
}
?>
