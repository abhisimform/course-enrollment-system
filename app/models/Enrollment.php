<?php

class EnrollmentModel
{
  private $pdo;
  private $table = 'enrollments';

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  private function baseCondition()
  {
    return "deleted_at IS NULL";
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

  public function countActive()
  {
    $stmt = $this->pdo->query("
      SELECT COUNT(*) 
      FROM {$this->table}
      WHERE status = 'active'
        AND {$this->baseCondition()}
    ");

    return $stmt->fetchColumn();
  }

  public function enroll($studentId, $courseId)
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO {$this->table}
      (student_id, course_id, enrolled_date)
      VALUES (?, ?, CURDATE())
    ");

    return $stmt->execute([$studentId, $courseId]);
  }

  public function cancel($id)
  {
    $stmt = $this->pdo->prepare("
      DELETE FROM {$this->table}
      WHERE id = ?
    ");

    return $stmt->execute([$id]);
  }

  public function delete($id)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table}
      SET deleted_at = NOW()
      WHERE id = ?
    ");

    return $stmt->execute([$id]);
  }

  public function restore($id)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table}
      SET deleted_at = NULL
      WHERE id = ?
    ");

    return $stmt->execute([$id]);
  }

  public function getAll($filters = [], $limit = 10, $offset = 0)
  {
    $sql = "
      SELECT e.*, 
        u.name AS student_name,
        c.course_name
      FROM {$this->table} e
      INNER JOIN users u ON u.id = e.student_id
      INNER JOIN courses c ON c.id = e.course_id
      WHERE e.deleted_at IS NULL
    ";

    $params = [];

    if (!empty($filters['search'])) {
      $sql .= " AND (u.name LIKE :search OR c.course_name LIKE :search)";
      $params['search'] = '%' . $filters['search'] . '%';
    }

    if (!empty($filters['status'])) {
      $sql .= " AND e.status = :status";
      $params['status'] = $filters['status'];
    }

    if (!empty($filters['student_id'])) {
      $sql .= " AND e.student_id = :student_id";
      $params['student_id'] = $filters['student_id'];
    }

    if (!empty($filters['course_id'])) {
      $sql .= " AND e.course_id = :course_id";
      $params['course_id'] = $filters['course_id'];
    }

    $sql .= " ORDER BY e.id DESC LIMIT $limit OFFSET $offset";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function countAll($filters = [])
  {
    $sql = "
    SELECT COUNT(*) 
    FROM {$this->table} e
    INNER JOIN users u ON u.id = e.student_id
    INNER JOIN courses c ON c.id = e.course_id
    WHERE e.deleted_at IS NULL
  ";

    $params = [];

    if (!empty($filters['search'])) {
      $sql .= " AND (u.name LIKE :search OR c.course_name LIKE :search)";
      $params['search'] = '%' . $filters['search'] . '%';
    }

    if (!empty($filters['status'])) {
      $sql .= " AND e.status = :status";
      $params['status'] = $filters['status'];
    }

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchColumn();
  }

  public function getByStudent($studentId, $limit = 10, $offset = 0)
  {
    $studentId = (int) $studentId;
    $limit = max(0, (int) $limit);
    $offset = max(0, (int) $offset);

    $stmt = $this->pdo->prepare("
      SELECT e.id, e.enrolled_date, e.course_id, c.course_name, c.duration_weeks
      FROM {$this->table} e
      INNER JOIN courses c ON c.id = e.course_id
      WHERE e.student_id = :student_id
      AND e.deleted_at IS NULL
      ORDER BY e.id DESC
      LIMIT $limit OFFSET $offset
    ");

    $stmt->execute([
      'student_id' => $studentId
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function countByStudent($studentId)
  {
    $stmt = $this->pdo->prepare("
    SELECT COUNT(*) 
    FROM {$this->table}
    WHERE student_id = ?
    AND deleted_at IS NULL
  ");

    $stmt->execute([$studentId]);
    return $stmt->fetchColumn();
  }

  public function getByUser($userId)
  {
    $stmt = $this->pdo->prepare("
      SELECT 
        e.id AS enrollment_id,
        e.student_id,
        e.course_id,
        e.enrolled_date,
        e.status AS enrollment_status,
        
        c.course_name,
        c.duration_weeks,
        c.max_seats,
        c.status AS course_status

      FROM enrollments e
      INNER JOIN courses c ON c.id = e.course_id
      WHERE e.student_id = ?
        AND e.deleted_at IS NULL
        AND c.deleted_at IS NULL
    ");

    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getDataTableRecords($start, $length, $search, $orderBy, $orderDir, $filters = [], $studentId = null)
  {
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

    if ($studentId !== null) {
      $allowedColumns = ['e.id', 'c.course_name', 'e.status', 'e.enrolled_date'];
      $orderBy = in_array($orderBy, $allowedColumns, true) ? $orderBy : 'e.id';

      $sql = "
        SELECT e.id, e.status, e.enrolled_date, c.course_name, c.duration_weeks
        FROM {$this->table} e
        INNER JOIN courses c ON c.id = e.course_id
        WHERE e.student_id = :student_id
          AND e.deleted_at IS NULL
      ";

      if ($search !== '') {
        $sql .= " AND c.course_name LIKE :search";
      }

      $sql .= " ORDER BY {$orderBy} {$orderDir} LIMIT :start, :length";

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':student_id', (int)$studentId, PDO::PARAM_INT);

      if ($search !== '') {
        $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
      }
    } else {
      $allowedColumns = ['e.id', 'u.name', 'c.course_name', 'e.status', 'e.enrolled_date'];
      $orderBy = in_array($orderBy, $allowedColumns, true) ? $orderBy : 'e.id';

      $sql = "
        SELECT e.id, e.status, e.enrolled_date, u.name AS student_name, c.course_name
        FROM {$this->table} e
        INNER JOIN users u ON u.id = e.student_id
        INNER JOIN courses c ON c.id = e.course_id
        WHERE e.deleted_at IS NULL
      ";

      if ($search !== '') {
        $sql .= " AND (u.name LIKE :search OR c.course_name LIKE :search)";
      }

      if (!empty($filters['status'])) {
        $sql .= " AND e.status = :status";
      }

      $sql .= " ORDER BY {$orderBy} {$orderDir} LIMIT :start, :length";

      $stmt = $this->pdo->prepare($sql);

      if ($search !== '') {
        $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
      }

      if (!empty($filters['status'])) {
        $stmt->bindValue(':status', $filters['status'], PDO::PARAM_STR);
      }
    }

    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getFilteredCount($search, $filters = [], $studentId = null)
  {
    if ($studentId !== null) {
      $sql = "
        SELECT COUNT(*)
        FROM {$this->table} e
        INNER JOIN courses c ON c.id = e.course_id
        WHERE e.student_id = :student_id
          AND e.deleted_at IS NULL
      ";

      if ($search !== '') {
        $sql .= " AND c.course_name LIKE :search";
      }

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':student_id', (int)$studentId, PDO::PARAM_INT);

      if ($search !== '') {
        $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
      }
    } else {
      $sql = "
        SELECT COUNT(*)
        FROM {$this->table} e
        INNER JOIN users u ON u.id = e.student_id
        INNER JOIN courses c ON c.id = e.course_id
        WHERE e.deleted_at IS NULL
      ";

      if ($search !== '') {
        $sql .= " AND (u.name LIKE :search OR c.course_name LIKE :search)";
      }

      if (!empty($filters['status'])) {
        $sql .= " AND e.status = :status";
      }

      $stmt = $this->pdo->prepare($sql);

      if ($search !== '') {
        $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
      }

      if (!empty($filters['status'])) {
        $stmt->bindValue(':status', $filters['status'], PDO::PARAM_STR);
      }
    }

    $stmt->execute();
    return (int)$stmt->fetchColumn();
  }

  public function getDataTableTotalCount($filters = [], $studentId = null)
  {
    return $this->getFilteredCount('', $filters, $studentId);
  }
}
