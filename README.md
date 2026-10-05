# Hamro Ecommerce

A complete, simple BCA 5th Semester e-commerce project built only with HTML5, CSS3, JavaScript, PHP, MySQL, Apache and XAMPP.

## Introduction

Hamro Ecommerce is a local e-commerce application. It includes customer registration/login, product browsing, session-based cart, checkout, Cash on Delivery, eSewa ePay UAT payment, order history and an admin panel with product CRUD and order management.

## Objectives

- Understand PHP server-side programming.
- Connect PHP with MySQL.
- Implement authentication and sessions.
- Demonstrate CRUD operations.
- Build a working shopping cart.
- Demonstrate checkout and a fake/test payment workflow.
- Practice responsive HTML/CSS/JavaScript.

## Features

- Home page with featured products and categories.
- Dynamic MySQL product catalog.
- Search, category filter and price/rating sorting.
- User registration and login.
- Password hashing with `password_hash()`.
- Session-based shopping cart.
- Checkout with address and payment selection.
- eSewa payment flow.
- Cash on Delivery flow.
- Generated transaction ID such as `TXN-HAMRO-20260829-12345`.
- Order creation with database transaction.
- User dashboard and order history.
- Separate admin login and role check.
- Admin product CRUD and stock updates.
- Admin order status management.
- Prepared statements and output escaping.
- Responsive desktop/tablet/mobile interface.

## Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP 8.x recommended
- MySQL / MariaDB
- Apache
- XAMPP
- phpMyAdmin

No Node.js, Express, MongoDB, React, Laravel, Python or Firebase is required.

## Hardware Requirements

- 4 GB RAM or more recommended
- Dual-core processor or better
- At least 1 GB free disk space for XAMPP/project
- Keyboard, mouse and display

## Software Requirements

- XAMPP with Apache and MySQL/MariaDB
- Web browser such as Chrome, Edge or Firefox
- Text editor / VS Code
- phpMyAdmin (included with XAMPP)

## System Architecture

Browser -> Apache -> PHP pages -> MySQL database

JavaScript runs in the browser for client-side interactions and validation. PHP handles sessions, authentication, cart processing, checkout, payment demonstration, order creation and admin operations. MySQL stores users, categories, products, orders and order items.

## Database Design

Tables:

1. `users`: customer/admin accounts.
2. `categories`: product categories.
3. `products`: product catalog and stock.
4. `orders`: order header, shipping and payment information.
5. `order_items`: products and quantities belonging to each order.

Relationships:

- One category has many products.
- One user has many orders.
- One order has many order items.
- One product can appear in many order items.

## ER Diagram Description

`users (1) ---- (many) orders`

`categories (1) ---- (many) products`

`orders (1) ---- (many) order_items`

`products (1) ---- (many) order_items`

`order_items` acts as the bridge between orders and products.

## Modules

1. User Module
2. Product Module
3. Cart Module
4. Checkout Module
5. Payment Module
6. Order Module
7. Admin Module

## Installation on XAMPP

1. Install XAMPP.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Copy the `hamro_ecommerce` folder into:
   `C:\xampp\htdocs\`
4. Open phpMyAdmin:
   `http://localhost/phpmyadmin/`
5. Select **Import**.
6. Choose:
   `database/hamro_ecommerce.sql`
7. Click **Go**.
8. Open:
   `http://localhost/hamro_ecommerce/`

The default XAMPP database configuration is:

- Host: `localhost`
- Database: `hamro_ecommerce`
- Username: `root`
- Password: empty

If your MySQL password is different, edit only `config/database.php`.

## Demo Admin Account

- Email: `admin@hamro.com`
- Password: `admin123`

For a real deployment, change/remove this demo account and use a stronger administrator password.

## eSewa Payment

The project uses the eSewa ePay V2 UAT gateway for testing. Customers are redirected to eSewa to complete the test transaction, and the returned payment is verified before the order is marked successful. The UAT environment is not a live production payment service.

Enter sample information such as:

- eSewa Name: `Demo Customer`
- eSewa Mobile Number: `9812345678`
- eSewa Reference ID: `ESW-123456`

The page validates the eSewa information, shows a processing animation, generates a transaction ID and creates the order in MySQL.

If an eSewa transaction is cancelled or cannot be verified, the application shows the payment failure page and keeps the cart available for another attempt.

Never enter real financial information into this academic project.

## Suggested Demonstration Flow

Customer:

Register -> Login -> Products -> Product Details -> Add to Cart -> Cart -> Checkout -> eSewa Payment -> Success -> Order Details -> Order History

Admin:

Admin Login -> Dashboard -> Add/Edit/Delete Product -> View Orders -> Change Order Status

## Project Structure

```text
hamro_ecommerce/
├── index.php
├── products.php
├── product_details.php
├── register.php
├── login.php
├── logout.php
├── cart.php
├── checkout.php
├── payment.php
├── payment_success.php
├── payment_failed.php
├── orders.php
├── order_details.php
├── dashboard.php
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   ├── auth.php
│   └── admin_auth.php
├── assets/
│   ├── css/style.css
│   ├── js/script.js
│   └── images/
├── admin/
│   ├── login.php
│   ├── logout.php
│   ├── header.php
│   ├── footer.php
│   ├── dashboard.php
│   ├── products.php
│   ├── add_product.php
│   ├── edit_product.php
│   ├── delete_product.php
│   ├── orders.php
│   ├── order_details.php
│   └── users.php
└── database/
    └── hamro_ecommerce.sql
```

## Notes

This is intentionally kept readable for a BCA student. It is suitable for local academic demonstration, not production e-commerce. A production system would require HTTPS, CSRF protection, stronger authorization, secure file handling, rate limiting, email verification, proper payment gateway integration and additional auditing.
