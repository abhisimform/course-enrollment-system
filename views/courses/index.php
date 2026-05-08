<h3>Courses</h3>

<?php if (Rbac::has('course.create')): ?>
  <a href="/courses/create">Add Course</a>
<?php endif; ?>

<?php
$courseColumns = [
  ['data' => 'id'],
  ['data' => 'course_name'],
  ['data' => 'instructor_name'],
  ['data' => 'duration_weeks'],
  ['data' => 'max_seats'],
  ['data' => 'filled_seats'],
  ['data' => 'available_seats'],
  ['data' => 'seat_usage', 'orderable' => false, 'searchable' => false],
  ['data' => 'status_label', 'orderable' => false]
];

if (Rbac::has('course.edit') || Rbac::has('course.delete') || Rbac::has('course.restore')) {
  $courseColumns[] = ['data' => 'manage', 'orderable' => false, 'searchable' => false];
}

if (Rbac::has('enrollment.create') && !Rbac::isAdmin()) {
  $courseColumns[] = ['data' => 'enrollment_action', 'orderable' => false, 'searchable' => false];
}
?>

<div class="dt-container">
  <table id="coursesTable" class="display">
    <thead>
      <tr>
        <th>ID</th>
        <th>Course Name</th>
        <th>Instructor</th>
        <th>Duration</th>
        <th>Total Seats</th>
        <th>Filled Seats</th>
        <th>Available Seats</th>
        <th>Seat Usage</th>
        <th>Status</th>
        <?php if (Rbac::has('course.edit') || Rbac::has('course.delete') || Rbac::has('course.restore')): ?>
          <th>Manage</th>
        <?php endif; ?>
        <?php if (Rbac::has('enrollment.create') && !Rbac::isAdmin()): ?>
          <th>Enrollment</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  $(document).ready(function() {
    $('#coursesTable').DataTable({
      processing: true,
      serverSide: true,
      scrollY: 420,
      scrollX: true,
      scrollCollapse: true,
      search: {
        search: <?= json_encode($filters['search'] ?? '') ?>
      },
      ajax: '/courses/ajax?<?= http_build_query($_GET) ?>',
      columns: <?= json_encode($courseColumns) ?>,
      order: [
        [0, 'asc']
      ],
      pageLength: <?= (int)($filters['limit'] ?? 10) ?>,
      lengthMenu: [5, 10, 25, 50, 100]
    });
  });
</script>