<?php

class CourseModel
{
  private $pdo;
  private $table = 'courses';

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  private function baseCondition()
  {
    return "deleted_at IS NULL";
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

  public function getCourses($search, $status, $instructorId, $currentPage, $perPage)
  {
    $sql = "SELECT c.*, u.name AS instructor_name FROM {$this->table} c
                LEFT JOIN users u ON c.instructor_id = u.id
                WHERE c.{$this->baseCondition()}";

    if ($search) {
      $sql .= " AND c.course_name LIKE :search";
    }

    if ($status !== '') {
      $sql .= " AND c.status = :status";
    }

    if ($instructorId) {
      $sql .= " AND c.instructor_id = :instructor";
    }

    $sql .= " ORDER BY c.id DESC LIMIT :offset, :perPage";

    $stmt = $this->pdo->prepare($sql);

    if ($search) {
      $stmt->bindValue(':search', '%' . $search . '%');
    }
    if ($status !== '') {
      $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    }
    if ($instructorId) {
      $stmt->bindValue(':instructor', $instructorId, PDO::PARAM_INT);
    }

    $offset = ($currentPage - 1) * $perPage;
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getInstructors()
  {
    $stmt = $this->pdo->prepare("SELECT id, name FROM users WHERE role = 'teacher'");
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
            INSERT INTO {$this->table} (course_name, instructor_id, duration_weeks, max_seats, status)
            VALUES (:course_name, :instructor_id, :duration_weeks, :max_seats, :status)
        ");

    return $stmt->execute([
      'course_name'   => $data['course_name'],
      'instructor_id' => $data['instructor_id'],
      'duration_weeks' => $data['duration_weeks'],
      'max_seats'     => $data['max_seats'],
      'status'        => $data['status']
    ]);
  }

  public function update($id, $data)
  {
    $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET course_name = :course_name,
                instructor_id = :instructor_id,
                duration_weeks = :duration_weeks,
                max_seats = :max_seats,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
            AND {$this->baseCondition()}
        ");

    return $stmt->execute([
      'id'            => $id,
      'course_name'   => $data['course_name'],
      'instructor_id' => $data['instructor_id'],
      'duration_weeks' => $data['duration_weeks'],
      'max_seats'     => $data['max_seats'],
      'status'        => $data['status']
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

  public function restore($id)
  {
    $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET deleted_at = NULL
            WHERE id = :id
        ");

    return $stmt->execute(['id' => $id]);
  }

  public function courseNameExists($courseName)
  {
    $stmt = $this->pdo->prepare("
            SELECT id 
            FROM {$this->table} 
            WHERE course_name = :course_name 
            AND {$this->baseCondition()}
            LIMIT 1
        ");

    $stmt->execute(['course_name' => $courseName]);
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

  public function countCourses($search, $status, $instructorId)
  {
    $sql = "SELECT COUNT(*) FROM {$this->table} c WHERE c.{$this->baseCondition()}";

    if ($search) {
      $sql .= " AND c.course_name LIKE :search";
    }

    if ($status !== '') {
      $sql .= " AND c.status = :status";
    }

    if ($instructorId) {
      $sql .= " AND c.instructor_id = :instructor";
    }

    $stmt = $this->pdo->prepare($sql);

    if ($search) {
      $stmt->bindValue(':search', '%' . $search . '%');
    }
    if ($status !== '') {
      $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    }
    if ($instructorId) {
      $stmt->bindValue(':instructor', $instructorId, PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetchColumn();
  }

  public function getCoursesByInstructor($instructorId)
  {
    $stmt = $this->pdo->prepare("
        SELECT * 
        FROM {$this->table} 
        WHERE instructor_id = :instructor_id 
        AND {$this->baseCondition()} 
        ORDER BY id DESC
    ");

    $stmt->execute(['instructor_id' => $instructorId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAvailableCourses()
  {
    $stmt = $this->pdo->prepare("
        SELECT * 
        FROM {$this->table} 
        WHERE status = 1 
        AND {$this->baseCondition()}
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getCourseByName($courseName)
  {
    $stmt = $this->pdo->prepare("
        SELECT * 
        FROM {$this->table} 
        WHERE course_name = :course_name 
        AND {$this->baseCondition()}
        LIMIT 1
    ");
    $stmt->execute(['course_name' => $courseName]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
