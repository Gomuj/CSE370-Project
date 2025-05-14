<?php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'customer') die('Access denied.');

$user_id = $_SESSION['user_id'];

// If a cancellation is requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
  $booking_id = $_POST['booking_id'];

  $stmt = $conn->prepare("DELETE FROM payments WHERE booking_id = ?");
  $stmt->bind_param("i", $booking_id);
  $stmt->execute();

  $stmt2 = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
  $stmt2->bind_param("ii", $booking_id, $user_id);
  $stmt2->execute();

  $message = "Booking #$booking_id has been successfully canceled.";
}

// Fetch all bookings for this user
$stmt = $conn->prepare("SELECT b.id, r.room_number, b.check_in_date, b.check_out_date, p.amount, p.status 
                        FROM bookings b 
                        JOIN rooms r ON b.room_id = r.id 
                        LEFT JOIN payments p ON p.booking_id = b.id 
                        WHERE b.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
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
    <h2>Cancel Booking</h2>

    <?php if (!empty($message)): ?>
      <p style="color: green; text-align: center;"><?= $message ?></p>
    <?php endif; ?>

    <?php if ($bookings->num_rows === 0): ?>
      <p style="text-align: center;">You have no active bookings.</p>
    <?php else: ?>
      <form method="post">
        <table>
          <tr>
            <th>Booking ID</th>
            <th>Room #</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
          <?php while ($row = $bookings->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['room_number']) ?></td>
              <td><?= $row['check_in_date'] ?></td>
              <td><?= $row['check_out_date'] ?></td>
              <td>$<?= number_format($row['amount'], 2) ?></td>
              <td><?= ucfirst($row['status']) ?></td>
              <td>
                <button type="submit" name="booking_id" value="<?= $row['id'] ?>" onclick="return confirm('Cancel booking #<?= $row['id'] ?>?');">
                  Cancel
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </form>
    <?php endif; ?>

    <a class="back-link" href="dashboard_customer.php">← Back to Customer Dashboard</a>
  </div>
</body>
</html>
