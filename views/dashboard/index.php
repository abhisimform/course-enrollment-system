<h1>Dashboard</h1>
<?php
// dd($_SESSION);
$flash = getFlash(); ?>
<?php if ($flash): ?>
  <div style="background: green; color:white; padding:10px;">
    <?= $flash['message'] ?>
  </div>
<?php endif; ?>

<?php if ($data['type'] === 'admin'): ?>

  <div style="margin-bottom:20px; display:flex; flex-wrap:wrap; gap:10px;">

    <a href="/users" style="padding:8px 12px; background:#333; color:#fff; border-radius:5px; text-decoration:none;">
      👤 Users
    </a>

    <a href="/students" style="padding:8px 12px; background:#333; color:#fff; border-radius:5px; text-decoration:none;">
      🎓 Students
    </a>

    <a href="/courses" style="padding:8px 12px; background:#333; color:#fff; border-radius:5px; text-decoration:none;">
      📚 Courses
    </a>

    <a href="/enrollments" style="padding:8px 12px; background:#333; color:#fff; border-radius:5px; text-decoration:none;">
      🧾 Enrollments
    </a>

    <a href="/permissions/roles" style="padding:8px 12px; background:#333; color:#fff; border-radius:5px; text-decoration:none;">
      🔐 Roles
    </a>

    <a href="/permissions/users" style="padding:8px 12px; background:#333; color:#fff; border-radius:5px; text-decoration:none;">
      👥 User Permissions
    </a>

    <a href="/audit" style="padding:8px 12px; background:#333; color:#fff; border-radius:5px; text-decoration:none;">
      📜 Audit Logs
    </a>

  </div>

  <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:15px;">

    <div style="padding:20px; background:#eee;">
      <h3>👥 Users</h3>
      <p><?= $data['users'] ?></p>
    </div>

    <div style="padding:20px; background:#eee;">
      <h3>🎓 Students</h3>
      <p><?= $data['students'] ?></p>
    </div>

    <div style="padding:20px; background:#eee;">
      <h3>👨‍🏫 Teachers</h3>
      <p><?= $data['teachers'] ?></p>
    </div>

    <div style="padding:20px; background:#eee;">
      <h3>📚 Courses</h3>
      <p><?= $data['courses'] ?></p>
    </div>

    <div style="padding:20px; background:#eee;">
      <h3>🧾 Active Enrollments</h3>
      <p><?= $data['enrollments'] ?></p>
    </div>

    <div style="padding:20px; background:#eee;">
      <h3>🔐 Permissions</h3>
      <p><?= $data['total_permissions'] ?></p>
    </div>

    <div style="padding:20px; background:#eee;">
      <h3>📜 Audit Logs</h3>
      <p><?= $data['audit_logs'] ?></p>
    </div>

  </div>

<?php elseif ($data['type'] === 'student'): ?>

  <h2>📚 My Learning</h2>

  <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:15px;">

    <?php foreach ($data['myEnrollments'] as $enr): ?>

      <div style="padding:15px; background:#f5f5f5; border-radius:8px; border:1px solid #ddd;">

        <h3 style="margin:0 0 10px 0;">
          <?= htmlspecialchars($enr['course_name']) ?>
        </h3>

        <p style="margin:5px 0;">
          <strong>Status:</strong>
          <?= ucfirst($enr['enrollment_status']) ?>
        </p>

        <p style="margin:5px 0;">
          <strong>Enrolled:</strong>
          <?= $enr['enrolled_date'] ?? 'N/A' ?>
        </p>

        <?php if (!empty($enr['duration_weeks'])): ?>
          <p style="margin:5px 0;">
            <strong>Duration:</strong>
            <?= $enr['duration_weeks'] ?> weeks
          </p>
        <?php endif; ?>

      </div>

    <?php endforeach; ?>

  </div>

<?php endif; ?>