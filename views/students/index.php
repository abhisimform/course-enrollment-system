<h3>Students</h3>

<form method="GET" style="margin-bottom:10px;">
  <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
  <label>
    <input type="checkbox" name="deleted" value="1" <?= isset($_GET['deleted']) ? 'checked' : '' ?>>
    Show Deleted
  </label>
  <button type="submit">Filter</button>
</form>

<?php if (Rbac::has('student.create')): ?>
  <a href="/students/create">Add Student</a>
  <a href="/students/bulkupload" style="margin-left:10px;">Bulk Upload</a>
<?php endif; ?>

<div class="dt-container">
  <table id="studentsTable" class="display">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Actions</th>
      </tr>
    </thead>
  </table>
</div>

<script>
  $(document).ready(function() {
    $('#studentsTable').DataTable({
      processing: true,
      serverSide: true,
      scrollY: 420,
      scrollX: true,
      scrollCollapse: true,
      search: {
        search: <?= json_encode($_GET['search'] ?? '') ?>
      },
      ajax: '/students/ajax?<?= http_build_query($_GET) ?>',
      columns: [{
          data: 'id'
        },
        {
          data: 'name'
        },
        {
          data: 'email'
        },
        {
          data: 'actions',
          orderable: false,
          searchable: false
        }
      ],
      order: [
        [0, 'desc']
      ],
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100]
    });
  });
</script>
