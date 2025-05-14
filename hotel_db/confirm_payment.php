<?php
// confirm_payment.php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'manager') die('Access denied.');
if (isset($_POST['mark_paid'])) {
  $id = $_POST['booking_id'];
  $conn->query("UPDATE payments SET status='paid', paid_at=NOW() WHERE booking_id=$id");
  $conn->query("UPDATE bookings SET status='paid' WHERE id=$id");
}
$result = $conn->query("SELECT p.booking_id, u.username, r.room_number, b.check_in_date, b.check_out_date, p.amount, p.status FROM payments p JOIN bookings b ON p.booking_id = b.id JOIN users u ON b.user_id = u.id JOIN rooms r ON b.room_id = r.id WHERE p.status='pending'");
?>
<h2>Pending Payments</h2>
<form method="post">
<table border="1">
<tr><th>#Booking</th><th>User</th><th>Room</th><th>Check In Date</th><th>Check Out Date</th><th>Amount</th><th>Status</th><th>Action</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= $row['booking_id'] ?></td>
  <td><?= $row['username'] ?></td>
  <td><?= $row['room_number'] ?></td>
  <td><?= $row['check_in_date'] ?></td>
  <td><?= $row['check_out_date'] ?></td>
  <td><?= $row['amount'] ?></td>
  <td><?= $row['status'] ?></td>
  <td>
    <button type="submit" name="mark_paid" value="<?= $row['booking_id'] ?>">Mark as Paid</button>
    <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
  </td>
</tr>
<?php endwhile; ?>
</table>
</form>

<br/>
<a href="dashboard_manager.php">Back to Manager Dashboard</a>