<?php

class AuditModel
{
  private $pdo;
  private $table = 'audit_logs';

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  public function getAll($table = null, $action = null)
  {
    $sql = "SELECT * FROM {$this->table} WHERE 1=1";
    $params = [];

    if (!empty($table)) {
      $sql .= " AND table_name = :table";
      $params[':table'] = $table;
    }

    if (!empty($action)) {
      $sql .= " AND action_type = :action";
      $params[':action'] = $action;
    }

    $sql .= " ORDER BY changed_at DESC LIMIT 100";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
  }

  public function count()
  {
    $stmt = $this->pdo->query("
      SELECT COUNT(*) 
      FROM {$this->table}
    ");

    return $stmt->fetchColumn();
  }

  public function getRecords($search = "", $orderBy = "changed_at", $sortOrder = "DESC", $page = 1, $limit = 10, $table = "", $actionType = "")
  {
    $allowedOrderColumns = ['id', 'table_name', 'action_type', 'record_id', 'changed_at'];
    $orderBy = in_array($orderBy, $allowedOrderColumns, true) ? $orderBy : 'changed_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    $page = max(1, (int)$page);
    $limit = min(100, max(1, (int)$limit));
    $offset = ($page - 1) * $limit;

    $conditions = [];
    $params = [];

    if (!empty($search)) {
      $conditions[] = "(table_name LIKE :search_table 
        OR old_data LIKE :search_old_data 
        OR new_data LIKE :search_new_data 
        OR action_type LIKE :search_action_type 
        OR record_id LIKE :search_record_id)";
      $params[':search_table'] = "%$search%";
      $params[':search_old_data'] = "%$search%";
      $params[':search_new_data'] = "%$search%";
      $params[':search_action_type'] = "%$search%";
      $params[':search_record_id'] = "%$search%";
    }

    if (!empty($table)) {
      $conditions[] = "table_name = :table";
      $params[':table'] = $table;
    }

    if (!empty($actionType)) {
      $conditions[] = "action_type = :action_type";
      $params[':action_type'] = $actionType;
    }

    $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = "
      SELECT * 
      FROM {$this->table}
      $whereSql
      ORDER BY $orderBy $sortOrder
      LIMIT :limit OFFSET :offset
    ";

    $stmt = $this->pdo->prepare($sql);

    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function getTotalCount($search = "", $table = "", $actionType = "")
  {
    $conditions = [];
    $params = [];

    if (!empty($search)) {
      $conditions[] = "(table_name LIKE :search_table 
        OR action_type LIKE :search_action_type 
        OR record_id LIKE :search_record_id)";
      $params[':search_table'] = "%$search%";
      $params[':search_action_type'] = "%$search%";
      $params[':search_record_id'] = "%$search%";
    }

    if (!empty($table)) {
      $conditions[] = "table_name = :table";
      $params[':table'] = $table;
    }

    if (!empty($actionType)) {
      $conditions[] = "action_type = :action_type";
      $params[':action_type'] = $actionType;
    }

    $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = "SELECT COUNT(*) FROM {$this->table} $whereSql";

    $stmt = $this->pdo->prepare($sql);

    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchColumn();
  }

  public function getAllTables()
  {
    $sql = "SHOW TABLES";
    $stmt = $this->pdo->query($sql);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $tables;
  }

  public function find($id)
  {
    $stmt = $this->pdo->prepare("
      SELECT *
      FROM {$this->table}
      WHERE id = :id
      LIMIT 1
    ");

    $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch();
  }

  public function getDataTableRecords($start, $length, $search, $orderBy, $orderDir)
  {
    $params = [];
    $where = "";

    if (!empty($search)) {
      $where = "WHERE table_name LIKE :search 
        OR action_type LIKE :search 
        OR record_id LIKE :search";

      $params[':search'] = "%$search%";
    }

    $sql = "
      SELECT * FROM {$this->table}
      $where
      ORDER BY $orderBy $orderDir
      LIMIT :start, :length
    ";

    $stmt = $this->pdo->prepare($sql);

    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);

    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // format data for frontend
    foreach ($rows as &$row) {
      $row['id'] = "<a href='/audit/view/" . $row['id'] . "'>#" . $row['id'] . "</a>";
      $row['old_data'] = $this->formatLogData($row['old_data']);
      $row['new_data'] = $this->formatLogData($row['new_data']);
      $row['changed_at'] = date('d M Y H:i', strtotime($row['changed_at']));
      $row['action_type'] = $this->formatAction($row['action_type']);
    }

    return $rows;
  }

  public function getFilteredCount($search)
  {
    $params = [];
    $where = "";

    if (!empty($search)) {
      $where = "WHERE table_name LIKE :search 
      OR action_type LIKE :search 
      OR record_id LIKE :search";

      $params[':search'] = "%$search%";
    }

    $sql = "SELECT COUNT(*) FROM {$this->table} $where";

    $stmt = $this->pdo->prepare($sql);

    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();

    return $stmt->fetchColumn();
  }

  private function formatLogData($data)
  {
    if (empty($data)) {
      return '<span style="color:gray;">-</span>';
    }

    $items = explode(',', $data);
    $html = '<ul style="margin:0;padding-left:15px;">';

    foreach ($items as $item) {
      $html .= '<li>' . htmlspecialchars(trim($item)) . '</li>';
    }

    $html .= '</ul>';

    return $html;
  }

  private function formatAction($action)
  {
    $color = $action === 'INSERT' ? 'green' : ($action === 'UPDATE' ? 'orange' : 'red');
    return "<span class='badge badge-$color'>$action</span>";
  }
}
