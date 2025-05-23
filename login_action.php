<?php
session_start();
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

$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();
  if (password_verify($pass, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $email;
    header("Location: Admin.php");
    exit;
  } else {
    echo "<script>alert('Incorrect password'); window.location.href='login.php';</script>";
  }
} else {
  echo "<script>alert('Email not found'); window.location.href='login.php';</script>";
}

$stmt->close();
$conn->close();
?>
