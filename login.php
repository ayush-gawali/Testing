<?php
require_once __DIR__ . '/config/database.php';
if (($_SESSION['role'] ?? '') === 'user') redirect('user/dashboard.php');
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pw    = $_POST['password'] ?? '';
    if (!csrf_ok()) {
        $error = 'Invalid request. Please reload the page and try again.';
    } elseif ($email === '' || $pw === '') {
        $error = 'Email and password are required.';
    } elseif (!validate_field('email', $email)) {
        $error = rule_message('email');
    } elseif (attempt_login($pdo, $email, $pw, 'user')) {
        flash('success', 'Welcome back, ' . $_SESSION['name'] . '!');
        redirect('user/dashboard.php');
    } else {
        $error = 'Incorrect email or password.';   // same message for both cases on purpose
    }
}
$pageTitle = 'Login';
include __DIR__ . '/includes/header.php';
?>
<div class="form-box">
  <h2 style="margin-top:0">User Login</h2>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="email">Email</label>
      <input type="email" id="email" name="email" data-rule="email" value="<?= e($email) ?>"><span class="err"></span></div>
    <div class="field"><label for="password">Password</label>
      <input type="password" id="password" name="password" data-rule="required"><span class="err"></span></div>
    <button class="btn" type="submit">Login</button>
    <p>New user? <a href="register.php">Register here</a></p>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
