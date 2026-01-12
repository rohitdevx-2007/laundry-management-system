# Laundry Management System

A modern, responsive Laundry Management System built with PHP and MySQL.

## Features
- **Dashboard**: Overview of orders and stats.
- **Service Management**: Add/Edit/Delete laundry services and prices.
- **Customer Management**: Manage customer details.
- **Order Management**: Create orders, track status (Pending, Processing, Ready, Delivered).
- **Billing**: Generate printable PDF bills/invoices.
- **Reports**: Monthly order reports.
- **Responsive UI**: Works on mobile, tablet, and desktop.

## Setup Instructions

1.  **Database Setup**:
    - Create a new database named `laundry_db` in your MySQL server (e.g., phpMyAdmin).
    - Import the `database.sql` file located in the root directory.

2.  **Configuration**:
    - Open `config/db.php` (to be created) and update your database credentials if they differ from the defaults (user: `root`, pass: ``).

3.  **Run**:
    - Place the project folder in your local server directory (e.g., `htdocs` for XAMPP).
    - Access the application via your browser (e.g., `http://localhost/laundry-system`).

4.  **Default Login**:
    - **Username**: `admin`
    - **Password**: `admin123`

## Technologies
- PHP
- MySQL
- Bootstrap 5
- jQuery
- FontAwesome
