<?php
// add_room.php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'manager') die('Access denied.');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $room_number = $_POST['room_number'];
  $capacity = $_POST['capacity'];
  $price = $_POST['price'];
  $stmt = $conn->prepare("INSERT INTO rooms (room_number, capacity, price) VALUES (?, ?, ?)");
  $stmt->bind_param("sid", $room_number, $capacity, $price);
  if ($stmt->execute()) echo "Room added.";
  else echo "Error adding room.";
}
?>
<form method="post">
  Room #: <input name="room_number">
  Capacity: <input type="number" name="capacity" min="1" required>
  Price: <input type="number" step="0.01" name="price" min="0" required>
  <button type="submit">Add Room</button>
</form>

<br/>
<a href="dashboard_manager.php">Back to Manager Dashboard</a>