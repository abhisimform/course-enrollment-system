<?php if ($role === 'admin'): ?>
  <h3>Enrollments</h3>

  <form method="GET" action="/enrollments">
    <input type="text" name="search" placeholder="Search student or course"
      value="<?= htmlspecialchars($filters['search'] ?? '') ?>">

    <select name="status">
      <option value="">All Status</option>
      <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>

    <select name="limit">
      <?php foreach ([5, 10, 25, 50] as $l): ?>
        <option value="<?= $l ?>" <?= ($filters['limit'] ?? 10) == $l ? 'selected' : '' ?>>
          <?= $l ?>
        </option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Filter</button>
  </form>
<?php else: ?>
  <h3>My Enrollments</h3>
<?php endif; ?>

<?php
$enrollmentColumns = $role === 'admin'
  ? [
    ['data' => 'id'],
    ['data' => 'student_name'],
    ['data' => 'course_name'],
    ['data' => 'status_label', 'orderable' => false],
    ['data' => 'enrolled_date'],
    ['data' => 'actions', 'orderable' => false, 'searchable' => false]
  ]
  : [
    ['data' => 'id'],
    ['data' => 'course_name'],
    ['data' => 'status_label', 'orderable' => false],
    ['data' => 'enrolled_date'],
    ['data' => 'actions', 'orderable' => false, 'searchable' => false]
  ];
?>

<div class="dt-container">
  <table id="enrollmentsTable" class="display">
    <thead>
      <tr>
        <th>ID</th>
        <?php if ($role === 'admin'): ?>
          <th>Student</th>
        <?php endif; ?>
        <th>Course</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
  </table>
</div>

<script>
  $(document).ready(function() {
    $('#enrollmentsTable').DataTable({
      processing: true,
      serverSide: true,
      scrollY: 420,
      scrollX: true,
      scrollCollapse: true,
      search: {
        search: <?= json_encode($filters['search'] ?? '') ?>
      },
      ajax: '/enrollments/ajax?<?= http_build_query($_GET) ?>',
      columns: <?= json_encode($enrollmentColumns) ?>,
      order: [
        [0, 'desc']
      ],
      pageLength: <?= (int)($filters['limit'] ?? 10) ?>,
      lengthMenu: [5, 10, 25, 50, 100]
    });
  });
</script>
