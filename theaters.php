<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$page_title = 'Theatres';
$base = '';
$asset_base = '';

$theaters = mysqli_query($conn, "SELECT * FROM theaters ORDER BY name");

include 'includes/header.php';
?>
<section class="section">
  <div class="section-head"><h2>Our Theatres</h2></div>
  <div class="classes">
    <?php while ($t = mysqli_fetch_assoc($theaters)): ?>
    <div class="class-card box">
      <div class="label"><?php echo h($t['location']); ?></div>
      <div class="price" style="font-size:22px;"><?php echo h($t['name']); ?></div>
      <p><?php echo (int)$t['screens']; ?> screens · Gold, Platinum &amp; Box seating available</p>
    </div>
    <?php endwhile; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
