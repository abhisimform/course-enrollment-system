<?php

require_once BASE_PATH . '/app/models/Student.php';
require_once BASE_PATH . '/app/models/Course.php';
require_once BASE_PATH . '/app/models/Enrollment.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Permission.php';
require_once BASE_PATH . '/app/models/Audit.php';

class Dashboard extends BaseController
{
  public function index()
  {
    requireLogin();

    Rbac::require('dashboard.view');

    $user = $_SESSION['user'];
    $role = $user['role'] ?? null;

    $data = [];

    if ($role === 'admin') {
      $data = $this->adminDashboard();
    } elseif ($role === 'student') {
      $data = $this->studentDashboard($user['id']);
    }

    return $this->render('dashboard/index', $data);
  }

  private function adminDashboard()
  {
    $studentModel = new StudentModel();
    $courseModel = new CourseModel();
    $enrollmentModel = new EnrollmentModel();
    $userModel = new UserModel();
    $permissionModel = new PermissionModel();
    $auditModel = new AuditModel();

    return [
      'type' => 'admin',

      // core stats
      'students' => $studentModel->count(),
      'courses' => $courseModel->count(),
      'enrollments' => $enrollmentModel->countActive(),

      // user stats
      'users' => $userModel->count(),
      'teachers' => $userModel->countByRole('teacher'),
      'inactive_users' => $userModel->countInactive(),

      // system stats
      'total_permissions' => $permissionModel->count(),
      'audit_logs' => $auditModel->count()
    ];
  }

  private function studentDashboard($userId)
  {
    $enrollmentModel = new EnrollmentModel();
    $courseModel = new CourseModel();

    return [
      'type' => 'student',
      'myCourses' => $courseModel->getByUser($userId),
      'myEnrollments' => $enrollmentModel->getByUser($userId)
    ];
  }
}
