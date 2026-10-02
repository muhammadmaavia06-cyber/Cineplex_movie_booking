<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$page_title = 'Now Showing';
$base = '';
$asset_base = '';

// Featured movie = most recently released "now_showing" movie
$featured = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM movies WHERE status = 'now_showing' ORDER BY release_date DESC LIMIT 1"));

$movies = mysqli_query($conn,
    "SELECT * FROM movies WHERE status = 'now_showing' ORDER BY release_date DESC");

$palette = ['p1', 'p2', 'p3', 'p4', 'p5'];

include 'includes/header.php';
?>

<?php if ($featured):
    $rating = average_rating($conn, $featured['id']);
    $genres = array_map('trim', explode(',', $featured['genre']));
?>
<?php
    $wall_posters = glob(__DIR__ . '/assets/img/poster-*.svg');
    natsort($wall_posters);
    $wall_posters = array_map(function ($p) { return 'assets/img/' . basename($p); }, $wall_posters);
    // Repeat the list enough times to comfortably tile a wide/tall wall
    $wall_tiles = array_merge($wall_posters, $wall_posters, $wall_posters);
?>
<section class="hero hero-fade-in">
  <div class="hero-bg-layer" aria-hidden="true">
    <div class="hero-poster-wall">
      <?php foreach ($wall_tiles as $i => $src): ?>
        <div class="hero-wall-tile" style="background-image:url('<?php echo h($src); ?>')"></div>
      <?php endforeach; ?>
    </div>
    <div class="hero-bg-glow"></div>
  </div>
  <div class="hero-overlay" aria-hidden="true"></div>
  <div>
    <div class="eyebrow">Now Showing · Featured</div>
    <h1><?php echo h($featured['title']); ?></h1>
    <p class="desc"><?php echo h($featured['description']); ?></p>
    <div class="genre-tags">
      <?php foreach ($genres as $g): ?><span class="tag"><?php echo h($g); ?></span><?php endforeach; ?>
      <span class="tag"><?php echo format_duration($featured['duration_minutes']); ?></span>
      <span class="tag"><?php echo h($featured['certificate']); ?></span>
    </div>
    <div class="hero-actions">
      <a href="movie-details.php?id=<?php echo $featured['id']; ?>" class="cta">Book Tickets</a>
      <?php if ($featured['trailer_url']): ?>
        <button type="button" class="trailer-link" data-trailer="<?php echo h($featured['trailer_url']); ?>" data-title="<?php echo h($featured['title']); ?>"><span class="play">▶</span> Watch trailer</button>
      <?php endif; ?>
    </div>
  </div>
  <div class="poster">
    <?php if ($featured['poster_url']): ?><img src="<?php echo h($featured['poster_url']); ?>" alt="<?php echo h($featured['title']); ?> poster"><?php endif; ?>
    <?php if ($rating['avg']): ?><div class="rating">★ <?php echo $rating['avg']; ?></div><?php endif; ?>
    <div class="title-plate">
      <h3><?php echo h($featured['title']); ?></h3>
      <p>Gold · Platinum · Box available</p>
    </div>
  </div>
  <?php
    // Latest movie posters for the hero marquee (independent of the $movies list used below)
    $marquee_result = mysqli_query($conn,
        "SELECT id, title, poster_url FROM movies WHERE status = 'now_showing' ORDER BY release_date DESC LIMIT 12");
    $marquee_list = [];
    while ($mm = mysqli_fetch_assoc($marquee_result)) { $marquee_list[] = $mm; }
  ?>
  <?php if (!empty($marquee_list)): ?>
  <div class="hero-marquee" aria-hidden="true">
    <div class="hero-marquee-track">
      <?php foreach (array_merge($marquee_list, $marquee_list) as $mm): ?>
        <div class="hero-marquee-item">
          <?php if ($mm['poster_url']): ?>
            <img src="<?php echo h($mm['poster_url']); ?>" alt="<?php echo h($mm['title']); ?> poster" loading="lazy">
          <?php endif; ?>
          <div class="mq-glass"><?php echo h($mm['title']); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<section class="section">
  <div class="section-head"><h2>Now Showing</h2></div>
  <div class="grid">
    <?php $i = 0; while ($m = mysqli_fetch_assoc($movies)):
        $rating = average_rating($conn, $m['id']);
        $cls = $palette[$i % count($palette)]; $i++;
        $first_genre = trim(explode(',', $m['genre'])[0]);
    ?>
    <div class="card">
      <div class="card-poster <?php echo $m['poster_url'] ? '' : $cls; ?>">
        <a href="movie-details.php?id=<?php echo $m['id']; ?>" class="poster-link" aria-label="View details for <?php echo h($m['title']); ?>"></a>
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
        <a href="movie-details.php?id=<?php echo $m['id']; ?>" class="card-cta">Select Showtime</a>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="section-head"><h2>Seat Classes</h2></div>
  <div class="classes">
    <div class="class-card gold"><div class="label">Gold</div><div class="price">$14<sup>.00</sup></div><p>Standard recliner seating with great sightlines. Best value for a full house.</p></div>
    <div class="class-card platinum"><div class="label">Platinum</div><div class="price">$22<sup>.00</sup></div><p>Extra legroom, wider seats, and priority entry. Ideal for date nights.</p></div>
    <div class="class-card box"><div class="label">Box</div><div class="price">$38<sup>.00</sup></div><p>Private box seating for up to 4, with the best view in the house.</p></div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
