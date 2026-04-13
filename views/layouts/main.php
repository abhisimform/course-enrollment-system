<?php require_once BASE_PATH . '/utils/helper.php'; ?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Panel</title>
</head>

<body>

  <h2>Admin Panel</h2>

  <p>
    Welcome, <?= $_SESSION['user']['name'] ?> (<?= $_SESSION['user']['role'] ?>)
  </p>

  <hr>

  <!-- NAVIGATION -->
  <a href="/dashboard">Dashboard</a> |

  <?php if (hasPermission('create_student')): ?>
    <a href="/students">Students</a> |
  <?php endif; ?>

  <?php if (hasPermission('create_course')): ?>
    <a href="/courses">Courses</a> |
  <?php endif; ?>

  <?php if (hasPermission('enroll_student')): ?>
    <a href="/enrollments">Enrollments</a> |
  <?php endif; ?>

  <?php if (hasPermission('manage_teachers')): ?>
    <a href="/users">Teachers</a> |
  <?php endif; ?>

  <?php if (hasPermission('view_audit_logs')): ?>
    <a href="/audit">Audit Logs</a> |
  <?php endif; ?>

  <a href="/auth/logout">Logout</a>

  <hr>

  <!-- FLASH -->
  <?php if ($flash = getFlash()): ?>
    <div style="color:white; background:<?= $flash['type'] == 'success' ? 'green' : 'red' ?>; padding:10px;">
      <?= $flash['message'] ?>
    </div>
  <?php endif; ?>

  <!-- PAGE CONTENT -->
  <?php require $view; ?>

</body>

</html>