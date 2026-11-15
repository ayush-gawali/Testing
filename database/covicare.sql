-- CoviCare database script. Import in phpMyAdmin (Import tab) or run in the SQL tab.
CREATE DATABASE IF NOT EXISTS covicare_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE covicare_db;

DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS test_registrations;
DROP TABLE IF EXISTS covid_stats;
DROP TABLE IF EXISTS users;

-- 1. Users and admins (role column separates them)
CREATE TABLE users (
  user_id  INT AUTO_INCREMENT PRIMARY KEY,
  name     VARCHAR(50)  NOT NULL,
  email    VARCHAR(100) NOT NULL UNIQUE,
  phone    CHAR(10)     NOT NULL,
  password VARCHAR(255) NOT NULL,           -- bcrypt hash, never plain text
  role     ENUM('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB;

-- 2. COVID test bookings made by users
CREATE TABLE test_registrations (
  registration_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  patient_name VARCHAR(50) NOT NULL,
  age          TINYINT UNSIGNED NOT NULL,
  phone        CHAR(10) NOT NULL,
  test_date    DATE NOT NULL,
  status       ENUM('Pending','Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Feedback / comments
CREATE TABLE comments (
  comment_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  comment    TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Statistics (ILLUSTRATIVE SAMPLE DATA - not live figures)
CREATE TABLE covid_stats (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  location     VARCHAR(60) NOT NULL,
  total_cases  INT NOT NULL,
  recovered    INT NOT NULL,
  active_cases INT NOT NULL,
  deaths       INT NOT NULL,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO covid_stats (location,total_cases,recovered,active_cases,deaths) VALUES
('Sample Region A',120000,115000,2000,3000),
('Sample Region B', 85000, 81500,1000,2500),
('Sample Region C', 60000, 57800, 500,1700),
('Sample Region D', 45000, 43500, 300,1200);
-- Admin account is created by create_admin.php (so the password is hashed by PHP).
