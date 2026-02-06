<?php
namespace App\Transactions;

use PDO;

class Transaction
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Get all transactions for a specific user
     */
    public function getAllByUser($userId)
    {
        $sql = "SELECT t.*, c.name as category_name 
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? 
                ORDER BY t.transaction_date DESC, t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get summary (Total income, total expense, balance)
     */
    public function getSummary($userId)
    {
        $sql = "SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
                FROM transactions 
                WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        $income = $result['total_income'] ?? 0;
        $expense = $result['total_expense'] ?? 0;

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense
        ];
    }

    /**
     * Create new transaction
     */
    public function create($userId, $data)
    {
        $sql = "INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $data['category_id'],
            $data['amount'],
            $data['type'],
            $data['description'],
            $data['date']
        ]);
    }

    /**
     * Update transaction
     */
    public function update($transactionId, $userId, $data)
    {
        $sql = "UPDATE transactions 
                SET category_id = ?, amount = ?, type = ?, description = ?, transaction_date = ? 
                WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['category_id'],
            $data['amount'],
            $data['type'],
            $data['description'],
            $data['date'],
            $transactionId,
            $userId
        ]);
    }

    /**
     * Delete transaction
     */
    public function delete($transactionId, $userId)
    {
        $sql = "DELETE FROM transactions WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$transactionId, $userId]);
    }

    /**
     * Get single transaction
     */
    public function getById($id, $userId)
    {
        $sql = "SELECT * FROM transactions WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    /**
     * Get category-wise expense distribution for Chart.js
     */
    public function getCategoryDistribution($userId)
    {
        $sql = "SELECT c.name, SUM(t.amount) as total 
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? AND t.type = 'expense' 
                GROUP BY c.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get monthly trends for Chart.js
     */
    public function getMonthlyTrends($userId)
    {
        $sql = "SELECT 
                DATE_FORMAT(transaction_date, '%b') as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
                FROM transactions 
                WHERE user_id = ? 
                AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
                ORDER BY transaction_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
