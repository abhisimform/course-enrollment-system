<h3>Courses</h3>

<?php if (Rbac::has('course.create')): ?>
  <a href="/courses/create">Add Course</a>
<?php endif; ?>

<?php
function buildQuery($overrides = [])
{
  $query = array_merge($GLOBALS['filters'] ?? [], $overrides);
  return http_build_query($query);
}

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

<hr>

<form method="GET" action="/courses">
  <input
    type="text"
    name="search"
    placeholder="Search by course name"
    value="<?= htmlspecialchars($filters['search'] ?? '') ?>" />

  <select name="status">
    <option value="">All Status</option>
    <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
    <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
  </select>

  <select name="instructor_id">
    <option value="">All Instructors</option>
    <?php foreach ($instructors as $instructor): ?>
      <option
        value="<?= $instructor['id'] ?>"
        <?= ($filters['instructor_id'] ?? '') == $instructor['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($instructor['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <select name="deleted">
    <option value="">Active Only</option>
    <option value="with" <?= ($filters['deleted'] ?? '') === 'with' ? 'selected' : '' ?>>With Deleted</option>
    <option value="only" <?= ($filters['deleted'] ?? '') === 'only' ? 'selected' : '' ?>>Only Deleted</option>
  </select>

  <select name="limit">
    <?php foreach ([5, 10, 25, 50, 100] as $limit): ?>
      <option value="<?= $limit ?>" <?= $currentLimit == $limit ? 'selected' : '' ?>>
        <?= $limit ?>
      </option>
    <?php endforeach; ?>
  </select>

  <input type="hidden" name="page" value="1" />

  <button type="submit">Apply Filters</button>

  <a href="/courses" style="margin-left:10px;">Clear</a>
</form>

<!-- 📊 Table -->
<table border="1" cellpadding="10">
  <tr>
    <th>
      <a href="/courses?<?= sortLink('id') ?>">
        ID<?= sortIcon('id') ?>
      </a>
    </th>

    <th>
      <a href="/courses?<?= sortLink('course_name') ?>">
        Course Name<?= sortIcon('course_name') ?>
      </a>
    </th>

    <th>
      <a href="/courses?<?= sortLink('instructor_name') ?>">
        Instructor<?= sortIcon('instructor_name') ?>
      </a>
    </th>

    <th>
      <a href="/courses?<?= sortLink('duration_weeks') ?>">
        Duration <?= sortIcon('duration_weeks') ?>
      </a>
    </th>

    <th>
      <a href="/courses?<?= sortLink('max_seats') ?>">
        Total Seats <?= sortIcon('max_seats') ?>
      </a>
    </th>

    <th>Filled Seats</th>
    <th>Available Seats</th>
    <th>Seat Usage</th>

    <th>
      <a href="/courses?<?= sortLink('status') ?>">
        Status<?= sortIcon('status') ?>
      </a>
    </th>

    <?php if (Rbac::has('course.edit') || Rbac::has('course.delete')): ?>
      <th>Manage</th>
    <?php endif; ?>

    <?php if (Rbac::has('enrollment.create')): ?>
      <th>Enrollment</th>
    <?php endif; ?>
  </tr>

  <?php if (!empty($courses)): ?>
    <?php foreach ($courses as $course): ?>

      <?php
      $filled = (int)$course['filled_seats'];
      $total = (int)$course['max_seats'];

      $percentage = $total > 0 ? ($filled / $total) * 100 : 0;

      // Decide color
      if ($percentage >= 90) {
        $color = 'green';
      } elseif ($percentage >= 50) {
        $color = 'orange';
      } else {
        $color = 'red';
      }
      ?>

      <tr>
        <td><?= $course['id'] ?></td>
        <td><?= htmlspecialchars($course['course_name']) ?></td>
        <td><?= htmlspecialchars($course['instructor_name']) ?></td>
        <td><?= htmlspecialchars($course['duration_weeks']) ?></td>
        <td><?= htmlspecialchars($course['max_seats']) ?></td>
        <td><?= $course['filled_seats'] ?></td>
        <td><?= $course['available_seats'] ?></td>
        <td style="width:150px;">
          <div style="background:#eee; border-radius:5px; overflow:hidden;">
            <div style="width:<?= $percentage ?>%; background:<?= $color ?>; color:white; text-align:center; font-size:12px; ">
              <?= round($percentage) ?>%
            </div>
          </div>
        </td>
        <td>
          <?php if ($course['deleted_at']): ?>
            <span>Deleted</span>
          <?php else: ?>
            <?= $course['status'] ? 'Active' : 'Inactive' ?>
          <?php endif; ?>
        </td>

        <!-- 👨‍💼 ADMIN / TEACHER ACTIONS -->
        <?php if (Rbac::has('course.edit') || Rbac::has('course.delete')): ?>
          <td>

            <?php if ($course['deleted_at']): ?>

              <?php if (Rbac::has('course.restore')): ?>
                <a href="/courses/restore/<?= $course['id'] ?>">Restore</a>
              <?php endif; ?>

              <?php if (Rbac::has('course.delete')): ?>
                <a style="color:red;cursor:pointer;"
                  href="/courses/force-delete/<?= $course['id'] ?>"
                  onclick="return confirm('Delete permanently?')">
                  Delete
                </a>
              <?php endif; ?>

            <?php else: ?>

              <?php if (Rbac::has('course.edit')): ?>
                <a href="/courses/edit/<?= $course['id'] ?>">Edit</a>
              <?php endif; ?>

              <?php if (Rbac::has('course.delete')): ?>
                <a href="/courses/delete/<?= $course['id'] ?>"
                  onclick="return confirm('Delete this course?')">
                  Delete
                </a>
              <?php endif; ?>

            <?php endif; ?>

          </td>
        <?php endif; ?>


        <!-- 🎓 STUDENT ENROLLMENT -->
        <?php if (Rbac::has('enrollment.create')): ?>
          <td>

            <?php if (!empty($course['is_enrolled'])): ?>

              <form method="POST" action="/enrollments/cancel">
                <input type="hidden" name="id" value="<?= $course['en_id'] ?>">
                <button style="background:red;color:white;border:none;padding:5px 10px;cursor:pointer;">
                  ❌ Cancel
                </button>
              </form>

            <?php else: ?>

              <form method="POST" action="/enrollments/enroll">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <button style="padding:5px 10px;cursor:pointer;">
                  ➕ Enroll
                </button>
              </form>

            <?php endif; ?>

          </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="8">No courses found</td>
    </tr>
  <?php endif; ?>
</table>

<hr>

<!-- 📄 Pagination -->
<div class="pagination">

  <?php if ($pagination['currentPage'] > 1): ?>
    <a href="/courses?<?= buildQuery(['page' => $pagination['currentPage'] - 1]) ?>">
      Previous
    </a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
    <a
      href="/courses?<?= buildQuery(['page' => $i]) ?>"
      <?= $i == $pagination['currentPage'] ? 'style="font-weight:bold;"' : '' ?>>
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
    <a href="/courses?<?= buildQuery(['page' => $pagination['currentPage'] + 1]) ?>">
      Next
    </a>
  <?php endif; ?>

</div>