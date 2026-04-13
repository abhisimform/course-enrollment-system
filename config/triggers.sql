-- =========================================
-- STEP 0: SELECT DATABASE
-- =========================================
USE enrollment_db;

-- =========================================
-- STEP 1: TRUNCATE AUDIT LOGS
-- =========================================
TRUNCATE TABLE audit_logs;

-- =========================================
-- STEP 2: DROP ALL EXISTING TRIGGERS
-- =========================================
-- Users
DROP TRIGGER IF EXISTS users_after_insert;
DROP TRIGGER IF EXISTS users_after_update;
DROP TRIGGER IF EXISTS users_after_delete;

-- Student Profiles
DROP TRIGGER IF EXISTS student_profiles_after_insert;
DROP TRIGGER IF EXISTS student_profiles_after_update;
DROP TRIGGER IF EXISTS student_profiles_after_delete;

-- Courses
DROP TRIGGER IF EXISTS courses_after_insert;
DROP TRIGGER IF EXISTS courses_after_update;
DROP TRIGGER IF EXISTS courses_after_delete;

-- Enrollments
DROP TRIGGER IF EXISTS enrollments_after_insert;
DROP TRIGGER IF EXISTS enrollments_after_update;
DROP TRIGGER IF EXISTS enrollments_after_delete;

-- Permissions
DROP TRIGGER IF EXISTS permissions_after_insert;
DROP TRIGGER IF EXISTS permissions_after_update;
DROP TRIGGER IF EXISTS permissions_after_delete;

-- Role Permissions
DROP TRIGGER IF EXISTS role_permissions_after_insert;
DROP TRIGGER IF EXISTS role_permissions_after_update;
DROP TRIGGER IF EXISTS role_permissions_after_delete;

-- User Permissions
DROP TRIGGER IF EXISTS user_permissions_after_insert;
DROP TRIGGER IF EXISTS user_permissions_after_update;
DROP TRIGGER IF EXISTS user_permissions_after_delete;

-- =========================================
-- STEP 3: CREATE NEW TRIGGERS WITH JSON AUDIT
-- =========================================
DELIMITER $$

-- ====== USERS ======
CREATE TRIGGER users_after_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('users', 'INSERT', NEW.id, JSON_OBJECT(
        'id', NEW.id,
        'name', NEW.name,
        'email', NEW.email,
        'role', NEW.role,
        'status', NEW.status,
        'created_at', NEW.created_at,
        'updated_at', NEW.updated_at,
        'deleted_at', NEW.deleted_at
    ));
END$$

CREATE TRIGGER users_after_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('users', 'UPDATE', NEW.id, 
        JSON_OBJECT(
            'id', OLD.id,
            'name', OLD.name,
            'email', OLD.email,
            'role', OLD.role,
            'status', OLD.status,
            'created_at', OLD.created_at,
            'updated_at', OLD.updated_at,
            'deleted_at', OLD.deleted_at
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'name', NEW.name,
            'email', NEW.email,
            'role', NEW.role,
            'status', NEW.status,
            'created_at', NEW.created_at,
            'updated_at', NEW.updated_at,
            'deleted_at', NEW.deleted_at
        )
    );
END$$

CREATE TRIGGER users_after_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('users', 'DELETE', OLD.id, JSON_OBJECT(
        'id', OLD.id,
        'name', OLD.name,
        'email', OLD.email,
        'role', OLD.role,
        'status', OLD.status,
        'created_at', OLD.created_at,
        'updated_at', OLD.updated_at,
        'deleted_at', OLD.deleted_at
    ));
END$$

-- ====== STUDENT PROFILES ======
CREATE TRIGGER student_profiles_after_insert
AFTER INSERT ON student_profiles
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('student_profiles', 'INSERT', NEW.user_id, JSON_OBJECT(
        'user_id', NEW.user_id,
        'phone', NEW.phone,
        'enrolled_on', NEW.enrolled_on,
        'status', NEW.status,
        'created_at', NEW.created_at,
        'updated_at', NEW.updated_at,
        'deleted_at', NEW.deleted_at
    ));
END$$

