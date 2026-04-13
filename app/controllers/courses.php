<?php

class Courses
{
  private $courseModel;

  public function __construct()
  {
    require_once BASE_PATH . "/app/models/Course.php";
    require_once BASE_PATH . "/utils/helper.php";

    requireLogin();

    $this->courseModel = new CourseModel();
  }

  // Updated index() method with search, filter, and pagination
  public function index()
  {
    // Get current page, search, and filter params from the query string
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 10; // Number of courses per page

    // Get search, filter params from query
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $instructorId = isset($_GET['instructor']) ? $_GET['instructor'] : '';

    // Call the model method to fetch filtered and paginated courses
    $courses = $this->courseModel->getCourses($search, $status, $instructorId, $currentPage, $perPage);

    // Get total courses count for pagination
    $totalCourses = $this->courseModel->countCourses($search, $status, $instructorId);
    $totalPages = ceil($totalCourses / $perPage);

    // Get list of instructors for filtering (assuming you have this in your model)
    $instructors = $this->courseModel->getInstructors();

    // Prepare the view
    $view = BASE_PATH . "/views/courses/index.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function create()
  {
    if (!hasPermission('create_course')) {
      die("Access denied");
    }
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
    if (!hasPermission('edit_course')) {
      die("Access denied");
    }

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
    if (!hasPermission('delete_course')) {
      die("Access denied");
    }

    $this->courseModel->softDelete($id);

    setFlash('success', 'Course deleted');
    header("Location: /courses");
    exit;
  }
}
