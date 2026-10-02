<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$movie = null;
$error = null;

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM movies WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $movie = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$movie) { header('Location: movies.php'); exit; }
}

$page_title = $movie ? 'Edit Movie' : 'Add Movie';
$active = 'movies';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $language = trim($_POST['language'] ?? 'English');
    $duration = (int) ($_POST['duration_minutes'] ?? 0);
    $certificate = trim($_POST['certificate'] ?? 'PG-13');
    $description = trim($_POST['description'] ?? '');
    $poster_url = trim($_POST['poster_url'] ?? '');
    $trailer_url = trim($_POST['trailer_url'] ?? '');
    $release_date = $_POST['release_date'] ?? null;
    $status = $_POST['status'] ?? 'now_showing';

    if ($title === '' || $genre === '' || $duration < 1) {
        $error = 'Title, genre, and a valid duration are required.';
    } elseif ($movie) {
        $stmt = mysqli_prepare($conn, "
            UPDATE movies SET title=?, genre=?, language=?, duration_minutes=?, certificate=?, description=?, poster_url=?, trailer_url=?, release_date=?, status=?
            WHERE id=?
        ");
        mysqli_stmt_bind_param($stmt, 'sssissssssi', $title, $genre, $language, $duration, $certificate, $description, $poster_url, $trailer_url, $release_date, $status, $id);
        mysqli_stmt_execute($stmt);
        header('Location: movies.php'); exit;
    } else {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO movies (title, genre, language, duration_minutes, certificate, description, poster_url, trailer_url, release_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, 'sssissssss', $title, $genre, $language, $duration, $certificate, $description, $poster_url, $trailer_url, $release_date, $status);
        mysqli_stmt_execute($stmt);
        header('Location: movies.php'); exit;
    }
}

include 'includes/header.php';
?>
<div class="admin-header"><h1><?php echo $movie ? 'Edit Movie' : 'Add Movie'; ?></h1></div>

<?php if ($error): ?><div class="alert alert-error" style="max-width:700px;"><?php echo h($error); ?></div><?php endif; ?>

<form method="post" style="max-width:700px;">
  <div class="field">
    <label>Title</label>
    <input type="text" name="title" value="<?php echo h($movie['title'] ?? ($_POST['title'] ?? '')); ?>" required>
  </div>
  <div class="field">
    <label>Genre (comma-separated)</label>
    <input type="text" name="genre" value="<?php echo h($movie['genre'] ?? ($_POST['genre'] ?? '')); ?>" placeholder="Action, Thriller" required>
  </div>
  <div class="field">
    <label>Language</label>
    <input type="text" name="language" value="<?php echo h($movie['language'] ?? 'English'); ?>">
  </div>
  <div class="field">
    <label>Duration (minutes)</label>
    <input type="number" name="duration_minutes" min="1" value="<?php echo h($movie['duration_minutes'] ?? ''); ?>" required>
  </div>
  <div class="field">
    <label>Certificate</label>
    <select name="certificate">
      <?php foreach (['G','PG','PG-13','R','NC-17'] as $c): ?>
      <option value="<?php echo $c; ?>" <?php echo ($movie['certificate'] ?? 'PG-13') === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label>Description</label>
    <textarea name="description"><?php echo h($movie['description'] ?? ''); ?></textarea>
  </div>
  <div class="field">
    <label>Poster URL</label>
    <input type="text" name="poster_url" value="<?php echo h($movie['poster_url'] ?? ''); ?>" placeholder="assets/img/poster.jpg">
  </div>
  <div class="field">
    <label>Trailer URL (YouTube embed link)</label>
    <input type="text" name="trailer_url" value="<?php echo h($movie['trailer_url'] ?? ''); ?>" placeholder="https://www.youtube.com/embed/...">
  </div>
  <div class="field">
    <label>Release Date</label>
    <input type="date" name="release_date" value="<?php echo h($movie['release_date'] ?? ''); ?>">
  </div>
  <div class="field">
    <label>Status</label>
    <select name="status">
      <?php foreach (['now_showing' => 'Now Showing', 'coming_soon' => 'Coming Soon', 'archived' => 'Archived'] as $k => $v): ?>
      <option value="<?php echo $k; ?>" <?php echo ($movie['status'] ?? 'now_showing') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn-solid"><?php echo $movie ? 'Save Changes' : 'Add Movie'; ?></button>
    <a href="movies.php" style="margin-left:14px; font-size:13px; color:var(--muted);">Cancel</a>
  </div>
</form>

<?php include 'includes/footer.php'; ?>
