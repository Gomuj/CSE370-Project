<?php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'customer') die('Access denied.');

$message = '';
$calendar_data = [];

if (isset($_GET['room_id'])) {
  $room_id = $_GET['room_id'];
  $stmt = $conn->prepare("SELECT check_in_date, check_out_date FROM bookings WHERE room_id = ? AND status = 'reserved'");
  $stmt->bind_param("i", $room_id);
  $stmt->execute();
  $calendar_data = $stmt->get_result();
}

if (isset($_GET['check_in']) && isset($_GET['check_out']) && isset($_GET['capacity'])) {
  $check_in = $_GET['check_in'];
  $check_out = $_GET['check_out'];
  $capacity = $_GET['capacity'];

  $stmt = $conn->prepare("SELECT * FROM rooms WHERE capacity >= ? AND id NOT IN (
                          SELECT room_id FROM bookings 
                          WHERE status = 'reserved' AND NOT (check_out_date <= ? OR check_in_date >= ?))");
  $stmt->bind_param("iss", $capacity, $check_in, $check_out);
  $stmt->execute();
  $available_rooms = $stmt->get_result();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['room_id'])) {
  $room_id = $_POST['room_id'];
  $check_in = $_POST['check_in'];
  $check_out = $_POST['check_out'];
  $user_id = $_SESSION['user_id'];

  $check = $conn->prepare("SELECT * FROM bookings WHERE room_id = ? AND status = 'reserved' AND NOT (check_out_date <= ? OR check_in_date >= ?)");
  $check->bind_param("iss", $room_id, $check_in, $check_out);
  $check->execute();
  $conflict = $check->get_result();

  if ($conflict->num_rows > 0) {
    $message = "Room is already booked during the selected dates.";
  } else {
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $room_id, $check_in, $check_out);
    if ($stmt->execute()) {
      $booking_id = $conn->insert_id;
      $conn->query("INSERT INTO payments (booking_id, amount) VALUES ($booking_id, (SELECT price FROM rooms WHERE id = $room_id))");
      $message = "Room booked successfully!";
    } else {
      $message = "Booking failed.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book a Room</title>
  <link rel="stylesheet" href="CSS/style1.css">
</head>
<body>
  <div class="form-container">
    <h2>Search Available Rooms</h2>

    <?php if (!empty($message)): ?>
      <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="get">
      <label for="check_in">Check-in Date:</label>
      <input type="date" name="check_in" id="check_in" required>

      <label for="check_out">Check-out Date:</label>
      <input type="date" name="check_out" id="check_out" required>

      <label for="capacity">Required Capacity:</label>
      <input type="number" name="capacity" id="capacity" min="1" required>

      <button type="submit">Search Rooms</button>
    </form>
  </div>

  <?php if (isset($available_rooms)): ?>
    <div class="form-container">
      <h2>Select a Room to Book</h2>
      <form method="post">
        <input type="hidden" name="check_in" value="<?= htmlspecialchars($check_in) ?>">
        <input type="hidden" name="check_out" value="<?= htmlspecialchars($check_out) ?>">

        <label for="room_id">Available Rooms:</label>
        <select name="room_id" id="room_id" required>
          <?php while($r = $available_rooms->fetch_assoc()): ?>
            <option value="<?= $r['id'] ?>">
              Room <?= htmlspecialchars($r['room_number']) ?> (Capacity: <?= $r['capacity'] ?>)
            </option>
          <?php endwhile; ?>
        </select>

        <button type="submit">Book Room</button>
      </form>
    </div>
  <?php endif; ?>

  <?php if (!empty($calendar_data)): ?>
    <div class="form-container">
      <h2>Room Booking Calendar</h2>
      <ul>
        <?php while ($row = $calendar_data->fetch_assoc()): ?>
          <li>Booked: <?= $row['check_in_date'] ?> to <?= $row['check_out_date'] ?></li>
        <?php endwhile; ?>
      </ul>
    </div>
  <?php endif; ?>

  <a class="back-link" href="dashboard_customer.php">← Back to Customer Dashboard</a>
</body>
</html>
