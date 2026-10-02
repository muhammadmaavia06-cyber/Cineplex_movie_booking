<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Users';
$active = 'users';

$users = mysqli_query($conn, "
    SELECT u.*, (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS booking_count
    FROM users u ORDER BY u.created_at DESC
");

include 'includes/header.php';
?>
<div class="admin-header"><h1>Registered Users</h1></div>

<table class="data-table">
  <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Bookings</th><th>Joined</th></tr></thead>
  <tbody>
    <?php while ($u = mysqli_fetch_assoc($users)): ?>
    <tr>
      <td><?php echo h($u['full_name']); ?></td>
      <td><?php echo h($u['email']); ?></td>
      <td><?php echo h($u['phone'] ?: '—'); ?></td>
      <td><?php echo (int)$u['booking_count']; ?></td>
      <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php include 'includes/footer.php'; ?>
