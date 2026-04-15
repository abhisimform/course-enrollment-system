<?php

require_once BASE_PATH . '/app/models/Audit.php';

class Audit
{
  private $auditModel;

  public function __construct()
  {
    requireLogin();

    Rbac::has('audit.view');
    
    $this->auditModel = new AuditModel();
  }

  public function index()
  {
    // $table = $_GET['table'] ?? null;
    // $actionType = $_GET['action_type'] ?? null;

    // $logs = $this->auditModel->getAll($table, $actionType);

    // $tables = $this->auditModel->getAllTables();

    $search = $_GET['search'] ?? '';
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $orderBy = $_GET['order_by'] ?? 'changed_at';
    $sortOrder = $_GET['sort_order'] ?? 'DESC';

    $logs = $this->auditModel->getRecords($search, $orderBy, $sortOrder, $page, $limit);

    $totalRecords = $this->auditModel->getTotalCount($search);
    $totalPages = ceil($totalRecords / $limit);

    $view = BASE_PATH . '/views/audit/index.php';
    require BASE_PATH . '/views/layouts/main.php';
  }
}
