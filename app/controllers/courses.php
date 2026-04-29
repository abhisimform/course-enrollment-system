<?php

require_once BASE_PATH . "/app/models/Course.php";

class Courses extends BaseController
{
  private $courseModel;

  public function __construct()
  {
    requireLogin();
    $this->courseModel = new CourseModel();
  }

  public function index()
  {
    Rbac::require('course.view_all');

    $options = QueryBuilder::build([
      'query' => $_GET,
      'filterableFields' => [
        'status' => [
          'column' => 'c.status',
          'type' => 'int'
        ],
        'instructor_id' => [
          'column' => 'c.instructor_id',
          'type' => 'int'
        ]
      ],
      'searchableFields' => [
        'c.course_name',
        'u.name'
      ],
      'joins' => [
        "LEFT JOIN users u ON c.instructor_id = u.id"
      ],
      'allowedSorts' => [
        'id' => 'c.id',
        'course_name' => 'c.course_name',
        'instructor_name' => 'u.name',
        'status' => 'c.status'
      ],
      'defaultSort' => 'id',
      'defaultOrder' => 'ASC',
      'deletedColumn' => 'c.deleted_at',
      'defaultLimit' => 10,
      'maxLimit' => 100
    ]);

    $user = $_SESSION['user'];
    $userId = $user['id'];

    $courses = $this->courseModel->getAll($options, $userId);
    $total = $this->courseModel->countAll($options);

    $totalPages = ceil($total / $options['limit']);
    $instructors = $this->courseModel->getInstructors();

    return $this->render('courses/index', [
      'courses' => $courses,
      'pagination' => [
        'totalItems' => $total,
        'totalPages' => $totalPages,
        'currentPage' => $options['page'],
        'limit' => $options['limit']
      ],
      'filters' => $_GET,
      'instructors' => $instructors
    ]);
  }

  public function create()
  {
    Rbac::require('course.create');

    $errors = [];

    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $csrf_token     = $_POST['csrf_token'] ?? '';
      $course_name    = trim($_POST['course_name'] ?? '');
      $instructor_id  = $_POST['instructor_id'] ?? '';
      $duration_weeks = $_POST['duration_weeks'] ?? '';
      $max_seats      = $_POST['max_seats'] ?? '';
      $status         = $_POST['status'] ?? '1';

      if ($csrf_token === '' || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors['csrf_token'] = 'Invalid request. Please refresh the form and try again.';
      }

      if ($course_name === '') {
        $errors['course_name'] = 'Course name is required';
      } elseif (mb_strlen($course_name) > 150) {
        $errors['course_name'] = 'Course name must not exceed 150 characters';
      } elseif ($this->courseModel->courseNameExists($course_name)) {
        $errors['course_name'] = 'Course name already exists';
      }

      if ($instructor_id === '') {
        $errors['instructor_id'] = 'Instructor is required';
      } elseif (!$this->courseModel->instructorExists($instructor_id)) {
        $errors['instructor_id'] = 'Selected instructor is invalid';
      }

      if ($duration_weeks === '' || !ctype_digit((string)$duration_weeks) || (int)$duration_weeks < 1 || (int)$duration_weeks > 260) {
        $errors['duration_weeks'] = 'Duration must be between 1 and 260 weeks';
      }

      if ($max_seats === '' || !ctype_digit((string)$max_seats) || (int)$max_seats < 1 || (int)$max_seats > 10000) {
        $errors['max_seats'] = 'Max seats must be between 1 and 10000';
      }

      if ($status !== '0' && $status !== '1') {
        $errors['status'] = 'Invalid status';
      }

      if (empty($errors)) {

        $this->courseModel->create([
          'course_name'    => $course_name,
          'instructor_id'  => $instructor_id,
          'duration_weeks' => (int)$duration_weeks,
          'max_seats'      => (int)$max_seats,
          'status'         => $status
        ]);

        setFlash('success', 'Course created');

        return $this->redirect("/courses");
      }
    }

    $instructors = $this->courseModel->getInstructors();

