<?php
require_once __DIR__ . '/../config/database.php';
if (($_SESSION['role'] ?? '') === 'admin') redirect('admin/dashboard.php');
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pw    = $_POST['password'] ?? '';
    if (!csrf_ok()) {
        $error = 'Invalid request. Please reload the page and try again.';
    } elseif ($email === '' || $pw === '') {
        $error = 'Email and password are required.';
    } elseif (attempt_login($pdo, $email, $pw, 'admin')) {   // role must be 'admin'
        redirect('admin/dashboard.php');
    } else {
        $error = 'Incorrect admin credentials.';
    }
}
$pageTitle = 'Admin Login';
include __DIR__ . '/../includes/header.php';
?>
<div class="form-box">
  <h2 style="margin-top:0">Admin Login</h2>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="email">Admin Email</label>
      <input type="email" id="email" name="email" data-rule="email" value="<?= e($email) ?>"><span class="err"></span></div>
    <div class="field"><label for="password">Password</label>
      <input type="password" id="password" name="password" data-rule="required"><span class="err"></span></div>
    <button class="btn" type="submit">Login as Admin</button>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
