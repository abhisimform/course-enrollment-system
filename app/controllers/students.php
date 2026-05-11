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

    if (isPOSTRequest()) {
      if (!$this->isValidCSRF()) {
        $errors['csrf_token'] = 'Invalid CSRF token';
      }

      if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
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

  public function create()
  {
    Rbac::require('student.create');

    $errors = [];

    if (isPOSTRequest()) {

      $data = $this->getStudentFormData();

      $errors = $this->validateStudentData($data);

      if (empty($errors)) {

        $payload = $this->sanitizeStudentData($data);

        $createdUserId =
          $this->studentModel->create($payload);

        if ($createdUserId) {

          $rolePermissionIds = array_column(
            $this->permissionModel
              ->getRolePermissions('student'),
            'id'
          );

          $this->permissionModel->assignToUser(
            (int)$createdUserId,
            $rolePermissionIds
          );

          $mailResult =
            Mailer::sendWelcomeCredentials(
              [
                'name'  => $payload['name'],
                'email' => $payload['email']
              ],
              [
                'role'     => 'student',
                'password' => $payload['password']
              ]
            );

          setFlash(
            $mailResult['sent']
              ? 'success'
              : 'error',

            $mailResult['sent']
              ? 'Student created and welcome email sent'
              : 'Student created. ' .
              $mailResult['message']
          );
        } else {

          setFlash(
            'error',
            'Student could not be created'
          );
        }

        return $this->redirect('/students');
      }
    }

    return $this->render(
      'students/create',
      compact('errors')
    );
  }

  public function edit($id)
  {
    Rbac::require('student.edit');

    $student = $this->studentModel->find($id);

    if (!$student) {

      setFlash('error', 'Student not found');

      return $this->redirect('/students');
    }

    $errors = [];

    if (isPOSTRequest()) {

      $data = $this->getStudentFormData();

      $errors = $this->validateStudentData($data, $student);

      if (empty($errors)) {

        $payload = $this->sanitizeStudentData($data);

        $this->studentModel->update($id, $payload);
        setFlash('success', 'Student updated');

        return $this->redirect(
          "/students/edit/$id"
        );
      }
    }

    return $this->render(
      'students/edit',
      compact('student', 'errors')
    );
  }

  public function restore($id)
  {
    Rbac::require('student.restore');

    if (!isPOSTRequest()) {
      setFlash('error', 'Invalid request type');
      return $this->redirect('/students');
    }

    if (!$this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');
      return $this->redirect('/students');
    }

    $this->studentModel->restore($id);
    setFlash('success', 'Student restored');

    return $this->redirect("/students?deleted=1");
  }

  public function delete($id)
  {
    Rbac::require('student.delete');

    if (!isPOSTRequest()) {
      setFlash('error', 'Invalid request type');
      return $this->redirect('/students');
    }

    if (!$this->isValidCSRF()) {
      setFlash('error', 'Invalid CSRF token');
      return $this->redirect('/students');
    }

    $this->studentModel->softDelete($id);

    setFlash('success', 'Student deleted');

    return $this->redirect("/students");
  }

  public function getStudentData()
  {
    Rbac::require('student.view_all');

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

    $rows = $this->studentModel->getDataTableRecords($start, $length, $search, $orderBy, $orderDir, $showDeleted);

    $canRestore = Rbac::has('student.restore');
    $caneEdit = Rbac::has('student.edit');
    $canDelete = Rbac::has('student.delete');

    foreach ($rows as &$row) {
      $actions = [];

      if ($showDeleted) {
        if ($canRestore) {
          $actions[] = postActionLink(
            'Restore',
            '/students/restore/' . (int)$row['id'],
            'Restore student?'
          );
        }
      } else {
        if ($caneEdit) {
          $actions[] = '<a href="/students/edit/' . (int)$row['id'] . '">Edit</a>';
        }

        if ($canDelete) {
          $actions[] = postActionLink(
            'Delete',
            '/students/delete/' . (int)$row['id'],
            'Delete student?'
          );
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

  private function sanitizeStudentData($data)
  {
    return [
      'name'     => $data['name'],
      'email'    => strtolower($data['email']),
      'password' => $data['password']
    ];
  }

  private function getStudentFormData()
  {
    return [
      'name'     => trim($_POST['name'] ?? ''),
      'email'    => trim($_POST['email'] ?? ''),
      'password' => trim($_POST['password'] ?? ''),
    ];
  }

  private function validateStudentData($data, $existingStudent = null)
  {
    $errors = [];

    if (!$this->isValidCSRF()) {

      $errors['csrf_token'] = 'Invalid CSRF token';
    }

    if ($data['name'] === '') {

      $errors['name'] = 'Name is required';
    } elseif (mb_strlen($data['name']) > 100) {

      $errors['name'] =
        'Name must not exceed 100 characters';
    }

    if ($data['email'] === '') {

      $errors['email'] = 'Email is required';
    } elseif (
      !filter_var(
        $data['email'],
        FILTER_VALIDATE_EMAIL
      )
    ) {

      $errors['email'] = 'Invalid email';
    } elseif (
      (
        !$existingStudent ||
        $data['email'] !== $existingStudent['email']
      ) &&
      $this->userModel->emailExists($data['email'])
    ) {

      $errors['email'] = 'Email already in use';
    }

    $isCreate = !$existingStudent;

    if ($isCreate && $data['password'] === '') {

      $errors['password'] = 'Password is required';
    } elseif (
      $data['password'] !== '' &&
      strlen($data['password']) < 6
    ) {

      $errors['password'] =
        'Password must be at least 6 characters';
    }

    return $errors;
  }
}
