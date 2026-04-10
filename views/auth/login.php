<h2>Login</h2>

<?php if (!empty($error)) : ?>
  <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form method="POST" action="/auth/login">
  <input type="email" name="email" placeholder="Email" required>
  <br><br>

  <input type="password" name="password" placeholder="Password" required>
  <br><br>

  <button type="submit">Login</button>
</form>