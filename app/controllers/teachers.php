<?php

require_once BASE_PATH . "/app/models/Teacher.php";

class Teachers extends BaseController
{
  private $teacherModel;

  public function __construct()
  {
    requireLogin();
    $this->teacherModel = new TeacherModel();
  }

  public function index()
  {
    Rbac::require('teacher.view_all');

    $currentPage = (int)($_GET['page'] ?? 1);
    $perPage = 10;

    $search = $_GET['search'] ?? '';
    $showDeleted = isset($_GET['deleted']);

    $teachers = $this->teacherModel->getTeachers($search, $currentPage, $perPage, $showDeleted);
    $totalTeachers = $this->teacherModel->countTeachers($search, $showDeleted);
    $totalPages = ceil($totalTeachers / $perPage);

    return $this->render('teachers/index', compact(
      'teachers',
      'currentPage',
      'perPage',
      'search',
      'showDeleted',
      'totalTeachers',
      'totalPages'
    ));
  }

  public function restore($id)
  {
    Rbac::require('teacher.restore');

    $this->teacherModel->restore($id);
    setFlash('success', 'Teacher restored');

    return $this->redirect("/teachers?deleted=1");
  }

  public function create()
  {
    Rbac::require('teacher.create');

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      if ($name === '') {
        $errors['name'] = 'Name is required';
      }

      if ($email === '') {
        $errors['email'] = 'Email is required';
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
      } elseif ($this->teacherModel->emailExists($email)) {
        $errors['email'] = 'Email already in use';
      }

      if ($password === '') {
        $errors['password'] = 'Password is required';
      } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
      }

      if (empty($errors)) {

        $this->teacherModel->create([
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Teacher created');

        return $this->redirect("/teachers");
      }
    }

    return $this->render('teachers/create', compact('errors'));
  }

  public function edit($id)
  {
    Rbac::require('teacher.edit');

    $teacher = $this->teacherModel->find($id);

    if (!$teacher) {
      die("Teacher not found");
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      if ($name === '') {
        $errors['name'] = 'Name is required';
      }

      if ($email === '') {
        $errors['email'] = 'Email is required';
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
      } elseif (
        $email !== $teacher['email'] &&
        $this->teacherModel->emailExists($email)
      ) {
        $errors['email'] = 'Email already in use';
      }

      if (empty($errors)) {

        $this->teacherModel->update($id, [
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Teacher updated');

        return $this->redirect("/teachers/view/$id");
      }
    }

    return $this->render('teachers/edit', compact('teacher', 'errors'));
  }

  public function delete($id)
  {
    Rbac::require('teacher.delete');

    $this->teacherModel->softDelete($id);

    setFlash('success', 'Teacher deleted');

    return $this->redirect("/teachers");
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
        if (hasPermission('restore_teacher')) {
          $actions[] = '<a href="/teachers/restore/' . (int)$row['id'] . '">Restore</a>';
        }
      } else {
        if (hasPermission('edit_teacher')) {
          $actions[] = '<a href="/teachers/edit/' . (int)$row['id'] . '">Edit</a>';
        }

        if (hasPermission('delete_teacher')) {
          $actions[] = '<a href="/teachers/delete/' . (int)$row['id'] . '" onclick="return confirm(\'Delete teacher?\')">Delete</a>';
        }
      }

      if (method_exists($this, 'view')) {
        $actions[] = '<a href="/teachers/view/' . (int)$row['id'] . '">View</a>';
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
