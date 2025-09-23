-- Create Database
CREATE DATABASE IF NOT EXISTS agrisage_db;
USE agrisage_db;

-- USERS
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CROPS (linked to user)
CREATE TABLE crops (
    crop_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100),
    disease_status VARCHAR(100),
    scan_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ACTIVITIES (like Stripe activity feed)
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity_type VARCHAR(100), -- scan, order, drone, etc.
    summary VARCHAR(255),
    payload JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- NOTIFICATIONS
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    message VARCHAR(255) NOT NULL,
    type ENUM('alert','weather','market') DEFAULT 'alert',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- MARKETPLACE PRODUCTS
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ORDERS
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10,2),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ORDER ITEMS
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT,
    unit_price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE SET NULL
);

-- DRONE SERVICES (optional, or log under activities)
CREATE TABLE drone_services (
    drone_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    service_type VARCHAR(100), -- e.g., "Mapping", "Spraying"
    status ENUM('pending','completed') DEFAULT 'pending',
    scheduled_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- -------------------------------
-- SAMPLE DATA
-- -------------------------------

-- Example farmer
INSERT INTO users (name, email, password) 
VALUES ('Mukayi Zhou', 'mukayi@example.com', '$2y$10$abcdefghijklmnopqrstuv'); 
-- (password must be hashed with password_hash in PHP)

-- Example products
INSERT INTO products (name, category, price, stock) VALUES
('Mancozeb 80 WP', 'Fungicide', 15.50, 100),
('Urea Fertilizer', 'Fertilizer', 30.00, 200),
('Neem Oil Spray', 'Pesticide', 12.00, 50);

-- Example notifications
INSERT INTO notifications (user_id, message, type) VALUES
(NULL, '☀️ Hot weather expected tomorrow (31°C)', 'weather'),
(NULL, '⚠️ Disease outbreak reported in maize crops regionally', 'alert'),
(NULL, '🛒 New fertilizer available in Marketplace', 'market');

-- Example activity
INSERT INTO activities (user_id, activity_type, summary) 
VALUES (1, 'scan', 'Scanned maize leaf - Rust detected');