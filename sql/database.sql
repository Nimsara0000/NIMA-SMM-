CREATE DATABASE IF NOT EXISTS nima_smm;
USE nima_smm;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(15,4) DEFAULT 0.0000,
    api_key VARCHAR(64) UNIQUE,
    role ENUM('user','admin') DEFAULT 'user',
    status ENUM('active','banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services table (synced from provider API)
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_service_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    type VARCHAR(50) DEFAULT 'Default',
    rate DECIMAL(15,4) NOT NULL,
    min INT DEFAULT 1,
    max INT DEFAULT 100000,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id VARCHAR(50),
    service_id INT NOT NULL,
    link TEXT NOT NULL,
    quantity INT NOT NULL,
    charge DECIMAL(15,4) NOT NULL,
    start_count INT DEFAULT 0,
    remains INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Pending',
    provider VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('deposit','order','refund','admin') NOT NULL,
    amount DECIMAL(15,4) NOT NULL,
    balance_after DECIMAL(15,4) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT
);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Nima SMM'),
('site_url', 'https://nima-smm.onrender.com'),
('currency', 'USD'),
('provider_api_url', 'https://smmpakpanel.com/api/v2'),
('provider_api_key', '924d90d325b79930a235cc83af4311fe'),
('admin_email', 'admin@nimasmm.com'),
('min_deposit', '5'),
('signup_bonus', '0.50');

-- Default admin (password: admin123)
INSERT INTO users (username, email, password, role, balance, api_key) VALUES
('admin', 'admin@nimasmm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0.00, 'ADMIN_API_KEY_001');
