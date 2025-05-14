<?php
// dashboard_manager.php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'manager') die('Access denied.');
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// Room booking chart
$rooms = $conn->query("SELECT id, room_number FROM rooms ORDER BY room_number");
$bookings = $conn->query("SELECT room_id, check_in_date, check_out_date, status FROM bookings");
$calendar = [];
while ($booking = $bookings->fetch_assoc()) {
  $start = strtotime($booking['check_in_date']);
  $end = strtotime($booking['check_out_date']);
  for ($d = $start; $d <= $end; $d += 86400) {
    $calendar[$booking['room_id']][date('Y-m-d', $d)] = $booking['status'];
  }
}

$totalResult = $conn->query("SELECT SUM(amount) as total_received FROM payments WHERE status = 'paid'");
$totalRow = $totalResult->fetch_assoc();
$totalReceived = $totalRow['total_received'] ?? 0;
?>
<h2>Manager Dashboard</h2>
<ul>
  <li><a href="add_room.php">Add Room</a></li>
  <li><a href="confirm_payment.php">Confirm Payments</a></li>
</ul>

<p><strong>Total Amount Received:</strong> $<?= number_format($totalReceived, 2) ?></p>

<h3>Room Booking Calendar</h3>
<table border=1>
  <tr>
    <th>Room</th>
    <?php for ($i = 0; $i < 7; $i++): ?>
      <th><?= date('m-d', strtotime("+{$i} day")) ?></th>
    <?php endfor; ?>
  </tr>
  <?php while ($room = $rooms->fetch_assoc()): ?>
  <tr>
    <td>Room <?= $room['room_number'] ?></td>
    <?php for ($i = 0; $i < 7; $i++): $date = date('Y-m-d', strtotime("+{$i} day")); ?>
      <td style="text-align: center;"><?= isset($calendar[$room['id']][$date]) ? $calendar[$room['id']][$date] : '...' ?></td>
    <?php endfor; ?>
  </tr>
  <?php endwhile; ?>
</table>

<br/> 
<form method="post" action="logout.php"><button type="submit">Logout</button></form>