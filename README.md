# Personal Finance & Expense Tracker

A simple, student-friendly web application for university projects.

## Project Structure
- `/config` - Database connection settings.
- `/public` - Frontend pages, CSS, and JS assets.
- `/src` - Modular logic files (Auth, Functions, UI components).

## Setup Instructions

### 1. Database Setup
1. Open **phpMyAdmin**.
2. Create a new database named `expense_tracker_db`.
3. Import the `database.sql` file provided in the root directory.

### 2. PHPMailer Integration (Mandatory for OTP and Password Reset)
Since this project requires sending emails for OTP and Password Recovery, you need to install [PHPMailer](https://github.com/PHPMailer/PHPMailer).

**Manual Installation:**
1. Download PHPMailer as a ZIP.
2. Extract it into `src/PHPMailer`.
3. Update `src/auth_logic.php` to include the PHPMailer files and configure your SMTP settings (e.g., Gmail or Mailtrap).

### 3. Running the App
1. Place the `expense-tracking` folder in your `xampp/htdocs/` directory.
2. Start Apache and MySQL in XAMPP.
3. Open `http://localhost/expense-tracking/public/index.php` in your browser.

## Features
- **Secure Authentication:** Signup with OTP and Login with sessions.
- **Financial Tracking:** CRUD for Income and Expenses.
- **Visual Analytics:** Summary cards and interactive charts.
- **Modern UI:** Bootstrap 5, Blue gradients, and a Dark Mode toggle.
- **Security:** SQL Injection prevention (Prepared Statements), CSRF, and XSS protection.

## Lecturer Oral Defense Note
- All database queries use **Prepared Statements** to prevent SQL injection.
- **htmlspecialchars()** is used on all user-displayed data to prevent XSS.
- **CSRF Tokens** are used in forms to prevent cross-site request forgery.
- The logic is **procedural**, making it easy to explain line-by-line.
