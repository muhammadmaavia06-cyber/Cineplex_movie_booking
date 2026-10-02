<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$show_id  = (int) ($_GET['show_id'] ?? 0);
$movie_id = (int) ($_GET['movie_id'] ?? 0);

if (!is_logged_in()) {
    $redirect = 'book-ticket.php';
    if ($show_id) $redirect .= '?show_id=' . $show_id;
    elseif ($movie_id) $redirect .= '?movie_id=' . $movie_id;
    header('Location: login.php?redirect=' . urlencode($redirect));
    exit;
}

$base = '';
$asset_base = '';

/* ================================================================
   STATE 3: a specific show was chosen -> seat class + ticket form
   ================================================================ */
if ($show_id) {
    $stmt = mysqli_prepare($conn, "
        SELECT s.*, m.title, m.certificate, m.duration_minutes, t.name AS theater_name
        FROM shows s
        JOIN movies m ON s.movie_id = m.id
        JOIN theaters t ON s.theater_id = t.id
        WHERE s.id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'i', $show_id);
    mysqli_stmt_execute($stmt);
    $show = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$show) {
        header('Location: book-ticket.php');
        exit;
    }

    $page_title = 'Book: ' . $show['title'];

    $classes = [
        'gold' => ['label' => 'Gold', 'price' => $show['gold_price'], 'left' => $show['gold_seats_total'] - $show['gold_seats_booked']],
        'platinum' => ['label' => 'Platinum', 'price' => $show['platinum_price'], 'left' => $show['platinum_seats_total'] - $show['platinum_seats_booked']],
        'box' => ['label' => 'Box', 'price' => $show['box_price'], 'left' => $show['box_seats_total'] - $show['box_seats_booked']],
    ];

    $error = flash('booking_error');

    include 'includes/header.php';
    ?>
    <section class="section">
      <div class="section-head">
        <h2><?php echo h($show['title']); ?></h2>
      </div>
      <p style="color:var(--muted); margin-bottom:8px;">
        <a href="book-ticket.php?movie_id=<?php echo (int)$show['movie_id']; ?>" style="color:var(--mint); text-decoration:none;">&larr; Choose a different showtime</a>
      </p>
      <p style="color:var(--muted); margin-bottom:8px;">
        <?php echo h($show['theater_name']); ?> · Screen <?php echo (int)$show['screen_number']; ?> ·
        <?php echo date('l, M j', strtotime($show['show_date'])); ?> at <?php echo date('g:i A', strtotime($show['show_time'])); ?>
        · <?php echo format_duration($show['duration_minutes']); ?> · <?php echo h($show['certificate']); ?>
      </p>

      <?php if ($error): ?><div class="alert alert-error" style="max-width:520px; margin-top:16px;"><?php echo h($error); ?></div><?php endif; ?>

      <form action="process-booking.php" method="post" style="margin-top:30px;">
        <input type="hidden" name="show_id" value="<?php echo $show_id; ?>">

        <div class="section-head"><h2 style="font-size:20px;">Choose your class</h2></div>
        <div class="classes" style="margin-bottom:30px;">
          <?php foreach ($classes as $key => $c): ?>
          <div class="class-card <?php echo $key; ?>">
            <div class="label"><?php echo $c['label']; ?></div>
            <div class="price">$<?php echo number_format($c['price'], 2); ?></div>
            <?php if ($c['left'] > 0): ?>
              <p class="seats-left"><?php echo $c['left']; ?> seats left</p>
              <label style="display:flex; align-items:center; gap:10px; margin-top:14px;">
                <input type="radio" name="seat_class" value="<?php echo $key; ?>" required style="width:auto;">
                <span style="font-size:13px; color:var(--mint); font-weight:700;">Select</span>
              </label>
            <?php else: ?>
              <p class="seats-left sold-out">Sold out</p>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="two-col">
          <div>
            <div class="field">
              <label>Adult Tickets</label>
              <input type="number" name="adult_tickets" min="0" value="1" required>
            </div>
            <div class="field">
              <label>Kid Tickets (ages 3–12, 50% off)</label>
              <input type="number" name="kid_tickets" min="0" value="0">
            </div>
            <div class="form-actions">
              <button type="submit" class="cta">Confirm Booking</button>
            </div>
          </div>
          <div class="summary-box">
            <h3>Booking Notes</h3>
            <div class="summary-row"><span>Kids concession</span><span>50% off ticket price</span></div>
            <div class="summary-row"><span>Cancellation</span><span>Non-refundable</span></div>
            <div class="summary-row"><span>Arrival</span><span>15 min before showtime</span></div>
          </div>
        </div>
      </form>
    </section>
    <?php include 'includes/footer.php'; ?>
    <?php
    exit;
}

/* ================================================================
   STATE 2: a movie was chosen -> list its showtimes
   ================================================================ */
