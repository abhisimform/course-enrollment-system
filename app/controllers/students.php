<?php

require_once BASE_PATH . "/app/models/Student.php";

class Students extends BaseController
{
  private $studentModel;

  public function __construct()
  {
    requireLogin();
    $this->studentModel = new StudentModel();
  }

  public function index()
  {
    Rbac::require('student.view_all');

    return $this->render('students/index');
  }

  public function restore($id)
  {
    Rbac::require('student.restore');

    $this->studentModel->restore($id);
    setFlash('success', 'Student restored');

    return $this->redirect("/students?deleted=1");
  }

  public function create()
  {
    Rbac::require('student.create');

    $errors = [];
    $this->ensureCsrf();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->validateCsrfOrFail();

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
        $errors['email'] = 'Invalid email';
      } elseif ($this->studentModel->emailExists($email)) {
        $errors['email'] = 'Email already in use';
      }

      if ($password === '') {
        $errors['password'] = 'Password is required';
      } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
      }

      if (empty($errors)) {

        $this->studentModel->create([
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Student created');

        return $this->redirect("/students");
      }
    }

    return $this->render('students/create', compact('errors'));
  }

  public function edit($id)
  {
    Rbac::require('student.edit');

    $student = $this->studentModel->find($id);

    if (!$student) {
      die("Student not found");
    }

    $errors = [];
    $this->ensureCsrf();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->validateCsrfOrFail();

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
        $errors['email'] = 'Invalid email';
      } elseif ($email !== $student['email'] && $this->studentModel->emailExists($email)) {
        $errors['email'] = 'Email already in use';
      }

      if ($password !== '' && strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
      }

      if (empty($errors)) {

        $this->studentModel->update($id, [
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Student updated');

        return $this->redirect("/students/edit/$id");
      }
    }

    return $this->render('students/edit', compact('student', 'errors'));
  }

  public function delete($id)
  {
    Rbac::require('student.delete');

    $this->studentModel->softDelete($id);

    setFlash('success', 'Student deleted');

    return $this->redirect("/students");
  }

  public function ajax()
  {
    Rbac::require('student.view_all');

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

    $rows = $this->studentModel->getDataTableRecords($start, $length, $search, $orderBy, $orderDir, $showDeleted);

    foreach ($rows as &$row) {
      $actions = [];

      if ($showDeleted) {
        if (Rbac::has('student.restore')) {
          $actions[] = '<a href="/students/restore/' . (int)$row['id'] . '">Restore</a>';
        }
      } else {
        if (Rbac::has('student.edit')) {
          $actions[] = '<a href="/students/edit/' . (int)$row['id'] . '">Edit</a>';
        }

        if (Rbac::has('student.delete')) {
          $actions[] = '<a href="/students/delete/' . (int)$row['id'] . '" onclick="return confirm(\'Delete student?\')">Delete</a>';
        }
      }

      $row['actions'] = implode(' | ', $actions);
    }

    echo json_encode([
      'draw' => $draw,
      'recordsTotal' => $this->studentModel->getDataTableTotalCount($showDeleted),
      'recordsFiltered' => $this->studentModel->getFilteredCount($search, $showDeleted),
      'data' => $rows
    ]);
    exit;
  }
}
