<?php
// cancel_booking.php
include 'dbconnect.php';
session_start();
if ($_SESSION['role'] != 'customer') die('Access denied.');

if (isset($_POST['booking_id'])) {
  $booking_id = $_POST['booking_id'];
  $user_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("DELETE FROM payments WHERE booking_id = ?");
  $stmt->bind_param("i", $booking_id);
  $stmt->execute();

  $stmt2 = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
  $stmt2->bind_param("ii", $booking_id, $user_id);
  $stmt2->execute();
}
header("Location: dashboard_customer.php");
?>