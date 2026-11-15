<?php
require_once __DIR__ . '/config/database.php';
$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors['form'] = 'Invalid request. Please reload the page and try again.';
    } else {
        $old['name']  = trim($_POST['name'] ?? '');
        $old['email'] = trim($_POST['email'] ?? '');
        $old['phone'] = trim($_POST['phone'] ?? '');
        $pw  = $_POST['password'] ?? '';
        $cpw = $_POST['confirm_password'] ?? '';

        foreach (['name', 'email', 'phone'] as $f)
            if (!validate_field($f, $old[$f])) $errors[$f] = rule_message($f);
        if (!validate_field('password', $pw)) $errors['password'] = rule_message('password');
        if ($pw !== $cpw) $errors['confirm_password'] = 'Passwords do not match.';

        if (!$errors) {
            $st = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
            $st->execute([strtolower($old['email'])]);
            if ($st->fetch()) {
                $errors['email'] = 'This email is already registered.';
            } else {
                $ins = $pdo->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, "user")');
                $ins->execute([$old['name'], strtolower($old['email']), $old['phone'], password_hash($pw, PASSWORD_DEFAULT)]);
                flash('success', 'Registration successful! Please login.');
                redirect('login.php');
            }
        }
    }
}
$pageTitle = 'Register';
include __DIR__ . '/includes/header.php';
?>
<div class="form-box">
  <h2 style="margin-top:0">Create Account</h2>
  <?php if (!empty($errors['form'])): ?><div class="alert alert-error"><?= e($errors['form']) ?></div><?php endif; ?>
  <form method="post" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="name">Full Name</label>
      <input type="text" id="name" name="name" data-rule="name" value="<?= e($old['name']) ?>">
      <span class="err"><?= e($errors['name'] ?? '') ?></span></div>
    <div class="field"><label for="email">Email</label>
      <input type="email" id="email" name="email" data-rule="email" value="<?= e($old['email']) ?>">
      <span class="err"><?= e($errors['email'] ?? '') ?></span></div>
    <div class="field"><label for="phone">Phone (10 digits)</label>
      <input type="text" id="phone" name="phone" data-rule="phone" maxlength="10" value="<?= e($old['phone']) ?>">
      <span class="err"><?= e($errors['phone'] ?? '') ?></span></div>
    <div class="field"><label for="password">Password</label>
      <input type="password" id="password" name="password" data-rule="password">
      <span class="err"><?= e($errors['password'] ?? '') ?></span></div>
    <div class="field"><label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" data-rule="required" data-match="password">
      <span class="err"><?= e($errors['confirm_password'] ?? '') ?></span></div>
    <button class="btn" type="submit">Register</button>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
