<?php
/**
 * Footer Component
 * Expense Tracking System
 */
?>
    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo APP_URL; ?>/transactions/add.php" method="POST" id="addTransactionForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="typeIncome" value="income" checked>
                                <label class="btn btn-outline-success" for="typeIncome">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Income
                                </label>
                                <input type="radio" class="btn-check" name="type" id="typeExpense" value="expense">
                                <label class="btn btn-outline-danger" for="typeExpense">
                                    <i class="bi bi-arrow-up-circle me-1"></i>Expense
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php
                                $categories = $pdo->prepare("SELECT * FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY name");
                                $categories->execute([getCurrentUserId()]);
                                while ($cat = $categories->fetch()):
                                ?>
                                    <option value="<?php echo $cat['id']; ?>" data-type="<?php echo $cat['type']; ?>">
                                        <i class="<?php echo $cat['icon']; ?>"></i> <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo CURRENCY_SYMBOL; ?></span>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       step="0.01" min="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="transaction_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="transaction_date" name="transaction_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Save Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo APP_URL; ?>/assets/js/app.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/charts.js"></script>
    
    <?php
    // Display flash messages
    $flash = displayFlash();
    if ($flash):
    ?>
    <script>
        Swal.fire({
            icon: '<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>',
            title: '<?php echo ucfirst($flash['type']); ?>',
            text: '<?php echo addslashes($flash['message']); ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    </script>
    <?php endif; ?>
</body>
</html>

