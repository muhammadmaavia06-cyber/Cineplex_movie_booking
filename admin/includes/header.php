<?php
// Expects $page_title, $active to be set by calling page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo isset($page_title) ? h($page_title) . ' — CinePlex Admin' : 'CinePlex Admin'; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/../../assets/css/style.css') ?: time(); ?>">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a href="index.php" class="logo"><span>●</span> CINEPLEX</a>
    <nav>
      <a href="index.php" class="<?php echo ($active ?? '') === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
      <a href="movies.php" class="<?php echo ($active ?? '') === 'movies' ? 'active' : ''; ?>">Movies</a>
      <a href="theaters.php" class="<?php echo ($active ?? '') === 'theaters' ? 'active' : ''; ?>">Theatres</a>
      <a href="shows.php" class="<?php echo ($active ?? '') === 'shows' ? 'active' : ''; ?>">Shows</a>
      <a href="bookings.php" class="<?php echo ($active ?? '') === 'bookings' ? 'active' : ''; ?>">Bookings</a>
      <a href="users.php" class="<?php echo ($active ?? '') === 'users' ? 'active' : ''; ?>">Users</a>
      <a href="logout.php" style="margin-top:20px; color:var(--danger);">Sign out</a>
    </nav>
  </aside>
  <main class="admin-main">
