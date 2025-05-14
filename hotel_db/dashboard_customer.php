<?php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'customer') die('Access denied.');
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

$id = $_SESSION['user_id'];
$unpaid = $conn->query("SELECT b.id, r.room_number, b.check_in_date, b.check_out_date, p.amount, p.status FROM bookings b JOIN rooms r ON b.room_id = r.id JOIN payments p ON b.id = p.booking_id WHERE b.user_id = $id AND p.status = 'pending'");
$paid = $conn->query("SELECT b.id, r.room_number, b.check_in_date, b.check_out_date, p.amount, p.status FROM bookings b JOIN rooms r ON b.room_id = r.id JOIN payments p ON b.id = p.booking_id WHERE b.user_id = $id AND p.status = 'paid'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Dashboard</title>
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

  <h1>Welcome, <?= htmlspecialchars($_SESSION['user_fname']) ?></h1>
  <h2>Your Bookings</h2>

  <h4>Unpaid Bookings</h4>
  <?php if ($unpaid->num_rows == 0): ?>
    <p>You have no unpaid bookings.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>#Booking</th>
        <th>Room</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Amount</th>
        <th>Action</th>
      </tr>
      <?php while ($row = $unpaid->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['room_number'] ?></td>
        <td><?= $row['check_in_date'] ?></td>
        <td><?= $row['check_out_date'] ?></td>
        <td style="text-align: right;">$<?= number_format($row['amount'], 2) ?></td>
        <td>
          <form method="post" action="cancel_booking.php" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
            <button type="submit" class="cancel-btn">Cancel Booking</button>
          </form>          
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php endif; ?>

  <h4>Paid Bookings</h4>
  <?php if ($paid->num_rows == 0): ?>
    <p>You have no paid bookings.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>#Booking</th>
        <th>Room</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Paid</th>
      </tr>
      <?php while ($row = $paid->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['room_number'] ?></td>
        <td><?= $row['check_in_date'] ?></td>
        <td><?= $row['check_out_date'] ?></td>
        <td style="text-align: right;">$<?= number_format($row['amount'], 2) ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php endif; ?>

  <a href="book_room1.php">Book Another Room</a>

  <form method="post" action="logout.php">
    <button type="submit" class="logout-btn">Logout</button>
  </form>

</body>
</html>
