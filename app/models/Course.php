<?php

class CourseModel
{
  private $pdo;

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  public function count()
  {
    $stmt = $this->pdo->query("
        SELECT COUNT(*) as total 
        FROM courses 
        WHERE deleted_at IS NULL
    ");
    return $stmt->fetch()['total'];
  }
}
