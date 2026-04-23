<?php if ($role === 'admin'): ?>

  <h3>Enrollments</h3>

  <?php

  // Helper for sorting links
  function sortLink($column)
  {
    $currentSort = $GLOBALS['filters']['sortBy'] ?? '';
    $currentOrder = $GLOBALS['filters']['order'] ?? 'DESC';

    $newOrder = 'ASC';

    if ($currentSort === $column) {
      $newOrder = ($currentOrder === 'ASC') ? 'DESC' : 'ASC';
    }

    return buildQuery([
      'sortBy' => $column,
      'order' => $newOrder,
      'page' => 1
    ]);
  }

  // Optional: show arrow icon
  function sortIcon($column)
  {
    if (($GLOBALS['filters']['sortBy'] ?? '') === $column) {
      return ($GLOBALS['filters']['order'] ?? '') === 'ASC' ? ' ↑' : ' ↓';
    }
    return '';
  }

  $currentLimit = (int)($filters['limit'] ?? 10);
  ?>

  <form method="GET" action="/enrollments">

    <input type="text" name="search" placeholder="Search student or course"
      value="<?= htmlspecialchars($filters['search'] ?? '') ?>">

    <select name="status">
      <option value="">All Status</option>
      <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>

    <select name="limit">
      <?php foreach ([5, 10, 25, 50] as $l): ?>
        <option value="<?= $l ?>" <?= ($filters['limit'] ?? 10) == $l ? 'selected' : '' ?>>
          <?= $l ?>
        </option>
      <?php endforeach; ?>
    </select>

    <input type="hidden" name="page" value="1">

    <button type="submit">Filter</button>
  </form>

  <hr>

  <table border="1" cellpadding="10" width="100%">
    <tr>
      <th>ID</th>
      <th>Student</th>
      <th>Course</th>
      <th>Status</th>
      <th>Date</th>
      <th>Actions</th>
    </tr>

    <?php foreach ($enrollments as $e): ?>
      <tr>
        <td><?= $e['id'] ?></td>
        <td><?= htmlspecialchars($e['student_name']) ?></td>
        <td><?= htmlspecialchars($e['course_name']) ?></td>
        <td><?= ucfirst($e['status']) ?></td>
        <td><?= $e['enrolled_date'] ?></td>
        <td>
          <?php if (hasPermission('enrollment.update') && $e['status'] === 'active'): ?>
            <form method="POST" action="/enrollments/cancel">
              <input type="hidden" name="id" value="<?= $e['id'] ?>">
              <button>Cancel</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <hr>

  <!-- pagination -->
  <div class="pagination">

    <?php
    function buildQuery($overrides = [])
    {
      $query = array_merge($_GET, $overrides);
      return http_build_query($query);
    }
    ?>

    <?php if ($pagination['page'] > 1): ?>
      <a href="?<?= buildQuery(['page' => $pagination['page'] - 1]) ?>">Prev</a>
    <?php else: ?>
      <span style="color:gray;">Prev</span>
    <?php endif; ?>


    <?php if ($pagination['totalPages'] <= 6): ?>

      <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
        <a href="?<?= buildQuery(['page' => $i]) ?>"
          <?= $i == $pagination['page'] ? 'style="font-weight:bold;"' : '' ?>>
          <?= $i ?>
        </a>
      <?php endfor; ?>

    <?php else: ?>

      <a href="?<?= buildQuery(['page' => 1]) ?>"
        <?= $pagination['page'] == 1 ? 'style="font-weight:bold;"' : '' ?>>
        1
      </a>

      <?php if ($pagination['page'] <= 3): ?>

        <?php for ($i = 2; $i <= 3; $i++): ?>
          <a href="?<?= buildQuery(['page' => $i]) ?>"
            <?= $i == $pagination['page'] ? 'style="font-weight:bold;"' : '' ?>>
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <span>...</span>

      <?php elseif ($pagination['page'] >= $pagination['totalPages'] - 2): ?>

        <span>...</span>

        <?php for ($i = $pagination['totalPages'] - 2; $i <= $pagination['totalPages'] - 1; $i++): ?>
          <a href="?<?= buildQuery(['page' => $i]) ?>"
            <?= $i == $pagination['page'] ? 'style="font-weight:bold;"' : '' ?>>
            <?= $i ?>
          </a>
        <?php endfor; ?>

      <?php else: ?>

        <span>...</span>

        <?php for ($i = $pagination['page'] - 1; $i <= $pagination['page'] + 1; $i++): ?>
          <a href="?<?= buildQuery(['page' => $i]) ?>"
            <?= $i == $pagination['page'] ? 'style="font-weight:bold;"' : '' ?>>
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <span>...</span>

      <?php endif; ?>

      <a href="?<?= buildQuery(['page' => $pagination['totalPages']]) ?>"
        <?= $pagination['page'] == $pagination['totalPages'] ? 'style="font-weight:bold;"' : '' ?>>
        <?= $pagination['totalPages'] ?>
      </a>

    <?php endif; ?>


    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
      <a href="?<?= buildQuery(['page' => $pagination['page'] + 1]) ?>">Next</a>
    <?php else: ?>
      <span style="color:gray;">Next</span>
    <?php endif; ?>

  </div>

<?php endif; ?>

<?php if ($role !== 'admin'): ?>

  <h3>🎓 My Enrollments</h3>

  <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap:15px;">

    <?php foreach ($enrollments as $e): ?>

      <div style="padding:15px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9;">

        <h4><?= htmlspecialchars($e['course_name']) ?></h4>

        <p>📅 <?= $e['enrolled_date'] ?></p>

        <form method="POST" action="/enrollments/cancel">
          <input type="hidden" name="id" value="<?= $e['id'] ?>">
          <button style="background:red;color:white;border:none;padding:5px 10px;cursor:pointer;">
            Cancel Enrollment
          </button>
        </form>

      </div>

    <?php endforeach; ?>

  </div>

<?php endif; ?>

<hr>

<div class="pagination">
  <?php if ($pagination['page'] > 1): ?>
    <a href="?<?= buildQuery(['page' => $pagination['page'] - 1]) ?>">Prev</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
    <a href="?<?= buildQuery(['page' => $i]) ?>"
      <?= $i == $pagination['page'] ? 'style="font-weight:bold;"' : '' ?>>
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($pagination['page'] < $pagination['totalPages']): ?>
    <a href="?<?= buildQuery(['page' => $pagination['page'] + 1]) ?>">Next</a>
  <?php endif; ?>

</div>