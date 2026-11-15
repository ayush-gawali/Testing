<?php
require_once __DIR__ . '/../config/database.php';
require_user();
$uid = $_SESSION['user_id'];

$st = $pdo->prepare('SELECT COUNT(*) FROM test_registrations WHERE user_id = ?'); $st->execute([$uid]);
$myTests = $st->fetchColumn();
$st = $pdo->prepare('SELECT COUNT(*) FROM comments WHERE user_id = ?'); $st->execute([$uid]);
$myComments = $st->fetchColumn();
$stats = $pdo->query('SELECT * FROM covid_stats ORDER BY id')->fetchAll();

$pageTitle = 'My Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<h2 style="margin-top:0">Welcome, <?= e($_SESSION['name']) ?></h2>
<div class="grid">
  <div class="card stat"><div class="num"><?= (int)$myTests ?></div>My Test Registrations<br><br><a class="btn btn-sm" href="test_registration.php">Book / View Tests</a></div>
  <div class="card stat"><div class="num"><?= (int)$myComments ?></div>My Comments<br><br><a class="btn btn-sm" href="comments.php">Give Feedback</a></div>
</div>

<h2>COVID-19 Statistics</h2>
<p class="sample-note"><strong>Illustrative sample data</strong> - not live statistics.</p><br>
<div class="table-wrap"><table>
  <tr><th>Location</th><th>Total</th><th>Recovered</th><th>Active</th><th>Deaths</th></tr>
  <?php foreach ($stats as $s): ?>
  <tr><td><?= e($s['location']) ?></td><td><?= number_format($s['total_cases']) ?></td><td><?= number_format($s['recovered']) ?></td>
      <td><?= number_format($s['active_cases']) ?></td><td><?= number_format($s['deaths']) ?></td></tr>
  <?php endforeach; ?>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
