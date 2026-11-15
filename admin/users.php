<?php
require_once __DIR__ . '/../config/database.php';
require_admin();
$rows = $pdo->query('SELECT user_id, name, email, phone, role FROM users ORDER BY user_id')->fetchAll(); // password never selected
$pageTitle = 'Users';
include __DIR__ . '/../includes/header.php';
?>
<h2 style="margin-top:0">Registered Users</h2>
<div class="table-wrap"><table>
  <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr><td><?= (int)$r['user_id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['role']) ?></td></tr>
  <?php endforeach; ?>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
