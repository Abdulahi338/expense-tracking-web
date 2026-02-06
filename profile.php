<?php
/**
 * User Profile Page
 * Expense Tracking System
 */

// Include configuration and require login
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/constants.php';

requireLogin();

$userId = getCurrentUserId();
$pageTitle = 'Profile';
$error = '';
$success = '';

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'User not found.');
    header('Location: dashboard/index.php');
    exit;
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name) || strlen($name) < 2 || strlen($name) > MAX_NAME_LENGTH) {
            $error = 'Name must be between 2 and ' . MAX_NAME_LENGTH . ' characters.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$name, $userId]);
            
            $_SESSION['user_name'] = $name;
            $user['name'] = $name;
            
            setFlash('success', 'Profile updated successfully!');
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Please fill in all password fields.';
        } elseif (strlen($newPassword) < MIN_PASSWORD_LENGTH) {
            $error = 'New password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$newPasswordHash, $userId]);
            
            setFlash('success', 'Password changed successfully!');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include_once __DIR__ . '/components/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1 class="h3 mb-0"><i class="bi bi-person-gear me-2"></i>Profile Settings</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <!-- Profile Info Card -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <div class="user-avatar mx-auto" style="width: 100px; height: 100px; font-size: 3rem; background: var(--primary-color);">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            </div>
                            <h4 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                            
                            <?php if ($user['is_verified']): ?>
                                <span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Unverified</span>
                            <?php endif; ?>
                            
                            <hr class="my-4">
                            
                            <div class="text-start">
                                <p class="mb-2"><strong>Member since:</strong> <?php echo date(DISPLAY_DATE_FORMAT, strtotime($user['created_at'])); ?></p>
                                <p class="mb-0"><strong>Last updated:</strong> <?php echo date(DISPLAY_DATE_FORMAT, strtotime($user['updated_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Settings Forms -->
                <div class="col-lg-8">
                    <!-- Update Profile -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-person-edit me-2"></i>Update Profile</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    <div class="form-text">Email cannot be changed.</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Must be at least <?php echo MIN_PASSWORD_LENGTH; ?> characters.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-key-fill me-1"></i>Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Include Footer -->
    <?php include_once __DIR__ . '/components/footer.php'; ?>
</body>
</html>

