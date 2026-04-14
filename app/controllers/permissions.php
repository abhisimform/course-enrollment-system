<?php

class Permissions
{
  private $permissionModel;
  private $userModel; // for assigning permissions to users
  private $rolePermissionsTable = 'role_permissions';
  private $userPermissionsTable = 'user_permissions';

  public function __construct()
  {
    require_once BASE_PATH . '/app/models/User.php';
    require_once BASE_PATH . '/app/models/Permission.php';

    $this->permissionModel = new PermissionModel();
    $this->userModel = new UserModel();
  }

  public function index()
  {
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    $permissions = $this->permissionModel->getPaginated($perPage, $offset, $_GET['q'] ?? '');
    $total = $this->permissionModel->countFiltered($_GET['q'] ?? '');
    $totalPages = ceil($total / $perPage);

    $view = BASE_PATH . "/views/permissions/index.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function view($id)
  {
    $permission = $this->permissionModel->getById($id);
    $view = BASE_PATH . "/views/permissions/view.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function create()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = trim($_POST['name']);
      $this->permissionModel->create($name);
      header('Location: /permissions');
      exit;
    }

    $view = BASE_PATH . "/views/permissions/create.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function edit($id)
  {
    $permission = $this->permissionModel->getById($id);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = trim($_POST['name']);
      $this->permissionModel->update($id, $name);
      header('Location: /permissions');
      exit;
    }

    $view = BASE_PATH . "/views/permissions/edit.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function delete($id)
  {
    $this->permissionModel->softDelete($id);
    header('Location: /permissions');
    exit;
  }

  public function restore($id)
  {
    $this->permissionModel->restore($id);
    header('Location: /permissions');
    exit;
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

      header("Location: /permissions/roles?role=" . $role);
      exit;
    }

    $selectedRole = $_GET['role'] ?? null;

    $rolePermissions = [];

    foreach ($roles as $role) {
      $rolePermissions[$role] = array_column(
        $this->permissionModel->getRolePermissions($role),
        'id'
      );
    }

    $view = BASE_PATH . "/views/permissions/roles.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function updateRolePermissions()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $roles = ['admin', 'teacher', 'student'];

      foreach ($roles as $role) {
        $permissionIds = $_POST[$role] ?? [];
        $this->permissionModel->assignToRole($role, $permissionIds);
      }

      header('Location: /permissions/roles');
      exit;
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

      header("Location: /permissions/users?user_id=" . $userId);
      exit;
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

    $view = BASE_PATH . "/views/permissions/users.php";
    require BASE_PATH . "/views/layouts/main.php";
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

      header('Location: /permissions/users?user_id=' . $userId);
      exit;
    }
  }
}
