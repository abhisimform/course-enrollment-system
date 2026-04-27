<h2>Login</h2>

<?php if (!empty($errors ?? [])): ?>
  <ul style="color:red;">
    <?php foreach ($errors as $error): ?>
      <li><?= htmlspecialchars($error) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form method="POST" action="/auth/login">

  <input
    type="hidden"
    name="csrf_token"
    value="<?= $_SESSION['csrf_token'] ?>">

  <input
    type="text"
    name="email"
    placeholder="Email"
    value="<?= htmlspecialchars($old['email'] ?? '') ?>">
  <br><br>
  
  <input
  type="password"
  name="password"
  placeholder="Password">
  <br><br>
  
  <img
    id="captcha-image"
    src="/auth/captcha"
    alt="captcha">
    
  <button
    type="button"
    onclick="document.getElementById('captcha-image').src='/auth/captcha?t=' + Date.now();">
    ⟲
  </button>
  <br><br>

  <input
    type="text"
    name="captcha"
    placeholder="Enter captcha"
    maxlength="6"
    autocomplete="off">
  <br><br>

  <button type="submit">Login</button>

</form>
