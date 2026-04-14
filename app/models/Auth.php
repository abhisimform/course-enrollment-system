<?php

class AuthModel
{
  private $pdo;
  private $table = 'users';

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  private function baseCondition()
  {
    return "deleted_at IS NULL";
  }

  public function login($email, $password)
  {
    $stmt = $this->pdo->prepare("
            SELECT * 
            FROM {$this->table} 
            WHERE email = :email 
            AND {$this->baseCondition()}
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
            FROM {$this->table}
            WHERE id = :id
            AND {$this->baseCondition()}
            LIMIT 1
        ");

    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
  }

  public function register($data)
  {
    $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (name, email, password, role)
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
    $sql = "SELECT p.name
      FROM permissions p
      LEFT JOIN role_permissions rp ON rp.permission_id = p.id
      LEFT JOIN user_permissions up ON up.user = u.id
      JOIN {$this->table} u ON u.role = rp.role
      WHERE u.id = :user_id
    ";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    return array_column($stmt->fetchAll(), 'name');
  }
}
