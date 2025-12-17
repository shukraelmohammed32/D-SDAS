SET FOREIGN_KEY_CHECKS = 0; -- Disable checks to allow dropping/creating tables cleanly

-- 1. USERS (Just Identity & Rank)
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    
    -- THE CRITICAL METRIC
    admission_rank INT, -- Example: Rank #450
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

-- 2. DEPARTMENTS (The Options)
DROP TABLE IF EXISTS departments;
CREATE TABLE departments (
    dept_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL UNIQUE, -- e.g., "Computer Science"
    base_capacity INT NOT NULL, -- e.g., 200 seats
    current_locked INT DEFAULT 0 -- Live counter for speed
);

-- 3. ADMISSION ROUNDS (The Time Windows)
DROP TABLE IF EXISTS admission_rounds;
CREATE TABLE admission_rounds (
    round_id INT AUTO_INCREMENT PRIMARY KEY,
    round_name VARCHAR(50) NOT NULL, -- e.g., "Round 1 - Merit"
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('scheduled', 'active', 'sealed', 'completed') DEFAULT 'scheduled'
);

-- 4. ALLOCATIONS (The Student's Choice)
DROP TABLE IF EXISTS allocations;
CREATE TABLE allocations (
    allocation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    dept_id INT,
    round_id INT,
    
    -- COOLDOWN TRACKING
    locked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_finalized BOOLEAN DEFAULT FALSE, -- True when round closes

    -- FOREIGN KEYS
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE SET NULL,
    FOREIGN KEY (round_id) REFERENCES admission_rounds(round_id) ON DELETE CASCADE
);

-- 5. PREDICTION STATS (Data for the Algorithm)
DROP TABLE IF EXISTS prediction_stats;
CREATE TABLE prediction_stats (
    stat_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_id INT,
    historical_cutoff_rank INT, -- Last year's cutoff
    implicit_demand_factor DECIMAL(5, 2) DEFAULT 1.0, -- e.g., 1.2 = 20% higher interest
    
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1; -- Re-enable checks


-- 1. CLEANING OLD DATA SAFELY
-- Disabling checks is the most reliable way for demo resets
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM allocations;
DELETE FROM prediction_stats;
DELETE FROM admission_rounds;
DELETE FROM departments;
DELETE FROM users;

-- Resetting the Auto-Increment counters to 1
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE departments AUTO_INCREMENT = 1;
ALTER TABLE admission_rounds AUTO_INCREMENT = 1;
ALTER TABLE allocations AUTO_INCREMENT = 1;
ALTER TABLE prediction_stats AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- 2. INSERT DEPARTMENTS (10 total)
INSERT INTO departments (dept_name, base_capacity, current_locked) VALUES
('Computer Science', 10, 0),
('Software Engineering', 8, 0),
('Information Technology', 15, 0),
('Electrical Engineering', 12, 0),
('Mechanical Engineering', 10, 0),
('Civil Engineering', 20, 0),
('Data Science', 5, 0),
('Cyber Security', 6, 0),
('Architecture', 8, 0),
('Business Information Systems', 15, 0);

-- 3. INSERT PREDICTION STATS
INSERT INTO prediction_stats (dept_id, historical_cutoff_rank, implicit_demand_factor) VALUES
(1, 150, 1.20), (2, 200, 1.15), (3, 400, 1.00), (4, 300, 1.10), (5, 450, 0.95),
(6, 600, 0.90), (7, 100, 1.30), (8, 120, 1.25), (9, 250, 1.05), (10, 500, 1.00);

-- 4. INSERT ADMIN
INSERT INTO users (username, email, password_hash, role) VALUES
('admin_user', 'admin@univ.edu', '123456', 'admin');

-- 5. INSERT 30 STUDENTS
INSERT INTO users (username, email, password_hash, role, admission_rank) VALUES
('student_top1', 's1@univ.edu', '123456', 'student', 10),
('student_top2', 's2@univ.edu', '123456', 'student', 45),
('student_top3', 's3@univ.edu', '123456', 'student', 89),
('student_mid1', 's4@univ.edu', '123456', 'student', 160),
('student_mid2', 's5@univ.edu', '123456', 'student', 210),
('student_mid3', 's6@univ.edu', '123456', 'student', 280),
('student_mid4', 's7@univ.edu', '123456', 'student', 350),
('student_mid5', 's8@univ.edu', '123456', 'student', 420),
('student_low1', 's9@univ.edu', '123456', 'student', 550),
('student_low2', 's10@univ.edu', '123456', 'student', 700),
('student11', 's11@univ.edu', '123456', 'student', 125),
('student12', 's12@univ.edu', '123456', 'student', 330),
('student13', 's13@univ.edu', '123456', 'student', 490),
('student14', 's14@univ.edu', '123456', 'student', 610),
('student15', 's15@univ.edu', '123456', 'student', 15),
('student16', 's16@univ.edu', '123456', 'student', 99),
('student17', 's17@univ.edu', '123456', 'student', 720),
('student18', 's18@univ.edu', '123456', 'student', 310),
('student19', 's19@univ.edu', '123456', 'student', 240),
('student20', 's20@univ.edu', '123456', 'student', 180),
('student21', 's21@univ.edu', '123456', 'student', 50),
('student22', 's22@univ.edu', '123456', 'student', 520),
('student23', 's23@univ.edu', '123456', 'student', 440),
('student24', 's24@univ.edu', '123456', 'student', 290),
('student25', 's25@univ.edu', '123456', 'student', 130),
('student26', 's26@univ.edu', '123456', 'student', 370),
('student27', 's27@univ.edu', '123456', 'student', 110),
('student28', 's28@univ.edu', '123456', 'student', 800),
('student29', 's29@univ.edu', '123456', 'student', 5),
('student30', 's30@univ.edu', '123456', 'student', 200);

-- 6. INSERT AN ACTIVE ROUND
INSERT INTO admission_rounds (round_name, start_time, end_time, status) VALUES
('Academic Round 2025-A', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'active');


