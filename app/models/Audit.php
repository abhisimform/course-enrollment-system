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

  public function getRecords($search = "", $orderBy = "changed_at", $sortOrder = "DESC", $page = 1, $limit = 10)
  {
    $offset = ($page - 1) * $limit;

    $searchCondition = "";
    if (!empty($search)) {
      $searchCondition = "WHERE table_name LIKE :search_table 
                          OR old_data LIKE :search_old_data 
                          OR new_data LIKE :search_new_data 
                          OR action_type LIKE :search_action_type 
                          OR record_id LIKE :search_record_id";
    }

    $sql = "
      SELECT * 
      FROM {$this->table}
      $searchCondition
      ORDER BY $orderBy $sortOrder
      LIMIT :limit OFFSET :offset
    ";

    $stmt = $this->pdo->prepare($sql);

    if (!empty($search)) {
      $stmt->bindValue(":search_table", "%$search%", PDO::PARAM_STR);
      $stmt->bindValue(":search_old_data", "%$search%", PDO::PARAM_STR);
      $stmt->bindValue(":search_new_data", "%$search%", PDO::PARAM_STR);
      $stmt->bindValue(":search_action_type", "%$search%", PDO::PARAM_STR);
      $stmt->bindValue(":search_record_id", "%$search%", PDO::PARAM_STR);
    }

    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function getTotalCount($search = "")
  {
    $searchCondition = "";
    if (!empty($search)) {
      $searchCondition = "WHERE table_name LIKE :search_table 
                          OR action_type LIKE :search_action_type 
                          OR record_id LIKE :search_record_id";
    }

    $sql = "SELECT COUNT(*) FROM {$this->table} $searchCondition";

    $stmt = $this->pdo->prepare($sql);

    if (!empty($search)) {
      $stmt->bindValue(":search_table", "%$search%", PDO::PARAM_STR);
      $stmt->bindValue(":search_action_type", "%$search%", PDO::PARAM_STR);
      $stmt->bindValue(":search_record_id", "%$search%", PDO::PARAM_STR);
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
}
