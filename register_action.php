<?php
$host = "localhost";
$user = "root";
$password = ""; 
$database = "coffee_co";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'] ?? '';
$pass = $_POST['password'] ?? '';

$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
  echo "<script>alert('Email already registered'); window.location.href='register.php';</script>";
  exit;
}

$check->close();

$hashed = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $hashed);

if ($stmt->execute()) {
  echo "<script>alert('Registration successful! Please login.'); window.location.href='login.php';</script>";
} else {
  echo "<script>alert('Registration failed.'); window.location.href='register.php';</script>";
}

$stmt->close();
$conn->close();
?>
