USE enrollment_db;

select * from users;

select * from courses;

select * from audit_logs;	
    
select * from permissions;
select * from user_permissions;
select * from role_permissions;

truncate table permissions;
desc user_permissions;
desc role_permissions;

select role from users where id = 6;

SELECT p.id, p.name
FROM permissions p
JOIN role_permissions rp ON rp.permission_id = p.id
JOIN users u ON u.role = rp.role AND u.id = 6
JOIN user_permissions up ON up.user_id = u.id

(SELECT DISTINCT p.name
FROM permissions p
JOIN role_permissions rp 
    ON rp.permission_id = p.id
JOIN users u 
    ON u.role = rp.role
WHERE u.id = 6
)
UNION
(
SELECT DISTINCT p.name
FROM permissions p
JOIN user_permissions up 
    ON up.permission_id = p.id
WHERE up.user_id = 6
);

SELECT DISTINCT p.name
FROM permissions p
LEFT JOIN role_permissions rp ON rp.permission_id = p.id
LEFT JOIN users u ON u.role = rp.role
LEFT JOIN user_permissions up ON up.permission_id = p.id
WHERE u.id = 6
   OR up.user_id = 6;
