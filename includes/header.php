<?php
// Expects $conn and functions.php to already be included by the calling page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo isset($page_title) ? h($page_title) . ' — CinePlex' : 'CinePlex — Online Movie Booking'; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo isset($asset_base) ? $asset_base : ''; ?>assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/style.css') ?: time(); ?>">
</head>
<body>
  <nav class="nav">
    <a href="<?php echo isset($base) ? $base : ''; ?>index.php" class="logo"><span>●</span> CINEPLEX</a>
    <div class="nav-links">
      <a href="<?php echo $base ?? ''; ?>index.php">Now Showing</a>
      <a href="<?php echo $base ?? ''; ?>book-ticket.php">Book Tickets</a>
      <a href="<?php echo $base ?? ''; ?>theaters.php">Theatres</a>
      <a href="<?php echo $base ?? ''; ?>my-bookings.php">My Bookings</a>
    </div>
    <div class="nav-search">
      <span class="nav-search-icon" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
          <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </span>
      <input type="text" id="nav-search-input" class="nav-search-input" placeholder="Search movies..." autocomplete="off" aria-label="Search movies">
      <div id="nav-search-dropdown" class="nav-search-dropdown"></div>
    </div>
    <div class="nav-actions">
      <?php if (is_logged_in()): ?>
        <span class="nav-user">Hi, <?php echo h(explode(' ', $_SESSION['user_name'])[0]); ?></span>
        <a href="<?php echo $base ?? ''; ?>logout.php" class="btn-ghost">Sign out</a>
      <?php else: ?>
        <a href="<?php echo $base ?? ''; ?>login.php" class="btn-ghost">Sign in</a>
        <a href="<?php echo $base ?? ''; ?>register.php" class="btn-solid">Register</a>
      <?php endif; ?>
    </div>
  </nav>
  <div class="wave"></div>
  <main>
  <script>window.CINEPLEX_BASE = <?php echo json_encode(isset($base) ? $base : ''); ?>;</script>
  <script src="<?php echo isset($asset_base) ? $asset_base : ''; ?>assets/js/nav-search.js?v=<?php echo @filemtime(__DIR__ . '/../assets/js/nav-search.js') ?: time(); ?>" defer></script>
