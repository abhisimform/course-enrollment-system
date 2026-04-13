<?php

class EnrollmentModel
{
  private $pdo;
  private $table = 'enrollments';

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  private function baseCondition()
  {
    return "deleted_at IS NULL";
  }

  public function countActive()
  {
    return $this->pdo->query("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE status = 'active'
            AND {$this->baseCondition()}
        ")->fetch()['total'];
  }
}