CREATE TRIGGER student_profiles_after_update
AFTER UPDATE ON student_profiles
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('student_profiles', 'UPDATE', NEW.user_id,
        JSON_OBJECT(
            'user_id', OLD.user_id,
            'phone', OLD.phone,
            'enrolled_on', OLD.enrolled_on,
            'status', OLD.status,
            'created_at', OLD.created_at,
            'updated_at', OLD.updated_at,
            'deleted_at', OLD.deleted_at
        ),
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'phone', NEW.phone,
            'enrolled_on', NEW.enrolled_on,
            'status', NEW.status,
            'created_at', NEW.created_at,
            'updated_at', NEW.updated_at,
            'deleted_at', NEW.deleted_at
        )
    );
END$$

CREATE TRIGGER student_profiles_after_delete
AFTER DELETE ON student_profiles
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('student_profiles', 'DELETE', OLD.user_id, JSON_OBJECT(
        'user_id', OLD.user_id,
        'phone', OLD.phone,
        'enrolled_on', OLD.enrolled_on,
        'status', OLD.status,
        'created_at', OLD.created_at,
        'updated_at', OLD.updated_at,
        'deleted_at', OLD.deleted_at
    ));
END$$

-- ====== COURSES ======
CREATE TRIGGER courses_after_insert
AFTER INSERT ON courses
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('courses', 'INSERT', NEW.id, JSON_OBJECT(
        'id', NEW.id,
        'course_name', NEW.course_name,
        'instructor_id', NEW.instructor_id,
        'duration_weeks', NEW.duration_weeks,
        'max_seats', NEW.max_seats,
        'status', NEW.status,
        'created_at', NEW.created_at,
        'updated_at', NEW.updated_at,
        'deleted_at', NEW.deleted_at
    ));
END$$

CREATE TRIGGER courses_after_update
AFTER UPDATE ON courses
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('courses', 'UPDATE', NEW.id,
        JSON_OBJECT(
            'id', OLD.id,
            'course_name', OLD.course_name,
            'instructor_id', OLD.instructor_id,
            'duration_weeks', OLD.duration_weeks,
            'max_seats', OLD.max_seats,
            'status', OLD.status,
            'created_at', OLD.created_at,
            'updated_at', OLD.updated_at,
            'deleted_at', OLD.deleted_at
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'course_name', NEW.course_name,
            'instructor_id', NEW.instructor_id,
            'duration_weeks', NEW.duration_weeks,
            'max_seats', NEW.max_seats,
            'status', NEW.status,
            'created_at', NEW.created_at,
            'updated_at', NEW.updated_at,
            'deleted_at', NEW.deleted_at
        )
    );
END$$

CREATE TRIGGER courses_after_delete
AFTER DELETE ON courses
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('courses', 'DELETE', OLD.id, JSON_OBJECT(
        'id', OLD.id,
        'course_name', OLD.course_name,
        'instructor_id', OLD.instructor_id,
        'duration_weeks', OLD.duration_weeks,
        'max_seats', OLD.max_seats,
        'status', OLD.status,
        'created_at', OLD.created_at,
        'updated_at', OLD.updated_at,
        'deleted_at', OLD.deleted_at
    ));
END$$

-- ====== ENROLLMENTS ======
CREATE TRIGGER enrollments_after_insert
AFTER INSERT ON enrollments
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('enrollments', 'INSERT', NEW.id, JSON_OBJECT(
        'id', NEW.id,
        'student_id', NEW.student_id,
        'course_id', NEW.course_id,
        'enrolled_date', NEW.enrolled_date,
        'status', NEW.status,
        'created_at', NEW.created_at,
        'updated_at', NEW.updated_at,
        'deleted_at', NEW.deleted_at
    ));
END$$

CREATE TRIGGER enrollments_after_update
AFTER UPDATE ON enrollments
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('enrollments', 'UPDATE', NEW.id,
        JSON_OBJECT(
            'id', OLD.id,
            'student_id', OLD.student_id,
            'course_id', OLD.course_id,
            'enrolled_date', OLD.enrolled_date,
            'status', OLD.status,
            'created_at', OLD.created_at,
            'updated_at', OLD.updated_at,
            'deleted_at', OLD.deleted_at
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'student_id', NEW.student_id,
            'course_id', NEW.course_id,
            'enrolled_date', NEW.enrolled_date,
            'status', NEW.status,
            'created_at', NEW.created_at,
            'updated_at', NEW.updated_at,
            'deleted_at', NEW.deleted_at
        )
    );
