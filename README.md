# Course Enrollment System 🎓

A production-ready Student Course Enrollment Management System built with PHP (OOP) and MySQL.

---

## 🚀 Features

* Student Management (CRUD)
* Course Management (CRUD)
* Enrollment System
* Seat Availability Tracking
* Duplicate Enrollment Prevention
* Clean URL Routing using `.htaccess`
* MVC Architecture
* Secure & Scalable Structure

---

## 🏗️ Tech Stack

* PHP (OOP)
* MySQL
* Apache Server
* JavaScript (Vanilla)
* HTML/CSS

---

## 📁 Project Structure

* `app/` → Core application logic (MVC)
* `public/` → Entry point (index.php, assets)
* `config/` → Database configuration
* `routes/` → Route definitions

---

## ⚙️ Setup Instructions

### 1. Clone Repository

```bash
git clone https://github.com/abhisimform/course-enrollment-system.git
cd course-enrollment-system
```

### 2. Configure Apache

* Set document root to `/public`
* Enable mod_rewrite

```bash
a2enmod rewrite
```

---

### 3. Database Setup

Create database:

```sql
CREATE DATABASE enrollment_db;
```

Tables:

```sql
students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100),
  phone VARCHAR(20),
  enrolled_on DATE
);

courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_name VARCHAR(100),
  instructor VARCHAR(100),
  duration_weeks INT,
  max_seats INT
);

enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT,
  course_id INT,
  enrolled_date DATE,
  status VARCHAR(20)
);
```

---

### 4. Configure Database

Edit:

```
config/database.php
```

---

### 5. Run Project

Open in browser:

```
http://localhost/
```

---

## 🔁 Git Workflow

* `main` → Production
* `develop` → Integration
* `feature/*` → Features

Flow:

```
feature → develop → main
```

---

## ✅ Validations

* No duplicate enrollment
* Max seat limit enforced
* JS confirmation before delete

---

## 📌 Future Improvements

* Authentication system
* REST API support
* Pagination & search
* Unit testing

---

## 👨‍💻 Author

Abhi Andani

---

## 📄 License

MIT License
