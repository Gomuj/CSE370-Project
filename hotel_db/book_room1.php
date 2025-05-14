<?php
// book_room.php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'customer') die('Access denied.');

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

  $stmt = $conn->prepare("SELECT * FROM rooms WHERE capacity >= ? AND id NOT IN (SELECT room_id FROM bookings WHERE status = 'reserved' AND NOT (check_out_date <= ? OR check_in_date >= ?))");
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
    echo "Room is already booked during the selected dates.";
  } else {
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $room_id, $check_in, $check_out);
    if ($stmt->execute()) {
      $booking_id = $conn->insert_id;
      $conn->query("INSERT INTO payments (booking_id, amount) VALUES ($booking_id, (SELECT price FROM rooms WHERE id = $room_id))");
      echo "Room booked.";
    } else echo "Booking failed.";
  }
}
?>
<form method="get">
  Check-in: <input type="date" name="check_in" required>
  Check-out: <input type="date" name="check_out" required>
  Capacity: <input type="number" name="capacity" required>
  <button type="submit">Search Rooms</button>
</form>

<?php if (isset($available_rooms)): ?>
<form method="post">
  <input type="hidden" name="check_in" value="<?= htmlspecialchars($check_in) ?>">
  <input type="hidden" name="check_out" value="<?= htmlspecialchars($check_out) ?>">
  <select name="room_id">
    <?php while($r = $available_rooms->fetch_assoc()): ?>
      <option value="<?= $r['id'] ?>">Room <?= $r['room_number'] ?> (Capacity: <?= $r['capacity'] ?>)</option>
    <?php endwhile; ?>
  </select><br>
  <button type="submit">Book Room</button>
</form>
<?php endif; ?>

<?php if (!empty($calendar_data)): ?>
<h3>Room Booking Calendar</h3>
<ul>
  <?php while($row = $calendar_data->fetch_assoc()): ?>
    <li>Booked: <?= $row['check_in_date'] ?> to <?= $row['check_out_date'] ?></li>
  <?php endwhile; ?>
</ul>
<?php endif; ?>