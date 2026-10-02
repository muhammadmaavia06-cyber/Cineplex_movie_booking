<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Dashboard';
$active = 'dashboard';

$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users"))['c'];
$total_movies = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM movies WHERE status='now_showing'"))['c'];
$total_theaters = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM theaters"))['c'];
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM bookings WHERE status='confirmed'"))['c'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) s FROM bookings WHERE status='confirmed'"))['s'];

$recent = mysqli_query($conn, "
    SELECT b.booking_reference, b.total_amount, b.created_at, u.full_name, m.title
    FROM bookings b JOIN users u ON b.user_id = u.id
    JOIN shows s ON b.show_id = s.id JOIN movies m ON s.movie_id = m.id
    ORDER BY b.created_at DESC LIMIT 8
");

include 'includes/header.php';
?>
<div class="admin-header"><h1>Dashboard</h1></div>

<div class="stat-cards">
  <div class="stat-card"><div class="label">Registered Users</div><div class="value"><?php echo $total_users; ?></div></div>
  <div class="stat-card"><div class="label">Movies Now Showing</div><div class="value"><?php echo $total_movies; ?></div></div>
  <div class="stat-card"><div class="label">Theatres</div><div class="value"><?php echo $total_theaters; ?></div></div>
  <div class="stat-card"><div class="label">Confirmed Bookings</div><div class="value"><?php echo $total_bookings; ?></div></div>
  <div class="stat-card"><div class="label">Total Revenue</div><div class="value">$<?php echo number_format($total_revenue, 2); ?></div></div>
</div>

<div class="section-head"><h2 style="font-size:20px; color:var(--mint);">Recent Bookings</h2></div>
<table class="data-table">
  <thead><tr><th>Reference</th><th>User</th><th>Movie</th><th>Amount</th><th>Date</th></tr></thead>
  <tbody>
    <?php while ($r = mysqli_fetch_assoc($recent)): ?>
    <tr>
      <td><?php echo h($r['booking_reference']); ?></td>
      <td><?php echo h($r['full_name']); ?></td>
      <td><?php echo h($r['title']); ?></td>
      <td>$<?php echo number_format($r['total_amount'], 2); ?></td>
      <td><?php echo date('M j, g:i A', strtotime($r['created_at'])); ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php include 'includes/footer.php'; ?>
