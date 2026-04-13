<?php

class TeacherModel
{
  private $pdo;
  private $table = 'users';

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  private function baseCondition()
  {
    return "role = 'teacher' AND deleted_at IS NULL";
  }

  public function getTeachers($search, $currentPage, $perPage, $deleted = false)
  {
    $sql = "SELECT * FROM {$this->table} WHERE role = 'teacher'";
    $sql .= $deleted ? " AND deleted_at IS NOT NULL" : " AND deleted_at IS NULL";

    if ($search) {
      $sql .= " AND (name LIKE :name_search OR email LIKE :email_search)";
    }

    $sql .= " ORDER BY id DESC LIMIT :offset, :perPage";

    $stmt = $this->pdo->prepare($sql);
    if ($search) {
      $stmt->bindValue(':name_search', "%$search%");
      $stmt->bindValue(':email_search', "%$search%");
    }
    $stmt->bindValue(':offset', ($currentPage - 1) * $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    // dd($stmt, $search, $currentPage, $perPage, $deleted);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function countTeachers($search, $deleted = false)
  {
    $sql = "SELECT COUNT(*) FROM {$this->table} WHERE role='teacher'";
    $sql .= $deleted ? " AND deleted_at IS NOT NULL" : " AND deleted_at IS NULL";

    if ($search) $sql .= " AND (name LIKE :name_search OR email LIKE :email_search)";

    $stmt = $this->pdo->prepare($sql);
    if ($search) {
      $stmt->bindValue(':name_search', "%$search%");
      $stmt->bindValue(':email_search', "%$search%");
    }
    $stmt->execute();

    return $stmt->fetchColumn();
  }

  public function getAll()
  {
    $stmt = $this->pdo->prepare("
      SELECT * 
      FROM {$this->table} 
      WHERE {$this->baseCondition()}
      ORDER BY id DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function find($id)
  {
    $stmt = $this->pdo->prepare("
      SELECT * 
      FROM {$this->table} 
      WHERE id = :id 
      AND {$this->baseCondition()}
      LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO {$this->table} (name, email, password, role)
      VALUES (:name, :email, :password, 'teacher')
    ");
    return $stmt->execute([
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => password_hash($data['password'], PASSWORD_BCRYPT)
    ]);
  }

  public function update($id, $data)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table} 
      SET name = :name, email = :email, updated_at = NOW()
      WHERE id = :id AND {$this->baseCondition()}
    ");
    return $stmt->execute([
      'id' => $id,
      'name' => $data['name'],
      'email' => $data['email']
    ]);
  }

  public function softDelete($id)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table} 
      SET deleted_at = NOW()
      WHERE id = :id
      AND role = 'teacher'
    ");
    return $stmt->execute(['id' => $id]);
  }

  public function restore($id)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table} 
      SET deleted_at = NULL
      WHERE id = :id
      AND role = 'teacher'
    ");
    return $stmt->execute(['id' => $id]);
  }

  public function emailExists($email)
  {
    $stmt = $this->pdo->prepare("
      SELECT id 
      FROM {$this->table} 
      WHERE email = :email 
      AND {$this->baseCondition()}
      LIMIT 1
    ");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
  }

  public function count()
  {
    $stmt = $this->pdo->query("
      SELECT COUNT(*) as total 
      FROM {$this->table} 
      WHERE {$this->baseCondition()}
    ");
    return $stmt->fetch()['total'];
  }
}