END$$

CREATE TRIGGER enrollments_after_delete
AFTER DELETE ON enrollments
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('enrollments', 'DELETE', OLD.id, JSON_OBJECT(
        'id', OLD.id,
        'student_id', OLD.student_id,
        'course_id', OLD.course_id,
        'enrolled_date', OLD.enrolled_date,
        'status', OLD.status,
        'created_at', OLD.created_at,
        'updated_at', OLD.updated_at,
        'deleted_at', OLD.deleted_at
    ));
END$$

-- ====== PERMISSIONS ======
CREATE TRIGGER permissions_after_insert
AFTER INSERT ON permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('permissions', 'INSERT', NEW.id, JSON_OBJECT(
        'id', NEW.id,
        'name', NEW.name,
        'created_at', NEW.created_at
    ));
END$$

CREATE TRIGGER permissions_after_update
AFTER UPDATE ON permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('permissions', 'UPDATE', NEW.id,
        JSON_OBJECT(
            'id', OLD.id,
            'name', OLD.name,
            'created_at', OLD.created_at
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'name', NEW.name,
            'created_at', NEW.created_at
        )
    );
END$$

CREATE TRIGGER permissions_after_delete
AFTER DELETE ON permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('permissions', 'DELETE', OLD.id, JSON_OBJECT(
        'id', OLD.id,
        'name', OLD.name,
        'created_at', OLD.created_at
    ));
END$$

-- ====== ROLE PERMISSIONS ======
CREATE TRIGGER role_permissions_after_insert
AFTER INSERT ON role_permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('role_permissions', 'INSERT', NEW.id, JSON_OBJECT(
        'id', NEW.id,
        'role', NEW.role,
        'permission_id', NEW.permission_id,
        'created_at', NEW.created_at
    ));
END$$

CREATE TRIGGER role_permissions_after_update
AFTER UPDATE ON role_permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('role_permissions', 'UPDATE', NEW.id,
        JSON_OBJECT(
            'id', OLD.id,
            'role', OLD.role,
            'permission_id', OLD.permission_id,
            'created_at', OLD.created_at
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'role', NEW.role,
            'permission_id', NEW.permission_id,
            'created_at', NEW.created_at
        )
    );
END$$

CREATE TRIGGER role_permissions_after_delete
AFTER DELETE ON role_permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('role_permissions', 'DELETE', OLD.id, JSON_OBJECT(
        'id', OLD.id,
        'role', OLD.role,
        'permission_id', OLD.permission_id,
        'created_at', OLD.created_at
    ));
END$$

-- ====== USER PERMISSIONS ======
CREATE TRIGGER user_permissions_after_insert
AFTER INSERT ON user_permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, new_data)
    VALUES ('user_permissions', 'INSERT', NEW.id, JSON_OBJECT(
        'id', NEW.id,
        'user_id', NEW.user_id,
        'permission_id', NEW.permission_id,
        'created_at', NEW.created_at
    ));
END$$

CREATE TRIGGER user_permissions_after_update
AFTER UPDATE ON user_permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('user_permissions', 'UPDATE', NEW.id,
        JSON_OBJECT(
            'id', OLD.id,
            'user_id', OLD.user_id,
            'permission_id', OLD.permission_id,
            'created_at', OLD.created_at
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'user_id', NEW.user_id,
            'permission_id', NEW.permission_id,
            'created_at', NEW.created_at
        )
    );
END$$

CREATE TRIGGER user_permissions_after_delete
AFTER DELETE ON user_permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data)
    VALUES ('user_permissions', 'DELETE', OLD.id, JSON_OBJECT(
        'id', OLD.id,
        'user_id', OLD.user_id,
        'permission_id', OLD.permission_id,
        'created_at', OLD.created_at
    ));
END$$

DELIMITER ;