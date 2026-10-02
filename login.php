<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$page_title = 'Sign In';
$base = '';
$asset_base = '';
$error = null;
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'index.php';

    $stmt = mysqli_prepare($conn, "SELECT id, full_name, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

include 'includes/header.php';
?>
<div class="auth-wrap">
  <h2>Welcome back</h2>
  <p class="sub">Sign in to book tickets and manage your reservations.</p>

  <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="redirect" value="<?php echo h($redirect); ?>">
    <div class="field">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn-solid btn-block">Sign In</button>
    </div>
  </form>
  <div class="auth-switch">New here? <a href="register.php">Create an account</a></div>
</div>
<?php include 'includes/footer.php'; ?>
