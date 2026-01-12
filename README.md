<h1 align="center">🧺 Laundry Management System</h1>

<p align="center">
  <img src="https://readme-typing-svg.herokuapp.com?font=Fira+Code&size=20&pause=1200&color=00BFFF&center=true&vCenter=true&width=650&lines=Modern+Laundry+Management+Web+App;Built+with+PHP+%26+MySQL;Responsive+%7C+Simple+%7C+Efficient" alt="Typing SVG" />
</p>

<p align="center">
  <img src="https://komarev.com/ghpvc/?username=rohit-dev-2007&label=Views&style=for-the-badge" alt="Views" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License" />
</p>

<p align="center">
  <a href="https://rohit-dev-2007.github.io/laundry-management-system/">
    <img src="https://img.shields.io/badge/Live%20Demo-Visit%20Now-00C2FF?style=for-the-badge&logo=google-chrome&logoColor=white" />
  </a>
</p>

---

## 📌 Project Overview

The **Laundry Management System** is a robust, web-based solution designed to automate the daily operations of a laundry business. From tracking incoming garments to generating professional invoices, this system ensures a seamless workflow for both staff and customers.

---

## ✨ Key Features

- **📊 Dynamic Dashboard:** Real-time stats on pending orders, total revenue, and customer count.
- **🧼 Service Catalog:** Manage different types of laundry (Wash, Iron, Dry Clean) with custom pricing.
- **👥 CRM:** Maintain a database of customers and their order history.
- **📦 Order Tracking:** Follow the lifecycle of an order: `Pending` → `Processing` → `Ready` → `Delivered`.
- **🧾 Automated Billing:** Instant generation of printable PDF invoices.
- **📱 Fully Responsive:** Works perfectly on mobile, tablets, and desktops thanks to Bootstrap 5.

---

## 🛠️ Tech Stack

| Component | Technology |
| :--- | :--- |
| **Frontend** | HTML5, CSS3, Bootstrap 5, jQuery |
| **Backend** | PHP|
| **Database** | MySQL |
| **Icons** | Font Awesome |

---

## ⚙️ Installation & Setup

### 1. Database Configuration
1. Open **phpMyAdmin**.
2. Create a new database named `laundry_db`.
3. Import the `database.sql` file provided in the repository.

### 2. Configure Backend
Edit `config/db.php` with your local server details:


```
<?php
$host = "localhost";
$user = "root";     
$pass = "";         
$dbname = "laundry_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);
?>
```
### 3. Run Project
1. Place the folder in htdocs (XAMPP) or www (WAMP).
2. Access via: http://localhost/laundry-management-system

### Part 4: Credentials, License & Developer
The final section with login info and your profile links.

## 🔐 Default Admin Credentials

| Role | Username | Password |
| :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` |

---

## 📜 License

Distributed under the **MIT License**. See `LICENSE` for more information.

---

## 👨‍💻 Developer Info

**Rohit** *BCA Student*

<p align="left">
  <a href="mailto:vadherrohit239@gmail.com">
    <img src="https://img.shields.io/badge/Email-D14836?style=flat&logo=gmail&logoColor=white" />
  </a>
  <a href="https://github.com/rohit-dev-2007">
    <img src="https://img.shields.io/badge/GitHub-181717?style=flat&logo=github&logoColor=white" />
  </a>
</p>

---

<p align="center">
  💡 <i>Built with passion to simplify real-world laundry management.</i>
</p>
