-- PHP Expense Tracking System Database Schema
-- Created for XAMPP MySQL

-- Create Database
CREATE DATABASE IF NOT EXISTS expense_tracking;
USE expense_tracking;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    verification_code VARCHAR(10) DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL, -- NULL means system default category
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    color VARCHAR(7) DEFAULT '#3788d8',
    icon VARCHAR(50) DEFAULT 'bi-tag',
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions Table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    description TEXT,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Categories (for all users)
INSERT INTO categories (name, type, color, icon, is_default) VALUES
('Salary', 'income', '#28a745', 'bi-cash-stack', 1),
('Freelance', 'income', '#20c997', 'bi-briefcase', 1),
('Investments', 'income', '#17a2b8', 'bi-graph-up', 1),
('Other Income', 'income', '#6c757d', 'bi-wallet2', 1),
('Food & Dining', 'expense', '#dc3545', 'bi-cup-straw', 1),
('Transportation', 'expense', '#fd7e14', 'bi-car-front', 1),
('Shopping', 'expense', '#e83e8c', 'bi-bag', 1),
('Entertainment', 'expense', '#6f42c1', 'bi-controller', 1),
('Bills & Utilities', 'expense', '#6610f2', 'bi-receipt', 1),
('Healthcare', 'expense', '#007bff', 'bi-heart-pulse', 1),
('Education', 'expense', '#17a2b8', 'bi-book', 1),
('Other Expense', 'expense', '#6c757d', 'bi-three-dots', 1);

