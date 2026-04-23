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

  public function getAll($options, $userId = null)
  {
    $joinSql = implode(' ', $options['joins']);

    $sql = "
      SELECT 
        c.*, 
        u.name as instructor_name,

        COUNT(e.id) AS filled_seats,
        (c.max_seats - COUNT(e.id)) AS available_seats,

        eu.id as en_id,
        -- ✅ Check if current student is enrolled
        MAX(CASE 
            WHEN eu.id IS NOT NULL THEN 1 
            ELSE 0 
        END) AS is_enrolled

      FROM {$this->table} c
      $joinSql

      LEFT JOIN enrollments e 
        ON e.course_id = c.id 
        AND e.deleted_at IS NULL

      LEFT JOIN enrollments eu 
        ON eu.course_id = c.id 
        AND eu.student_id = :user_id
        AND eu.deleted_at IS NULL

      WHERE {$options['whereSql']}

      GROUP BY c.id

      ORDER BY {$options['orderBy']}
      LIMIT :offset, :limit
    ";

    $stmt = $this->pdo->prepare($sql);

    foreach ($options['bindings'] as $k => $v) {
      $stmt->bindValue($k, $v);
    }

    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $options['offset'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', $options['limit'], PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function countAll($options)
  {
    $joinSql = implode(' ', $options['joins']);

    $sql = "SELECT COUNT(*) 
      FROM {$this->table} c
      $joinSql 
      WHERE {$options['whereSql']}
    ";

    $stmt = $this->pdo->prepare($sql);

    foreach ($options['bindings'] as $key => $value) {
      $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    return $stmt->fetchColumn();
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

  public function getByUser($userId)
  {
    $stmt = $this->pdo->prepare("
      SELECT c.*
      FROM courses c
      INNER JOIN enrollments e ON e.course_id = c.id
      WHERE e.student_id = ?
        AND e.status = 'active'
        AND c.deleted_at IS NULL
    ");

    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
