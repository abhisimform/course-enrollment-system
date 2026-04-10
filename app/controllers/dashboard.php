<?php

session_start();

class Dashboard
{
  public function index()
  {
    requireLogin();

    // require_once BASE_PATH . '/app/models/Student.php';
    // require_once BASE_PATH . '/app/models/Course.php';
    // require_once BASE_PATH . '/app/models/Enrollment.php';

    // $studentModel = new StudentModel();
    // $courseModel = new CourseModel();
    // $enrollmentModel = new EnrollmentModel();

    $data = [
      // 'students' => $studentModel->count(),
      // 'courses' => $courseModel->count(),
      // 'enrollments' => $enrollmentModel->countActive()
    ];

    require BASE_PATH . '/views/dashboard/index.php';
  }
}
