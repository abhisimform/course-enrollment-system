<h3>Teachers</h3>

<form method="GET" style="margin-bottom:10px;">
  <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
  <label>
    <input type="checkbox" name="deleted" value="1" <?= isset($_GET['deleted']) ? 'checked' : '' ?>>
    Show Deleted
  </label>
  <button type="submit">Filter</button>
</form>

<?php if (hasPermission('create_teacher') && !isset($_GET['deleted'])): ?>
  <a href="/teachers/create">Add Teacher</a>
<?php endif; ?>

<div class="dt-container">
  <table id="teachersTable" class="display">
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
    $('#teachersTable').DataTable({
      processing: true,
      serverSide: true,
      scrollY: 420,
      scrollX: true,
      scrollCollapse: true,
      search: {
        search: <?= json_encode($_GET['search'] ?? '') ?>
      },
      ajax: '/teachers/ajax?<?= htmlspecialchars(http_build_query($_GET), ENT_QUOTES) ?>',
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
