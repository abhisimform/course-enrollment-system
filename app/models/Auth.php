<?php

class AuthModel
{
  private $pdo;

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  public function login($email, $password)
  {
    $stmt = $this->pdo->prepare("
            SELECT * 
            FROM users 
            WHERE email = :email 
            AND deleted_at IS NULL
            LIMIT 1
        ");

    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
      return false;
    }

    if (!password_verify($password, $user['password'])) {
      return false;
    }

    return $user;
  }

  public function findUser($id)
  {
    $stmt = $this->pdo->prepare("
            SELECT id, name, email, role
            FROM users
            WHERE id = :id
            AND deleted_at IS NULL
            LIMIT 1
        ");

    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
  }

  public function register($data)
  {
    $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, password, role)
            VALUES (:name, :email, :password, :role)
        ");

    return $stmt->execute([
      'name'     => $data['name'],
      'email'    => $data['email'],
      'password' => password_hash($data['password'], PASSWORD_BCRYPT),
      'role'     => $data['role'] ?? 'teacher'
    ]);
  }

  public function getPermissions($userId)
  {
    $stmt = $this->pdo->prepare("
        SELECT p.name
        FROM permissions p
        LEFT JOIN role_permissions rp ON rp.permission_id = p.id
        JOIN users u ON u.role = rp.role
        WHERE u.id = :user_id
    ");

    $stmt->execute(['user_id' => $userId]);

    return array_column($stmt->fetchAll(), 'name');
  }
}
