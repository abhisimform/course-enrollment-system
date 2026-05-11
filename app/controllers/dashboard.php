<?php

require_once BASE_PATH . '/app/models/Student.php';
require_once BASE_PATH . '/app/models/Course.php';
require_once BASE_PATH . '/app/models/Enrollment.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Permission.php';
require_once BASE_PATH . '/app/models/Audit.php';

class Dashboard extends BaseController
{
  private $studentModel;
  private $courseModel;
  private $enrollmentModel;
  private $userModel;
  private $permissionModel;
  private $auditModel;

  public function __construct()
  {
    $this->studentModel = new StudentModel();
    $this->courseModel = new CourseModel();
    $this->enrollmentModel = new EnrollmentModel();
    $this->userModel = new UserModel();
    $this->permissionModel = new PermissionModel();
    $this->auditModel = new AuditModel();
  }

  public function index()
  {
    requireLogin();

    Rbac::require('dashboard.view');

    $user = $_SESSION['user'];
    $role = $user['role'] ?? null;

    $data = [];

    if ($role === 'admin' || $role === 'teacher') {
      $data = $this->adminDashboard();
    } elseif ($role === 'student') {
      $data = $this->studentDashboard($user['id']);
    }

    return $this->render('dashboard/index', compact('data'));
  }

  private function adminDashboard()
  {

    return [
      'type' => 'admin',

      // core stats
      'students' => $this->studentModel->count(),
      'courses' => $this->courseModel->count(),
      'enrollments' => $this->enrollmentModel->countActive(),

      // user stats
      'users' => $this->userModel->count(),
      'teachers' => $this->userModel->countByRole('teacher'),
      'inactive_users' => $this->userModel->countInactive(),

      // system stats
      'total_permissions' => $this->permissionModel->count(),
      'audit_logs' => $this->auditModel->count()
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
