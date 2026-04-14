<?php

class PermissionModel
{
  private $pdo;
  private $table = 'permissions';
  private $rolePermissionsTable = 'role_permissions';
  private $userPermissionsTable = 'user_permissions';

  public function __construct()
  {
    $this->pdo = getPDO();
  }

  private function baseCondition()
  {
    return "deleted_at IS NULL";
  }

  public function getAll()
  {
    $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->baseCondition()} ORDER BY name ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPaginated($limit, $offset, $search = null)
  {
    $sql = "SELECT * FROM permissions WHERE deleted_at IS NULL";
    $params = [];

    if (!empty($search)) {
      $sql .= " AND name LIKE :search";
    }

    $sql .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";

    $stmt = $this->pdo->prepare($sql);

    if (!empty($search)) {
      $stmt->bindValue(':search', $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function countFiltered($search = null)
  {
    $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
    $params = [];

    if (!empty($search)) {
      $sql .= " AND name LIKE ?";
      $params[] = "%$search%";
    }

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return intval($row['total']);
  }

  public function getAllDeleted()
  {
    $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE deleted_at IS NOT NULL ORDER BY name ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getById($id)
  {
    $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ? AND {$this->baseCondition()}");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function create($name)
  {
    $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (name, created_at) VALUES (?, NOW())");
    return $stmt->execute([$name]);
  }

  public function update($id, $name)
  {
    $stmt = $this->pdo->prepare("UPDATE {$this->table} SET name = ? WHERE id = ? AND {$this->baseCondition()}");
    return $stmt->execute([$name, $id]);
  }

  public function softDelete($id)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table} 
      SET deleted_at = NOW() 
      WHERE id = ? 
      AND deleted_at IS NULL
    ");
    return $stmt->execute([$id]);
  }

  public function restore($id)
  {
    $stmt = $this->pdo->prepare("
      UPDATE {$this->table} 
      SET deleted_at = NULL 
      WHERE id = ? 
      AND deleted_at IS NOT NULL
    ");
    return $stmt->execute([$id]);
  }

  public function assignToRole($role, array $permissionIds)
  {
    $stmt = $this->pdo->prepare("DELETE FROM {$this->rolePermissionsTable} WHERE role = ?");
    $stmt->execute([$role]);

    $stmt = $this->pdo->prepare("
      INSERT INTO {$this->rolePermissionsTable} 
      (role, permission_id, created_at) 
      VALUES (?, ?, NOW())
    ");
    foreach ($permissionIds as $pid) {
      $stmt->execute([$role, $pid]);
    }
  }

  public function assignToUser($userId, array $permissionIds)
  {
    $stmt = $this->pdo->prepare("DELETE FROM {$this->userPermissionsTable} WHERE user_id = ?");
    $stmt->execute([$userId]);

    $stmt = $this->pdo->prepare("
      INSERT INTO {$this->userPermissionsTable} 
      (user_id, permission_id, created_at) 
      VALUES (?, ?, NOW())
    ");

    foreach ($permissionIds as $pid) {
      $stmt->execute([$userId, $pid]);
    }
  }

  public function getRolePermissions($role)
  {
    $stmt = $this->pdo->prepare("
      SELECT p.id, p.name
      FROM {$this->rolePermissionsTable} rp
      JOIN {$this->table} p ON p.id = rp.permission_id
      WHERE rp.role = ?
        AND p.deleted_at IS NULL
    ");

    $stmt->execute([$role]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getUserPermissions($userId)
  {
    $stmt = $this->pdo->prepare("
        SELECT p.id, p.name
        FROM {$this->userPermissionsTable} up
        JOIN {$this->table} p ON p.id = up.permission_id
        WHERE up.user_id = ?
          AND p.deleted_at IS NULL
    ");

    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getUserRole($userId)
  {
    $stmt = $this->pdo->prepare("
        SELECT role FROM users WHERE id = ?
    ");

    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
  }
}
