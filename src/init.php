<?php
/**
 * Global Initialization File
 */

// Define absolute path
define('ROOT_PATH', dirname(__DIR__));

// Require Config and Security
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/src/Core/Security.php';

use App\Core\Security;

// Start Session
Security::startSession();

// Helper to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        Security::redirect('/auth/login.php', 'Please login to access this page.', 'warning');
    }
}
