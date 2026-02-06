<?php
/**
 * Sidebar Component (Sticky Navigation)
 * Expense Tracking System
 */

requireLogin();
$currentPage = basename($_SERVER['PHP_SELF']);
$user = getCurrentUser($pdo);
?>
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo APP_URL; ?>/dashboard/index.php" class="text-decoration-none">
            <i class="bi bi-wallet2 fs-4"></i>
            <span class="sidebar-title"><?php echo APP_NAME; ?></span>
        </a>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="bi bi-person-circle"></i>
        </div>
        <div class="user-info">
            <h6 class="user-name mb-0"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h6>
            <small class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></small>
        </div>
    </div>
    
    <ul class="nav flex-column sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>" 
               href="<?php echo APP_URL; ?>/dashboard/index.php">
                <i class="bi bi-speedometer2 me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'index.php' && strpos($_SERVER['REQUEST_URI'], 'transactions') !== false) ? 'active' : ''; ?>" 
               href="<?php echo APP_URL; ?>/transactions/index.php">
                <i class="bi bi-cash-stack me-2"></i>
                <span>Transactions</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'index.php' && strpos($_SERVER['REQUEST_URI'], 'categories') !== false) ? 'active' : ''; ?>" 
               href="<?php echo APP_URL; ?>/categories/index.php">
                <i class="bi bi-tags me-2"></i>
                <span>Categories</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'index.php' && strpos($_SERVER['REQUEST_URI'], 'reports') !== false) ? 'active' : ''; ?>" 
               href="<?php echo APP_URL; ?>/reports/index.php">
                <i class="bi bi-bar-chart-line me-2"></i>
                <span>Reports</span>
            </a>
        </li>
        
        <li class="nav-divider"></li>
        
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                <i class="bi bi-plus-circle me-2"></i>
                <span>Add Transaction</span>
            </a>
        </li>
        
        <li class="nav-divider"></li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>" 
               href="<?php echo APP_URL; ?>/profile.php">
                <i class="bi bi-person-gear me-2"></i>
                <span>Profile</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="#" onclick="confirmLogout(); return false;">
                <i class="bi bi-box-arrow-right me-2"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <small class="text-muted"><?php echo APP_NAME; ?> v1.0</small>
    </div>
</nav>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to logout?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo APP_URL; ?>/auth/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Logout',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i>Logout',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo APP_URL; ?>/auth/logout.php';
        }
    });
}
</script>

