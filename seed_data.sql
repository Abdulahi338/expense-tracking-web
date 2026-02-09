-- seed_data.sql
-- Population script for testing the Expense Tracker DB

-- 1. Clear existing test data (Optional, but helps with clean testing)
DELETE FROM expenses;
DELETE FROM income;
DELETE FROM users;
DELETE FROM categories;

-- 2. Populate Categories
INSERT INTO categories (name, type) VALUES 
('Salary', 'income'),
('Freelance', 'income'),
('Gift', 'income'),
('Investment', 'income'),
('Food', 'expense'),
('Transport', 'expense'),
('Rent', 'expense'),
('Utilities', 'expense'),
('Entertainment', 'expense'),
('Healthcare', 'expense'),
('Shopping', 'expense');

-- 3. Create 5 Users
-- Password for all users is "password123" (Hashed using bcrypt)
-- Hashed: $2y$10$8W3bFzL1q1VlR1L1X1L1OeG1q1q1q1q1q1q1q1q1q1q1q1q1q1q1q (Standard placeholder)
SET @pass = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

INSERT INTO users (username, email, password, role, is_verified) VALUES 
('mohamed_ali', 'mohamed@example.com', @pass, 'admin', 1),
('hodan_abdi', 'hodan@example.com', @pass, 'user', 1),
('ahmed_yusuf', 'ahmed@example.com', @pass, 'user', 1),
('leyla_osman', 'leyla@example.com', @pass, 'user', 1),
('abdullahi_farah', 'abdullahi@example.com', @pass, 'user', 1);

-- 4. Get the user IDs into variables
SELECT id INTO @u1 FROM users WHERE username = 'mohamed_ali';
SELECT id INTO @u2 FROM users WHERE username = 'hodan_abdi';
SELECT id INTO @u3 FROM users WHERE username = 'ahmed_yusuf';
SELECT id INTO @u4 FROM users WHERE username = 'leyla_osman';
SELECT id INTO @u5 FROM users WHERE username = 'abdullahi_farah';

-- 5. Helper procedure to insert random expenses (MySQL doesn't support loops easily in raw script without procedures)
-- For simplicity in a single .sql file, we will use hardcoded random-looking inserts.

-- Transactions for User 1
INSERT INTO expenses (user_id, amount, category, date, description) VALUES 
(@u1, 55.20, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Grocery shopping'),
(@u1, 15.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Bus fare'),
(@u1, 500.00, 'Rent', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Monthly rent'),
(@u1, 120.45, 'Utilities', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Electricity bill'),
(@u1, 45.00, 'Entertainment', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Movie night'),
(@u1, 89.99, 'Shopping', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'New shoes'),
(@u1, 12.50, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Lunch'),
(@u1, 30.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Gas refill'),
(@u1, 65.00, 'Healthcare', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Pharmacy'),
(@u1, 25.00, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Coffee');

-- Transactions for User 2
INSERT INTO expenses (user_id, amount, category, date, description) VALUES 
(@u2, 42.10, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Dinner'),
(@u2, 22.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Uber ride'),
(@u2, 450.00, 'Rent', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Room rent'),
(@u2, 95.00, 'Utilities', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Water bill'),
(@u2, 110.00, 'Shopping', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Department store'),
(@u2, 55.00, 'Entertainment', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Concert ticket'),
(@u2, 18.50, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Pizza delivery'),
(@u2, 40.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Train pass'),
(@u2, 35.00, 'Healthcare', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Checkup'),
(@u2, 12.00, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Snacks');

-- Transactions for User 3
INSERT INTO expenses (user_id, amount, category, date, description) VALUES 
(@u3, 75.30, 'Shopping', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Clothes'),
(@u3, 10.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Parking'),
(@u3, 480.00, 'Rent', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Apartment'),
(@u3, 60.00, 'Utilities', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Internet'),
(@u3, 33.00, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Fast food'),
(@u3, 150.00, 'Healthcare', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Dentist'),
(@u3, 20.00, 'Entertainment', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Video game'),
(@u3, 44.00, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Groceries'),
(@u3, 28.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Taxi'),
(@u3, 9.99, 'Entertainment', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Netflix');

-- Transactions for User 4
INSERT INTO expenses (user_id, amount, category, date, description) VALUES 
(@u4, 25.00, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Breakfast'),
(@u4, 300.00, 'Shopping', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Electronics'),
(@u4, 400.00, 'Rent', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Studio rent'),
(@u4, 80.00, 'Utilities', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Phone bill'),
(@u4, 50.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Gas'),
(@u4, 12.00, 'Entertainment', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'App store'),
(@u4, 66.40, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Restaurant'),
(@u4, 15.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Bus'),
(@u4, 90.00, 'Healthcare', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Vitamins'),
(@u4, 30.00, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Takeout');

-- Transactions for User 5
INSERT INTO expenses (user_id, amount, category, date, description) VALUES 
(@u5, 120.00, 'Entertainment', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Theme park'),
(@u5, 45.00, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Weekly groceries'),
(@u5, 500.00, 'Rent', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Home rent'),
(@u5, 70.00, 'Utilities', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Gas bill'),
(@u5, 35.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Toll fees'),
(@u5, 200.00, 'Shopping', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Gifts'),
(@u5, 22.50, 'Food', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Lunch with friends'),
(@u5, 18.00, 'Transport', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Metro card'),
(@u5, 40.00, 'Healthcare', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Optician'),
(@u5, 15.00, 'Entertainment', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY), 'Spotify');

-- 6. Add some Income records so the dashboards aren't empty
INSERT INTO income (user_id, amount, source, date, description) VALUES 
(@u1, 3500.00, 'Salary', DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'Main monthly salary'),
(@u2, 3200.00, 'Salary', DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'Main monthly salary'),
(@u3, 2800.00, 'Salary', DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'Main monthly salary'),
(@u4, 4000.00, 'Salary', DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'Main monthly salary'),
(@u5, 3600.00, 'Salary', DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'Main monthly salary'),
(@u1, 250.00, 'Freelance', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Logo design project'),
(@u2, 100.00, 'Gift', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Birthday gift');
