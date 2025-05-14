<?php
// cancel_booking.php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'customer') die('Access denied.');

$user_id = $_SESSION['user_id'];

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'])) {
  $booking_id = $_POST['booking_id'];

  $stmt = $conn->prepare("DELETE FROM payments WHERE booking_id = ?");
  $stmt->bind_param("i", $booking_id);
  $stmt->execute();

  $stmt2 = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
  $stmt2->bind_param("ii", $booking_id, $user_id);
  $stmt2->execute();

  $message = "Booking #$booking_id has been canceled.";
}

// Fetch active bookings
$stmt = $conn->prepare("SELECT b.id, r.room_number, b.check_in_date, b.check_out_date, p.amount, p.status
                        FROM bookings b
                        JOIN rooms r ON b.room_id = r.id
                        LEFT JOIN payments p ON p.booking_id = b.id
                        WHERE b.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cancel Booking</title>
  <link rel="stylesheet" href="CSS/payment.css">
</head>
<body>
  <div class="container">
    <h2>Your Bookings</h2>

    <?php if (!empty($message)): ?>
      <p style="color: green; text-align: center;"><?= $message ?></p>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>
      <p style="text-align: center;">You have no bookings.</p>
    <?php else: ?>
      <form method="post">
        <table>
          <tr>
            <th>#Booking</th>
            <th>Room</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= $row['room_number'] ?></td>
              <td><?= $row['check_in_date'] ?></td>
              <td><?= $row['check_out_date'] ?></td>
              <td>$<?= number_format($row['amount'], 2) ?></td>
              <td><?= ucfirst($row['status']) ?></td>
              <td>
                <button type="submit" name="booking_id" value="<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to cancel this booking?');">
                  Cancel
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </form>
    <?php endif; ?>

    <a href="dashboard_customer.php" class="back-link">← Back to Customer Dashboard</a>
  </div>
</body>
</html>
