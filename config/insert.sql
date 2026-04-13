-- =========================================
-- TRUNCATE TABLES (clean slate)
-- =========================================
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE user_permissions;
TRUNCATE TABLE role_permissions;
TRUNCATE TABLE permissions;
TRUNCATE TABLE enrollments;
TRUNCATE TABLE courses;
TRUNCATE TABLE student_profiles;
TRUNCATE TABLE users;
TRUNCATE TABLE audit_logs;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================
-- PERMISSIONS
-- =========================================
INSERT INTO permissions (name) VALUES
-- STUDENT
('create_student'),
('view_student'),
('edit_student'),
('delete_student'),
('edit_own_profile'),
-- COURSE
('create_course'),
('view_course'),
('edit_course'),
('delete_course'),
('view_course_students'),
-- ENROLLMENT
('enroll_student'),
('view_enrollment'),
('cancel_enrollment'),
-- TEACHERS / USERS
('create_teacher'),
('view_teacher'),
('edit_teacher'),
('delete_teacher'),
('view_all_users'),
-- SYSTEM
('manage_roles'),
('assign_permissions'),
('view_audit_logs'),
('manage_audit_logs');

-- =========================================
-- USERS (Admin, Teachers, Students)
-- =========================================
INSERT INTO users (name, email, password, role) VALUES
-- Admin
('AdminUser', 'admin@example.com', '1', 'admin'),
-- Teachers
('Hiral Desai', 'hiral.teacher@example.com', '1', 'teacher'),
('Rohit Mehta', 'rohit.teacher@example.com', '1', 'teacher'),
-- Students
('Jignesh Solanki', 'jignesh.student@example.com', '1', 'student'),
('Priya Chauhan', 'priya.student@example.com', '1', 'student'),
('Krishna Patel', 'krishna.student@example.com', '1', 'student');

-- =========================================
-- STUDENT PROFILES
-- =========================================
INSERT INTO student_profiles (user_id, phone, enrolled_on) VALUES
(4, '9876543210', '2023-06-01'),
(5, '9823456789', '2023-06-15'),
(6, '9901234567', '2023-07-01');

-- =========================================
-- COURSES
-- =========================================
INSERT INTO courses (course_name, instructor_id, duration_weeks, max_seats) VALUES
('Mathematics - Grade 10', 2, 12, 30),
('Science - Grade 10', 3, 12, 30),
('Gujarati Language', 2, 8, 25);

-- =========================================
-- ENROLLMENTS
-- =========================================
INSERT INTO enrollments (student_id, course_id, enrolled_date) VALUES
(4, 1, '2023-06-01'),
(5, 1, '2023-06-05'),
(6, 2, '2023-06-10'),
(4, 3, '2023-06-15');

-- =========================================
-- ROLE PERMISSIONS (Admin, Instructor)
-- =========================================
-- Admin gets all permissions
INSERT INTO role_permissions (role, permission_id)
SELECT 'admin', id FROM permissions;

-- Instructor gets limited permissions
INSERT INTO role_permissions (role, permission_id)
SELECT 'teacher', id FROM permissions
WHERE name IN ('view_student','view_enrollment','enroll_student','view_course_students','view_course','edit_own_profile');

-- =========================================
-- USER PERMISSIONS (Optional - per user override)
-- =========================================
-- Example: give one student permission to edit own profile explicitly
INSERT INTO user_permissions (user_id, permission_id)
SELECT 4, id FROM permissions WHERE name='edit_own_profile';

-- STUDENT
INSERT INTO permissions (name) VALUES ('restore_student');

-- TEACHER / USERS
INSERT INTO permissions (name) VALUES ('restore_teacher');

-- COURSE
INSERT INTO permissions (name) VALUES ('restore_course');