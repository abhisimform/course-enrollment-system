<?php

require_once BASE_PATH . '/app/models/Audit.php';

class Audit extends BaseController
{
  private $auditModel;

  public function __construct()
  {
    requireLogin();

    $this->auditModel = new AuditModel();
  }

  public function index()
  {
    Rbac::require('audit.view_all');

    $search = $_GET['search'] ?? '';
    $table = $_GET['table'] ?? '';
    $actionType = $_GET['action_type'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 10)));
    $orderBy = $_GET['order_by'] ?? 'changed_at';
    $sortOrder = $_GET['sort_order'] ?? 'DESC';

    $logs = $this->auditModel->getRecords($search, $orderBy, $sortOrder, $page, $limit, $table, $actionType);

    $totalRecords = $this->auditModel->getTotalCount($search, $table, $actionType);
    $totalPages = ceil($totalRecords / $limit);
    $tables = $this->auditModel->getAllTables();
    $filters = $_GET;

    $this->render("/audit/index", compact('search', 'table', 'actionType', 'page', 'limit', 'orderBy', 'sortOrder', 'logs', 'totalPages', 'totalRecords', 'tables', 'filters'));
  }

  public function view($id)
  {
    Rbac::require('audit.view');

    $log = $this->auditModel->find($id);

    $this->render('/audit/view', compact('log'));
  }

  public function ajax()
  {
    Rbac::require('course.view_all');

    header('Content-Type: application/json');

    $draw   = (int)($_GET['draw'] ?? 1);
    $start  = (int)($_GET['start'] ?? 0);
    $length = (int)($_GET['length'] ?? 10);

    $searchValue = $_GET['search']['value'] ?? '';

    $orderColumnIndex = $_GET['order'][0]['column'] ?? 0;
    $orderDir = $_GET['order'][0]['dir'] ?? 'desc';

    $columns = ['id', 'table_name', 'action_type', 'record_id', 'old_data', 'new_data', 'changed_at'];
    $orderBy = $columns[$orderColumnIndex] ?? 'changed_at';
    $orderDir = $orderDir === 'asc' ? 'ASC' : 'DESC';

    $data = $this->auditModel->getDataTableRecords($start, $length, $searchValue, $orderBy, $orderDir);

    $totalRecords = $this->auditModel->getTotalCount();
    $filteredRecords = $this->auditModel->getFilteredCount($searchValue);

    echo json_encode([
      "draw" => $draw,
      "recordsTotal" => (int)$totalRecords,
      "recordsFiltered" => (int)$filteredRecords,
      "data" => $data
    ]);
    exit;
  }
}
