-- =========================================
-- DATABASE SETUP
-- =========================================
CREATE DATABASE IF NOT EXISTS enrollment_db;
USE enrollment_db;

-- =========================================
-- USERS TABLE (Admins / Teachers)
-- =========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher') DEFAULT 'teacher',
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- =========================================
-- STUDENTS TABLE
-- =========================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    enrolled_on DATE,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- =========================================
-- COURSES TABLE
-- =========================================
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(150) NOT NULL,
    instructor_id INT,
    duration_weeks INT,
    max_seats INT NOT NULL,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_instructor
    FOREIGN KEY (instructor_id) REFERENCES users(id)
    ON DELETE SET NULL
);

-- =========================================
-- ENROLLMENTS TABLE
-- =========================================
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_date DATE,
    status ENUM('active', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,

    UNIQUE KEY unique_enrollment (student_id, course_id),

    CONSTRAINT fk_student FOREIGN KEY (student_id)
        REFERENCES students(id) ON DELETE CASCADE,

    CONSTRAINT fk_course FOREIGN KEY (course_id)
        REFERENCES courses(id) ON DELETE CASCADE
);

-- =========================================
-- AUDIT LOG TABLE
-- =========================================
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50),
    action_type ENUM('INSERT', 'UPDATE', 'DELETE'),
    record_id INT,
    old_data TEXT,
    new_data TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- INDEXES (Performance)
-- =========================================
CREATE INDEX idx_students_email ON students(email);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_courses_instructor ON courses(instructor_id);
CREATE INDEX idx_enrollments_student ON enrollments(student_id);
CREATE INDEX idx_enrollments_course ON enrollments(course_id);

-- (Optional but good for soft delete performance)
CREATE INDEX idx_users_deleted_at ON users(deleted_at);
CREATE INDEX idx_students_deleted_at ON students(deleted_at);
CREATE INDEX idx_courses_deleted_at ON courses(deleted_at);
CREATE INDEX idx_enrollments_deleted_at ON enrollments(deleted_at);

-- =========================================
-- TRIGGERS SECTION
-- =========================================
DELIMITER $$

-- USERS TRIGGERS
CREATE TRIGGER users_after_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('users', 'INSERT', NEW.id,
        CONCAT('Name:', NEW.name, ', Email:', NEW.email));
END$$

CREATE TRIGGER users_after_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('users', 'UPDATE', NEW.id,
        CONCAT('Old Name:', OLD.name, ', Old Email:', OLD.email),
        CONCAT('New Name:', NEW.name, ', New Email:', NEW.email));
END$$

CREATE TRIGGER users_after_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('users', 'DELETE', OLD.id,
        CONCAT('Deleted Name:', OLD.name, ', Email:', OLD.email));
END$$

-- STUDENTS TRIGGERS
CREATE TRIGGER students_after_insert
AFTER INSERT ON students
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('students', 'INSERT', NEW.id,
        CONCAT('Name:', NEW.name, ', Email:', NEW.email));
END$$

CREATE TRIGGER students_after_update
AFTER UPDATE ON students
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('students', 'UPDATE', NEW.id,
        CONCAT('Old Name:', OLD.name),
        CONCAT('New Name:', NEW.name));
END$$

CREATE TRIGGER students_after_delete
AFTER DELETE ON students
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('students', 'DELETE', OLD.id,
        CONCAT('Deleted Name:', OLD.name));
END$$

-- COURSES TRIGGERS
CREATE TRIGGER courses_after_insert
AFTER INSERT ON courses
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('courses', 'INSERT', NEW.id,
        CONCAT('Course:', NEW.course_name));
END$$

CREATE TRIGGER courses_after_update
AFTER UPDATE ON courses
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('courses', 'UPDATE', NEW.id,
        CONCAT('Old Course:', OLD.course_name),
        CONCAT('New Course:', NEW.course_name));
END$$

CREATE TRIGGER courses_after_delete
AFTER DELETE ON courses
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('courses', 'DELETE', OLD.id,
        CONCAT('Deleted Course:', OLD.course_name));
END$$

-- ENROLLMENTS TRIGGERS
CREATE TRIGGER enrollments_after_insert
AFTER INSERT ON enrollments
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('enrollments', 'INSERT', NEW.id,
        CONCAT('Student ID:', NEW.student_id, ', Course ID:', NEW.course_id));
END$$

CREATE TRIGGER enrollments_after_update
AFTER UPDATE ON enrollments
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('enrollments', 'UPDATE', NEW.id,
        CONCAT('Old Status:', OLD.status),
        CONCAT('New Status:', NEW.status));
END$$

CREATE TRIGGER enrollments_after_delete
AFTER DELETE ON enrollments
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('enrollments', 'DELETE', OLD.id,
        CONCAT('Deleted Enrollment ID:', OLD.id));
END$$

DELIMITER ;