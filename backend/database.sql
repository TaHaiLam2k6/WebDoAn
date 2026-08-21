CREATE DATABASE IF NOT EXISTS phoneshop;
USE phoneshop;

CREATE TABLE IF NOT EXISTS accounts (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(80) NOT NULL UNIQUE,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 full_name VARCHAR(150) NOT NULL DEFAULT '',
 role ENUM('admin','user') NOT NULL DEFAULT 'user',
 status ENUM('active','locked') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL, brand VARCHAR(80) NOT NULL, price DECIMAL(15,2) NOT NULL DEFAULT 0,
 image_url TEXT NULL, description TEXT NULL, stock INT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_products_brand (brand), INDEX idx_products_name (name)
);

CREATE TABLE IF NOT EXISTS account_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, account_id INT UNSIGNED NOT NULL, token_hash CHAR(64) NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_token_hash (token_hash), INDEX idx_expires_at (expires_at),
 CONSTRAINT fk_token_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
);

INSERT INTO accounts (username,email,password_hash,full_name,role,status) VALUES
('admin','admin@phoneshop.local','$2y$12$20nvoGR8WJKFpOQkHEbr7e5yv0ByFQVLlAxrudxd1yOhSKT76sxTm','Administrator','admin','active')
ON DUPLICATE KEY UPDATE username=username;

INSERT INTO products (name,brand,price,image_url,description,stock)
SELECT 'iPhone 16','Apple',21990000,'https://images.unsplash.com/photo-1591337676887-a217a6970a8a?auto=format&fit=crop&w=800&q=80','Điện thoại Apple iPhone 16.',10
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name='iPhone 16');

INSERT INTO products (name,brand,price,image_url,description,stock)
SELECT 'Galaxy S25','Samsung',18990000,'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=800&q=80','Điện thoại Samsung Galaxy S25.',8
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name='Galaxy S25');
