<?php
function decodeAuditData($payload)
{
  if ($payload === null || $payload === '') {
    return [];
  }

  $decoded = json_decode($payload, true);

  return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
}

function auditValueLabel($value)
{
  if ($value === null || $value === '') {
    return '<span class="status-pill">Empty</span>';
  }

  if (is_bool($value)) {
    return $value ? 'true' : 'false';
  }

  if (is_array($value)) {
    return '<code>' . e(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</code>';
  }

  return e((string)$value);
}

function auditFieldLabel($field)
{
  return ucwords(str_replace('_', ' ', (string)$field));
}

$oldData = decodeAuditData($log['old_data'] ?? '');
$newData = decodeAuditData($log['new_data'] ?? '');
$allFields = array_values(array_unique(array_merge(array_keys($oldData), array_keys($newData))));
sort($allFields);

$changedRows = [];

foreach ($allFields as $field) {
  $oldValue = $oldData[$field] ?? null;
  $newValue = $newData[$field] ?? null;
  $isChanged = json_encode($oldValue) !== json_encode($newValue);

  $changedRows[] = [
    'field' => $field,
    'old' => $oldValue,
    'new' => $newValue,
    'changed' => $isChanged
  ];
}

$changedCount = count(array_filter($changedRows, static fn($row) => $row['changed']));
$actionClass = $log['action_type'] === 'INSERT' ? 'success' : ($log['action_type'] === 'UPDATE' ? 'warning' : 'danger');
?>

<style>
  .audit-meta-grid,
  .audit-diff-grid {
    display: grid;
    gap: 16px;
  }

  .audit-meta-grid {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    margin-bottom: 16px;
  }

  .audit-meta-item {
    padding: 16px;
  }

  .audit-meta-item span {
    display: block;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 6px;
  }

  .audit-diff-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .audit-panel {
    padding: 16px;
  }

  .audit-panel h2 {
    margin: 0 0 12px;
    font-size: 18px;
  }

  .audit-json-table {
    width: 100%;
    border-collapse: collapse;
  }

  .audit-json-table td {
    padding: 10px 12px;
    vertical-align: top;
  }

  .audit-json-table td:first-child {
    width: 34%;
    font-weight: 700;
    color: var(--muted);
  }

  .audit-json-table tr.is-changed td {
    background: #fff7ed;
  }

  .audit-json-table tr.is-same td {
    background: #f8fafc;
  }

  .audit-json-table code {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 12px;
    white-space: pre-wrap;
    word-break: break-word;
  }

  @media (max-width: 860px) {
    .audit-diff-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="page-header">
  <div>
    <h1 class="page-title">Audit Log #<?= (int)$log['id'] ?></h1>
    <p class="page-subtitle">Side-by-side change view for this audit record</p>
  </div>

  <div class="actions">
    <a class="button secondary" href="/audit">Back to Audit Logs</a>
  </div>
</div>

<div class="audit-meta-grid">
  <div class="content-card audit-meta-item">
    <span>Table</span>
    <strong><?= e(ucfirst($log['table_name'])) ?></strong>
  </div>
  <div class="content-card audit-meta-item">
    <span>Action</span>
    <strong><span class="status-pill <?= $actionClass ?>"><?= e($log['action_type']) ?></span></strong>
  </div>
  <div class="content-card audit-meta-item">
    <span>Record ID</span>
    <strong>#<?= (int)$log['record_id'] ?></strong>
  </div>
  <div class="content-card audit-meta-item">
    <span>Changed At</span>
    <strong><?= e(date('d M Y H:i', strtotime($log['changed_at']))) ?></strong>
  </div>
  <div class="content-card audit-meta-item">
    <span>Changed Fields</span>
    <strong><?= (int)$changedCount ?></strong>
  </div>
</div>

<div class="audit-diff-grid">
  <div class="content-card audit-panel">
    <h2>Old Data</h2>
    <?php if (empty($changedRows)): ?>
      <p class="page-subtitle">No previous data available.</p>
    <?php else: ?>
      <table class="audit-json-table">
        <tbody>
          <?php foreach ($changedRows as $row): ?>
            <tr class="<?= $row['changed'] ? 'is-changed' : 'is-same' ?>">
              <td><?= e(auditFieldLabel($row['field'])) ?></td>
              <td><?= auditValueLabel($row['old']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="content-card audit-panel">
    <h2>New Data</h2>
    <?php if (empty($changedRows)): ?>
      <p class="page-subtitle">No updated data available.</p>
    <?php else: ?>
      <table class="audit-json-table">
        <tbody>
          <?php foreach ($changedRows as $row): ?>
            <tr class="<?= $row['changed'] ? 'is-changed' : 'is-same' ?>">
              <td><?= e(auditFieldLabel($row['field'])) ?></td>
              <td><?= auditValueLabel($row['new']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
