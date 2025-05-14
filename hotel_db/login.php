<?php
// login.php
include 'dbconnect.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $stmt = $conn->prepare("SELECT id, password, role, fname FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['LAST_ACTIVITY'] = time();
      $_SESSION['user_fname'] = $user['fname'];

      if ($user['role'] == 'manager') header("Location: dashboard_manager.php");
      else header("Location: dashboard_customer.php");
    } else echo "Invalid password.";
  } else echo "User not found.";
}
?>
<form method="post">
  Username: <input name="username">
  Password: <input type="password" name="password">
  <button type="submit">Login</button>
</form>
<a href="index.php">Back to Home</a>