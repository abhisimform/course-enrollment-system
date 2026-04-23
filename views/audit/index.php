<h3>Audit Logs</h3>

<form method="GET" action="/audit">
  <input type="text" name="search" placeholder="Search Logs" value="<?= htmlspecialchars($search) ?>">
  <button type="submit">Search</button>
</form>

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
    <th>Id</th>
    <th>Table</th>
    <th>Action</th>
    <th>Record ID</th>
    <th>Old Data</th>
    <th>New Data</th>
    <th>Created</th>
  </tr>

  <?php foreach ($logs as $log): ?>
    <tr>
      <td><?= ucfirst($log['id']) ?></td>
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

<div class="pagination">

  <?php
  function buildQuery($overrides = [])
  {
    $query = array_merge($_GET, $overrides);
    return http_build_query($query);
  }
  ?>

  <?php if ($page > 1): ?>
    <a href="?<?= buildQuery(['page' => $page - 1]) ?>">Prev</a>
  <?php else: ?>
    <span style="color:gray;">Prev</span>
  <?php endif; ?>


  <?php if ($totalPages <= 6): ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?<?= buildQuery(['page' => $i]) ?>"
        <?= $i == $page ? 'style="font-weight:bold;"' : '' ?>>
        <?= $i ?>
      </a>
    <?php endfor; ?>

  <?php else: ?>

    <a href="?<?= buildQuery(['page' => 1]) ?>"
      <?= $page == 1 ? 'style="font-weight:bold;"' : '' ?>>
      1
    </a>

    <?php if ($page <= 3): ?>

      <?php for ($i = 2; $i <= 3; $i++): ?>
        <a href="?<?= buildQuery(['page' => $i]) ?>"
          <?= $i == $page ? 'style="font-weight:bold;"' : '' ?>>
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <span>...</span>

    <?php elseif ($page >= $totalPages - 2): ?>

      <span>...</span>

      <?php for ($i = $totalPages - 2; $i <= $totalPages - 1; $i++): ?>
        <a href="?<?= buildQuery(['page' => $i]) ?>"
          <?= $i == $page ? 'style="font-weight:bold;"' : '' ?>>
          <?= $i ?>
        </a>
      <?php endfor; ?>

    <?php else: ?>

      <span>...</span>

      <?php for ($i = $page - 1; $i <= $page + 1; $i++): ?>
        <a href="?<?= buildQuery(['page' => $i]) ?>"
          <?= $i == $page ? 'style="font-weight:bold;"' : '' ?>>
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <span>...</span>

    <?php endif; ?>

    <a href="?<?= buildQuery(['page' => $totalPages]) ?>"
      <?= $page == $totalPages ? 'style="font-weight:bold;"' : '' ?>>
      <?= $totalPages ?>
    </a>

  <?php endif; ?>


  <?php if ($page < $totalPages): ?>
    <a href="?<?= buildQuery(['page' => $page + 1]) ?>">Next</a>
  <?php else: ?>
    <span style="color:gray;">Next</span>
  <?php endif; ?>

</div>

<form method="GET" style="margin-top:10px;">

  <?php foreach ($_GET as $key => $value): ?>
    <?php if ($key !== 'page'): ?>
      <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
    <?php endif; ?>
  <?php endforeach; ?>

  Go to page:
  <input
    type="number"
    name="page"
    min="1"
    max="<?= $totalPages ?>"
    value="<?= $page ?>"
    style="width:70px;">

  <button type="submit">Go</button>
</form>

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