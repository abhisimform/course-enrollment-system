<h2>Create Permission</h2>

<a href="/permissions" class="btn btn-secondary" style="margin-bottom: 15px; display: inline-block;">
  &larr; Back to Permissions
</a>

<?php if (!empty($errors)): ?>
  <div class="error-summary">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="form-container">
  <form method="POST" action="/permissions/create" id="createPermissionForm">
    <?= csrfInput() ?>

    <div class="form-group">
      <label for="name">Permission Name:</label>
      <input
        type="text"
        name="name"
        id="name"
        placeholder="Enter Permission Name"
        value="<?= e($_POST['name'] ?? '') ?>"
        required>
      <span class="error-message"></span>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Create</button>
    </div>
  </form>
</div>

<script>
  $(document).ready(function() {
    $('#createPermissionForm').validate({
      errorElement: 'span',
      errorClass: 'error-message',

      errorPlacement: function(error, element) {
        error.appendTo(element.parent());
      },

      rules: {
        name: {
          required: true,
          minlength: 3
        }
      },

      messages: {
        name: {
          required: 'Please enter permission name',
          minlength: 'Permission name must be at least 3 characters'
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