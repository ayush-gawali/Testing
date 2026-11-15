<?php
require_once __DIR__ . '/../config/database.php';
require_user();
$uid = $_SESSION['user_id'];
$error = '';
$text = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['comment'] ?? '');
    if (!csrf_ok()) {
        $error = 'Invalid request. Please reload the page and try again.';
    } elseif (!validate_field('comment', $text)) {
        $error = rule_message('comment');
    } else {
        $pdo->prepare('INSERT INTO comments (user_id, comment) VALUES (?, ?)')->execute([$uid, $text]);
        flash('success', 'Thank you! Your feedback has been submitted.');
        redirect('user/comments.php');
    }
}
$st = $pdo->prepare('SELECT comment, created_at FROM comments WHERE user_id = ? ORDER BY comment_id DESC');
$st->execute([$uid]);
$mine = $st->fetchAll();

$pageTitle = 'Feedback';
include __DIR__ . '/../includes/header.php';
?>
<div class="form-box wide">
  <h2 style="margin-top:0">Share Your Feedback</h2>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="comment">Comment (5-500 characters)</label>
      <textarea id="comment" name="comment" rows="4" data-rule="comment"><?= e($text) ?></textarea>
      <span class="err"></span></div>
    <button class="btn" type="submit">Submit</button>
  </form>
</div>
<h2>My Previous Comments</h2>
<?php if (!$mine): ?><p>No comments yet.</p><?php endif; ?>
<?php foreach ($mine as $c): ?>
  <div class="card" style="margin-bottom:10px"><?= nl2br(e($c['comment'])) ?><br><small><?= e($c['created_at']) ?></small></div>
<?php endforeach; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
