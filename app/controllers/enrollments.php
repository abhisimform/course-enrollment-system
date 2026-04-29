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

    $filters = $_GET;

    $limit = (int)($filters['limit'] ?? 10);
    $page = (int)($filters['page'] ?? 1);
    $offset = ($page - 1) * $limit;


    if ($role === 'admin') {
      Rbac::require('enrollment.view_all');

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
    Rbac::require('enrollment.create');

    $studentId = $_SESSION['user']['id'];
    $courseId = $_POST['course_id'];

    $this->enrollmentModel->enroll($studentId, $courseId);

    return $this->redirect('/courses');
  }

  public function cancel()
  {
    Rbac::require('enrollment.update');

    $id = $_POST['id'] ?? null;

    if ($id) {
      $this->enrollmentModel->cancel($id);
    }

    return $this->redirect('/enrollments');
  }

  public function delete()
  {
    Rbac::require('enrollment.delete');

    $id = $_POST['id'] ?? null;

    if ($id) {
      $this->enrollmentModel->delete($id);
    }

    return $this->redirect('/enrollments');
  }

  public function ajax()
  {
    Rbac::require('enrollment.view');

    header('Content-Type: application/json');

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

    foreach ($rows as &$row) {
      $row['status_label'] = strtolower((string)$row['status']) === 'active'
        ? '<span class="dt-badge dt-badge-success">Active</span>'
        : '<span class="dt-badge dt-badge-warning">Cancelled</span>';

      $row['actions'] = '';
      if (((!$isAdmin) || hasPermission('enrollment.update')) && strtolower((string)$row['status']) === 'active') {
        $row['actions'] = '<form method="POST" action="/enrollments/cancel" class="dt-inline-form"><input type="hidden" name="id" value="' . (int)$row['id'] . '"><button type="submit" class="dt-btn dt-btn-danger">Cancel</button></form>';
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
