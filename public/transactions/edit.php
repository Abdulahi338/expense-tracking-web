<?php
require_once dirname(__DIR__, 2) . '/src/init.php';
require_once ROOT_PATH . '/src/Transactions/Transaction.php';
require_once ROOT_PATH . '/src/Categories/Category.php';

use App\Transactions\Transaction;
use App\Categories\Category;
use App\Core\Security;

requireLogin();

$pageTitle = 'Edit Transaction';
$userId = $_SESSION['user_id'];
$transId = $_GET['id'] ?? null;

if (!$transId) {
    header('Location: /transactions/index.php');
    exit();
}

$transObj = new Transaction($pdo);
$transaction = $transObj->getById($transId, $userId);

if (!$transaction) {
    Security::redirect('/transactions/index.php', 'Transaction not found.', 'danger');
}

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

    if (empty($data['amount']) || empty($data['date']) || empty($data['category_id'])) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        if ($transObj->update($transId, $userId, $data)) {
            Security::redirect('/transactions/index.php', 'Transaction updated successfully!', 'success');
        }
        else {
            $errors[] = "Failed to update transaction.";
        }
    }
}

require_once ROOT_PATH . '/public/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
                <h4 class="mb-0">Edit Transaction</h4>
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

                <form action="/transactions/edit.php?id=<?php echo $transId; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Transaction Type</label>
                            <select name="type" id="transType" class="form-select" required>
                                <option value="expense" <?php echo $transaction['type'] === 'expense' ? 'selected' : ''; ?>>Expense</option>
                                <option value="income" <?php echo $transaction['type'] === 'income' ? 'selected' : ''; ?>>Income</option>
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
                            <input type="number" name="amount" step="0.01" class="form-control" value="<?php echo $transaction['amount']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo $transaction['transaction_date']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo Security::sanitize($transaction['description']); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/transactions/index.php" class="btn btn-light px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-gradient px-5 shadow-sm">Update Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const allCategories = <?php echo json_encode($categories); ?>;
const typeSelect = document.getElementById('transType');
const categorySelect = document.getElementById('categorySelect');
const currentCategoryId = <?php echo $transaction['category_id']; ?>;

function filterCategories() {
    const selectedType = typeSelect.value;
    const filtered = allCategories.filter(cat => cat.type === selectedType);
    
    categorySelect.innerHTML = '';
    
    filtered.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat.id;
        opt.textContent = cat.name;
        if (cat.id == currentCategoryId) opt.selected = true;
        categorySelect.appendChild(opt);
    });
}

filterCategories();
typeSelect.addEventListener('change', filterCategories);
</script>

<?php require_once ROOT_PATH . '/public/includes/footer.php'; ?>
