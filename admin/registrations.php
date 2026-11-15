<?php
require_once __DIR__ . '/../config/database.php';
require_admin();
$allowed = ['Pending', 'Scheduled', 'Completed', 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['registration_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (!csrf_ok()) {
        flash('error', 'Invalid request.');
    } elseif ($id < 1 || !in_array($status, $allowed, true)) {
        flash('error', 'Invalid registration or status.');
    } else {
        $pdo->prepare('UPDATE test_registrations SET status = ? WHERE registration_id = ?')->execute([$status, $id]);
        flash('success', 'Status updated.');
    }
    redirect('admin/registrations.php');
}
$rows = $pdo->query('SELECT t.*, u.email FROM test_registrations t JOIN users u ON u.user_id = t.user_id ORDER BY t.registration_id DESC')->fetchAll();
$pageTitle = 'Test Registrations';
include __DIR__ . '/../includes/header.php';
?>
<h2 style="margin-top:0">Test Registrations</h2>
<div class="table-wrap"><table>
  <tr><th>ID</th><th>User Email</th><th>Patient</th><th>Age</th><th>Phone</th><th>Date</th><th>Status</th><th>Update</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= (int)$r['registration_id'] ?></td><td><?= e($r['email']) ?></td><td><?= e($r['patient_name']) ?></td>
    <td><?= (int)$r['age'] ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['test_date']) ?></td>
    <td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
    <td><form method="post" style="display:flex;gap:6px">
      <?= csrf_field() ?>
      <input type="hidden" name="registration_id" value="<?= (int)$r['registration_id'] ?>">
      <select name="status"><?php foreach ($allowed as $s): ?><option <?= $s === $r['status'] ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
      <button class="btn btn-sm" type="submit">Save</button>
    </form></td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
