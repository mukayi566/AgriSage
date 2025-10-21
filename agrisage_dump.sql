
-- SQL Dump for AgriSage Database

-- Create Database
CREATE DATABASE IF NOT EXISTS agrisage_db;
USE agrisage_db;

-- Drop tables if exist (for clean import)
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS medications;
DROP TABLE IF EXISTS diseases;
DROP TABLE IF EXISTS crops;
DROP TABLE IF EXISTS users;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('farmer','admin') DEFAULT 'farmer'
);

-- Crops Table
CREATE TABLE crops (
    crop_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    disease_status VARCHAR(100),
    scan_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Diseases Table
CREATE TABLE diseases (
    disease_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Medications Table
CREATE TABLE medications (
    med_id INT AUTO_INCREMENT PRIMARY KEY,
    disease_id INT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50),
    price DECIMAL(10,2),
    description TEXT,
    FOREIGN KEY (disease_id) REFERENCES diseases(disease_id)
);

-- Orders Table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    med_id INT,
    quantity INT,
    total_price DECIMAL(10,2),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (med_id) REFERENCES medications(med_id)
);

-- Sample Data
INSERT INTO users (name, email, password, role) VALUES
('John Farmer', 'john@example.com', 'hashed_password1', 'farmer'),
('Admin User', 'admin@example.com', 'hashed_password2', 'admin');

INSERT INTO diseases (name, description) VALUES
('Blight', 'A common plant disease causing leaf spots'),
('Rust', 'Fungal disease causing rust-colored pustules');

INSERT INTO medications (disease_id, name, type, price, description) VALUES
(1, 'Blight Cure A', 'Fungicide', 15.50, 'Effective against blight disease'),
(2, 'Rust Shield', 'Fungicide', 20.00, 'Protects crops from rust infection');

INSERT INTO crops (user_id, name, disease_status, scan_date) VALUES
(1, 'Tomato', 'Blight Detected', '2025-08-27'),
(1, 'Wheat', 'Healthy', '2025-08-28');

INSERT INTO orders (user_id, med_id, quantity, total_price) VALUES
(1, 1, 2, 31.00),
(1, 2, 1, 20.00);