if ($movie_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM movies WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $movie_id);
    mysqli_stmt_execute($stmt);
    $movie = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$movie) {
        header('Location: book-ticket.php');
        exit;
    }

    $page_title = 'Showtimes: ' . $movie['title'];

    $stmt = mysqli_prepare($conn, "
        SELECT s.*, t.name AS theater_name, t.location
        FROM shows s
        JOIN theaters t ON s.theater_id = t.id
        WHERE s.movie_id = ? AND s.show_date >= CURDATE()
        ORDER BY s.show_date ASC, s.show_time ASC
    ");
    mysqli_stmt_bind_param($stmt, 'i', $movie_id);
    mysqli_stmt_execute($stmt);
    $shows_result = mysqli_stmt_get_result($stmt);

    $by_date = [];
    while ($s = mysqli_fetch_assoc($shows_result)) {
        $by_date[$s['show_date']][] = $s;
    }

    include 'includes/header.php';
    ?>
    <section class="section">
      <div class="section-head">
        <h2>Showtimes — <?php echo h($movie['title']); ?></h2>
      </div>
      <p style="color:var(--muted); margin-bottom:24px;">
        <a href="book-ticket.php" style="color:var(--mint); text-decoration:none;">&larr; Back to Showing Movies</a>
        &nbsp;·&nbsp; <?php echo format_duration($movie['duration_minutes']); ?> · <?php echo h($movie['certificate']); ?> · <?php echo h($movie['genre']); ?>
      </p>

      <?php if (empty($by_date)): ?>
        <div class="alert alert-error" style="max-width:600px;">No upcoming showtimes are currently scheduled for this movie. Please check back soon.</div>
      <?php else: ?>
        <?php foreach ($by_date as $date => $shows): ?>
          <div class="showtime-group">
            <h3 class="showtime-date"><?php echo date('l, F j', strtotime($date)); ?></h3>
            <div class="st-list">
              <?php foreach ($shows as $s):
                  $seats_left = ($s['gold_seats_total'] - $s['gold_seats_booked'])
                              + ($s['platinum_seats_total'] - $s['platinum_seats_booked'])
                              + ($s['box_seats_total'] - $s['box_seats_booked']);
              ?>
                <a href="book-ticket.php?show_id=<?php echo $s['id']; ?>" class="st-pill <?php echo $seats_left <= 0 ? 'sold-out' : ''; ?>">
                  <span class="time"><?php echo date('g:i A', strtotime($s['show_time'])); ?></span>
                  <span class="meta"><?php echo h($s['theater_name']); ?> · Screen <?php echo (int)$s['screen_number']; ?></span>
                  <span class="meta"><?php echo $seats_left > 0 ? $seats_left . ' seats left' : 'Sold out'; ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
    <?php include 'includes/footer.php'; ?>
    <?php
    exit;
}

/* ================================================================
   STATE 1 (default): "Showing Movies" — browse all available movies
   ================================================================ */
$page_title = 'Book Tickets';

$movies = mysqli_query($conn,
    "SELECT * FROM movies WHERE status = 'now_showing' ORDER BY release_date DESC");

$palette = ['p1', 'p2', 'p3', 'p4', 'p5'];

include 'includes/header.php';
?>
<section class="section">
  <div class="section-head">
    <h2>Book Tickets</h2>
  </div>
  <p style="color:var(--muted); margin-bottom:24px; max-width:640px;">
    Pick any movie below to see its available showtimes and reserve your seats.
  </p>

  <div class="section-head"><h2 style="font-size:20px;">Showing Movies</h2></div>
  <div class="grid">
    <?php $i = 0; while ($m = mysqli_fetch_assoc($movies)):
        $rating = average_rating($conn, $m['id']);
        $cls = $palette[$i % count($palette)]; $i++;
        $first_genre = trim(explode(',', $m['genre'])[0]);
    ?>
    <div class="card">
      <div class="card-poster <?php echo $m['poster_url'] ? '' : $cls; ?>">
        <a href="book-ticket.php?movie_id=<?php echo $m['id']; ?>" class="poster-link" aria-label="View showtimes for <?php echo h($m['title']); ?>"></a>
        <?php if ($m['poster_url']): ?><img src="<?php echo h($m['poster_url']); ?>" alt="<?php echo h($m['title']); ?> poster" loading="lazy"><?php endif; ?>
        <?php if ($rating['avg']): ?><div class="rating">★ <?php echo $rating['avg']; ?></div><?php endif; ?>
        <?php if ($m['trailer_url']): ?>
          <button type="button" class="trailer-btn" data-trailer="<?php echo h($m['trailer_url']); ?>" data-title="<?php echo h($m['title']); ?>" aria-label="Watch trailer for <?php echo h($m['title']); ?>"><span class="play-sm">▶</span></button>
        <?php endif; ?>
        <h4><?php echo h($m['title']); ?></h4>
      </div>
      <div class="card-body">
        <div class="card-meta">
          <span class="genre"><?php echo h($first_genre); ?></span>
          <span><?php echo format_duration($m['duration_minutes']); ?></span>
        </div>
        <a href="book-ticket.php?movie_id=<?php echo $m['id']; ?>" class="card-cta">View Showtimes</a>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
