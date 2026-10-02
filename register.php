<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$page_title = 'Register';
$base = '';
$asset_base = '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($full_name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $error = 'An account with that email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssss', $full_name, $email, $hash, $phone);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['user_name'] = $full_name;
                header('Location: index.php');
                exit;
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>
<div class="auth-wrap">
  <h2>Create your account</h2>
  <p class="sub">Register to book tickets, save your history, and rate movies.</p>

  <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>

  <form method="post">
    <div class="field">
      <label>Full Name</label>
      <input type="text" name="full_name" value="<?php echo h($_POST['full_name'] ?? ''); ?>" required>
    </div>
    <div class="field">
      <label>Email</label>
      <input type="email" name="email" value="<?php echo h($_POST['email'] ?? ''); ?>" required>
    </div>
    <div class="field">
      <label>Phone (optional)</label>
      <input type="text" name="phone" value="<?php echo h($_POST['phone'] ?? ''); ?>">
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <div class="field">
      <label>Confirm Password</label>
      <input type="password" name="confirm_password" required>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn-solid btn-block">Register</button>
    </div>
  </form>
  <div class="auth-switch">Already have an account? <a href="login.php">Sign in</a></div>
</div>
<?php include 'includes/footer.php'; ?>
