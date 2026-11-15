<?php
require_once __DIR__ . '/../config/database.php';
require_admin();
$count = fn($sql) => (int)$pdo->query($sql)->fetchColumn();
$users    = $count("SELECT COUNT(*) FROM users WHERE role = 'user'");
$tests    = $count('SELECT COUNT(*) FROM test_registrations');
$pending  = $count("SELECT COUNT(*) FROM test_registrations WHERE status = 'Pending'");
$comments = $count('SELECT COUNT(*) FROM comments');
$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<h2 style="margin-top:0">Admin Dashboard</h2>
<div class="grid">
  <div class="card stat"><div class="num"><?= $users ?></div>Registered Users<br><br><a class="btn btn-sm" href="users.php">View</a></div>
  <div class="card stat"><div class="num"><?= $tests ?></div>Test Registrations<br><br><a class="btn btn-sm" href="registrations.php">Manage</a></div>
  <div class="card stat"><div class="num"><?= $pending ?></div>Pending Tests<br><br><a class="btn btn-sm" href="registrations.php">Review</a></div>
  <div class="card stat"><div class="num"><?= $comments ?></div>Comments<br><br><a class="btn btn-sm" href="comments.php">Manage</a></div>
</div>
<p><br><a class="btn" href="../testing/regex_testing.php">Open Regex Testing Page</a></p>
<?php include __DIR__ . '/../includes/footer.php'; ?>
