<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Shows';
$active = 'shows';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM shows WHERE id = $id");
    header('Location: shows.php');
    exit;
}

$shows = mysqli_query($conn, "
    SELECT s.*, m.title, t.name AS theater_name
    FROM shows s JOIN movies m ON s.movie_id = m.id JOIN theaters t ON s.theater_id = t.id
    ORDER BY s.show_date DESC, s.show_time DESC
");

include 'includes/header.php';
?>
<div class="admin-header">
  <h1>Shows</h1>
  <a href="show-form.php" class="btn-solid">+ Add Show</a>
</div>

<table class="data-table">
  <thead><tr><th>Movie</th><th>Theatre</th><th>Screen</th><th>Date</th><th>Time</th><th>Prices (G/P/B)</th><th>Booked</th><th></th></tr></thead>
  <tbody>
    <?php while ($s = mysqli_fetch_assoc($shows)): ?>
    <tr>
      <td><?php echo h($s['title']); ?></td>
      <td><?php echo h($s['theater_name']); ?></td>
      <td><?php echo (int)$s['screen_number']; ?></td>
      <td><?php echo date('M j, Y', strtotime($s['show_date'])); ?></td>
      <td><?php echo date('g:i A', strtotime($s['show_time'])); ?></td>
      <td>$<?php echo number_format($s['gold_price'],0); ?> / $<?php echo number_format($s['platinum_price'],0); ?> / $<?php echo number_format($s['box_price'],0); ?></td>
      <td><?php echo $s['gold_seats_booked'] + $s['platinum_seats_booked'] + $s['box_seats_booked']; ?> / <?php echo $s['gold_seats_total'] + $s['platinum_seats_total'] + $s['box_seats_total']; ?></td>
      <td>
        <a href="show-form.php?id=<?php echo $s['id']; ?>" class="action-link">Edit</a>
        <a href="shows.php?delete=<?php echo $s['id']; ?>" class="action-link danger" onclick="return confirm('Delete this show? Existing bookings for it will remain but reference a removed show.');">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php include 'includes/footer.php'; ?>
