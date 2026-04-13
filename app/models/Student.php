<?php

class StudentModel
{
  private $pdo;

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  public function getAll()
  {
    $stmt = $this->pdo->prepare("
            SELECT * 
            FROM students 
            WHERE deleted_at IS NULL 
            ORDER BY id DESC
        ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function find($id)
  {
    $stmt = $this->pdo->prepare("
            SELECT * 
            FROM students 
            WHERE id = :id 
            AND deleted_at IS NULL
            LIMIT 1
        ");

    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    $stmt = $this->pdo->prepare("
            INSERT INTO students (name, email, phone, enrolled_on)
            VALUES (:name, :email, :phone, :enrolled_on)
        ");

    return $stmt->execute([
      'name'         => $data['name'],
      'email'        => $data['email'],
      'phone'        => $data['phone'],
      'enrolled_on'  => date('Y-m-d')
    ]);
  }

  public function update($id, $data)
  {
    $stmt = $this->pdo->prepare("
            UPDATE students 
            SET name = :name,
                email = :email,
                phone = :phone,
                updated_at = NOW()
            WHERE id = :id
            AND deleted_at IS NULL
        ");

    return $stmt->execute([
      'id'    => $id,
      'name'  => $data['name'],
      'email' => $data['email'],
      'phone' => $data['phone']
    ]);
  }

  public function softDelete($id)
  {
    $stmt = $this->pdo->prepare("
            UPDATE students 
            SET deleted_at = NOW()
            WHERE id = :id
        ");

    return $stmt->execute(['id' => $id]);
  }

  public function restore($id)
  {
    $stmt = $this->pdo->prepare("
            UPDATE students 
            SET deleted_at = NULL
            WHERE id = :id
        ");

    return $stmt->execute(['id' => $id]);
  }

  public function emailExists($email)
  {
    $stmt = $this->pdo->prepare("
            SELECT id 
            FROM students 
            WHERE email = :email 
            AND deleted_at IS NULL
            LIMIT 1
        ");

    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
  }

  public function count()
  {
    $stmt = $this->pdo->query("
        SELECT COUNT(*) as total 
        FROM students 
        WHERE deleted_at IS NULL
    ");
    return $stmt->fetch()['total'];
  }
}
