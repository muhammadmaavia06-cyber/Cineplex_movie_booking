<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

require_login();

$base = '';
$asset_base = '';
$page_title = 'Booking Confirmed';

$ref = $_GET['ref'] ?? '';

$stmt = mysqli_prepare($conn, "
    SELECT b.*, s.show_date, s.show_time, m.title, t.name AS theater_name
    FROM bookings b
    JOIN shows s ON b.show_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN theaters t ON s.theater_id = t.id
    WHERE b.booking_reference = ? AND b.user_id = ?
");
mysqli_stmt_bind_param($stmt, 'si', $ref, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$booking) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>
<section class="container">
  <div class="auth-wrap" style="max-width:560px;">
    <div class="alert alert-success">Booking confirmed! Enjoy the show.</div>
    <h2 style="margin-bottom:20px;"><?php echo h($booking['title']); ?></h2>
    <div class="summary-row"><span>Booking Reference</span><span style="color:var(--mint); font-weight:700;"><?php echo h($booking['booking_reference']); ?></span></div>
    <div class="summary-row"><span>Theatre</span><span><?php echo h($booking['theater_name']); ?></span></div>
    <div class="summary-row"><span>Date &amp; Time</span><span><?php echo date('M j, Y', strtotime($booking['show_date'])); ?> · <?php echo date('g:i A', strtotime($booking['show_time'])); ?></span></div>
    <div class="summary-row"><span>Class</span><span><?php echo ucfirst($booking['seat_class']); ?></span></div>
    <div class="summary-row"><span>Adult Tickets</span><span><?php echo (int)$booking['adult_tickets']; ?></span></div>
    <div class="summary-row"><span>Kid Tickets</span><span><?php echo (int)$booking['kid_tickets']; ?></span></div>
    <div class="summary-row total"><span>Total Paid</span><span>$<?php echo number_format($booking['total_amount'], 2); ?></span></div>
    <div class="form-actions"><a href="my-bookings.php" class="btn-solid btn-block" style="display:block; text-align:center;">View My Bookings</a></div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
