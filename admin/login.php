<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM admins WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Sign In — CinePlex</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <h2>Admin Sign In</h2>
  <p class="sub">Manage movies, theatres, shows and bookings.</p>
  <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
  <form method="post">
    <div class="field">
      <label>Username</label>
      <input type="text" name="username" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn-solid btn-block">Sign In</button>
    </div>
  </form>
  <div class="auth-switch"><a href="../index.php">← Back to site</a></div>
</div>
</body>
</html>
