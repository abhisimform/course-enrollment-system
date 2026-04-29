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
      SELECT u.id, u.name, u.email, u.role, sp.enrolled_on, sp.phone
      FROM {$this->table} u
      LEFT JOIN student_profiles sp ON sp.user_id = u.id
      WHERE id = :id
      AND u.{$this->baseCondition()}
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

  public function updateProfile($userId, array $data)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table}
      SET name = :name,
        email = :email,
        updated_at = NOW()
      WHERE id = :id
      AND {$this->baseCondition()}
    ");

    $stmt->execute([
      'id' => $userId,
      'name' => $data['name'],
      'email' => $data['email']
    ]);

    if (($data['role'] ?? '') === 'student') {
      $profileStmt = $this->pdo->prepare("
        INSERT INTO student_profiles (user_id, phone, enrolled_on, status)
        VALUES (:user_id, :phone, :enrolled_on, 1)
        ON DUPLICATE KEY UPDATE
          phone = VALUES(phone),
          enrolled_on = VALUES(enrolled_on),
          deleted_at = NULL,
          updated_at = NOW()
      ");

      $profileStmt->execute([
        'user_id' => $userId,
        'phone' => $data['phone'] ?: null,
        'enrolled_on' => $data['enrolled_on'] ?: null
      ]);
    }

    return true;
  }

  public function changePassword($userId, $currentPassword, $newPassword)
  {
    $stmt = $this->pdo->prepare("
      SELECT password
      FROM {$this->table}
      WHERE id = :id
      AND {$this->baseCondition()}
      LIMIT 1
    ");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($currentPassword, $user['password'])) {
      return false;
    }

    $update = $this->pdo->prepare("
      UPDATE {$this->table}
      SET password = :password,
        updated_at = NOW()
      WHERE id = :id
    ");

    return $update->execute([
      'id' => $userId,
      'password' => password_hash($newPassword, PASSWORD_BCRYPT)
    ]);
  }

  public function emailExistsForOtherUser($email, $userId)
  {
    $stmt = $this->pdo->prepare("
      SELECT id
      FROM {$this->table}
      WHERE email = :email
      AND id <> :id
      AND {$this->baseCondition()}
      LIMIT 1
    ");

    $stmt->execute([
      'email' => $email,
      'id' => $userId
    ]);

    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
  }
}
