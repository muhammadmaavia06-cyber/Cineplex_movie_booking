<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$base = '';
$asset_base = '';

$movie_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM movies WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $movie_id);
mysqli_stmt_execute($stmt);
$movie = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$movie) {
    header('Location: index.php');
    exit;
}

$page_title = $movie['title'];
$rating = average_rating($conn, $movie_id);
$genres = array_map('trim', explode(',', $movie['genre']));

// Group shows by date, then list theater/time
$shows_stmt = mysqli_prepare($conn, "
    SELECT s.*, t.name AS theater_name, t.location
    FROM shows s JOIN theaters t ON s.theater_id = t.id
    WHERE s.movie_id = ? AND s.show_date >= CURDATE()
    ORDER BY s.show_date, s.show_time
");
mysqli_stmt_bind_param($shows_stmt, 'i', $movie_id);
mysqli_stmt_execute($shows_stmt);
$shows_result = mysqli_stmt_get_result($shows_stmt);

$shows_by_date = [];
while ($row = mysqli_fetch_assoc($shows_result)) {
    $shows_by_date[$row['show_date']][] = $row;
}

// Reviews
$reviews_stmt = mysqli_prepare($conn, "
    SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id
    WHERE r.movie_id = ? ORDER BY r.created_at DESC
");
mysqli_stmt_bind_param($reviews_stmt, 'i', $movie_id);
mysqli_stmt_execute($reviews_stmt);
$reviews = mysqli_stmt_get_result($reviews_stmt);

$review_error = flash('review_error');
$review_success = flash('review_success');

include 'includes/header.php';
?>

<?php
  $release_year = $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : null;
  $backdrop_style = $movie['poster_url']
      ? 'style="--backdrop-img:url(' . h($movie['poster_url']) . ')"'
      : '';
?>
<section class="hero hero-fade-in">
  <div class="hero-backdrop<?php echo $movie['poster_url'] ? '' : ' no-image'; ?>" <?php echo $backdrop_style; ?> aria-hidden="true"></div>
  <div class="hero-backdrop-overlay" aria-hidden="true"></div>
  <div>
    <div class="eyebrow">Movie Details<?php echo $release_year ? ' · ' . $release_year : ''; ?></div>
    <h1><?php echo h($movie['title']); ?></h1>
    <p class="desc"><?php echo h($movie['description']); ?></p>
    <div class="genre-tags">
      <?php foreach ($genres as $g): ?><span class="tag"><?php echo h($g); ?></span><?php endforeach; ?>
      <span class="tag"><?php echo format_duration($movie['duration_minutes']); ?></span>
      <span class="tag"><?php echo h($movie['certificate']); ?></span>
      <span class="tag"><?php echo h($movie['language']); ?></span>
      <?php if ($rating['avg']): ?><span class="tag">★ <?php echo $rating['avg']; ?></span><?php endif; ?>
    </div>
    <div class="hero-actions" id="trailer">
      <a href="#showtimes" class="cta btn-watch">▶ Watch Now</a>
      <button type="button" id="watchlist-btn" class="btn-watchlist" data-movie-id="<?php echo $movie_id; ?>">+ Add to Watchlist</button>
      <?php if ($movie['trailer_url']): ?>
        <button type="button" class="trailer-link" data-trailer="<?php echo h($movie['trailer_url']); ?>" data-title="<?php echo h($movie['title']); ?>"><span class="play">▶</span> Watch trailer</button>
      <?php endif; ?>
    </div>
  </div>
  <div class="poster poster-zoom">
    <?php if ($movie['poster_url']): ?><img src="<?php echo h($movie['poster_url']); ?>" alt="<?php echo h($movie['title']); ?> poster"><?php endif; ?>
    <?php if ($rating['avg']): ?><div class="rating">★ <?php echo $rating['avg']; ?> (<?php echo $rating['total']; ?>)</div><?php endif; ?>
    <div class="title-plate">
      <h3><?php echo h($movie['title']); ?></h3>
      <p>Gold · Platinum · Box available</p>
    </div>
  </div>
</section>

<script src="assets/js/watchlist.js" defer></script>

<section class="section" id="showtimes" style="padding-top:0;">
  <div class="section-head"><h2>Showtimes</h2></div>
  <?php if (empty($shows_by_date)): ?>
    <p style="color:var(--muted);">No upcoming showtimes scheduled for this movie yet.</p>
  <?php else: ?>
    <?php foreach ($shows_by_date as $date => $shows): ?>
      <p style="color:var(--mint); font-weight:700; margin:20px 0 10px; font-family:'JetBrains Mono',monospace; font-size:13px;">
        <?php echo date('l, M j', strtotime($date)); ?>
      </p>
      <div class="showtime-list">
        <?php foreach ($shows as $s):
            $seats_left = ($s['gold_seats_total'] - $s['gold_seats_booked'])
                        + ($s['platinum_seats_total'] - $s['platinum_seats_booked'])
                        + ($s['box_seats_total'] - $s['box_seats_booked']);
        ?>
        <div class="showtime-chip">
          <span class="theater"><?php echo h($s['theater_name']); ?> · Screen <?php echo (int)$s['screen_number']; ?></span>
          <span class="time"><?php echo date('g:i A', strtotime($s['show_time'])); ?></span>
          <?php if ($seats_left > 0): ?>
            <a href="book-ticket.php?show_id=<?php echo $s['id']; ?>" class="book-link">Book Now</a>
          <?php else: ?>
            <a class="book-link" style="background:var(--hairline); color:var(--muted); cursor:not-allowed;">Sold Out</a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<section class="section" style="padding-top:0;">
  <div class="section-head"><h2>Reviews &amp; Ratings</h2></div>

  <?php if ($review_error): ?><div class="alert alert-error"><?php echo h($review_error); ?></div><?php endif; ?>
  <?php if ($review_success): ?><div class="alert alert-success"><?php echo h($review_success); ?></div><?php endif; ?>

  <?php if (is_logged_in()): ?>
  <form action="submit-review.php" method="post" style="max-width:520px; margin-bottom:32px;">
    <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
    <div class="field">
      <label>Your Rating (1–10)</label>
      <input type="number" name="rating" min="1" max="10" required>
    </div>
    <div class="field">
      <label>Your Review</label>
      <textarea name="comment" placeholder="What did you think of the movie?"></textarea>
    </div>
    <div class="form-actions"><button type="submit" class="btn-solid">Submit Review</button></div>
  </form>
  <?php else: ?>
    <p style="color:var(--muted); margin-bottom:24px;"><a href="login.php" style="color:var(--mint); font-weight:700;">Sign in</a> to leave a review.</p>
  <?php endif; ?>

  <?php if (mysqli_num_rows($reviews) === 0): ?>
    <p style="color:var(--muted);">No reviews yet. Be the first to share your thoughts!</p>
  <?php else: ?>
    <?php while ($r = mysqli_fetch_assoc($reviews)): ?>
      <div class="review">
        <div class="review-head">
          <span class="rev-name"><?php echo h($r['full_name']); ?></span>
          <span class="rev-rating">★ <?php echo (int)$r['rating']; ?>/10</span>
        </div>
        <p><?php echo nl2br(h($r['comment'])); ?></p>
        <div class="review-date"><?php echo date('M j, Y', strtotime($r['created_at'])); ?></div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
