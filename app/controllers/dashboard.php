<?php

require_once BASE_PATH . '/app/models/Student.php';
require_once BASE_PATH . '/app/models/Course.php';
require_once BASE_PATH . '/app/models/Enrollment.php';

class Dashboard extends BaseController
{
  public function index()
  {
    requireLogin();

    $studentModel = new StudentModel();
    $courseModel = new CourseModel();
    $enrollmentModel = new EnrollmentModel();

    $data = [
      'students' => $studentModel->count(),
      'courses' => $courseModel->count(),
      'enrollments' => $enrollmentModel->countActive()
    ];

    return $this->render('dashboard/index', $data);
  }
}