    return $this->render('courses/create', compact('errors', 'instructors'));
  }

  public function edit($id)
  {
    Rbac::require('course.edit');

    $course = $this->courseModel->find($id);

    if (!$course) {
      die("Course not found");
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $course_name    = trim($_POST['course_name'] ?? '');
      $instructor_id  = $_POST['instructor_id'] ?? '';
      $duration_weeks = $_POST['duration_weeks'] ?? '';
      $max_seats      = $_POST['max_seats'] ?? '';
      $status         = $_POST['status'] ?? '1';

      if ($course_name === '') {
        $errors['course_name'] = 'Course name is required';
      } elseif (
        $course_name !== $course['course_name'] &&
        $this->courseModel->courseNameExists($course_name)
      ) {
        $errors['course_name'] = 'Course name already exists';
      }

      if ($instructor_id === '') {
        $errors['instructor_id'] = 'Instructor is required';
      }

      if ($duration_weeks === '' || !is_numeric($duration_weeks) || (int)$duration_weeks <= 0) {
        $errors['duration_weeks'] = 'Duration must be a positive number';
      }

      if ($max_seats === '' || !is_numeric($max_seats) || (int)$max_seats <= 0) {
        $errors['max_seats'] = 'Max seats must be a positive number';
      }

      if ($status !== '0' && $status !== '1') {
        $errors['status'] = 'Invalid status';
      }

      if (empty($errors)) {

        $this->courseModel->update($id, [
          'course_name'    => $course_name,
          'instructor_id'  => $instructor_id,
          'duration_weeks' => (int)$duration_weeks,
          'max_seats'      => (int)$max_seats,
          'status'         => $status
        ]);

        setFlash('success', 'Course updated');

        return $this->redirect("/courses");
      }
    }

    $instructors = $this->courseModel->getInstructors();

    return $this->render('courses/edit', compact('course', 'errors', 'instructors'));
  }

  public function delete($id)
  {
    Rbac::require('course.delete');

    $this->courseModel->softDelete($id);

    setFlash('success', 'Course deleted');

    return $this->redirect("/courses");
  }

  public function ajax()
  {
    Rbac::require('course.view_all');

    header('Content-Type: application/json');

    $draw = (int)($_GET['draw'] ?? 1);
    $start = (int)($_GET['start'] ?? 0);
    $length = (int)($_GET['length'] ?? 10);
    $searchParam = $_GET['search'] ?? '';
    $search = trim(is_array($searchParam) ? ($searchParam['value'] ?? '') : $searchParam);

    $columnIndex = (int)($_GET['order'][0]['column'] ?? 0);
    $orderDir = $_GET['order'][0]['dir'] ?? 'asc';
    $columns = [
      'c.id',
      'c.course_name',
      'u.name',
      'c.duration_weeks',
      'c.max_seats',
      'filled_seats',
      'available_seats',
      'c.status'
    ];
    $orderBy = $columns[$columnIndex] ?? 'c.id';

    $filters = [
      'status' => $_GET['status'] ?? '',
      'instructor_id' => $_GET['instructor_id'] ?? '',
      'deleted' => $_GET['deleted'] ?? ''
    ];

    $userId = $_SESSION['user']['id'] ?? 0;
    $rows = $this->courseModel->getDataTableRecords($start, $length, $search, $orderBy, $orderDir, $filters, $userId);

    foreach ($rows as &$row) {
      $filled = (int)$row['filled_seats'];
      $totalSeats = max(1, (int)$row['max_seats']);
      $percentage = (int)round(($filled / $totalSeats) * 100);
      $barColor = $percentage >= 90 ? '#137333' : ($percentage >= 50 ? '#d97706' : '#c62828');

      $statusLabel = $row['deleted_at']
        ? '<span class="dt-badge dt-badge-muted">Deleted</span>'
        : ((int)$row['status'] === 1
          ? '<span class="dt-badge dt-badge-success">Active</span>'
          : '<span class="dt-badge dt-badge-warning">Inactive</span>');

      $manage = [];

      if ($row['deleted_at']) {
        if (Rbac::has('course.restore') && method_exists($this, 'restore')) {
          $manage[] = '<a href="/courses/restore/' . (int)$row['id'] . '">Restore</a>';
        }
      } else {
        if (Rbac::has('course.edit')) {
          $manage[] = '<a href="/courses/edit/' . (int)$row['id'] . '">Edit</a>';
        }

        if (Rbac::has('course.delete')) {
          $manage[] = '<a href="/courses/delete/' . (int)$row['id'] . '" onclick="return confirm(\'Delete this course?\')">Delete</a>';
        }
      }

      $enrollment = '';
      if (Rbac::has('enrollment.create') && !Rbac::isAdmin() && !$row['deleted_at']) {
        if (!empty($row['is_enrolled'])) {
          $enrollment = '<form method="POST" action="/enrollments/cancel" class="dt-inline-form"><input type="hidden" name="id" value="' . (int)$row['en_id'] . '"><button type="submit" class="dt-btn dt-btn-danger">Cancel</button></form>';
        } else {
          $enrollment = '<form method="POST" action="/enrollments/enroll" class="dt-inline-form"><input type="hidden" name="course_id" value="' . (int)$row['id'] . '"><button type="submit" class="dt-btn">Enroll</button></form>';
        }
      }

      $row['seat_usage'] = '<div class="dt-progress">
      <div class="dt-progress-bar" style="width:' . $percentage . '%; background:' . $barColor . ';">' . $percentage . '%</div></div>';
      $row['status_label'] = $statusLabel;
      $row['manage'] = implode(' | ', $manage);
      $row['enrollment_action'] = $enrollment;
    }

    echo json_encode([
      'draw' => $draw,
      'recordsTotal' => $this->courseModel->getDataTableTotalCount($filters),
      'recordsFiltered' => $this->courseModel->getFilteredCount($search, $filters),
      'data' => $rows
    ]);
    exit;
  }
}
