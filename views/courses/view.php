<h2>View Course</h2>

<div>
    <strong>Course Name:</strong> <?= htmlspecialchars($course['course_name']) ?>
</div>
<br>

<div>
    <strong>Instructor:</strong> <?= htmlspecialchars($course['instructor_name'] ?? 'N/A') ?>
</div>
<br>

<div>
    <strong>Duration (Weeks):</strong> <?= htmlspecialchars($course['duration_weeks']) ?>
</div>
<br>

<div>
    <strong>Max Seats:</strong> <?= htmlspecialchars($course['max_seats']) ?>
</div>
<br>

<div>
    <strong>Status:</strong> <?= $course['status'] == 1 ? 'Active' : 'Inactive' ?>
</div>
<br>

<a href="/courses">Back</a>

<a href="/courses/edit/<?= $course['id'] ?>">Update Course Details</a>
