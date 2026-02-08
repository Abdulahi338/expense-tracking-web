<?php
// public/admin_dashboard.php
session_start();
include_once '../config/db.php';
include_once '../src/functions.php';

// Check if user is logged in AND is an admin
check_login();
check_admin();

// --- Handle User Management ---
if (isset($_POST['add_user'])) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = sanitize($_POST['role']);

    $query = "INSERT INTO users (username, email, password, role, is_verified) VALUES (?, ?, ?, ?, 1)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password, $role);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: admin_dashboard.php?success=user_added");
        exit();
    }
}

// --- Handle Role Update ---
if (isset($_POST['update_role'])) {
    $user_id_to_update = sanitize($_POST['user_id']);
    $new_role = sanitize($_POST['role']);

    // Prevent admin from changing their own role (safety first!)
    if ($user_id_to_update != $_SESSION['user_id']) {
        $query = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $new_role, $user_id_to_update);
        mysqli_stmt_execute($stmt);
        header("Location: admin_dashboard.php?success=role_updated");
        exit();
    }
}

if (isset($_GET['delete_user'])) {
    $id = sanitize($_GET['delete_user']);
    // Prevent admin from deleting themselves
    if ($id != $_SESSION['user_id']) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }
}

// --- Handle Category Management ---
if (isset($_POST['add_category'])) {
    $name = sanitize($_POST['name']);
    $type = sanitize($_POST['type']);

    $query = "INSERT INTO categories (name, type) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $name, $type);
    mysqli_stmt_execute($stmt);
}

// --- Handle Category Update ---
if (isset($_POST['update_category'])) {
    $cat_id = sanitize($_POST['cat_id']);
    $name = sanitize($_POST['name']);
    $type = sanitize($_POST['type']);

    $query = "UPDATE categories SET name = ?, type = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $name, $type, $cat_id);
    mysqli_stmt_execute($stmt);
    header("Location: admin_dashboard.php?success=cat_updated");
    exit();
}

if (isset($_GET['delete_cat'])) {
    $id = sanitize($_GET['delete_cat']);
    $query = "DELETE FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}

// Get data for display
$users_res = mysqli_query($conn, "SELECT * FROM users");
$cats_res = mysqli_query($conn, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - FinanceTracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include_once '../src/sidebar.php'; ?>

    <div class="main-content">
        <!-- Distinct Admin Header -->
        <div class="header-gradient shadow-sm d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-5 fw-bold mb-0">Admin Dashboard</h1>
                <p class="lead mb-0">System-wide Management & Control Panel</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm">Administrator Access</span>
            </div>
        </div>

        <div class="row">
            <!-- User Management -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">User Management</h5>
                    </div>
                    <div class="card-body">
                        <form action="admin_dashboard.php" method="POST" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <input type="text" name="username" class="form-control" placeholder="Username" value="<?php echo isset($_POST['add_user']) ? sanitize($_POST['username']) : ''; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo isset($_POST['add_user']) ? sanitize($_POST['email']) : ''; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="col-md-2">
                                <select name="role" class="form-select">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" name="add_user" class="btn btn-primary-gradient"><i class="bi bi-plus"></i></button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($users_res)): ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td><strong><?php echo sanitize($row['username']); ?></strong></td>
                                        <td><?php echo sanitize($row['email']); ?></td>
                                        <td>
                                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                <form action="admin_dashboard.php" method="POST" class="d-flex gap-2">
                                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                    <select name="role" class="form-select form-select-sm" style="width: auto;">
                                                        <option value="user" <?php echo $row['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                                        <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                    </select>
                                                    <button type="submit" name="update_role" class="btn btn-sm btn-outline-primary"><i class="bi bi-save"></i></button>
                                                </form>
                                            <?php
    else: ?>
                                                <span class="badge bg-danger">ADMIN (YOU)</span>
                                            <?php
    endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                <a href="admin_dashboard.php?delete_user=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php
    endif; ?>
                                        </td>
                                    </tr>
                                    <?php
endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Management -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Categories</h5>
                    </div>
                    <div class="card-body">
                        <form action="admin_dashboard.php" method="POST" class="mb-4">
                            <div class="mb-3">
                                <input type="text" name="name" class="form-control" placeholder="Category Name" required>
                            </div>
                            <div class="mb-3">
                                <select name="type" class="form-select">
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                </select>
                            </div>
                            <button type="submit" name="add_category" class="btn btn-primary-gradient w-100">Add Category</button>
                        </form>

                        <ul class="list-group list-group-flush">
                            <?php while ($row = mysqli_fetch_assoc($cats_res)): ?>
                            <li class="list-group-item p-3">
                                <form action="admin_dashboard.php" method="POST" class="row g-2 align-items-center">
                                    <input type="hidden" name="cat_id" value="<?php echo $row['id']; ?>">
                                    <div class="col-6">
                                        <input type="text" name="name" class="form-control form-control-sm" value="<?php echo sanitize($row['name']); ?>" required>
                                    </div>
                                    <div class="col-3">
                                        <select name="type" class="form-select form-select-sm">
                                            <option value="expense" <?php echo $row['type'] == 'expense' ? 'selected' : ''; ?>>Exp</option>
                                            <option value="income" <?php echo $row['type'] == 'income' ? 'selected' : ''; ?>>Inc</option>
                                        </select>
                                    </div>
                                    <div class="col-3 d-flex gap-1">
                                        <button type="submit" name="update_category" class="btn btn-sm btn-outline-primary" title="Update"><i class="bi bi-save"></i></button>
                                        <a href="admin_dashboard.php?delete_cat=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')" title="Delete"><i class="bi bi-trash"></i></a>
                                    </div>
                                </form>
                            </li>
                            <?php
endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>
