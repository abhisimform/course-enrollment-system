DELIMITER $$

DROP TRIGGER IF EXISTS users_after_update;
CREATE TRIGGER users_after_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('users', 'UPDATE', NEW.id,
        CONCAT('Old Name:', OLD.name, ', Old Email:', OLD.email, ', Old Password:', OLD.password, 
               ', Old Role:', OLD.role, ', Old Status:', OLD.status, ', Old Created At:', OLD.created_at, 
               ', Old Updated At:', OLD.updated_at, ', Old Deleted At:', OLD.deleted_at),
        CONCAT('New Name:', NEW.name, ', New Email:', NEW.email, ', New Password:', NEW.password, 
               ', New Role:', NEW.role, ', New Status:', NEW.status, ', New Created At:', NEW.created_at, 
               ', New Updated At:', NEW.updated_at, ', New Deleted At:', NEW.deleted_at));
END$$

DROP TRIGGER IF EXISTS students_after_update;
CREATE TRIGGER students_after_update
AFTER UPDATE ON students
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('students', 'UPDATE', NEW.id,
        CONCAT('Old Name:', OLD.name, ', Old Email:', OLD.email, ', Old Phone:', OLD.phone, 
               ', Old Enrolled On:', OLD.enrolled_on, ', Old Status:', OLD.status, 
               ', Old Created At:', OLD.created_at, ', Old Updated At:', OLD.updated_at, 
               ', Old Deleted At:', OLD.deleted_at),
        CONCAT('New Name:', NEW.name, ', New Email:', NEW.email, ', New Phone:', NEW.phone, 
               ', New Enrolled On:', NEW.enrolled_on, ', New Status:', NEW.status, 
               ', New Created At:', NEW.created_at, ', New Updated At:', NEW.updated_at, 
               ', New Deleted At:', NEW.deleted_at));
END$$

DROP TRIGGER IF EXISTS courses_after_update;
CREATE TRIGGER courses_after_update
AFTER UPDATE ON courses
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('courses', 'UPDATE', NEW.id,
        CONCAT('Old Course Name:', OLD.course_name, ', Old Instructor ID:', OLD.instructor_id, 
               ', Old Duration Weeks:', OLD.duration_weeks, ', Old Max Seats:', OLD.max_seats, 
               ', Old Status:', OLD.status, ', Old Created At:', OLD.created_at, 
               ', Old Updated At:', OLD.updated_at, ', Old Deleted At:', OLD.deleted_at),
        CONCAT('New Course Name:', NEW.course_name, ', New Instructor ID:', NEW.instructor_id, 
               ', New Duration Weeks:', NEW.duration_weeks, ', New Max Seats:', NEW.max_seats, 
               ', New Status:', NEW.status, ', New Created At:', NEW.created_at, 
               ', New Updated At:', NEW.updated_at, ', New Deleted At:', NEW.deleted_at));
END$$

DROP TRIGGER IF EXISTS enrollments_after_update;
CREATE TRIGGER enrollments_after_update
AFTER UPDATE ON enrollments
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('enrollments', 'UPDATE', NEW.id,
        CONCAT('Old Student ID:', OLD.student_id, ', Old Course ID:', OLD.course_id, 
               ', Old Enrolled Date:', OLD.enrolled_date, ', Old Status:', OLD.status, 
               ', Old Created At:', OLD.created_at, ', Old Updated At:', OLD.updated_at, 
               ', Old Deleted At:', OLD.deleted_at),
        CONCAT('New Student ID:', NEW.student_id, ', New Course ID:', NEW.course_id, 
               ', New Enrolled Date:', NEW.enrolled_date, ', New Status:', NEW.status, 
               ', New Created At:', NEW.created_at, ', New Updated At:', NEW.updated_at, 
               ', New Deleted At:', NEW.deleted_at));
END$$

DROP TRIGGER IF EXISTS permissions_after_update;
CREATE TRIGGER permissions_after_update
AFTER UPDATE ON permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('permissions', 'UPDATE', NEW.id,
        CONCAT('Old Permission Name:', OLD.name),
        CONCAT('New Permission Name:', NEW.name));
END$$

DROP TRIGGER IF EXISTS role_permissions_after_update;
CREATE TRIGGER role_permissions_after_update
AFTER UPDATE ON role_permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('role_permissions', 'UPDATE', NEW.id,
        CONCAT('Old Role:', OLD.role, ', Old Permission ID:', OLD.permission_id),
        CONCAT('New Role:', NEW.role, ', New Permission ID:', NEW.permission_id));
END$$

DROP TRIGGER IF EXISTS user_permissions_after_update;
CREATE TRIGGER user_permissions_after_update
AFTER UPDATE ON user_permissions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, action_type, record_id, old_data, new_data)
    VALUES ('user_permissions', 'UPDATE', NEW.id,
        CONCAT('Old User ID:', OLD.user_id, ', Old Permission ID:', OLD.permission_id),
        CONCAT('New User ID:', NEW.user_id, ', New Permission ID:', NEW.permission_id));
END$$