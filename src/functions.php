<?php
// src/functions.php
// This file contains reusable functions for security and general tasks.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Path to autoload.php relative to this file
require_once __DIR__ . '/../vendor/autoload.php';

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

/**
 * Sends an email notification to the user when a new expense is added.
 * 
 * @param string $userEmail The recipient's email address.
 * @param float $expenseAmount The amount of the expense.
 * @param string $category The category of the expense.
 * @return bool True on success, false on failure.
 */
function sendExpenseEmail($userEmail, $expenseAmount, $category)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->SMTPDebug = 2; // Enable verbose debug output
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = 'cabdulahii723@gmail.com'; // IMPORTANT: Check if this is your Mailtrap username
        $mail->Password = 'Abdulahii1234321@'; // IMPORTANT: Check if this is your Mailtrap password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525; // Alternative port often better for Mailtrap

        // Recipients
        $mail->setFrom('no-reply@expense-tracker.local', 'Expense Tracker');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Expense Notification';

        $formattedAmount = format_currency($expenseAmount);

        // HTML Body
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <div style='background-color: #6c5ce7; padding: 15px; border-radius: 8px 8px 0 0; text-align: center;'>
                    <h2 style='color: #ffffff; margin: 0;'>Expense Added!</h2>
                </div>
                <div style='padding: 20px; color: #333;'>
                    <p>Hello,</p>
                    <p>This is a notification that a new expense has been successfully added to your account.</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p><strong>Category:</strong> <span style='color: #6c5ce7;'>$category</span></p>
                    <p><strong>Amount:</strong> <span style='font-size: 1.2em; font-weight: bold;'>$formattedAmount</span></p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p>Keep track of your spending and stay on top of your budget!</p>
                </div>
                <div style='text-align: center; color: #888; font-size: 0.8em; padding-top: 10px;'>
                    <p>&copy; " . date('Y') . " Expense Tracker System. All rights reserved.</p>
                </div>
            </div>
        ";

        // Plain text version for non-HTML mail clients
        $mail->AltBody = "New Expense Added!\n\nCategory: $category\nAmount: $formattedAmount\n\nThank you for using Expense Tracker.";

        $mail->send();
        return true;
    }
    catch (Exception $e) {
        // According to requirements: display the ErrorInfo if it fails.
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>
