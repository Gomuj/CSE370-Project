<?php
// book_room.php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'customer') die('Access denied.');

if (isset($_GET['check_in']) && isset($_GET['check_out']) && isset($_GET['capacity'])) {
  $check_in = $_GET['check_in'];
  $check_out = $_GET['check_out'];
  $today = date('Y-m-d');

  if ($check_in < $today || $check_out < $check_in) {
    echo "Invalid date range. Check-in must be today or later, and check-out must be after check-in.";
  } else {
    $capacity = $_GET['capacity'];
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE capacity >= ? AND id NOT IN (SELECT room_id FROM bookings WHERE (status = 'reserved' OR status = 'paid') AND NOT (check_out_date < ? OR check_in_date > ?)) ORDER BY price");
    $stmt->bind_param("iss", $capacity, $check_in, $check_out);
    $stmt->execute();
    $available_rooms = $stmt->get_result();
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['room_id'])) {
  $room_id = $_POST['room_id'];
  $check_in = $_POST['check_in'];
  $check_out = $_POST['check_out'];
  $user_id = $_SESSION['user_id'];
  $price = $_POST['price'];

  $check = $conn->prepare("SELECT * FROM bookings WHERE room_id = ? AND (status = 'reserved' OR status = 'paid') AND NOT (check_out_date < ? OR check_in_date > ?)");
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
      $conn->query("INSERT INTO payments (booking_id, amount) VALUES ($booking_id, $price)");
      echo "Room booked.";
    } else echo "Booking failed.";
  }
}
?>

<h2>Search Rooms</h2>

<form method="get">
  <p>Check-in: <input type="date" name="check_in" required></p>
  <p>Check-out: <input type="date" name="check_out" required></p>
  <p>Capacity: <input type="number" name="capacity" min="1" required></p>
  <button type="submit">Search Rooms</button>
</form>

<?php if (isset($available_rooms)): ?>
  <h3>Available Rooms</h3>
  <?php if ($available_rooms->num_rows == 0): ?>
    <p>No rooms found with your specifications. </p>
  <?php else: ?>
    <?php while($r = $available_rooms->fetch_assoc()): ?>
      <form method="post" style="margin-bottom: 10px; border: 1px solid #ccc; padding: 10px;">
        <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
        <input type="hidden" name="check_in" value="<?= htmlspecialchars($check_in) ?>">
        <input type="hidden" name="check_out" value="<?= htmlspecialchars($check_out) ?>">

        <?php 
          $day_cnt = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24) + 1;
          $price = $r['price'] * $day_cnt;
        ?>

        <input type="hidden" name="price" value="<?= $price ?>">

        <p><strong>Room <?= $r['room_number'] ?></strong></p>
        <p>Capacity: <?= $r['capacity'] ?></p>
        
        <p>Price: $<?= number_format($price, 2) ?></p>
        <button type="submit">Book This Room</button>
      </form>
    <?php endwhile; ?>
  <?php endif; ?>
<?php endif; ?>

<a href="dashboard_customer.php">Back to customer dashboard</a>
