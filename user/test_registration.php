<?php
require_once __DIR__ . '/../config/database.php';
require_user();
$uid = $_SESSION['user_id'];
$errors = [];
$old = ['patient_name' => '', 'age' => '', 'phone' => '', 'test_date' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors['form'] = 'Invalid request. Please reload the page and try again.';
    } else {
        foreach ($old as $k => $v) $old[$k] = trim($_POST[$k] ?? '');

        if (!validate_field('name', $old['patient_name'])) $errors['patient_name'] = rule_message('name');
        if (!validate_field('age', $old['age']))           $errors['age']          = rule_message('age');
        if (!validate_field('phone', $old['phone']))       $errors['phone']        = rule_message('phone');

        if (!validate_field('date', $old['test_date'])) {
            $errors['test_date'] = rule_message('date');
        } else {
            [$y, $m, $d] = array_map('intval', explode('-', $old['test_date']));
            if (!checkdate($m, $d, $y))                        $errors['test_date'] = 'This date does not exist.';
            elseif ($old['test_date'] < date('Y-m-d'))         $errors['test_date'] = 'Test date cannot be in the past.';
            elseif ($old['test_date'] > date('Y-m-d', strtotime('+1 year'))) $errors['test_date'] = 'Test date must be within one year.';
        }

        if (!$errors) {
            $pdo->prepare('INSERT INTO test_registrations (user_id, patient_name, age, phone, test_date) VALUES (?, ?, ?, ?, ?)')
                ->execute([$uid, $old['patient_name'], (int)$old['age'], $old['phone'], $old['test_date']]);
            flash('success', 'Test registered successfully. Status: Pending.');
            redirect('user/test_registration.php');     // avoids duplicate submit on refresh
        }
    }
}
$st = $pdo->prepare('SELECT * FROM test_registrations WHERE user_id = ? ORDER BY registration_id DESC');
$st->execute([$uid]);
$records = $st->fetchAll();

$pageTitle = 'Book COVID Test';
include __DIR__ . '/../includes/header.php';
?>
<div class="form-box wide">
  <h2 style="margin-top:0">Register for a COVID Test</h2>
  <?php if (!empty($errors['form'])): ?><div class="alert alert-error"><?= e($errors['form']) ?></div><?php endif; ?>
  <form method="post" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="patient_name">Patient Name</label>
      <input type="text" id="patient_name" name="patient_name" data-rule="name" value="<?= e($old['patient_name']) ?>">
      <span class="err"><?= e($errors['patient_name'] ?? '') ?></span></div>
    <div class="field"><label for="age">Age</label>
      <input type="text" id="age" name="age" data-rule="age" maxlength="3" value="<?= e($old['age']) ?>">
      <span class="err"><?= e($errors['age'] ?? '') ?></span></div>
    <div class="field"><label for="phone">Phone</label>
      <input type="text" id="phone" name="phone" data-rule="phone" maxlength="10" value="<?= e($old['phone']) ?>">
      <span class="err"><?= e($errors['phone'] ?? '') ?></span></div>
    <div class="field"><label for="test_date">Preferred Test Date</label>
      <input type="date" id="test_date" name="test_date" data-rule="date" min="<?= date('Y-m-d') ?>" value="<?= e($old['test_date']) ?>">
      <span class="err"><?= e($errors['test_date'] ?? '') ?></span></div>
    <button class="btn" type="submit">Register Test</button>
  </form>
</div>

<h2>My Test Registrations</h2>
<?php if (!$records): ?>
  <p>You have not registered for any test yet.</p>
<?php else: ?>
<div class="table-wrap"><table>
  <tr><th>ID</th><th>Patient</th><th>Age</th><th>Phone</th><th>Test Date</th><th>Status</th></tr>
  <?php foreach ($records as $r): ?>
  <tr><td><?= (int)$r['registration_id'] ?></td><td><?= e($r['patient_name']) ?></td><td><?= (int)$r['age'] ?></td>
      <td><?= e($r['phone']) ?></td><td><?= e($r['test_date']) ?></td><td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td></tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
