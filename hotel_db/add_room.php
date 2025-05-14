<?php
// add_room.php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'manager') die('Access denied.');

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $room_number = $_POST['room_number'];
  $capacity = $_POST['capacity'];
  $price = $_POST['price'];
  $stmt = $conn->prepare("INSERT INTO rooms (room_number, capacity, price) VALUES (?, ?, ?)");
  $stmt->bind_param("sid", $room_number, $capacity, $price);
  if ($stmt->execute()) $message = "✅ Room added successfully.";
  else $message = "❌ Error adding room.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Room</title>
  <link rel="stylesheet" href="CSS/style1.css">
</head>
<body>
  <div class="form-container">
    <h2>Add New Room</h2>
    
    <?php if ($message): ?>
      <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="post">
      <label for="room_number">Room Number</label>
      <input type="text" id="room_number" name="room_number" required>

      <label for="capacity">Capacity</label>
      <input type="number" id="capacity" name="capacity" min="1" required>

      <label for="price">Price (TK)</label>
      <input type="number" id="price" name="price" min="0" step="1" required>

      <button type="submit">Add Room</button>
    </form>

    <br>
    <a class="back-link" href="dashboard_manager.html">← Back to Manager Dashboard</a>
  </div>
</body>
</html>
