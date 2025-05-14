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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirm Payments</title>
  <link rel="stylesheet" href="CSS/payment.css">
</head>
<body>
  <div class="container">
    <h2>Pending Payments</h2>

    <?php if ($result->num_rows === 0): ?>
      <p style="text-align:center;">No pending payments at the moment.</p>
    <?php else: ?>
      <form method="post">
        <table>
          <tr>
            <th>#Booking</th>
            <th>User</th>
            <th>Room</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['booking_id'] ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= $row['room_number'] ?></td>
              <td><?= $row['check_in_date'] ?></td>
              <td><?= $row['check_out_date'] ?></td>
              <td>$<?= number_format($row['amount'], 2) ?></td>
              <td><?= ucfirst($row['status']) ?></td>
              <td>
                <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                <button type="submit" name="mark_paid">Mark as Paid</button>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </form>
    <?php endif; ?>

    <a href="dashboard_manager.php" class="back-link">← Back to Manager Dashboard</a>
  </div>
</body>
</html>
