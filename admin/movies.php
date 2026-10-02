<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Movies';
$active = 'movies';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM movies WHERE id = $id");
    header('Location: movies.php');
    exit;
}

$movies = mysqli_query($conn, "SELECT * FROM movies ORDER BY created_at DESC");

include 'includes/header.php';
?>
<div class="admin-header">
  <h1>Movies</h1>
  <a href="movie-form.php" class="btn-solid">+ Add Movie</a>
</div>

<table class="data-table">
  <thead><tr><th>Title</th><th>Genre</th><th>Duration</th><th>Status</th><th>Release</th><th></th></tr></thead>
  <tbody>
    <?php while ($m = mysqli_fetch_assoc($movies)): ?>
    <tr>
      <td><?php echo h($m['title']); ?></td>
      <td><?php echo h($m['genre']); ?></td>
      <td><?php echo format_duration($m['duration_minutes']); ?></td>
      <td><span class="badge badge-confirmed"><?php echo str_replace('_', ' ', $m['status']); ?></span></td>
      <td><?php echo $m['release_date'] ? date('M j, Y', strtotime($m['release_date'])) : '—'; ?></td>
      <td>
        <a href="movie-form.php?id=<?php echo $m['id']; ?>" class="action-link">Edit</a>
        <a href="movies.php?delete=<?php echo $m['id']; ?>" class="action-link danger" onclick="return confirm('Delete this movie? Its shows and reviews will also be removed.');">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php include 'includes/footer.php'; ?>
