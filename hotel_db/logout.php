<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="refresh" content="2;url=login.html">
  <title>Logging Out</title>
  <link rel="stylesheet" href="CSS/style.css">
  <style>
    
  </style>
</head>
<body>
  <div class="logout-container">
    <h2>Logging You Out...</h2>
    <p>You will be redirected to the login page shortly.</p>
  </div>
</body>
</html>
