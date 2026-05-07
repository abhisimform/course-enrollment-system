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
      margin: 0;
      padding: 24px;
      background: linear-gradient(180deg, #f8fbff 0%, #eef3f8 100%);
      color: var(--text);
      font-family: Arial, sans-serif;
      line-height: 1.5;
    }

    a {
      margin-right: 10px;
      text-decoration: none;
      color: var(--primary);
    }

    .nav {
      padding: 14px 16px;
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(188, 204, 220, 0.8);
      border-radius: 14px;
      margin-bottom: 18px;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .flash {
      padding: 12px 14px;
      color: #fff;
      margin-bottom: 16px;
      border-radius: 12px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .dttt-container {
      margin-top: 20px;
      padding: 18px;
      background: var(--surface);
      border: 1px solid rgba(188, 204, 220, 0.7);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    .dttt-inline-form {
      display: inline-block;
      margin: 0;
    }

    .dttt-btn {
      min-height: 34px;
      padding: 0 12px;
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #fff;
      color: var(--text);
      cursor: pointer;
      font-weight: 600;
    }

    .dttt-btn:hover {
      border-color: var(--primary);
      color: var(--primary);
      background: #f6f9ff;
    }

    .dttt-btn-danger {
      border-color: #f2b8b5;
      color: var(--danger);
      background: #fff5f5;
    }

    .dttt-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 28px;
      padding: 0 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
    }

    .dttt-badge-success {
      background: #e7f6ec;
      color: var(--success);
    }

    .dttt-badge-warning {
      background: #fff4db;
      color: #a15c07;
    }

    .dttt-badge-muted {
      background: #edf2f7;
      color: var(--text-muted);
    }

    .dttt-progress {
      min-width: 120px;
      background: #e9eef5;
      border-radius: 999px;
      overflow: hidden;
    }

    .dttt-progress-bar {
      min-height: 22px;
      padding: 0 8px;
      color: #fff;
      font-size: 12px;
      line-height: 22px;
      text-align: center;
      white-space: nowrap;
    }

    .dttt-container .dataTables_wrapper,
    .dttt-container .dttt-container {
      color: var(--text);
      font-size: 14px;
    }

    .dttt-container .dttt-layout-row {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 12px 16px;
      margin-bottom: 14px;
    }

    .dttt-container .dttt-layout-table {
      margin-bottom: 0;
    }

    .dttt-container .dttt-scroll {
      overflow: hidden;
      border: 1px solid #e6edf5;
      border-radius: 14px;
      background: var(--surface);
    }

    .dttt-container .dttt-scroll-head,
    .dttt-container .dttt-scroll-foot {
      overflow: hidden;
      background: var(--surface);
    }

    .dttt-container .dttt-scroll-headInner,
    .dttt-container .dttt-scroll-footInner {
      width: 100% !important;
    }

    .dttt-container .dttt-scroll-body {
      max-height: 420px;
      overflow: auto !important;
      overscroll-behavior: contain;
      scrollbar-gutter: stable both-edges;
      background: var(--surface);
    }

    .dttt-container .dttt-length,
    .dttt-container .dttt-search,
    .dttt-container .dttt-info,
    .dttt-container .dttt-paging {
      margin: 0;
    }

    .dttt-container .dttt-length label,
    .dttt-container .dttt-search label,
    .dttt-container .dttt-info {
      color: var(--text-muted);
      font-size: 13px;
      font-weight: 600;
    }

    .dttt-container .dttt-input,
    .dttt-container .dttt-length select,
    .dttt-container .dttt-search input {
      min-height: 40px;
      border: 1px solid var(--border);
      border-radius: 10px;
      background: #fff;
      color: var(--text);
      padding: 0 12px;
      outline: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .dttt-container .dttt-length select {
      min-width: 84px;
      padding-right: 32px;
    }

    .dttt-container .dttt-search input {
      min-width: 240px;
    }

    .dttt-container .dttt-input:focus,
    .dttt-container .dttt-length select:focus,
    .dttt-container .dttt-search input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.12);
    }

    .dttt-container table.dataTable {
      width: 100% !important;
      margin: 0 !important;
      border-collapse: separate !important;
      border-spacing: 0;
      background: var(--surface);
    }

    .dttt-container .dttt-scroll-body table.dataTable {
      border-top: none !important;
    }

    .dttt-container table.dataTable thead th,
    .dttt-container table.dataTable thead td {
      position: relative;
      padding: 14px 16px;
      background: linear-gradient(180deg, #f9fbfd 0%, #f1f5f9 100%);
      color: var(--text);
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.02em;
      text-transform: uppercase;
      border-bottom: 1px solid var(--border-strong);
      white-space: nowrap;
    }

    .dttt-container table.dataTable thead th:first-child {
      border-top-left-radius: 12px;
    }

    .dttt-container table.dataTable thead th:last-child {
      border-top-right-radius: 12px;
    }

    .dttt-container table.dataTable tbody td {
      padding: 14px 16px;
      color: var(--text);
      border-bottom: 1px solid #e9eef5;
      vertical-align: middle;
      background: #fff;
    }

    .dttt-container table.dataTable tbody tr {
      transition: background 0.2s ease, transform 0.2s ease;
    }

    .dttt-container table.dataTable tbody tr:nth-child(even) td {
      background: #fbfdff;
    }

    .dttt-container table.dataTable tbody tr:hover td {
      background: #f4f8ff;
    }

    .dttt-container table.dataTable tbody tr:last-child td {
      border-bottom: none;
    }

    .dttt-container table.dataTable tbody tr.selected td,
    .dttt-container table.dataTable tbody tr>.selected {
      background: var(--primary-soft) !important;
      color: var(--text);
    }

    .dttt-container table.dataTable.order-column tbody tr>.sorting_1,
    .dttt-container table.dataTable.display tbody tr>.sorting_1,
    .dttt-container table.dataTable.order-column tbody tr>.sorting_2,
    .dttt-container table.dataTable.display tbody tr>.sorting_2,
    .dttt-container table.dataTable.order-column tbody tr>.sorting_3,
    .dttt-container table.dataTable.display tbody tr>.sorting_3 {
      background: rgba(224, 236, 255, 0.45);
    }

    .dttt-container .dttt-paging {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .dttt-container .dttt-paging .dttt-paging-button {
      min-width: 38px;
      height: 38px;
      margin: 0;
      padding: 0 12px;
      border: 1px solid var(--border);
      border-radius: 10px;
      background: #fff;
      color: var(--text);
      font-weight: 600;
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .dttt-container .dttt-paging .dttt-paging-button:hover {
      border-color: var(--primary);
      background: #f6f9ff;
      color: var(--primary);
    }

    .dttt-container .dttt-paging .dttt-paging-button.current,
    .dttt-container .dttt-paging .dttt-paging-button.current:hover {
      border-color: var(--primary);
      background: var(--primary);
      color: #fff !important;
      box-shadow: 0 10px 18px rgba(29, 78, 216, 0.22);
    }

    .dttt-container .dttt-paging .dttt-paging-button.disabled,
    .dttt-container .dttt-paging .dttt-paging-button.disabled:hover {
      opacity: 0.45;
      cursor: not-allowed;
      background: #f8fafc;
      border-color: var(--border);
      color: var(--text-muted);
    }

    .dttt-container .dttt-info {
      padding-top: 0;
    }

    .dttt-container .dataTables_processing,
    .dttt-container .dttt-processing {
      border: 1px solid rgba(188, 204, 220, 0.9) !important;
      border-radius: 12px !important;
      background: rgba(255, 255, 255, 0.96) !important;
      color: var(--text) !important;
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
      padding: 12px 18px !important;
    }

    @media (max-width: 768px) {
      body {
        padding: 16px;
      }

      .dttt-container {
        padding: 14px;
        border-radius: 14px;
      }

      .dttt-container .dttt-layout-row {
        align-items: stretch;
      }

      .dttt-container .dttt-search,
      .dttt-container .dttt-length,
      .dttt-container .dttt-info,
      .dttt-container .dttt-paging {
        width: 100%;
      }

      .dttt-container .dttt-search input,
      .dttt-container .dttt-length select {
        width: 100%;
        min-width: 0;
      }

      .dttt-container .dttt-paging {
        justify-content: flex-start;
      }

      .dttt-container table.dataTable thead th,
      .dttt-container table.dataTable tbody td {
        padding: 12px;
      }

      .dttt-progress {
        min-width: 96px;
      }
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

  <h2><?= ucfirst(e($_SESSION['user']['role'])) ?> Panel</h2>

  <p>
    Welcome, <?= e($_SESSION['user']['name']) ?> (<?= e($_SESSION['user']['role']) ?>)
  </p>

  <hr>

  <div class="nav">

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

    <?php if (Rbac::isAdmin()): ?>
      <a href="/notifications">Email Queue</a>
    <?php endif; ?>

    <?php if (Rbac::has('audit.auth_log')): ?>
      <a href="/authlogs">Auth Logs</a>
    <?php endif; ?>

    <?php if (Rbac::has('profile.view')): ?>
      <a href="/auth/profile">My Profile</a>
    <?php endif; ?>

    <a href="/auth/logout">Logout</a>
  </div>

  <hr>

  <?php if ($flash = getFlash()): ?>
    <div class="flash" style="background: <?= $flash['type'] == 'success' ? 'green' : 'red' ?>;">
      <?= $flash['message'] ?>
    </div>
  <?php endif; ?>

  <?php require $view; ?>

  <script>
    <?php readfile(BASE_PATH . '/public/assets/js/app.js'); ?>
  </script>
</body>

</html>
