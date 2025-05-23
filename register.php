<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>REGISTER PAGE</title>
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
      <div class="login-title">SIGN UP</div>

       <form action="register_action.php" method="POST">
        <label for="email">EMAIL ADDRESS</label>
        <div class="input-block">
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

<label for="password">PASSWORD</label>
<div class="input-group">
  <input type="password" id="password" name="password" placeholder="Enter your password" required>
  <button type="button" class="toggle-password" onclick="togglePassword()">👁</button>
</div>

<div class="forgot">
        already account? <a href="login.php">Login here</a>
      </div>

        <button type="submit" class="login-button">REGISTER</button>
      </form>

      <button class="back-button" onclick="window.location.href='homepage.php';">
        IF YOU'RE A CUSTOMER, CLICK HERE TO RETURN TO THE HOMEPAGE
      </button>
    </div>
  </div>

  <script>
    function validateLogin(event) {
      event.preventDefault();
      const email = document.getElementById("email").value;
      const password = document.getElementById("password").value;
      const users = {
        "coffee&co@gmail.com": "coffee12345"
      };
      if (users[email] && users[email] === password) {
        alert("Login successful! Welcome!");
        window.location.href = "order.php";
      } else {
        alert("Login failed. Incorrect email or password.");
      }
      return false;
    }

    function togglePassword() {
      const passwordInput = document.getElementById("password");
      passwordInput.type = passwordInput.type === "password" ? "text" : "password";
    }
  </script>

</body>
</html>