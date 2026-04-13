<?php

class AuditModel
{
  private $pdo;

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  public function getAll($table = null, $action = null)
  {
    $sql = "SELECT * FROM audit_logs WHERE 1=1";
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

  public function getAllTables()
  {
    $sql = "SHOW TABLES";
    $stmt = $this->pdo->query($sql);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $tables;
  }
}
