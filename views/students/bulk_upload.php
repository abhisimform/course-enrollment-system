<h3>Bulk Upload Students</h3>

<a href="/students" style="margin-bottom: 15px; display: inline-block;">&larr; Back to Students</a>

<?php if (!empty($errors)): ?>
  <div style="color:red;">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<p><strong>CSV Format:</strong></p>
<pre>
name,email
John Doe,john@example.com
Jane Doe,jane@example.com
</pre>

<form method="POST" enctype="multipart/form-data">
  <?= csrfInput() ?>

  <input type="file" name="file" accept=".csv,.xlsx" required><br><br>

  <button type="submit">Upload</button>
</form>