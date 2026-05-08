<!DOCTYPE html>
<html>

<head>
  <style>
    /* Reset */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      /* background: linear-gradient(135deg, #4b6cb7, #182848); */
    }

    .login-container {
      background: #ffffff;
      padding: 40px 30px;
      border-radius: 10px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 400px;
    }

    .login-container h2 {
      text-align: center;
      color: #333;
      margin-bottom: 30px;
      font-weight: 600;
    }

    .login-container .form-group {
      margin-bottom: 20px;
      position: relative;
    }

    .login-container label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      color: #555;
    }

    .login-container input[type="text"],
    .login-container input[type="email"],
    .login-container input[type="password"] {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      transition: border 0.3s;
    }

    .login-container input:focus {
      border-color: #4b6cb7;
      outline: none;
      box-shadow: 0 0 5px rgba(75, 108, 183, 0.5);
    }

    .login-container .error-message {
      color: red;
      font-size: 13px;
      margin-top: 4px;
      display: block;
    }

    .login-container .captcha-wrapper {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .login-container .captcha-wrapper img {
      border: 1px solid #ccc;
      border-radius: 5px;
      height: 40px;
    }

    .login-container .btn {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 6px;
      background: #4b6cb7;
      color: white;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
    }

    .login-container .btn:hover {
      background: #182848;
    }

    .login-container .refresh-btn {
      padding: 8px 12px;
      background: #f0f0f0;
      border: 1px solid #ccc;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }

    .login-container .error-summary {
      background: #ffe6e6;
      color: #b30000;
      padding: 10px 15px;
      margin-bottom: 20px;
      border-radius: 6px;
      font-size: 14px;
    }
  </style>

  <!-- jQuery library -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- jQuery Validation -->
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.22.0/dist/jquery.validate.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.22.0/dist/additional-methods.min.js"></script>
</head>

<body>
  <?php require $view; ?>
</body>

</html>