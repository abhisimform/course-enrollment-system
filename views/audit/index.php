<h3>Audit Logs</h3>

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