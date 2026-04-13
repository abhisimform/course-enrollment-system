<?php

class EnrollmentModel
{
  private $pdo;

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  public function countActive()
  {
    return $this->pdo->query("
            SELECT COUNT(*) as total 
            FROM enrollments 
            WHERE status = 'active'
            AND deleted_at IS NULL
        ")->fetch()['total'];
  }
}
