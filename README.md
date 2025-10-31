# 🗳️ Online Student Voting System for College Elections

This is a secure, web-based platform designed to replace manual student elections in colleges, eliminating inefficiency, human errors, and delays in result declaration.

## ✨ Features

* **Secure Authentication:** Separate portals for Students and Administrators. Passwords are secured using PHP's built-in hashing (`bcrypt`).
* **Candidate Management:** Admin dashboard allows creation, updating, and deletion of candidates (including photo upload).
* **One-Time Voting:** Students can cast their vote only once per election, enforced via application logic and database constraints.
* **Live Results:** Admin can view real-time vote counts presented via dynamic charts (Chart.js).
* **Election Scheduling:** Admin can define start and end times for the voting period.

## 🛠️ Technology Stack

* **Backend:** PHP (Native, PHP 7.x+)
* **Database:** MySQL / MariaDB
* **Server:** Apache (via XAMPP)
* **Frontend:** HTML5, Bootstrap 5 (for UI), CSS
* **Charting:** Chart.js (for live graphs)

## 🚀 Setup Instructions

### 1. Project Structure

Ensure your file structure matches the following (the sensitive `db_connect.php` file must be recreated manually):
### 2. Database Configuration

1.  Access your local **phpMyAdmin** (`http://localhost/phpmyadmin/`).
2.  Create a database named: `college_elections`.
3.  Run the **complete SQL schema** provided in the project files to create all tables (`students`, `elections`, `candidates`, `votes`).

### 3. Connection File

Since `db_connect.php` is ignored for security, you must **create this file manually** in `includes/` and fill in your XAMPP database credentials:

```php
// File: includes/db_connect.php
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$servername = "localhost";
$username = "root";     
$password = "";         // Use your XAMPP password (often empty)
$dbname = "college_elections"; 
// ... rest of connection logic ...
?>"# Online TNP Voting System" 
