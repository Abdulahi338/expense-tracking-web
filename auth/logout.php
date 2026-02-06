<?php
/**
 * Logout Handler
 * Expense Tracking System
 */

// Start session
require_once __DIR__ . '/../config/session.php';

// Destroy session
$_SESSION = array();
session_destroy();

// Redirect to login page
header('Location: ../auth/login.php');
exit;

