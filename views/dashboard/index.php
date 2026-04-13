<h1>Dashboard</h1>
<?php
// dd($_SESSION);
$flash = getFlash(); ?>
<?php if ($flash): ?>
  <div style="background: green; color:white; padding:10px;">
    <?= $flash['message'] ?>
  </div>
<?php endif; ?>

<div style="display:flex; gap:20px;">

  <div style="padding:20px; background:#eee;">
    <h3>Total Students</h3>
    <p><?= $data['students'] ?? 'No Data' ?></p>
    <a href="/students">View Students</a>
  </div>

  <div style="padding:20px; background:#eee;">
    <h3>Total Courses</h3>
    <p><?= $data['courses'] ?? 'No Data' ?></p>
  </div>

  <div style="padding:20px; background:#eee;">
    <h3>Active Enrollments</h3>
    <p><?= $data['enrollments'] ?? 'No Data' ?></p>
  </div>

</div>