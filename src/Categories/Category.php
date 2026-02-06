<?php
namespace App\Categories;

class Category
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Get all categories
     */
    public function getAll()
    {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get categories by type
     */
    public function getByType($type)
    {
        $sql = "SELECT id, name FROM categories WHERE type = ? ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
}
