# Frontend - Fleet Ledger Transport Management System

This is the frontend (user interface layer) of the Fleet Ledger Transport Management System. It allows users to log in, dispatch trucks, generate waybills, and track expenses.

Although built using PHP (a backend language), this folder is referred to as the "frontend" because it handles user-facing interactions and displays.

---

## ⚙️ How to Run the System

1. Place the project folder (`pcl`) inside your `htdocs` directory:
   ```
   C:/xampp/htdocs/pcl
   ```

2. Launch XAMPP and start:
   - **Apache**
   - **MySQL**

3. Open your browser and visit:
   ```
   http://localhost/pcl/login.php
   ```

---

## 🌐 Technologies Used

- **PHP**: Server-side scripting for generating dynamic pages
- **HTML/CSS**: Page structure and styling
- **MySQL**: Database used via backend integration
- **XAMPP**: Local server environment

---

# Backend - Fleet Ledger Transport Management System

This README provides setup instructions for the backend of the Fleet Ledger Transport Management System, including configuration of XAMPP, importing the SQL database, and connecting everything with the frontend.

The backend handles data processing, request handling, and interacts with the `pcldb` MySQL database.

---

## 📦 Project Overview

The **Fleet Ledger Transport Management System** is designed to enhance the efficiency of logistics operations. It streamlines truck dispatching, waybill generation, expense tracking, and analytics using a relational database.

This system was developed for **Producers Connection Logistics Inc. (PCL Inc.)** to improve logistics workflow and operational management.

---

## ⚙️ Backend Setup Instructions

### 🔧 Requirements

- [XAMPP](https://www.apachefriends.org/index.html)
- Web browser (e.g., Google Chrome, Firefox)

### 🛠️ Setup Steps

1. **Download** the project `.zip` file.
2. **Extract** and place the `pcl` folder inside the `htdocs` directory of your XAMPP installation (e.g., `C:/xampp/htdocs/pcl`).

3. **Add PHP to System PATH**:
   - Go to `C:/xampp/php`
   - Copy the path
   - Open **Settings > About > Advanced System Settings > Environment Variables**
   - Under **System Variables**, find `Path` → Click **Edit**
   - Click **New** → Paste the path → Click **OK**

4. **Import the database**:
   - Visit `http://localhost/phpmyadmin`
   - Click **Import**
   - Select the SQL file: `pcldb.sql` *(included in the project folder under `/database` if applicable)*
   - Click **Go** to import

5. **Start the Server**:
   - Open the XAMPP Control Panel
   - Start **Apache** and **MySQL**

6. **Test the System**:
   - Open your browser and go to: `http://localhost/pcl/login.php`

---

## 🧪 Testing Login Functionality

1. Go to `http://localhost/phpmyadmin`
2. Open the `pcldb` database
3. Browse the `employee` table
4. Copy any existing email and password
5. Use these credentials at: `http://localhost/pcl/login.php`
