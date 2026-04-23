<?php

require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Permission.php';

class Permissions extends BaseController
{
  private $permissionModel;
  private $userModel; // for assigning permissions to users
  private $rolePermissionsTable = 'role_permissions';
  private $userPermissionsTable = 'user_permissions';

  public function __construct()
  {
    Rbac::require('permission.manage');
    $this->permissionModel = new PermissionModel();
    $this->userModel = new UserModel();
  }

  public function index()
  {
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    $q = $_GET['q'] ?? '';

    $permissions = $this->permissionModel->getPaginated($perPage, $offset, $q);
    $total = $this->permissionModel->countFiltered($q ?? '');

    $this->render('permissions/index', [
      'permissions' => $permissions,
      'totalPages' => ceil($total / $perPage),
      'page' => $page,
      'q' => $q
    ]);
  }

  public function view($id)
  {
    $permission = $this->permissionModel->getById($id);

    $this->render('permissions/view', compact('permission'));
  }

  public function create()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = trim($_POST['name']);
      $this->permissionModel->create($name);

      return $this->redirect('/permissions');
    }

    $this->render('permissions/create');
  }

  public function edit($id)
  {
    $permission = $this->permissionModel->getById($id);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = trim($_POST['name']);
      $this->permissionModel->update($id, $name);

      return $this->redirect('/permissions');
    }

    $this->render('permissions/edit', compact('permission'));
  }

  public function delete($id)
  {
    $this->permissionModel->softDelete($id);

    return $this->redirect('/permissions');
  }

  public function restore($id)
  {
    $this->permissionModel->restore($id);

    return $this->redirect('/permissions');
  }

  public function roles()
  {
    $roles = ['admin', 'teacher', 'student'];

    $allPermissions = $this->permissionModel->getAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $role = $_POST['role'] ?? null;
      $permissionIds = $_POST['permissions'] ?? [];

      if ($role) {
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $userId = $_POST['user_id'] ?? null;
      $permissionIds = $_POST['permissions'] ?? [];

      if ($userId) {
        $this->permissionModel->assignToUser($userId, $permissionIds);
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $userId = $_POST['user_id'];
      $permissionIds = $_POST['permissions'] ?? [];

      $this->permissionModel->assignToUser($userId, $permissionIds);

      return $this->redirect("/permissions/users?user_id=' . $userId");
    }
  }
}
