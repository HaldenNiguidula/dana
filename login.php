<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>LOGIN PAGE</title>
  <link href="https://fonts.googleapis.com/css2?family=Itim&family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="login.css">
</head>
<body>

  <div class="container">
    <div class="top-bar">
      <div class="main-title">
        <div class="coffee-part">
          <div>COFFEE&</div>
          <div class="underline"></div>
        </div>
        <div class="co-part">
          <div class="co">CO.</div>
          <div class="sub-title">CAFE</div>
        </div>
      </div>
    </div>

    <div class="login-box">
      <div class="login-title">LOGIN</div>

      <form action="login_action.php" method="POST">
  <label for="email">EMAIL ADDRESS</label>
  <div class="input-block">
    <input type="text" id="email" name="email" placeholder="Enter your email" required>
  </div>

<label for="password">PASSWORD</label>
<div class="input-group">
  <input type="password" id="password" name="password" placeholder="Enter your password" required>
  <button type="button" class="toggle-password" onclick="togglePassword()">👁</button>
</div>

<script>
  function togglePassword() {
    const passwordInput = document.getElementById("password");
    const type = passwordInput.type === "password" ? "text" : "password";
    passwordInput.type = type;
  }
</script>

  <div class="forgot">
    <a href="#">FORGOT PASSWORD?</a>
  </div>

<button type="submit" class="login-button">LOGIN</button>
<a href="register.php" class="login-button">SIGN UP</a>
</form>
      <button class="back-button" onclick="window.location.href='homepage.php';">
        IF YOU'RE A CUSTOMER, CLICK HERE TO RETURN TO THE HOMEPAGE
      </button>
      
      

</body>
</html>
