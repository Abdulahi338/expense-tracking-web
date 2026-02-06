<?php
require_once dirname(__DIR__, 2) . '/src/init.php';
require_once ROOT_PATH . '/src/Core/Security.php';

use App\Core\Security;

Security::startSession();
session_unset();
session_destroy();

// Start a new session for flash message
Security::startSession();
Security::redirect('/auth/login.php', 'You have been logged out.', 'info');
