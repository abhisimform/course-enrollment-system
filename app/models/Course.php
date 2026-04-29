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

  public function instructorExists($id)
  {
    $stmt = $this->pdo->prepare("
      SELECT id
      FROM users
      WHERE id = :id
        AND role = 'teacher'
        AND deleted_at IS NULL
      LIMIT 1
    ");

    $stmt->execute(['id' => (int)$id]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
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

  public function getDataTableRecords($start, $length, $search, $orderBy, $orderDir, $filters = [], $userId = null)
  {
    $allowedColumns = [
      'c.id',
      'c.course_name',
      'u.name',
      'c.duration_weeks',
      'c.max_seats',
      'filled_seats',
      'available_seats',
      'c.status'
    ];

    $orderBy = in_array($orderBy, $allowedColumns, true) ? $orderBy : 'c.id';
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

    $conditions = [];
    $params = [];

    $deleted = $filters['deleted'] ?? '';
    if ($deleted === 'only') {
      $conditions[] = 'c.deleted_at IS NOT NULL';
    } elseif ($deleted !== 'with') {
      $conditions[] = 'c.deleted_at IS NULL';
    }

    if (($filters['status'] ?? '') !== '') {
      $conditions[] = 'c.status = :status';
      $params[':status'] = (int)$filters['status'];
    }

    if (!empty($filters['instructor_id'])) {
      $conditions[] = 'c.instructor_id = :instructor_id';
      $params[':instructor_id'] = (int)$filters['instructor_id'];
    }

    if ($search !== '') {
      $conditions[] = '(c.course_name LIKE :search OR u.name LIKE :search)';
      $params[':search'] = "%{$search}%";
    }

    $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = "
      SELECT
        c.*,
        u.name AS instructor_name,
        COUNT(e.id) AS filled_seats,
        (c.max_seats - COUNT(e.id)) AS available_seats,
        eu.id AS en_id,
        MAX(CASE WHEN eu.id IS NOT NULL THEN 1 ELSE 0 END) AS is_enrolled
      FROM {$this->table} c
      LEFT JOIN users u ON c.instructor_id = u.id
      LEFT JOIN enrollments e
        ON e.course_id = c.id
        AND e.deleted_at IS NULL
      LEFT JOIN enrollments eu
        ON eu.course_id = c.id
        AND eu.student_id = :user_id
        AND eu.deleted_at IS NULL
      {$whereSql}
      GROUP BY c.id
      ORDER BY {$orderBy} {$orderDir}
      LIMIT :start, :length
    ";

    $stmt = $this->pdo->prepare($sql);

    foreach ($params as $key => $value) {
      $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
      $stmt->bindValue($key, $value, $type);
    }

    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getFilteredCount($search, $filters = [])
  {
    $conditions = [];
    $params = [];

    $deleted = $filters['deleted'] ?? '';
    if ($deleted === 'only') {
      $conditions[] = 'c.deleted_at IS NOT NULL';
    } elseif ($deleted !== 'with') {
      $conditions[] = 'c.deleted_at IS NULL';
    }

    if (($filters['status'] ?? '') !== '') {
      $conditions[] = 'c.status = :status';
      $params[':status'] = (int)$filters['status'];
    }

    if (!empty($filters['instructor_id'])) {
      $conditions[] = 'c.instructor_id = :instructor_id';
      $params[':instructor_id'] = (int)$filters['instructor_id'];
    }

    if ($search !== '') {
      $conditions[] = '(c.course_name LIKE :search OR u.name LIKE :search)';
      $params[':search'] = "%{$search}%";
    }

    $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = "
      SELECT COUNT(DISTINCT c.id)
      FROM {$this->table} c
      LEFT JOIN users u ON c.instructor_id = u.id
      {$whereSql}
    ";

    $stmt = $this->pdo->prepare($sql);

    foreach ($params as $key => $value) {
      $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
      $stmt->bindValue($key, $value, $type);
    }

    $stmt->execute();
    return (int)$stmt->fetchColumn();
  }

  public function getDataTableTotalCount($filters = [])
  {
    return $this->getFilteredCount('', $filters);
  }
}
