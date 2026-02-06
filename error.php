<?php
/**
 * Custom Error Page
 * Expense Tracking System
 */

$errorCode = $_GET['code'] ?? 404;
$errorMessages = [
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Page Not Found',
    500 => 'Internal Server Error'
];

$errorMessage = $errorMessages[$errorCode] ?? 'Unknown Error';

// Get base URL
$baseUrl = 'http://localhost/expense-tracking';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $errorCode; ?> - Expense Tracker</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .error-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #667eea;
            line-height: 1;
        }
        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="error-card">
                    <div class="error-icon">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                    </div>
                    <div class="error-code"><?php echo $errorCode; ?></div>
                    <h2 class="mt-3"><?php echo $errorMessage; ?></h2>
                    <p class="text-muted mt-3">
                        Sorry, something went wrong. The page you're looking for doesn't exist or has been moved.
                    </p>
                    <div class="mt-4">
                        <a href="<?php echo $baseUrl; ?>/" class="btn btn-primary btn-home text-white">
                            <i class="bi bi-house-door me-2"></i>Go to Homepage
                        </a>
                    </div>
                    <div class="mt-4">
                        <a href="javascript:history.back()" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Go Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

