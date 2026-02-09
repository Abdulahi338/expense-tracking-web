<?php
// src/sidebar.php
?>
<!-- Mobile Header -->
<div class="mobile-header">
    <h4 class="m-0 text-white fw-bold">FinanceTracker</h4>
    <button class="btn text-white fs-3 p-0" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar d-flex flex-column p-3" id="mainSidebar">
    <h4 class="text-center mb-4 text-white fw-bold d-none d-lg-block">FinanceTracker</h4>
    <hr class="d-none d-lg-block">
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-house-door me-2"></i> Dashboard
            </a>
        </li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <li class="nav-item">
            <a href="admin_dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?> text-warning fw-bold">
                <i class="bi bi-shield-lock me-2"></i> Admin Panel
            </a>
        </li>
        <?php
endif; ?>
        <li>
            <a href="income.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'income.php' ? 'active' : ''; ?>">
                <i class="bi bi-cash-stack me-2"></i> Income
            </a>
        </li>
        <li>
            <a href="expenses.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'expenses.php' ? 'active' : ''; ?>">
                <i class="bi bi-cart me-2"></i> Expenses
            </a>
        </li>
        <li>
            <a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart me-2"></i> Reports
            </a>
        </li>
    </ul>
    <hr>
    <div class="form-check form-switch ms-3 mb-3">
        <input class="form-check-input" type="checkbox" id="darkModeToggle">
        <label class="form-check-label text-white" for="darkModeToggle">Dark Mode</label>
    </div>
    <a href="logout.php" class="btn btn-light w-100 fw-bold text-danger">Logout</a>
</div>

<script>
    // Sidebar Toggle Logic
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainSidebar = document.getElementById('mainSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        mainSidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }

    // Dark Mode Toggle Logic
    const toggle = document.getElementById('darkModeToggle');
    
    // Check for previous preference
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        if(toggle) toggle.checked = true;
    }

    if(toggle) {
        toggle.addEventListener('change', () => {
            if (toggle.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'true');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'false');
            }
        });
    }
</script>
