# SQL Injection Prevention Lab (PHP & MySQL)

Practical laboratory environment developed for the **Principles of Information Security** course at **FIIT STU in Bratislava**.

## Project Overview
This repository contains a custom, intentionally vulnerable web application built to demonstrate manual and automated SQL injection exploitation, followed by a comparative implementation of legacy and modern prevention techniques.

## Repository Structure
- `setup_db.sql` – MySQL database schema and sample test records (`pib_auth_demo`).
- `login_unsafe.php` – Baseline vulnerable implementation using raw string concatenation.
- `login_escaped.php` – Legacy defense using `mysqli_real_escape_string()` (demonstrating failure on unquoted numeric parameters).
- `login_pdo.php` – Secure implementation utilizing PHP Data Objects (PDO) with native Prepared Statements (`ATTR_EMULATE_PREPARES => false`).

## Local Setup (Windows / XAMPP)
1. Clone or copy this repository into `C:\xampp\htdocs\pib_auth_demo\`.
2. Start **Apache** and **MySQL** via the XAMPP Control Panel.
3. Import `setup_db.sql` using phpMyAdmin (`http://localhost/phpmyadmin`).
4. Access the test endpoints at `http://localhost/pib_auth_demo/vuln.php`, `login_escaped.php`, and `login_pdo.php`.