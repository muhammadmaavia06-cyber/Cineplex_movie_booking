<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Theatres';
$active = 'theaters';
$error = null;

// Handle add / edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $screens = (int) ($_POST['screens'] ?? 1);

    if ($name === '' || $location === '') {
        $error = 'Name and location are required.';
    } elseif ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE theaters SET name=?, location=?, screens=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssii', $name, $location, $screens, $id);
        mysqli_stmt_execute($stmt);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO theaters (name, location, screens) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $location, $screens);
        mysqli_stmt_execute($stmt);
    }
    if (!$error) { header('Location: theaters.php'); exit; }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM theaters WHERE id = $id");
    header('Location: theaters.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM theaters WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$theaters = mysqli_query($conn, "SELECT * FROM theaters ORDER BY name");

include 'includes/header.php';
?>
<div class="admin-header"><h1>Theatres</h1></div>

<?php if ($error): ?><div class="alert alert-error" style="max-width:600px;"><?php echo h($error); ?></div><?php endif; ?>

<div class="two-col" style="grid-template-columns:320px 1fr;">
  <form method="post" class="summary-box">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?php echo $edit['id'] ?? 0; ?>">
    <h3><?php echo $edit ? 'Edit Theatre' : 'Add Theatre'; ?></h3>
    <div class="field">
      <label>Name</label>
      <input type="text" name="name" value="<?php echo h($edit['name'] ?? ''); ?>" required>
    </div>
    <div class="field">
      <label>Location</label>
      <input type="text" name="location" value="<?php echo h($edit['location'] ?? ''); ?>" required>
    </div>
    <div class="field">
      <label>Screens</label>
      <input type="number" name="screens" min="1" value="<?php echo h($edit['screens'] ?? 1); ?>" required>
    </div>
    <button type="submit" class="btn-solid btn-block"><?php echo $edit ? 'Save Changes' : 'Add Theatre'; ?></button>
    <?php if ($edit): ?><a href="theaters.php" style="display:block; text-align:center; margin-top:10px; font-size:12px; color:var(--muted);">Cancel</a><?php endif; ?>
  </form>

  <table class="data-table">
    <thead><tr><th>Name</th><th>Location</th><th>Screens</th><th></th></tr></thead>
    <tbody>
      <?php while ($t = mysqli_fetch_assoc($theaters)): ?>
      <tr>
        <td><?php echo h($t['name']); ?></td>
        <td><?php echo h($t['location']); ?></td>
        <td><?php echo (int)$t['screens']; ?></td>
        <td>
          <a href="theaters.php?edit=<?php echo $t['id']; ?>" class="action-link">Edit</a>
          <a href="theaters.php?delete=<?php echo $t['id']; ?>" class="action-link danger" onclick="return confirm('Delete this theatre? Associated shows will also be removed.');">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
