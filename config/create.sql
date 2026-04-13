-- =========================================
-- DATABASE CREATION
-- =========================================
CREATE DATABASE IF NOT EXISTS enrollment_db;
USE enrollment_db;

-- =========================================
-- TABLE: users
-- =========================================
CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher','student') DEFAULT 'student',
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_users_deleted_at(deleted_at)
);

-- =========================================
-- TABLE: student_profiles
-- =========================================
CREATE TABLE student_profiles (
    user_id INT NOT NULL PRIMARY KEY,
    phone VARCHAR(20),
    enrolled_on DATE,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_student_profile_user FOREIGN KEY(user_id) REFERENCES users(id)
);

-- =========================================
-- TABLE: courses
-- =========================================
CREATE TABLE courses (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(150) NOT NULL,
    instructor_id INT,
    duration_weeks INT,
    max_seats INT NOT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_courses_instructor(instructor_id),
    INDEX idx_courses_deleted_at(deleted_at),
    CONSTRAINT fk_instructor FOREIGN KEY(instructor_id) REFERENCES users(id)
);

-- =========================================
-- TABLE: enrollments
-- =========================================
CREATE TABLE enrollments (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_date DATE,
    status ENUM('active','cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY unique_enrollment(student_id, course_id),
    INDEX idx_enrollments_student(student_id),
    INDEX idx_enrollments_course(course_id),
    INDEX idx_enrollments_deleted_at(deleted_at),
    CONSTRAINT fk_student FOREIGN KEY(student_id) REFERENCES users(id),
    CONSTRAINT fk_course FOREIGN KEY(course_id) REFERENCES courses(id)
);

-- =========================================
-- TABLE: audit_logs
-- =========================================
CREATE TABLE audit_logs (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50),
    action_type ENUM('INSERT','UPDATE','DELETE'),
    record_id INT,
    old_data TEXT,
    new_data TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- TABLE: permissions
-- =========================================
CREATE TABLE permissions (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- TABLE: role_permissions
-- =========================================
CREATE TABLE role_permissions (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin','teacher') NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_permission(role, permission_id),
    CONSTRAINT fk_role_permission FOREIGN KEY(permission_id) REFERENCES permissions(id)
);

-- =========================================
-- TABLE: user_permissions
-- =========================================
CREATE TABLE user_permissions (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_permission(user_id, permission_id),
    CONSTRAINT fk_user_permission_user FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_user_permission_permission FOREIGN KEY(permission_id) REFERENCES permissions(id)
);


INSERT INTO permissions (name) VALUES
-- STUDENT
('create_student'),
('view_student'),
('edit_student'),
('delete_student'),

-- COURSE
('create_course'),
('view_course'),
('edit_course'),
('delete_course'),

-- ENROLLMENT
('enroll_student'),
('view_enrollment'),
('cancel_enrollment'),

-- TEACHERS / USERS
('create_teacher'),
('view_teacher'),
('edit_teacher'),
('delete_teacher'),

-- SYSTEM
('manage_roles'),
('assign_permissions'),
('view_audit_logs');