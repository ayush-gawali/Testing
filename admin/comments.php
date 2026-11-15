<?php
require_once __DIR__ . '/../config/database.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['comment_id'] ?? 0);
    if (!csrf_ok() || $id < 1) {
        flash('error', 'Invalid request.');
    } else {
        $pdo->prepare('DELETE FROM comments WHERE comment_id = ?')->execute([$id]);
        flash('success', 'Comment deleted.');
    }
    redirect('admin/comments.php');
}
$rows = $pdo->query('SELECT c.*, u.name FROM comments c JOIN users u ON u.user_id = c.user_id ORDER BY c.comment_id DESC')->fetchAll();
$pageTitle = 'Comments';
include __DIR__ . '/../includes/header.php';
?>
<h2 style="margin-top:0">User Comments</h2>
<?php if (!$rows): ?><p>No comments yet.</p><?php else: ?>
<div class="table-wrap"><table>
  <tr><th>ID</th><th>User</th><th>Comment</th><th>Date</th><th>Action</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr><td><?= (int)$r['comment_id'] ?></td><td><?= e($r['name']) ?></td><td><?= nl2br(e($r['comment'])) ?></td><td><?= e($r['created_at']) ?></td>
    <td><form method="post" onsubmit="return confirm('Delete this comment?');">
      <?= csrf_field() ?><input type="hidden" name="comment_id" value="<?= (int)$r['comment_id'] ?>">
      <button class="btn btn-sm btn-danger" type="submit">Delete</button></form></td></tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
