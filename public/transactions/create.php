<?php
require_once dirname(__DIR__, 2) . '/src/init.php';
require_once ROOT_PATH . '/src/Transactions/Transaction.php';
require_once ROOT_PATH . '/src/Categories/Category.php';

use App\Transactions\Transaction;
use App\Categories\Category;
use App\Core\Security;

requireLogin();

$pageTitle = 'Add Transaction';
$userId = $_SESSION['user_id'];
$catObj = new Category($pdo);
$categories = $catObj->getAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $data = [
        'type' => $_POST['type'],
        'category_id' => $_POST['category_id'],
        'amount' => $_POST['amount'],
        'description' => trim($_POST['description']),
        'date' => $_POST['date']
    ];

    // Simple Validation
    if (empty($data['amount']) || empty($data['date']) || empty($data['category_id'])) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        $transObj = new Transaction($pdo);
        if ($transObj->create($userId, $data)) {
            Security::redirect('/dashboard.php', 'Transaction added successfully!', 'success');
        }
        else {
            $errors[] = "Failed to add transaction. Please try again.";
        }
    }
}

require_once ROOT_PATH . '/public/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
                <h4 class="mb-0">Add New Transaction</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php
    endforeach; ?>
                        </ul>
                    </div>
                <?php
endif; ?>

                <form action="/transactions/create.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Transaction Type</label>
                            <select name="type" id="transType" class="form-select" required>
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="categorySelect" class="form-select" required>
                                <!-- Categories will be filtered by JS -->
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount ($)</label>
                            <input type="number" name="amount" step="0.01" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="What was this for?"></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/dashboard.php" class="btn btn-light px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-gradient px-5 shadow-sm">Save Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Dependent Dropdown Logic -->
<script>
const allCategories = <?php echo json_encode($categories); ?>;
const typeSelect = document.getElementById('transType');
const categorySelect = document.getElementById('categorySelect');

function filterCategories() {
    const selectedType = typeSelect.value;
    const filtered = allCategories.filter(cat => cat.type === selectedType);
    
    // Clear current options
    categorySelect.innerHTML = '';
    
    // Add new options
    filtered.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat.id;
        opt.textContent = cat.name;
        categorySelect.appendChild(opt);
    });
}

// Initial filter
filterCategories();

// Change event
typeSelect.addEventListener('change', filterCategories);
</script>

<?php require_once ROOT_PATH . '/public/includes/footer.php'; ?>
