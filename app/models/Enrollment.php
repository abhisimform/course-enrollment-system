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
    $stmt = $this->pdo->prepare("
    SELECT e.id, e.enrolled_date, e.course_id, c.course_name, c.duration_weeks
    FROM {$this->table} e
    INNER JOIN courses c ON c.id = e.course_id
    WHERE e.student_id = $studentId
    AND e.deleted_at IS NULL
    ORDER BY e.id DESC
    LIMIT $limit OFFSET $offset
  ");

    $stmt->execute();
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
}
