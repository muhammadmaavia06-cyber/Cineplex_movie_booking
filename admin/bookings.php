<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Bookings';
$active = 'bookings';

if (isset($_GET['cancel'])) {
    $id = (int) $_GET['cancel'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM bookings WHERE id = ? AND status = 'confirmed'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($b) {
        mysqli_begin_transaction($conn);
        $col = $b['seat_class'] . '_seats_booked';
        $tickets = $b['adult_tickets'] + $b['kid_tickets'];
        mysqli_query($conn, "UPDATE bookings SET status='cancelled' WHERE id=" . $id);
        mysqli_query($conn, "UPDATE shows SET $col = GREATEST(0, $col - $tickets) WHERE id=" . (int)$b['show_id']);
        mysqli_commit($conn);
    }
    header('Location: bookings.php');
    exit;
}

$bookings = mysqli_query($conn, "
    SELECT b.*, u.full_name, u.email, m.title, s.show_date, s.show_time
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN shows s ON b.show_id = s.id
    JOIN movies m ON s.movie_id = m.id
    ORDER BY b.created_at DESC
");

include 'includes/header.php';
?>
<div class="admin-header"><h1>All Bookings</h1></div>

<table class="data-table">
  <thead><tr><th>Reference</th><th>User</th><th>Movie</th><th>Show</th><th>Class</th><th>Tickets</th><th>Total</th><th>Status</th><th></th></tr></thead>
  <tbody>
    <?php while ($b = mysqli_fetch_assoc($bookings)): ?>
    <tr>
      <td><?php echo h($b['booking_reference']); ?></td>
      <td><?php echo h($b['full_name']); ?><br><span style="color:var(--muted); font-size:11px;"><?php echo h($b['email']); ?></span></td>
      <td><?php echo h($b['title']); ?></td>
      <td><?php echo date('M j', strtotime($b['show_date'])); ?> · <?php echo date('g:i A', strtotime($b['show_time'])); ?></td>
      <td><?php echo ucfirst($b['seat_class']); ?></td>
      <td><?php echo (int)$b['adult_tickets']; ?>A / <?php echo (int)$b['kid_tickets']; ?>K</td>
      <td>$<?php echo number_format($b['total_amount'], 2); ?></td>
      <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
      <td>
        <?php if ($b['status'] === 'confirmed'): ?>
        <a href="bookings.php?cancel=<?php echo $b['id']; ?>" class="action-link danger" onclick="return confirm('Cancel this booking and release the seats?');">Cancel</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php include 'includes/footer.php'; ?>
