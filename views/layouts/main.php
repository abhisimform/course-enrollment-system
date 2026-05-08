<?php require_once BASE_PATH . '/utils/helper.php'; ?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Panel</title>
  <style>
    :root {
      --page-bg: #f5f7fb;
      --surface: #ffffff;
      --surface-muted: #f8fafc;
      --border: #d9e2ec;
      --border-strong: #bcccdc;
      --text: #102a43;
      --text-muted: #52606d;
      --primary: #1d4ed8;
      --primary-soft: #e0ecff;
      --success: #137333;
      --danger: #c62828;
      --shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
      --radius: 16px;
    }

    body {
      display: flex;
      margin: 0;
      font-family: Arial, sans-serif;
      overflow-x: hidden;
    }

    .sidebar {
      width: 200px;
      min-height: 100vh;
      background: #ffffff;
      border-right: 1px solid #bcccdc;
      padding: 15px;
      box-shadow: 2px 0 10px rgba(15, 23, 42, 0.05);
      display: flex;
      flex-direction: column;
      gap: 8px;
      position: fixed;
      top: 0;
      left: 0;
      overflow-y: auto;
    }

    .sidebar-header h3 {
      margin: 0 0 4px 0;
      font-size: 16px;
      font-weight: bold;
      color: #1d4ed8;
    }

    .sidebar-header span {
      font-size: 12px;
      color: #52606d;
      display: block;
      margin-bottom: 10px;
    }

    .sidebar a {
      display: block;
      padding: 6px 10px;
      font-size: 15px;
      color: #102a43;
      text-decoration: none;
      border-radius: 4px;
      transition: background 0.2s, color 0.2s;
    }

    .sidebar a:hover {
      background-color: #e0ecff;
      color: #1d4ed8;
      font-weight: 500;
    }

    .content {
      margin-left: 240px;
      padding: 24px;
      flex: 1;
      min-width: 0;
      max-width: calc(100vw - 240px);
      box-sizing: border-box;
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 180px;
      }

      .content {
        margin-left: 180px;
        max-width: calc(100vw - 180px);
      }
    }

    @media (max-width: 480px) {
      .sidebar {
        position: relative;
        width: 100%;
        min-height: auto;
        padding: 10px;
        border-right: none;
        box-shadow: none;
      }

      .content {
        margin-left: 0;
        max-width: 100%;
      }
    }

    a {
      margin-right: 10px;
      text-decoration: none;
      color: var(--primary);
    }

    .flash {
      padding: 12px 14px;
      color: #fff;
      margin-bottom: 16px;
      border-radius: 12px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .form-container {
      max-width: 600px;
      margin: 20px auto;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background-color: #f9f9f9;
      font-family: Arial, sans-serif;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      margin-bottom: 15px;
    }

    .form-group label {
      font-weight: bold;
      margin-bottom: 5px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      padding: 8px 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #3498db;
      box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
      outline: none;
    }

    .is-invalid {
      border-color: #e74c3c !important;
    }

    .is-valid {
      border-color: #2ecc71 !important;
    }

    .error-message {
      color: #e74c3c;
      font-size: 13px;
      margin-top: 3px;
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .btn {
      padding: 8px 16px;
      font-weight: bold;
      border-radius: 4px;
      border: none;
      cursor: pointer;
      text-decoration: none;
    }

    .btn-primary {
      background-color: #3498db;
      color: #fff;
    }

    .btn-secondary {
      background-color: #bdc3c7;
      color: #2c3e50;
    }

    .btn:hover {
      opacity: 0.9;
    }

    .error-summary {
      max-width: 600px;
      margin: 0 auto 15px auto;
      padding: 10px;
      border: 1px solid #e74c3c;
      background-color: #fdecea;
      border-radius: 4px;
      color: #e74c3c;
    }

    @media (max-width: 480px) {
      .form-actions {
        flex-direction: column;
      }
    }

    /* Data tables CSS */
    .dt-container {
      width: 100%;
      max-width: 100%;
      overflow-x: auto;
      overflow-y: hidden;
    }

    .dt-container .dataTables_wrapper {
      width: 100%;
      max-width: 100%;
      overflow-x: auto;
      overflow-y: hidden;
    }

    .dt-container .dataTables_scroll {
      width: 100%;
      max-width: 100%;
      border: 1px solid #d9e2ec;
      border-radius: 8px;
      background-color: #ffffff;
      overflow: hidden;
      box-sizing: border-box;
    }

    .dt-container .dataTables_scrollHead,
    .dt-container .dataTables_scrollBody {
      overflow-x: auto !important;
    }

    .dt-container .dataTables_scrollHeadInner,
    .dt-container .dataTables_scrollHeadInner table,
    .dt-container .dataTables_scrollBody table {
      max-width: 100%;
      box-sizing: border-box;
    }

    .dt-container .dataTables_scrollBody {
      max-height: 420px !important;
      overflow-y: auto !important;
    }

    table.display {
      width: 100% !important;
      border-collapse: collapse;
      font-size: 14px;
      background-color: #ffffff;
    }

    table.display thead th {
      background-color: #eaf1fb;
      color: #102a43;
      font-weight: 600;
      padding: 10px 12px;
      border-bottom: 2px solid #cbd5e1;
      text-align: left;
    }

    table.display tbody td {
      padding: 8px 12px;
      border-bottom: 1px solid #e1e8ed;
    }

    table.display tbody tr:nth-child(odd) {
      background-color: #ffffff;
    }

    table.display tbody tr:nth-child(even) {
      background-color: #f8fafc;
    }

    table.display tbody tr:hover {
      background-color: #e0ecff;
    }

    table.display tbody a {
      font-size: 13px;
      text-decoration: none;
      color: #1d4ed8;
    }

    table.display tbody a:hover {
      text-decoration: underline;
    }

    .dataTables_wrapper .dataTables_paginate {
      margin-top: 12px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
      min-width: 34px;
      padding: 6px 10px !important;
      margin-left: 4px;
      border: 1px solid #d9e2ec !important;
      border-radius: 6px;
      background: #ffffff !important;
      color: #334e68 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      border-color: #1d4ed8 !important;
      background: #e0ecff !important;
      color: #1d4ed8 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
      border-color: #1d4ed8 !important;
      background: #1d4ed8 !important;
      color: #ffffff !important;
    }

    .dataTables_wrapper .dataTables_filter {
      float: right;
      margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_length {
      float: left;
      margin-bottom: 10px;
    }
  </style>

  <!-- jQuery library -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- jQuery Validation -->
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.22.0/dist/jquery.validate.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.22.0/dist/additional-methods.min.js"></script>

  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css" />

  <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
</head>

<body>

  <div class="sidebar">
    <div class="sidebar-header">
      <h3><?= ucfirst(e($_SESSION['user']['role'])) ?> Panel</h3>

      <span>Welcome, <?= e($_SESSION['user']['name']) ?> (<?= e($_SESSION['user']['role']) ?>)</span>
    </div>

    <?php if (Rbac::has('dashboard.view')): ?>
      <a href="/dashboard">Dashboard</a>
    <?php endif; ?>

    <?php if (Rbac::has('student.view_all')): ?>
      <a href="/students">Students</a>
    <?php endif; ?>

    <?php if (Rbac::has('course.view_all')): ?>
      <a href="/courses">Courses</a>
    <?php endif; ?>

    <?php if (Rbac::has('enrollment.view')): ?>
      <a href="/enrollments">Enrollments</a>
    <?php endif; ?>

    <?php if (Rbac::has('teacher.view_all')): ?>
      <a href="/teachers">Teachers</a>
    <?php endif; ?>

    <?php if (Rbac::has('permission.manage')): ?>
      <a href="/permissions">Permissions</a>
      <a href="/permissions/roles">Role Permissions</a>
      <a href="/permissions/users">User Permissions</a>
    <?php endif; ?>

    <?php if (Rbac::has('audit.view_all')): ?>
      <a href="/audit">Audit Logs</a>
    <?php endif; ?>

    <?php if (Rbac::has('profile.view')): ?>
      <a href="/auth/profile">My Profile</a>
    <?php endif; ?>

    <a href="/auth/logout">Logout</a>
  </div>

  <div class="content">

    <?php if ($flash = getFlash()): ?>
      <div class="flash" style="background: <?= $flash['type'] == 'success' ? 'green' : 'red' ?>;">
        <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>

    <?php require $view; ?>
  </div>

  <script>
    <?php readfile(BASE_PATH . '/public/assets/js/app.js'); ?>
  </script>
</body>

</html>
