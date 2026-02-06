<?php
/**
 * Root Index - Redirect to Dashboard or Login
 * Expense Tracking System
 */

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: dashboard/index.php');
} else {
    header('Location: auth/login.php');
}
exit;

