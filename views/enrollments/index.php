<h3>My Enrollments</h3>

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
    <tbody></tbody>
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
      ajax: '/enrollments/getEnrollmentData?<?= http_build_query($_GET) ?>',
      columns: <?= json_encode($enrollmentColumns) ?>,
      order: [
        [0, 'desc']
      ],
      pageLength: <?= (int)($filters['limit'] ?? 10) ?>,
      lengthMenu: [5, 10, 25, 50, 100]
    });
  });
</script>