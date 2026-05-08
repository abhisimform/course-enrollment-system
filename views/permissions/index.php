<h2>Permissions</h2>

<div style="margin-bottom: 15px;">
  <a href="/permissions/create">➕ Add Permission</a> |

  <a href="/permissions/roles">⚙️ Manage Role Permissions</a> |
  <a href="/permissions/users">👤 Manage User Permissions</a>
</div>

<div class="dt-container">
  <table id="permissionsTable" class="display">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  $(document).ready(function() {
    $('#permissionsTable').DataTable({
      processing: true,
      serverSide: true,
      scrollY: 420,
      scrollX: true,
      scrollCollapse: true,
      search: {
        search: <?= json_encode($_GET['q'] ?? '') ?>
      },
      ajax: '/permissions/ajax?<?= http_build_query($_GET) ?>',
      columns: [{
          data: 'id'
        },
        {
          data: 'name'
        },
        {
          data: 'status',
          orderable: false
        },
        {
          data: 'actions',
          orderable: false,
          searchable: false
        }
      ],
      order: [
        [1, 'asc']
      ],
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100]
    });
  });
</script>
