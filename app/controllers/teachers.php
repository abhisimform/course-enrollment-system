<?php

require_once BASE_PATH . "/app/models/User.php";
require_once BASE_PATH . '/app/models/Teacher.php';
require_once BASE_PATH . '/app/models/Permission.php';

class Teachers extends BaseController
{
  private $userModel;
  private $teacherModel;
  private $permissionModel;

  public function __construct()
  {
    requireLogin();
    $this->userModel = new UserModel();
    $this->teacherModel = new TeacherModel();
    $this->permissionModel = new PermissionModel();
  }

  public function index()
  {
    Rbac::require('teacher.view_all');

    return $this->render('teachers/index');
  }

  public function restore($id)
  {
    Rbac::require('teacher.restore');

    $this->teacherModel->restore($id);
    setFlash('success', 'Teacher restored');

    return $this->redirect('/teachers?deleted=1');
  }

  public function create()
  {
    Rbac::require('teacher.create');

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if ($this->validateCsrfOrFail())
        $errors['csrf_token'] = 'Invalid CSRF token';

      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      if ($name === '') {
        $errors['name'] = 'Name is required';
      } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Name must not exceed 100 characters';
      }

      if ($email === '') {
        $errors['email'] = 'Email is required';
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
      } elseif ($this->userModel->emailExists($email)) {
        $errors['email'] = 'Email already in use';
      }

      if ($password === '') {
        $errors['password'] = 'Password is required';
      } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
      }

      if (empty($errors)) {
        $createdUserId = $this->teacherModel->create([
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        if ($createdUserId) {
          $rolePermissionIds = array_column(
            $this->permissionModel->getRolePermissions('teacher'),
            'id'
          );

          $this->permissionModel->assignToUser((int)$createdUserId, $rolePermissionIds);

          $mailResult = Mailer::sendWelcomeCredentials(
            ['name' => $name, 'email' => $email],
            ['role' => 'teacher', 'password' => $password]
          );

          setFlash(
            $mailResult['sent'] ? 'success' : 'error',
            $mailResult['sent']
              ? 'Teacher created and welcome email sent'
              : 'Teacher created. ' . $mailResult['message']
          );
        } else {
          setFlash('error', 'Teacher could not be created');
        }

        return $this->redirect('/teachers');
      }
    }

    return $this->render('teachers/create', compact('errors'));
  }

  public function edit($id)
  {
    Rbac::require('teacher.edit');

    $teacher = $this->teacherModel->find($id);

    if (!$teacher) {
      die('Teacher not found');
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if ($this->validateCsrfOrFail())
        $errors['csrf_token'] = 'Invalid CSRF token';

      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');

      if ($name === '') {
        $errors['name'] = 'Name is required';
      } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Name must not exceed 100 characters';
      }

      if ($email === '') {
        $errors['email'] = 'Email is required';
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
      } elseif (
        $email !== $teacher['email'] &&
        $this->userModel->emailExists($email)
      ) {
        $errors['email'] = 'Email already in use';
      }

      if (empty($errors)) {

        $this->teacherModel->update($id, [
          'name' => $name,
          'email' => $email
        ]);

        setFlash('success', 'Teacher updated');

        return $this->redirect('/teachers');
      }
    }

    return $this->render('teachers/edit', compact('teacher', 'errors'));
  }

  public function delete($id)
  {
    Rbac::require('teacher.delete');

    $this->teacherModel->softDelete($id);

    setFlash('success', 'Teacher deleted');

    return $this->redirect('/teachers');
  }

  public function ajax()
  {
    Rbac::require('teacher.view_all');

    header('Content-Type: application/json');

    $draw = (int)($_GET['draw'] ?? 1);
    $start = (int)($_GET['start'] ?? 0);
    $length = (int)($_GET['length'] ?? 10);
    $searchParam = $_GET['search'] ?? '';
    $search = trim(is_array($searchParam) ? ($searchParam['value'] ?? '') : $searchParam);
    $showDeleted = isset($_GET['deleted']) && $_GET['deleted'] == '1';

    $columnIndex = (int)($_GET['order'][0]['column'] ?? 0);
    $orderDir = $_GET['order'][0]['dir'] ?? 'desc';
    $columns = ['id', 'name', 'email'];
    $orderBy = $columns[$columnIndex] ?? 'id';

    $rows = $this->teacherModel->getDataTableRecords($start, $length, $search, $orderBy, $orderDir, $showDeleted);

    foreach ($rows as &$row) {
      $actions = [];

      if ($showDeleted) {
        if (Rbac::has('teacher.restore')) {
          $actions[] = '<a href="/teachers/restore/' . (int)$row['id'] . '">Restore</a>';
        }
      } else {
        if (Rbac::has('teacher.edit')) {
          $actions[] = '<a href="/teachers/edit/' . (int)$row['id'] . '">Edit</a>';
        }

        if (Rbac::has('teacher.delete')) {
          $actions[] = '<a href="/teachers/delete/' . (int)$row['id'] . '" onclick="return confirm(\'Delete teacher?\')">Delete</a>';
        }
      }

      $row['actions'] = implode(' | ', $actions);
    }

    echo json_encode([
      'draw' => $draw,
      'recordsTotal' => $this->teacherModel->getDataTableTotalCount($showDeleted),
      'recordsFiltered' => $this->teacherModel->getFilteredCount($search, $showDeleted),
      'data' => $rows
    ]);
    exit;
  }
}
