<?php

class UserModel
{
  private $pdo;
  private $table = 'users';

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  public function getAll($role = null)
  {
    $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
    $params = [];

    if ($role) {
      $sql .= " AND role = :role";
      $params['role'] = $role;
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function find($id)
  {
    $stmt = $this->pdo->prepare("
      SELECT * 
      FROM {$this->table} 
      WHERE id = :id
      AND deleted_at IS NULL
      LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function create($data, $role)
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO {$this->table} (name, email, password, role)
      VALUES (:name, :email, :password, :role)
    ");

    return $stmt->execute([
      'name'     => $data['name'],
      'email'    => $data['email'],
      'password' => password_hash($data['password'], PASSWORD_DEFAULT),
      'role'     => $role
    ]);
  }

  public function softDelete($id)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table} 
      SET deleted_at = NOW()
      WHERE id = :id
    ");

    return $stmt->execute(['id' => $id]);
  }

  public function count()
  {
    $stmt = $this->pdo->query("
      SELECT COUNT(*) 
      FROM {$this->table}
      WHERE deleted_at IS NULL
    ");

    return $stmt->fetchColumn();
  }

  public function countByRole($role)
  {
    $stmt = $this->pdo->prepare("
      SELECT COUNT(*) 
      FROM {$this->table}
      WHERE role = ?
        AND deleted_at IS NULL
    ");

    $stmt->execute([$role]);

    return $stmt->fetchColumn();
  }

  public function countInactive()
  {
    $stmt = $this->pdo->query("
      SELECT COUNT(*) 
      FROM {$this->table}
      WHERE status = 0
        AND deleted_at IS NULL
    ");

    return $stmt->fetchColumn();
  }

  public function emailExists($email)
  {

    $stmt = $this->pdo->prepare("
      SELECT id 
      FROM {$this->table} 
      WHERE email = :email 
      AND deleted_at IS NULL
      LIMIT 1
    ");

    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
  }
}
