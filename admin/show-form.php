<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$show = null;
$error = null;

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM shows WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $show = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$show) { header('Location: shows.php'); exit; }
}

$page_title = $show ? 'Edit Show' : 'Add Show';
$active = 'shows';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = (int) ($_POST['movie_id'] ?? 0);
    $theater_id = (int) ($_POST['theater_id'] ?? 0);
    $screen_number = (int) ($_POST['screen_number'] ?? 1);
    $show_date = $_POST['show_date'] ?? '';
    $show_time = $_POST['show_time'] ?? '';
    $gold_price = (float) ($_POST['gold_price'] ?? 0);
    $platinum_price = (float) ($_POST['platinum_price'] ?? 0);
    $box_price = (float) ($_POST['box_price'] ?? 0);
    $gold_total = (int) ($_POST['gold_seats_total'] ?? 60);
    $platinum_total = (int) ($_POST['platinum_seats_total'] ?? 30);
    $box_total = (int) ($_POST['box_seats_total'] ?? 8);

    if (!$movie_id || !$theater_id || !$show_date || !$show_time) {
        $error = 'Movie, theatre, date and time are required.';
    } elseif ($show) {
        $stmt = mysqli_prepare($conn, "
            UPDATE shows SET movie_id=?, theater_id=?, screen_number=?, show_date=?, show_time=?,
            gold_price=?, platinum_price=?, box_price=?, gold_seats_total=?, platinum_seats_total=?, box_seats_total=?
            WHERE id=?
        ");
        mysqli_stmt_bind_param($stmt, 'iiissdddiiii', $movie_id, $theater_id, $screen_number, $show_date, $show_time, $gold_price, $platinum_price, $box_price, $gold_total, $platinum_total, $box_total, $id);
        mysqli_stmt_execute($stmt);
        header('Location: shows.php'); exit;
    } else {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO shows (movie_id, theater_id, screen_number, show_date, show_time, gold_price, platinum_price, box_price, gold_seats_total, platinum_seats_total, box_seats_total)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, 'iiissdddiii', $movie_id, $theater_id, $screen_number, $show_date, $show_time, $gold_price, $platinum_price, $box_price, $gold_total, $platinum_total, $box_total);
        mysqli_stmt_execute($stmt);
        header('Location: shows.php'); exit;
    }
}

$movies = mysqli_query($conn, "SELECT id, title FROM movies ORDER BY title");
$theaters = mysqli_query($conn, "SELECT id, name FROM theaters ORDER BY name");

include 'includes/header.php';
?>
<div class="admin-header"><h1><?php echo $show ? 'Edit Show' : 'Add Show'; ?></h1></div>

<?php if ($error): ?><div class="alert alert-error" style="max-width:700px;"><?php echo h($error); ?></div><?php endif; ?>

<form method="post" style="max-width:700px;">
  <div class="field">
    <label>Movie</label>
    <select name="movie_id" required>
      <option value="">Select a movie</option>
      <?php mysqli_data_seek($movies, 0); while ($m = mysqli_fetch_assoc($movies)): ?>
      <option value="<?php echo $m['id']; ?>" <?php echo ($show['movie_id'] ?? '') == $m['id'] ? 'selected' : ''; ?>><?php echo h($m['title']); ?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="field">
    <label>Theatre</label>
    <select name="theater_id" required>
      <option value="">Select a theatre</option>
      <?php mysqli_data_seek($theaters, 0); while ($t = mysqli_fetch_assoc($theaters)): ?>
      <option value="<?php echo $t['id']; ?>" <?php echo ($show['theater_id'] ?? '') == $t['id'] ? 'selected' : ''; ?>><?php echo h($t['name']); ?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="field">
    <label>Screen Number</label>
    <input type="number" name="screen_number" min="1" value="<?php echo h($show['screen_number'] ?? 1); ?>" required>
  </div>
  <div class="field">
    <label>Show Date</label>
    <input type="date" name="show_date" value="<?php echo h($show['show_date'] ?? ''); ?>" required>
  </div>
  <div class="field">
    <label>Show Time</label>
    <input type="time" name="show_time" value="<?php echo h($show['show_time'] ?? ''); ?>" required>
  </div>

  <div class="section-head"><h2 style="font-size:16px; color:var(--mint);">Seat Classes &amp; Rates</h2></div>
  <div class="two-col" style="grid-template-columns:repeat(3,1fr); gap:16px;">
    <div class="field">
      <label>Gold Price</label>
      <input type="number" step="0.01" name="gold_price" value="<?php echo h($show['gold_price'] ?? 14.00); ?>" required>
    </div>
    <div class="field">
      <label>Platinum Price</label>
      <input type="number" step="0.01" name="platinum_price" value="<?php echo h($show['platinum_price'] ?? 22.00); ?>" required>
    </div>
    <div class="field">
      <label>Box Price</label>
      <input type="number" step="0.01" name="box_price" value="<?php echo h($show['box_price'] ?? 38.00); ?>" required>
    </div>
    <div class="field">
      <label>Gold Seats Total</label>
      <input type="number" name="gold_seats_total" value="<?php echo h($show['gold_seats_total'] ?? 60); ?>" required>
    </div>
    <div class="field">
      <label>Platinum Seats Total</label>
      <input type="number" name="platinum_seats_total" value="<?php echo h($show['platinum_seats_total'] ?? 30); ?>" required>
    </div>
    <div class="field">
      <label>Box Seats Total</label>
      <input type="number" name="box_seats_total" value="<?php echo h($show['box_seats_total'] ?? 8); ?>" required>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-solid"><?php echo $show ? 'Save Changes' : 'Add Show'; ?></button>
    <a href="shows.php" style="margin-left:14px; font-size:13px; color:var(--muted);">Cancel</a>
  </div>
</form>

<?php include 'includes/footer.php'; ?>
