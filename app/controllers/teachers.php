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

  public function create()
  {
    Rbac::require('teacher.create');

    $errors = [];

    if (isPOSTRequest()) {

      if (!$this->isValidCSRF()) {
        setFlash('error', 'Invalid CSRF token');
        return $this->redirect('/teachers');
      }

      $data = $this->getTeacherFormData();

      $errors = $this->validateTeacherData($data);

      if (empty($errors)) {

        $createdUserId =
          $this->teacherModel->create($data);

        if ($createdUserId) {

          $rolePermissionIds = array_column(
            $this->permissionModel
              ->getRolePermissions('teacher'),
            'id'
          );

          $this->permissionModel->assignToUser(
            (int)$createdUserId,
            $rolePermissionIds
          );

          $mailResult =
            Mailer::sendWelcomeCredentials(
              [
                'name'  => $data['name'],
                'email' => $data['email']
              ],
              [
                'role'     => 'teacher',
                'password' => $data['password']
              ]
            );

          setFlash(
            $mailResult['sent']
              ? 'success'
              : 'error',
            $mailResult['sent']
              ? 'Teacher created and welcome email sent'
              : 'Teacher created. ' .
              $mailResult['message']
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
      setFlash('error', 'Teacher not found');
      return $this->redirect('/teachers');
    }

    $errors = [];

    if (isPOSTRequest()) {

      if (!$this->isValidCSRF()) {
        setFlash('error', 'Invalid CSRF token');
        return $this->redirect('/teachers');
      }

      $data = $this->getTeacherFormData();

      $errors = $this->validateTeacherData($data, $teacher);

      if (empty($errors)) {

        $this->teacherModel->update($id, [
          'name'  => $data['name'],
          'email' => $data['email']
        ]);

        setFlash('success', 'Teacher updated');

        return $this->redirect('/teachers');
      }
    }

    return $this->render(
      'teachers/edit',
      compact('teacher', 'errors')
    );
  }

  public function restore()
  {
    Rbac::require('teacher.restore');

    if (!isPOSTRequest()) {
      setFlash('error', 'Invalid request type');
      return $this->redirect('/teachers');
    }

    if (!$this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');
      return $this->redirect('/teachers');
    }

    $id = $_POST['teacher_id'] ?? null;
    $this->teacherModel->restore($id);
    setFlash('success', 'Teacher restored');

    return $this->redirect('/teachers?deleted=1');
  }

  public function delete($id)
  {
    Rbac::require('teacher.delete');

    if (!isPOSTRequest()) {
      setFlash('error', 'Invalid request type');
      return $this->redirect('/teachers');
    }

    if (!$this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');
      return $this->redirect('/teachers');
    }

    $this->teacherModel->softDelete($id);

    setFlash('success', 'Teacher deleted');

    return $this->redirect('/teachers');
  }

  public function getTeacherData()
  {
    Rbac::require('teacher.view_all');

    header('Content-Type: application/json; charset=utf-8');

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

    $canRestore = Rbac::has('teacher.restore');
    $caneEdit = Rbac::has('teacher.edit');
    $canDelete = Rbac::has('teacher.delete');

    foreach ($rows as &$row) {
      $actions = [];

      if ($showDeleted) {
        if ($canRestore) {
          $actions[] = postActionLink(
            'Restore',
            '/teachers/restore/' . (int)$row['id'],
            'Restore teacher?'
          );
        }
      } else {
        if ($caneEdit) {
          $actions[] = '<a href="/teachers/edit/' . (int)$row['id'] . '">Edit</a>';
        }

        if ($canDelete) {
          $actions[] = postActionLink(
            'Delete',
            '/teachers/delete/' . (int)$row['id'],
            'Delete teacher?'
          );
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

  private function getTeacherFormData()
  {
    return [
      'name'  => trim($_POST['name'] ?? ''),
      'email' => trim($_POST['email'] ?? ''),
      'password' => $_POST['password'] ?? '',
    ];
  }

  private function validateTeacherData($data, $existingTeacher = null)
  {
    $errors = [];

    if ($data['name'] === '') {
      $errors['name'] = 'Name is required';
    } elseif (mb_strlen($data['name']) > 100) {
      $errors['name'] = 'Name must not exceed 100 characters';
    }

    if ($data['email'] === '') {
      $errors['email'] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Invalid email format';
    } elseif (
      (
        !$existingTeacher ||
        $data['email'] !== $existingTeacher['email']
      ) &&
      $this->userModel->emailExists($data['email'])
    ) {
      $errors['email'] = 'Email already in use';
    }

    if (!$existingTeacher) {

      if ($data['password'] === '') {
        $errors['password'] = 'Password is required';
      } elseif (strlen($data['password']) < 6) {
        $errors['password'] =
          'Password must be at least 6 characters';
      }
    }

    return $errors;
  }
}
