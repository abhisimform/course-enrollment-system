<h3>Audit Logs</h3>

<div class="dt-container">
  <table id="myAuditTable" class="display">
    <thead>
      <tr>
        <th>Id</th>
        <th>Table</th>
        <th>Action</th>
        <th>Record ID</th>
        <th>Old Data</th>
        <th>New Data</th>
        <th>Created</th>
      </tr>
    </thead>

    <tbody></tbody>
  </table>
</div>

<script>
  $(document).ready(function() {
    $('#myAuditTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: '/audit/ajax',

      columns: [{
          data: 'id'
        },
        {
          data: 'table_name'
        },
        {
          data: 'action_type'
        },
        {
          data: 'record_id'
        },
        {
          data: 'old_data'
        },
        {
          data: 'new_data'
        },
        {
          data: 'changed_at'
        }
      ],

      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      scrollY: 420,
      scrollCollapse: true,

      order: [
        [6, 'desc']
      ],

      columnDefs: [{
        orderable: false,
        targets: [4, 5]
      }]
    });
  });
</script>
