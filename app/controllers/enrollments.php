<?php

require_once BASE_PATH . "/app/models/Enrollment.php";

class Enrollments extends BaseController
{
  private $enrollmentModel;

  public function __construct()
  {
    requireLogin();

    $this->enrollmentModel = new EnrollmentModel();
  }

  public function index()
  {
    requireLogin();

    $user = $_SESSION['user'];
    $role = $user['role'];

    $filters = $_GET;

    $limit = (int)($filters['limit'] ?? 10);
    $page = (int)($filters['page'] ?? 1);
    $offset = ($page - 1) * $limit;


    if ($role === 'admin') {

      Rbac::require('enrollment.view');

      $enrollments = $this->enrollmentModel->getAll($filters, $limit, $offset);
      $total = $this->enrollmentModel->countAll($filters);
    } else {
      $enrollments = $this->enrollmentModel->getByStudent($user['id'], $limit, $offset);
      $total = $this->enrollmentModel->countByStudent($user['id']);
    }

    $totalPages = ceil($total / $limit);

    return $this->render('enrollments/index', [
      'enrollments' => $enrollments,
      'filters' => $filters,
      'role' => $role,
      'pagination' => [
        'page' => $page,
        'totalPages' => $totalPages,
        'limit' => $limit
      ]
    ]);
  }

  public function enroll()
  {
    Rbac::has('enrollment.create');

    $studentId = $_SESSION['user']['id'];
    $courseId = $_POST['course_id'];

    $this->enrollmentModel->enroll($studentId, $courseId);

    return $this->redirect('/courses');
  }

  public function cancel()
  {
    Rbac::has('enrollment.update');

    $id = $_POST['id'] ?? null;

    if ($id) {
      $this->enrollmentModel->cancel($id);
    }

    return $this->redirect('/enrollments');
  }

  public function delete()
  {
    Rbac::has('enrollment.delete');

    $id = $_POST['id'] ?? null;

    if ($id) {
      $this->enrollmentModel->delete($id);
    }

    return $this->redirect('/enrollments');
  }
}
