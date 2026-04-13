<h3>Courses</h3>

<?php if (hasPermission('create_course')): ?>
  <a href="/courses/create">Add Course</a>
<?php endif; ?>

<!-- Search Form -->
<form method="GET" action="/courses/view">
  <input type="text" name="search" placeholder="Search by course name" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" />

  <!-- Filter by Status -->
  <select name="status">
    <option value="">All Status</option>
    <option value="1" <?= isset($_GET['status']) && $_GET['status'] == '1' ? 'selected' : '' ?>>Active</option>
    <option value="0" <?= isset($_GET['status']) && $_GET['status'] == '0' ? 'selected' : '' ?>>Inactive</option>
  </select>

  <!-- Filter by Instructor (optional, assuming you have an array of instructors) -->
  <select name="instructor">
    <option value="">All Instructors</option>
    <?php foreach ($instructors as $instructor): ?>
      <option value="<?= $instructor['id'] ?>" <?= isset($_GET['instructor']) && $_GET['instructor'] == $instructor['id'] ? 'selected' : '' ?>>
        <?= $instructor['name'] ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Apply Filters</button>
</form>

<!-- Courses Table -->
<table border="1" cellpadding="10">
  <tr>
    <th>ID</th>
    <th>Course Name</th>
    <th>Instructor</th>
    <th>Status</th>
    <th>Actions</th>
  </tr>

  <?php foreach ($courses as $course): ?>
    <tr>
      <td><?= $course['id'] ?></td>
      <td><?= htmlspecialchars($course['course_name']) ?></td>
      <td><?= htmlspecialchars($course['instructor_name']) ?></td> <!-- Assuming the instructor's name is part of the course data -->
      <td><?= $course['status'] ? 'Active' : 'Inactive' ?></td>
      <td>
        <?php if (hasPermission('view_course')): ?>
          <a href="/courses/view/<?= $course['id'] ?>">View</a>
        <?php endif; ?>
        
        <?php if (hasPermission('edit_course')): ?>
          <a href="/courses/edit/<?= $course['id'] ?>">Edit</a>
        <?php endif; ?>

        <?php if (hasPermission('delete_course')): ?>
          <a href="/courses/delete/<?= $course['id'] ?>" onclick="return confirm('Delete this course?')">Delete</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<!-- Pagination -->
<div class="pagination">
  <?php if ($currentPage > 1): ?>
    <a href="/courses/view?page=<?= $currentPage - 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&instructor=<?= htmlspecialchars($_GET['instructor'] ?? '') ?>">Previous</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="/courses/view?page=<?= $i ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&instructor=<?= htmlspecialchars($_GET['instructor'] ?? '') ?>" <?= $i == $currentPage ? 'class="active"' : '' ?>>
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($currentPage < $totalPages): ?>
    <a href="/courses/view?page=<?= $currentPage + 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&instructor=<?= htmlspecialchars($_GET['instructor'] ?? '') ?>">Next</a>
  <?php endif; ?>
</div>