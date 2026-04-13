USE enrollment_db;

select * from users;

select * from students;

select * from audit_logs;
    
select * from permissions;
   
ALTER TABLE students
MODIFY COLUMN updated_at TIMESTAMP
DEFAULT CURRENT_TIMESTAMP
ON UPDATE CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role, permission_id)
SELECT 'admin', id FROM permissions;

INSERT INTO role_permissions (role, permission_id)
SELECT 'teacher', id FROM permissions
WHERE name IN (
    'view_student',
    'view_course',
    'enroll_student',
    'view_enrollment'
);

SELECT p.name
FROM permissions p
JOIN role_permissions rp ON p.id = rp.permission_id
JOIN users u ON u.role = rp.role
WHERE u.id = 1;