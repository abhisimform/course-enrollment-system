<?php

require_once BASE_PATH . "/app/models/User.php";
require_once BASE_PATH . "/app/models/Student.php";
require_once BASE_PATH . "/app/models/Permission.php";

class Students extends BaseController
{
  private $userModel;
  private $studentModel;
  private $permissionModel;

  public function __construct()
  {
    requireLogin();
    $this->userModel = new UserModel();
    $this->studentModel = new StudentModel();
    $this->permissionModel = new PermissionModel();
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
        $errors['email'] = 'Invalid email';
      } elseif ($this->userModel->emailExists($email)) {
        $errors['email'] = 'Email already in use';
      }

      if ($password === '') {
        $errors['password'] = 'Password is required';
      } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
      }

      if (empty($errors)) {
        $createdUserId = $this->studentModel->create([
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        if ($createdUserId) {
          $rolePermissionIds = array_column(
            $this->permissionModel->getRolePermissions('student'),
            'id'
          );

          $this->permissionModel->assignToUser((int)$createdUserId, $rolePermissionIds);

          $mailResult = Mailer::sendWelcomeCredentials(
            ['name' => $name, 'email' => $email],
            ['role' => 'student', 'password' => $password]
          );

          setFlash(
            $mailResult['sent'] ? 'success' : 'error',
            $mailResult['sent']
              ? 'Student created and welcome email sent'
              : 'Student created. ' . $mailResult['message']
          );
        } else {
          setFlash('error', 'Student could not be created');
        }

        return $this->redirect("/students");
      }
    }

    return $this->render('students/create', compact('errors'));
  }

  private function parseCsv($filePath)
  {
    $rows = [];

    if (($handle = fopen($filePath, 'r')) !== false) {
      while (($data = fgetcsv($handle, 1000, ',', '"', '\\')) !== false) {
        $rows[] = $data;
      }
      fclose($handle);
    }

    return $rows;
  }

  public function bulkUpload()
  {
    Rbac::require('student.create');

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if ($this->validateCsrfOrFail())
        $errors['csrf_token'] = 'Invalid CSRF token';

      if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        dd($_FILES);
        $errors[] = 'File upload failed';
        return $this->render('students/bulk_upload', compact('errors'));
      }

      $file = $_FILES['file'];

      if ($file['size'] > 2 * 1024 * 1024) {
        $errors[] = 'File too large (max 2MB)';
        return $this->render('students/bulk_upload', compact('errors'));
      }

      $allowedTypes = [
        'text/csv',
      ];

      if (!in_array($file['type'], $allowedTypes)) {
        $errors[] = 'Only CSV files are allowed';
        return $this->render('students/bulk_upload', compact('errors'));
      }

      $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

      if ($ext === 'csv') {
        $rows = $this->parseCsv($file['tmp_name']);
      }

      if (empty($rows)) {
        $errors[] = 'File is empty';
        return $this->render('students/bulk_upload', compact('errors'));
      }

      $header = array_map('strtolower', $rows[0]);

      if ($header !== ['name', 'email']) {
        $errors[] = 'Invalid columns. Required: name, email';
        return $this->render('students/bulk_upload', compact('errors'));
      }

      unset($rows[0]);

      $validData = [];
      $rowErrors = [];

      foreach ($rows as $index => $row) {
        $rowNumber = $index + 2;

        $name = trim($row[0] ?? '');
        $email = trim($row[1] ?? '');

        if ($name === '') {
          $rowErrors[] = "Row {$rowNumber}: Name is required";
          continue;
        }

        if (mb_strlen($name) > 100) {
          $rowErrors[] = "Row {$rowNumber}: Name too long";
          continue;
        }

        if ($email === '') {
          $rowErrors[] = "Row {$rowNumber}: Email is required";
          continue;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $rowErrors[] = "Row {$rowNumber}: Invalid email";
          continue;
        }

        if ($this->userModel->emailExists($email)) {
          $rowErrors[] = "Row {$rowNumber}: Email already exists";
          continue;
        }

        $validData[] = [
          'name' => $name,
          'email' => $email,
          'password' => '123456'
        ];
      }

      if (!empty($rowErrors)) {
        return $this->render('students/bulk_upload', [
          'errors' => $rowErrors
        ]);
      }

      $emails = array_column($validData, 'email');
      if (count($emails) !== count(array_unique($emails))) {
        $errors[] = 'Duplicate emails found in file';
        return $this->render('students/bulk_upload', compact('errors'));
      }

      $this->studentModel->bulkInsert($validData);

      setFlash('success', 'Students uploaded successfully');
      return $this->redirect('/students');
    }

    return $this->render('students/bulk_upload', compact('errors'));
  }

  public function edit($id)
  {
    Rbac::require('student.edit');

    $student = $this->studentModel->find($id);

    if (!$student) {
      die("Student not found");
    }

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
        $errors['email'] = 'Invalid email';
      } elseif ($email !== $student['email'] && $this->userModel->emailExists($email)) {
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
