CREATE DATABASE IF NOT EXISTS hamro_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hamro_ecommerce;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(25) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    image VARCHAR(255) NOT NULL DEFAULT 'product-placeholder.svg',
    stock INT NOT NULL DEFAULT 0,
    rating DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_products_category (category_id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    phone VARCHAR(25) NOT NULL,
    payment_method VARCHAR(60) NOT NULL,
    payment_status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    order_status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    transaction_id VARCHAR(80) DEFAULT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_orders_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_items_order (order_id)
) ENGINE=InnoDB;

INSERT INTO users (name,email,phone,password,role) VALUES
('Hamro Administrator','admin@hamro.com','9800000000','$2y$12$cmdb/38RUXrHfDnfXe50jeKLAAVKw8qiVBTwa2i4A3lGpX.0RwwIu','admin');

INSERT INTO categories (name,description) VALUES
('Electronics','Phones, computers, audio devices and smart gadgets.'),
('Fashion','Comfortable clothing and everyday fashion essentials.'),
('Accessories','Useful accessories for work, travel and daily life.');

INSERT INTO products (category_id,name,description,price,image,stock,rating) VALUES
(1,'Hamro X1 Smartphone','A fictional mid-range smartphone with a bright display, dependable battery and dual camera setup.',25999,'smartphone.svg',18,4.6),
(1,'Himal Laptop 14','A lightweight fictional laptop for study, office work and everyday browsing.',68999,'laptop.svg',10,4.7),
(1,'Sonic Wireless Headphones','Comfortable over-ear wireless headphones with long battery life for study and entertainment.',5499,'headphones.svg',25,4.4),
(1,'Smart Watch Pro','A fictional smart watch with activity tracking, notifications and a crisp touch display.',7999,'smartwatch.svg',14,4.3),
(1,'TypePro Keyboard','A simple mechanical-style keyboard for coding, study and office work.',3499,'keyboard.svg',30,4.5),
(1,'Click Wireless Mouse','An ergonomic wireless mouse with silent clicks and adjustable sensitivity.',1799,'mouse.svg',35,4.2),
(1,'VisionCam 4K','A fictional compact camera for learning photography and recording class projects.',42999,'camera.svg',7,4.1),
(2,'Classic Cotton T-Shirt','Soft everyday cotton T-shirt with a relaxed fit.',999,'tshirt.svg',50,4.4),
(2,'Urban Hoodie','Warm casual hoodie designed for cool mornings and campus life.',2499,'hoodie.svg',22,4.6),
(2,'Campus Runner Shoes','Comfortable fictional sneakers for walking, commuting and everyday campus use.',3999,'shoes.svg',16,4.5),
(3,'Daily Backpack','A practical backpack with laptop space, multiple compartments and water-resistant fabric.',2299,'backpack.svg',28,4.5),
(3,'Pocket Air Earbuds','Compact fictional wireless earbuds with a charging case and touch controls.',2999,'earbuds.svg',20,4.2);

-- Demo admin login:
-- Email: admin@hamro.com
-- Password: admin123
