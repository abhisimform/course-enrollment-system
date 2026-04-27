<?php

require_once BASE_PATH . '/app/models/Audit.php';

class Audit extends BaseController
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
    $log = $this->auditModel->find($id);

    $this->render('/audit/view', compact('log'));
  }
}
