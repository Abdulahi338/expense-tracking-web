<?php
require_once dirname(__DIR__, 2) . '/src/init.php';
require_once ROOT_PATH . '/src/Transactions/Transaction.php';

use App\Transactions\Transaction;
use App\Core\Security;

requireLogin();

if (isset($_GET['id'])) {
    $transId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    $transObj = new Transaction($pdo);
    if ($transObj->delete($transId, $userId)) {
        Security::redirect('/transactions/index.php', 'Transaction deleted successfully!', 'success');
    }
    else {
        Security::redirect('/transactions/index.php', 'Failed to delete transaction.', 'danger');
    }
}
else {
    Security::redirect('/transactions/index.php');
}
