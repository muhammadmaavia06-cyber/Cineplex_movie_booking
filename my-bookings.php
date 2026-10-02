<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

require_login();

$base = '';
$asset_base = '';
$page_title = 'My Bookings';

$stmt = mysqli_prepare($conn, "
    SELECT b.*, s.show_date, s.show_time, m.title, t.name AS theater_name
    FROM bookings b
    JOIN shows s ON b.show_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN theaters t ON s.theater_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$bookings = mysqli_stmt_get_result($stmt);

include 'includes/header.php';
?>
<section class="section">
  <div class="section-head"><h2>My Bookings</h2></div>

  <?php if (mysqli_num_rows($bookings) === 0): ?>
    <p style="color:var(--muted);">You haven't booked any tickets yet. <a href="index.php" style="color:var(--mint); font-weight:700;">Browse movies →</a></p>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr><th>Reference</th><th>Movie</th><th>Theatre</th><th>Date / Time</th><th>Class</th><th>Tickets</th><th>Total</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php while ($b = mysqli_fetch_assoc($bookings)): ?>
      <tr>
        <td><?php echo h($b['booking_reference']); ?></td>
        <td><?php echo h($b['title']); ?></td>
        <td><?php echo h($b['theater_name']); ?></td>
        <td><?php echo date('M j', strtotime($b['show_date'])); ?> · <?php echo date('g:i A', strtotime($b['show_time'])); ?></td>
        <td><?php echo ucfirst($b['seat_class']); ?></td>
        <td><?php echo (int)$b['adult_tickets']; ?> adult<?php echo $b['kid_tickets'] > 0 ? ', ' . (int)$b['kid_tickets'] . ' kid' : ''; ?></td>
        <td>$<?php echo number_format($b['total_amount'], 2); ?></td>
        <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>
