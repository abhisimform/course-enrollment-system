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

    return $this->render('courses/index');
  }

  public function create()
  {
    Rbac::require('course.create');

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if ($this->isValidCSRF())
        $errors['csrf_token'] = 'Invalid CSRF token';

      $course_name    = trim($_POST['course_name'] ?? '');
      $instructor_id  = $_POST['instructor_id'] ?? '';
      $duration_weeks = $_POST['duration_weeks'] ?? '';
      $max_seats      = $_POST['max_seats'] ?? '';
      $status         = $_POST['status'] ?? '1';

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
      setFlash('error', 'Course not found');

      return $this->redirect('/students');
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if ($this->isValidCSRF())
        $errors['csrf_token'] = 'Invalid CSRF token';

      $course_name    = trim($_POST['course_name'] ?? '');
      $instructor_id  = $_POST['instructor_id'] ?? '';
      $duration_weeks = $_POST['duration_weeks'] ?? '';
      $max_seats      = $_POST['max_seats'] ?? '';
      $status         = $_POST['status'] ?? '1';

      if ($course_name === '') {
        $errors['course_name'] = 'Course name is required';
      } elseif (mb_strlen($course_name) > 150) {
        $errors['course_name'] = 'Course name must not exceed 150 characters';
      } elseif (
        $course_name !== $course['course_name'] &&
        $this->courseModel->courseNameExists($course_name)
      ) {
        $errors['course_name'] = 'Course name already exists';
      }

      if ($instructor_id === '') {
        $errors['instructor_id'] = 'Instructor is required';
      } elseif (!ctype_digit((string)$instructor_id) || !$this->courseModel->instructorExists($instructor_id)) {
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
    if (!isPOSTRequest()) {
      return $this->redirect('/courses');
    }

    if ($this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');

      return $this->redirect('/courses');
    }

    Rbac::require('course.delete');

    $this->courseModel->softDelete($id);

    setFlash('success', 'Course deleted');

    return $this->redirect("/courses");
  }

  public function getCourseData()
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
        if (Rbac::has('course.restore')) {
          $row['actions'] = postActionLink(
            'Restore',
            '/courses/restore/' . (int)$row['id'],
            'Restore course?'
          );
        }
      } else {
        if (Rbac::has('course.edit')) {
          $manage[] = '<a href="/courses/edit/' . (int)$row['id'] . '">Edit</a>';
        }

        if (Rbac::has('course.delete')) {
          $row['actions'] = postActionLink(
            'Delete',
            '/courses/delete/' . (int)$row['id'],
            'Delete course?'
          );
        }
      }

      $enrollment = '';
      if (Rbac::has('enrollment.create') && !Rbac::isAdmin() && !$row['deleted_at']) {
        if (!empty($row['is_enrolled'])) {
          // $enrollment = '<form method="POST" action="/enrollments/cancel" class="dt-inline-form">' . csrfInput() . '<input type="hidden" name="id" value="' . (int)$row['en_id'] . '"><button type="submit" class="dt-btn dt-btn-danger">Cancel</button></form>';
          $enrollment = postActionLink(
            'Cancel',
            '/enrollments/cancel/' . (int)$row['en_id'],
            'Cancel enrollment?'
          );
        } else {
          $enrollment = '<form method="POST" action="/enrollments/enroll" class="dt-inline-form">' . csrfInput() . '<input type="hidden" name="course_id" value="' . (int)$row['id'] . '"><button type="submit" class="dt-btn">Enroll</button></form>';
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
