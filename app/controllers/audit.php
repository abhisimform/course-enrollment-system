<?php

class Audit
{
  private $auditModel;

  public function __construct()
  {
    require_once BASE_PATH . '/app/models/Audit.php';
    require_once BASE_PATH . '/utils/helper.php';

    requireLogin();

    if (!hasPermission('view_audit_logs')) {
      die("Access denied");
    }

    $this->auditModel = new AuditModel();
  }

  public function index()
  {
    $table = $_GET['table'] ?? null;
    $actionType = $_GET['action_type'] ?? null;

    $logs = $this->auditModel->getAll($table, $actionType);

    $tables = $this->auditModel->getAllTables();

    $view = BASE_PATH . '/views/audit/index.php';
    require BASE_PATH . '/views/layouts/main.php';
  }
}
