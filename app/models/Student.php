<?php

class StudentModel
{
  private $pdo;
  private $table = 'users';

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  private function baseCondition()
  {
    return "role = 'student' AND deleted_at IS NULL";
  }

  public function getStudents($search, $page, $perPage, $deleted = false)
  {
    $sql = "SELECT * 
      FROM {$this->table} 
      WHERE role = 'student'
    ";

    $sql .= $deleted ? " AND deleted_at IS NOT NULL" : " AND deleted_at IS NULL";

    if ($search) {
      $sql .= " AND (name LIKE :search OR email LIKE :search)";
    }

    $sql .= " ORDER BY id DESC LIMIT :offset, :perPage";

    $stmt = $this->pdo->prepare($sql);
    if ($search) $stmt->bindValue(':search', "%$search%");
    $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function countStudents($search, $deleted = false)
  {
    $sql = "SELECT COUNT(*) FROM {$this->table} WHERE role = 'student'";
    $sql .= $deleted ? " AND deleted_at IS NOT NULL" : " AND deleted_at IS NULL";

    if ($search) $sql .= " AND (name LIKE :search OR email LIKE :search)";

    $stmt = $this->pdo->prepare($sql);
    if ($search) $stmt->bindValue(':search', "%$search%");
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
      VALUES (:name, :email, :password, 'student')
    ");
    return $stmt->execute([
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => password_hash($data['password'], PASSWORD_BCRYPT)
    ]);
  }

  public function bulkInsert($data)
  {
    $sql = "INSERT INTO users (name, email, password, role) VALUES ";
    $values = [];
    $params = [];

    foreach ($data as $i => $row) {
      $values[] = "(:name$i, :email$i, :password$i, 'student')";
      $params["name$i"] = $row['name'];
      $params["email$i"] = $row['email'];
      $params["password$i"] = password_hash($row['password'], PASSWORD_BCRYPT);
    }

    $sql .= implode(',', $values);

    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($params);
  }

  public function update($id, $data)
  {
    if (!empty($data['password'])) {
      $stmt = $this->pdo->prepare("
          UPDATE {$this->table} 
          SET name = :name,
            email = :email,
            password = :password,
            updated_at = NOW()
          WHERE id = :id
          AND {$this->baseCondition()}
        ");
      return $stmt->execute([
        'id' => $id,
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => password_hash($data['password'], PASSWORD_BCRYPT)
      ]);
    } else {
      $stmt = $this->pdo->prepare("
        UPDATE {$this->table} 
        SET name = :name,
          email = :email,
          updated_at = NOW()
        WHERE id = :id
        AND {$this->baseCondition()}
      ");
      return $stmt->execute([
        'id' => $id,
        'name' => $data['name'],
        'email' => $data['email']
      ]);
    }
  }

  public function softDelete($id)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table} 
      SET deleted_at = NOW()
      WHERE id = :id
      AND role = 'student'
    ");
    return $stmt->execute(['id' => $id]);
  }

  public function restore($id)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table} 
      SET deleted_at = NULL
      WHERE id = :id
      AND role = 'student'
    ");
    return $stmt->execute(['id' => $id]);
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

  public function getEnrolledCourses($studentId)
  {
    $stmt = $this->pdo->prepare("
      SELECT c.* 
      FROM courses c
      JOIN enrollments e ON e.course_id = c.id
      WHERE e.student_id = :student_id
      AND e.status = 'active'
      AND c.deleted_at IS NULL
    ");
    $stmt->execute(['student_id' => $studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getDataTableRecords($start, $length, $search, $orderBy, $orderDir, $deleted = false)
  {
    $allowedColumns = ['id', 'name', 'email'];
    $orderBy = in_array($orderBy, $allowedColumns, true) ? $orderBy : 'id';
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

    $sql = "SELECT id, name, email, deleted_at
      FROM {$this->table}
      WHERE role = 'student'";

    $sql .= $deleted ? " AND deleted_at IS NOT NULL" : " AND deleted_at IS NULL";

    if ($search !== '') {
      $sql .= " AND (name LIKE :search OR email LIKE :search)";
    }

    $sql .= " ORDER BY {$orderBy} {$orderDir} LIMIT :start, :length";

    $stmt = $this->pdo->prepare($sql);

    if ($search !== '') {
      $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
    }

    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getFilteredCount($search, $deleted = false)
  {
    $sql = "SELECT COUNT(*)
      FROM {$this->table}
      WHERE role = 'student'";

    $sql .= $deleted ? " AND deleted_at IS NOT NULL" : " AND deleted_at IS NULL";

    if ($search !== '') {
      $sql .= " AND (name LIKE :search OR email LIKE :search)";
    }

    $stmt = $this->pdo->prepare($sql);

    if ($search !== '') {
      $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
    }

    $stmt->execute();
    return (int)$stmt->fetchColumn();
  }

  public function getDataTableTotalCount($deleted = false)
  {
    $sql = "SELECT COUNT(*)
      FROM {$this->table}
      WHERE role = 'student'";

    $sql .= $deleted ? " AND deleted_at IS NOT NULL" : " AND deleted_at IS NULL";

    $stmt = $this->pdo->query($sql);
    return (int)$stmt->fetchColumn();
  }
}
