<?php

require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Permission.php';

class Permissions extends BaseController
{
  private $permissionModel;
  private $userModel;

  public function __construct()
  {
    Rbac::require('permission.manage');
    $this->permissionModel = new PermissionModel();
    $this->userModel = new UserModel();
  }

  public function index()
  {
    $this->render('permissions/index');
  }

  public function create()
  {
    $errors = [];

    if (isPOSTRequest()) {
      if (!$this->isValidCSRF()) {
        $errors['csrf_token'] = 'Invalid CSRF token';
      }

      $name = trim($_POST['name'] ?? '');

      if ($name === '') {
        $errors[] = 'Permission name is required';
      } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Permission name must not exceed 100 characters';
      }

      if ($this->permissionModel->permissionExists($name)) {
        $errors[] = 'Permission name already exists';
      }

      if (empty($errors)) {
        $this->permissionModel->create($name);

        return $this->redirect('/permissions');
      }
    }

    $this->render('permissions/create', compact('errors'));
  }

  public function edit($id)
  {
    $permission = $this->permissionModel->getById($id);
    $errors = [];

    if (isPOSTRequest()) {
      if (!$this->isValidCSRF()) {
        $errors['csrf_token'] = 'Invalid CSRF token';
      }

      $name = trim($_POST['name'] ?? '');

      if ($name === '') {
        $errors[] = 'Permission name is required';
      } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Permission name must not exceed 100 characters';
      }

      if (empty($errors)) {
        $this->permissionModel->update($id, $name);

        return $this->redirect('/permissions');
      }
    }

    $this->render('permissions/edit', compact('permission', 'errors'));
  }

  public function delete($id)
  {
    if (!isPOSTRequest()) {
      setFlash('error', 'Invalid request type');
      return $this->redirect('/permissions');
    }

    if (!$this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');
      return $this->redirect('/permissions');
    }

    $this->permissionModel->hardDelete($id);

    return $this->redirect('/permissions');
  }

  public function restore($id)
  {
    if (!isPOSTRequest()) {
      setFlash('error', 'Invalid request type');
      return $this->redirect('/permissions');
    }

    if (!$this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');
      return $this->redirect('/permissions');
    }

    $this->permissionModel->restore($id);

    return $this->redirect('/permissions');
  }

  public function roles()
  {
    $roles = ['admin', 'teacher', 'student'];

    $allPermissions = $this->permissionModel->getAll();

    if (isPOSTRequest()) {
      if (!$this->isValidCSRF()) {
        $errors['csrf_token'] = 'Invalid CSRF token';
      }

      $role = $_POST['role'] ?? null;
      $permissionIds = $_POST['permissions'] ?? [];

      if (in_array($role, $roles, true)) {
        $this->permissionModel->assignToRole($role, $permissionIds);
      }

      return $this->redirect('/permissions/roles?role=' . $role);
    }

    $selectedRole = $_GET['role'] ?? null;

    $rolePermissions = [];

    foreach ($roles as $role) {
      $rolePermissions[$role] = array_column(
        $this->permissionModel->getRolePermissions($role),
        'id'
      );
    }

    $this->render('/permissions/roles', compact('rolePermissions', 'selectedRole', 'allPermissions', 'roles'));
  }

  public function updateRolePermissions()
  {
    if (isPOSTRequest()) {
      if (!$this->isValidCSRF()) {
        $errors['csrf_token'] = 'Invalid CSRF token';
      }

      $roles = ['admin', 'teacher', 'student'];

      foreach ($roles as $role) {
        $permissionIds = $_POST[$role] ?? [];
        $this->permissionModel->assignToRole($role, $permissionIds);
      }

      return $this->redirect("/permissions/roles");
    }
  }

  public function users()
  {
    if (isPOSTRequest()) {
      if (!$this->isValidCSRF()) {
        $errors['csrf_token'] = 'Invalid CSRF token';
      }

      $userId = $_POST['user_id'] ?? null;
      $permissionIds = $_POST['permissions'] ?? [];

      if (ctype_digit((string)$userId) && (int)$userId > 0) {
        $this->permissionModel->assignToUser((int)$userId, $permissionIds);
      }

      return $this->redirect("/permissions/users?user_id=" . $userId);
    }

    $users = $this->userModel->getAll();
    $allPermissions = $this->permissionModel->getAll();

    $selectedUserId = $_GET['user_id'] ?? null;

    $selectedUser = null;
    $userPermissions = [];
    $selectedUserRole = null;

    if ($selectedUserId) {
      $selectedUser = $this->userModel->find($selectedUserId);

      $userPermissions = array_column(
        $this->permissionModel->getUserPermissions($selectedUserId),
        'id'
      );

      $selectedUserRole = $this->permissionModel->getUserRole($selectedUserId);
    }

    $roles = ['admin', 'teacher', 'student'];

    return $this->render('permissions/users', [
      'users' => $users,
      'allPermissions' => $allPermissions,
      'selectedUserId' => $selectedUserId,
      'selectedUser' => $selectedUser,
      'userPermissions' => $userPermissions,
      'selectedUserRole' => $selectedUserRole,
      'roles' => $roles
    ]);
  }

  public function getRolePermissionsJson()
  {
    $role = $_GET['role'] ?? null;

    if (!$role) {
      echo json_encode([]);
      exit;
    }

    $permissions = $this->permissionModel->getRolePermissions($role);

    echo json_encode(array_column($permissions, 'id'));
    exit;
  }

  public function updateUserPermissions()
  {
    if (isPOSTRequest()) {
      if (!$this->isValidCSRF()) {
        $errors['csrf_token'] = 'Invalid CSRF token';
      }

      $userId = $_POST['user_id'] ?? '';
      $permissionIds = $_POST['permissions'] ?? [];

      if (ctype_digit((string)$userId) && (int)$userId > 0) {
        $this->permissionModel->assignToUser((int)$userId, $permissionIds);
      }

      return $this->redirect("/permissions/users?user_id=" . $userId);
    }
  }

  public function getPermissionData()
  {
    header('Content-Type: application/json; charset=utf-8');

    $draw = (int)($_GET['draw'] ?? 1);
    $start = (int)($_GET['start'] ?? 0);
    $length = (int)($_GET['length'] ?? 10);
    $searchParam = $_GET['search'] ?? ($_GET['q'] ?? '');
    $search = trim(is_array($searchParam) ? ($searchParam['value'] ?? '') : $searchParam);

    $columnIndex = (int)($_GET['order'][0]['column'] ?? 1);
    $orderDir = $_GET['order'][0]['dir'] ?? 'asc';
    $columns = ['id', 'name', 'deleted_at'];
    $orderBy = $columns[$columnIndex] ?? 'name';

    $rows = $this->permissionModel->getDataTableRecords($start, $length, $search, $orderBy, $orderDir);

    foreach ($rows as &$row) {
      $actions = [];
      $actions[] = '<a href="/permissions/edit/' . (int)$row['id'] . '">Edit</a>';
      $actions[] = postActionLink(
        'Delete',
        '/permissions/delete/' . (int)$row['id'],
        'Delete permission?'
      );

      $row['status'] = $row['deleted_at'] ? 'Deleted' : 'Active';
      $row['actions'] = implode(' | ', $actions);
    }

    echo json_encode([
      'draw' => $draw,
      'recordsTotal' => $this->permissionModel->getDataTableTotalCount(),
      'recordsFiltered' => $this->permissionModel->getFilteredCount($search),
      'data' => $rows
    ]);
    exit;
  }
}
