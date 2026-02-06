<?php
/**
 * Security Helper Class
 * Handles CSRF protection, XSS prevention, and session management.
 */

namespace App\Core;

class Security
{
    /**
     * Start session if not already started
     */
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Generate a CSRF token and store it in the session
     */
    public static function generateCSRFToken()
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token)
    {
        self::startSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitize output for XSS prevention
     */
    public static function sanitize($data)
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Redirect with a message
     */
    public static function redirect($url, $message = null, $type = 'success')
    {
        self::startSession();
        if ($message) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        header("Location: $url");
        exit();
    }
}
