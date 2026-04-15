<?php

require_once BASE_PATH . "/app/models/Course.php";

class Courses
{
  private $courseModel;

  public function __construct()
  {
    requireLogin();

    $this->courseModel = new CourseModel();
  }

  public function index()
  {
    Rbac::require('course.view');

    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 10;

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $instructorId = isset($_GET['instructor']) ? $_GET['instructor'] : '';

    $courses = $this->courseModel->getCourses($search, $status, $instructorId, $currentPage, $perPage);

    $totalCourses = $this->courseModel->countCourses($search, $status, $instructorId);
    $totalPages = ceil($totalCourses / $perPage);

    $instructors = $this->courseModel->getInstructors();

    $view = BASE_PATH . "/views/courses/index.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function create()
  {
    Rbac::require('course.create');
    
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $course_name   = trim($_POST['course_name'] ?? '');
      $instructor_id = $_POST['instructor_id'] ?? '';
      $duration_weeks = $_POST['duration_weeks'] ?? '';
      $max_seats     = $_POST['max_seats'] ?? '';
      $status        = $_POST['status'] ?? '1';

      if ($course_name === '') {
        $errors['course_name'] = 'Course name is required';
      } elseif ($this->courseModel->courseNameExists($course_name)) {
        $errors['course_name'] = 'Course name already exists';
      }

      if ($instructor_id === '') $errors['instructor_id'] = 'Instructor is required';

      if ($duration_weeks === '' || !is_numeric($duration_weeks) || (int)$duration_weeks <= 0) {
        $errors['duration_weeks'] = 'Duration must be a positive number';
      }

      if ($max_seats === '' || !is_numeric($max_seats) || (int)$max_seats <= 0) {
        $errors['max_seats'] = 'Max seats must be a positive number';
      }

      if ($status !== '0' && $status !== '1') $errors['status'] = 'Invalid status';

      if (empty($errors)) {
        $this->courseModel->create([
          'course_name'   => $course_name,
          'instructor_id' => $instructor_id,
          'duration_weeks' => (int)$duration_weeks,
          'max_seats'     => (int)$max_seats,
          'status'        => $status
        ]);

        setFlash('success', 'Course created');
        header("Location: /courses");
        exit;
      }
    }

    $instructors = $this->courseModel->getInstructors();
    $view = BASE_PATH . "/views/courses/create.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function view($id)
  {
    $course = $this->courseModel->find($id);

    if (!$course) {
      die("Course not found");
    }

    require BASE_PATH . "/views/courses/view.php";
  }

  public function edit($id)
  {
    Rbac::require('course.edit');

    $course = $this->courseModel->find($id);
    if (!$course) die("Course not found");

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $course_name   = trim($_POST['course_name'] ?? '');
      $instructor_id = $_POST['instructor_id'] ?? '';
      $duration_weeks = $_POST['duration_weeks'] ?? '';
      $max_seats     = $_POST['max_seats'] ?? '';
      $status        = $_POST['status'] ?? '1';

      if ($course_name === '') {
        $errors['course_name'] = 'Course name is required';
      } elseif ($course_name !== $course['course_name'] && $this->courseModel->courseNameExists($course_name)) {
        $errors['course_name'] = 'Course name already exists';
      }

      if ($instructor_id === '') $errors['instructor_id'] = 'Instructor is required';

      if ($duration_weeks === '' || !is_numeric($duration_weeks) || (int)$duration_weeks <= 0) {
        $errors['duration_weeks'] = 'Duration must be a positive number';
      }

      if ($max_seats === '' || !is_numeric($max_seats) || (int)$max_seats <= 0) {
        $errors['max_seats'] = 'Max seats must be a positive number';
      }

      if ($status !== '0' && $status !== '1') $errors['status'] = 'Invalid status';

      if (empty($errors)) {
        $this->courseModel->update($id, [
          'course_name'   => $course_name,
          'instructor_id' => $instructor_id,
          'duration_weeks' => (int)$duration_weeks,
          'max_seats'     => (int)$max_seats,
          'status'        => $status
        ]);

        setFlash('success', 'Course updated');
        header("Location: /courses");
        exit;
      }
    }

    $instructors = $this->courseModel->getInstructors();

    $view = BASE_PATH . "/views/courses/edit.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function delete($id)
  {
    Rbac::require('course.delete');

    $this->courseModel->softDelete($id);

    setFlash('success', 'Course deleted');
    header("Location: /courses");
    exit;
  }
}
