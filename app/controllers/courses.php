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
    Rbac::require('course.view');

    $currentPage = (int)($_GET['page'] ?? 1);
    $perPage = 10;

    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $instructorId = $_GET['instructor'] ?? '';

    $courses = $this->courseModel->getCourses(
      $search,
      $status,
      $instructorId,
      $currentPage,
      $perPage
    );

    $totalCourses = $this->courseModel->countCourses($search, $status, $instructorId);
    $totalPages = ceil($totalCourses / $perPage);

    $instructors = $this->courseModel->getInstructors();

    return $this->render('courses/index', compact(
      'courses',
      'currentPage',
      'perPage',
      'search',
      'status',
      'instructorId',
      'totalCourses',
      'totalPages',
      'instructors'
    ));
  }

  public function create()
  {
    Rbac::require('course.create');

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $course_name    = trim($_POST['course_name'] ?? '');
      $instructor_id  = $_POST['instructor_id'] ?? '';
      $duration_weeks = $_POST['duration_weeks'] ?? '';
      $max_seats      = $_POST['max_seats'] ?? '';
      $status         = $_POST['status'] ?? '1';

      if ($course_name === '') {
        $errors['course_name'] = 'Course name is required';
      } elseif ($this->courseModel->courseNameExists($course_name)) {
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

  public function view($id)
  {
    $course = $this->courseModel->find($id);

    if (!$course) {
      die("Course not found");
    }

    return $this->render('courses/view', compact('course'));
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
}