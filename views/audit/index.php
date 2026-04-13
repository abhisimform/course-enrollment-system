<h3>Audit Logs</h3>

<!-- Search Form -->
<form method="GET" action="/audit">
  <input type="text" name="search" placeholder="Search Logs" value="<?= htmlspecialchars($search) ?>">
  <button type="submit">Search</button>
</form>

<!-- Sorting Links -->
<a href="?order_by=table_name&sort_order=ASC">Sort by Table (Asc)</a>
<a href="?order_by=table_name&sort_order=DESC">Sort by Table (Desc)</a>
<a href="?order_by=changed_at&sort_order=ASC">Sort by Date (Asc)</a>
<a href="?order_by=changed_at&sort_order=DESC">Sort by Date (Desc)</a>

<form method="GET" action="/audit" style="margin-bottom: 15px;">
  <select name="table">
    <option value="">All Tables</option>
    <?php foreach ($tables as $table): ?>
      <option value="<?= $table ?>" <?= isset($_GET['table']) && $_GET['table'] == $table ? 'selected' : '' ?>>
        <?= ucfirst($table) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <select name="action_type">
    <option value="">All Actions</option>
    <?php
    $actionTypes = ['INSERT', 'UPDATE', 'DELETE'];
    foreach ($actionTypes as $action): ?>
      <option value="<?= $action ?>" <?= isset($_GET['action_type']) && $_GET['action_type'] == $action ? 'selected' : '' ?>>
        <?= $action ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Filter</button>
</form>

<table border="1" cellpadding="10" width="100%">
  <tr>
    <th>Table</th>
    <th>Action</th>
    <th>Record ID</th>
    <th>Old Data</th>
    <th>New Data</th>
    <th>Created</th>
  </tr>

  <?php foreach ($logs as $log): ?>
    <tr>
      <td><?= ucfirst($log['table_name']) ?></td>

      <td>
        <span style="font-weight:bold; color:
          <?= $log['action_type'] == 'INSERT' ? 'green' : ($log['action_type'] == 'UPDATE' ? 'orange' : 'red') ?>">
          <?= $log['action_type'] ?>
        </span>
      </td>

      <td>#<?= $log['record_id'] ?></td>

      <td><?= formatLogData($log['old_data']) ?></td>
      <td><?= formatLogData($log['new_data']) ?></td>

      <td><?= date('d M Y H:i', strtotime($log['changed_at'])) ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<!-- Pagination Links -->
<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&order_by=<?= $orderBy ?>&sort_order=<?= $sortOrder ?>">Previous</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&order_by=<?= $orderBy ?>&sort_order=<?= $sortOrder ?>"><?= $i ?></a>
  <?php endfor; ?>

  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&order_by=<?= $orderBy ?>&sort_order=<?= $sortOrder ?>">Next</a>
  <?php endif; ?>
</div>

<?php
function formatLogData($data)
{
  if (empty($data)) {
    return '<span style="color:gray;">-</span>';
  }

  $items = explode(',', $data);
  $html = '<ul style="margin:0;padding-left:15px;">';

  foreach ($items as $item) {
    $html .= '<li>' . htmlspecialchars(trim($item)) . '</li>';
  }

  $html .= '</ul>';

  return $html;
}
?>