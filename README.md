# Personal Finance & Expense Tracking System

This is a university project built with Core PHP, MySQL, and Bootstrap 5. It features secure authentication, transaction management, and visual data reporting.

## 🚀 Key Features
- **Secure Authentication**: Session-based login/register with 6-digit OTP verification.
- **Transaction Management**: CRUD functionality for Income and Expenses.
- **Dashboard**: Summary cards for Balance, Income, and Expenses.
- **Visual Analytics**: Interactive charts using Chart.js.
- **Security**: PDO Prepared Statements, CSRF tokens, and XSS protection.
- **UI/UX**: Bootstrap 5 with Dark Mode support and responsive design.

## 🛠️ Setup Instructions
1. **Database Setup**:
   - Create a database named `expense_tracker` in your MySQL server.
   - Import the `database.sql` file provided in the root directory.
2. **Configuration**:
   - Update `config/db.php` with your database credentials.
   - Update `config/constants.php` with your SMTP settings for email verification.
3. **Web Server**:
   - Point your document root to the `public/` directory or run from the root using:
     `php -S localhost:8000 -t public`

## 📋 Jira Activity / Task Log
*Below are the documented tasks for individual contribution verification:*

| Task ID | Component | Task Description |
|---------|-----------|------------------|
| EXT-01  | Infrastructure | Setup Directory Structure & Boilerplate |
| EXT-02  | Database | Design Normalized MySQL Schema (`database.sql`) |
| EXT-03  | Security | Implement CSRF protection & PDO Connection |
| EXT-04  | Auth | Create Login & OTP-based Registration |
| EXT-05  | CRUD | Implement Income/Expense CRUD logic |
| EXT-06  | Frontend | Dashboard UI with Bootstrap 5 & Dark Mode |
| EXT-07  | Reporting | Integrate Chart.js for Spending Trends |
| EXT-08  | UI/UX | Implement Dependent Dropdowns for Categories |
| EXT-09  | Polish | Custom 404 Error handling & Form Validations |

## 🔒 Security Measures
- **SQL Injection**: Prevented using PDO Prepared Statements.
- **XSS**: Prevented using `htmlspecialchars` sanitization on output.
- **CSRF**: tokens generated per session and validated on all POST requests.
- **Password Security**: Hashed using `password_hash()` with BCRYPT.

## 🎓 Oral Defense Notes
- **Modular Structure**: The logic is separated into `src/` while `public/` handles assets and routing.
- **Normalized Schema**: Users are linked to transactions via foreign keys to ensure data integrity.
- **SSR**: Server-Side Rendering ensures clean SEO and faster initial data load.
