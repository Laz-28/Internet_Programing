Setup notes for form_input

Database changes required:

Run these SQL commands (phpMyAdmin or mysql client) to prepare the database/table:

CREATE DATABASE IF NOT EXISTS contact_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE contact_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  age INT NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

Notes:
- Passwords are stored using PHP's password_hash().
- Registration now requires a password and confirmation. Login requires email + password.
- To test locally, start Apache + MySQL (XAMPP), import the SQL above, then open http://localhost/form_input/index.php

Items table (used by `home.php` to store user items):

CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  selected TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

Run the two CREATE TABLE statements in your DB (or use ALTER TABLE to add columns) before using the item features.
