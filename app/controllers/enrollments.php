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

    Rbac::require('enrollment.view');

    $user = $_SESSION['user'];
    $role = $user['role'];

    return $this->render('enrollments/index', [
      'role' => $role
    ]);
  }

  public function enroll()
  {
    Rbac::require('enrollment.create');

    if (!$this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');
      return $this->redirect('/enrollments');
    }

    $studentId = $_SESSION['user']['id'];
    $courseId = $_POST['course_id'] ?? '';

    if (!ctype_digit((string)$courseId) || (int)$courseId <= 0) {
      setFlash('error', 'Invalid course selected');
      return $this->redirect('/courses');
    }

    $this->enrollmentModel->enroll($studentId, $courseId);

    return $this->redirect('/courses');
  }

  public function cancel($id)
  {
    Rbac::require('enrollment.cancel');

    if (!isPOSTRequest()) {
      setFlash('error', 'Invalid request type');
      return $this->redirect('/enrollments');
    }

    if (!$this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');
      return $this->redirect('/enrollments');
    }

    $this->enrollmentModel->cancel($id);

    return $this->redirect('/enrollments');
  }

  public function delete($id)
  {
    Rbac::require('enrollment.delete');

    if (!isPOSTRequest()) {
      setFlash('error', 'Invalid request type');
      return $this->redirect('/enrollments');
    }

    if (!$this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');
      return $this->redirect('/enrollments');
    }

    $this->enrollmentModel->delete($id);

    return $this->redirect('/enrollments');
  }

  public function getEnrollmentData()
  {
    Rbac::require('enrollment.view');

    header('Content-Type: application/json; charset=utf-8');

    $draw = (int)($_GET['draw'] ?? 1);
    $start = (int)($_GET['start'] ?? 0);
    $length = (int)($_GET['length'] ?? 10);
    $searchParam = $_GET['search'] ?? '';
    $search = trim(is_array($searchParam) ? ($searchParam['value'] ?? '') : $searchParam);

    $user = $_SESSION['user'];
    $isAdmin = $user['role'] === 'admin';

    $columnIndex = (int)($_GET['order'][0]['column'] ?? 0);
    $orderDir = $_GET['order'][0]['dir'] ?? 'desc';
    $columns = $isAdmin
      ? ['e.id', 'u.name', 'c.course_name', 'e.status', 'e.enrolled_date']
      : ['e.id', 'c.course_name', 'e.status', 'e.enrolled_date'];
    $orderBy = $columns[$columnIndex] ?? 'e.id';

    $filters = [
      'status' => $_GET['status'] ?? ''
    ];

    $studentId = $isAdmin ? null : (int)$user['id'];
    $rows = $this->enrollmentModel->getDataTableRecords($start, $length, $search, $orderBy, $orderDir, $filters, $studentId);

    $canUpdate = Rbac::has('enrollment.update');

    foreach ($rows as &$row) {
      $row['status_label'] = strtolower((string)$row['status']) === 'active'
        ? '<span class="dt-badge dt-badge-success">Active</span>'
        : '<span class="dt-badge dt-badge-warning">Cancelled</span>';

      $row['actions'] = '';
      if (((!$isAdmin) || $canUpdate) && strtolower((string)$row['status']) === 'active') {
        $row['actions'] = postActionLink(
          'Cancel',
          '/enrollments/cancel/' . (int)$row['id'],
          'Cancel enrollment?'
        );
      }
    }

    echo json_encode([
      'draw' => $draw,
      'recordsTotal' => $this->enrollmentModel->getDataTableTotalCount($filters, $studentId),
      'recordsFiltered' => $this->enrollmentModel->getFilteredCount($search, $filters, $studentId),
      'data' => $rows
    ]);
    exit;
  }
}
