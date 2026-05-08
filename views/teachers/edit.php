<h2>Edit Teacher</h2>

<a href="/teachers" class="btn btn-secondary" style="margin-bottom: 15px; display: inline-block;">&larr; Back to All Teachers</a>

<?php if (!empty($errors)): ?>
  <div class="error-summary">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="form-container">
  <form method="POST" id="editTeacherForm">
    <?= csrfInput() ?>

    <div class="form-group">
      <label for="name">Name:</label>
      <input 
        type="text" 
        name="name" 
        id="name" 
        placeholder="Name" 
        value="<?= htmlspecialchars($_POST['name'] ?? $teacher['name']) ?>" 
        required>
      <span class="error-message"></span>
    </div>

    <div class="form-group">
      <label for="email">Email:</label>
      <input 
        type="email" 
        name="email" 
        id="email" 
        placeholder="Email" 
        value="<?= htmlspecialchars($_POST['email'] ?? $teacher['email']) ?>" 
        required>
      <span class="error-message"></span>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Update Teacher</button>
    </div>
  </form>
</div>

<script>
$(document).ready(function() {
  $('#editTeacherForm').validate({
    errorElement: 'span',
    errorClass: 'error-message',
    errorPlacement: function(error, element) {
      error.appendTo(element.parent());
    },
    rules: {
      name: {
        required: true,
        minlength: 3
      },
      email: {
        required: true,
        email: true
      }
    },
    messages: {
      name: {
        required: 'Please enter the teacher name',
        minlength: 'Name must be at least 3 characters'
      },
      email: {
        required: 'Please enter the email',
        email: 'Please enter a valid email'
      }
    },
    highlight: function(element) {
      $(element).addClass('is-invalid').removeClass('is-valid');
    },
    unhighlight: function(element) {
      $(element).removeClass('is-invalid').addClass('is-valid');
    },
    submitHandler: function(form) {
      form.submit();
    }
  });
});
</script>