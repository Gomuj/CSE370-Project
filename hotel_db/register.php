<?php
// signup.php
include 'dbconnect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $fname = $_POST['fname'];
  $lname = $_POST['lname'];
  $stmt = $conn->prepare("INSERT INTO users (username, password, fname, lname, role) VALUES (?, ?, ?, ?, 'customer')");
  $stmt->bind_param("ssss", $username, $password, $fname, $lname);
  if ($stmt->execute()) header("Location: login.php");
  else echo "Error: Username already exists.";
}
?>
<form method="post">
  <p>Username: <input name="username"></p>
  <p>Password: <input type="password" name="password"></p>
  <p>First Name: <input name="fname"></p>
  <p>Last Name: <input name="lname"></p>
  <p><button type="submit">Sign Up</button></p>
</form>
<a href="index.php">Back to Home</a>
