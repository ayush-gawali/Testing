<?php
// testing/regex_testing.php - interactive regex tester + automatic test-suite with summary.
require_once __DIR__ . '/../config/database.php';

// ---------- Part A: interactive tester ----------
$rules = covicare_rules();
$tField = 'email';
$tValue = '';
$tResult = null;

// Keep interactive test history in the user's session. Each submitted test
// is appended once, and the row number is generated from the history length.
if (!isset($_SESSION['interactive_test_history']) || !is_array($_SESSION['interactive_test_history'])) {
    $_SESSION['interactive_test_history'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_interactive_test'])) {
    $tField = $_POST['field'] ?? 'email';
    $tValue = trim($_POST['value'] ?? '');

    if (!csrf_ok()) {
        flash('error', 'Invalid request. Please try the test again.');
        redirect('testing/regex_testing.php');
    }

    if (isset($rules[$tField])) {
        $tResult = validate_field($tField, $tValue);
        $_SESSION['interactive_test_history'][] = [
            'field' => $tField,
            'input' => $tValue,
            'actual' => $tResult,
            'tested_at' => date('Y-m-d H:i:s'),
        ];
    } else {
        flash('error', 'Please select a valid field.');
    }

    // Post/Redirect/Get prevents the browser refresh from adding the same row again.
    $_SESSION['last_interactive_test'] = [
        'field' => $tField,
        'value' => $tValue,
        'result' => $tResult,
    ];
    redirect('testing/regex_testing.php');
}

$lastTest = $_SESSION['last_interactive_test'] ?? null;
if ($lastTest) {
    $tField = $lastTest['field'];
    $tValue = $lastTest['value'];
    $tResult = $lastTest['result'];
}
$interactiveHistory = $_SESSION['interactive_test_history'];

// ---------- Part B: automatic test cases ----------
// Each case: [field, input, expected pass (true/false), category, description]
// Special field 'escape' checks that e() neutralises HTML (expected true = no raw "<" remains).
$cases = [
  // NAME
  ['name', 'Rahul Sharma', true, 'Valid', 'Normal name'],
  ['name', "Anne-Marie O'Neil", true, 'Valid', 'Hyphen and apostrophe allowed'],
  ['name', 'A', false, 'Boundary', '1 character (min is 2)'],
  ['name', 'Al', true, 'Boundary', '2 characters (min)'],
  ['name', str_repeat('a', 50), true, 'Boundary', '50 characters (max)'],
  ['name', str_repeat('a', 51), false, 'Boundary', '51 characters'],
  ['name', '', false, 'Empty', 'Empty name'],
  ['name', 'R4hul', false, 'Invalid', 'Digit inside name'],
  ['name', '<script>alert(1)</script>', false, 'Security', 'XSS payload as name'],
  ['name', "Robert'); DROP TABLE users;--", false, 'Security', 'SQL injection as name'],
  // EMAIL
  ['email', 'student@example.com', true, 'Valid', 'Normal email'],
  ['email', 'a.b+c@mail.co.in', true, 'Valid', 'Dots, plus and sub-domain'],
  ['email', 'a@b.co', true, 'Boundary', '6 characters (shortest allowed)'],
  ['email', 'student@', false, 'Invalid', 'Missing domain'],
  ['email', 'student.example.com', false, 'Invalid', 'Missing @'],
  ['email', '@example.com', false, 'Invalid', 'Missing local part'],
  ['email', 'a@b.c', false, 'Invalid', 'TLD of 1 letter'],
  ['email', 'user@exa mple.com', false, 'Invalid', 'Space inside email'],
  ['email', '', false, 'Empty', 'Empty email'],
  ['email', "' OR '1'='1", false, 'Security', 'SQL injection as email'],
  // PHONE
  ['phone', '9876543210', true, 'Valid', 'Valid mobile number'],
  ['phone', '6000000000', true, 'Boundary', 'Starts with 6 (lowest valid start)'],
  ['phone', '5876543210', false, 'Boundary', 'Starts with 5'],
  ['phone', '987654321', false, 'Boundary', '9 digits'],
  ['phone', '98765432101', false, 'Boundary', '11 digits'],
  ['phone', '98765abcde', false, 'Invalid', 'Letters in phone'],
  ['phone', '+919876543210', false, 'Invalid', 'Country code not allowed'],
  ['phone', '', false, 'Empty', 'Empty phone'],
  // PASSWORD
  ['password', 'Covid@2024', true, 'Valid', 'Strong password'],
  ['password', 'Abcdefg1', true, 'Boundary', '8 characters (min)'],
  ['password', 'Abcdef1', false, 'Boundary', '7 characters'],
  ['password', 'A' . str_repeat('a', 18) . '1', true, 'Boundary', '20 characters (max)'],
  ['password', 'A' . str_repeat('a', 19) . '1', false, 'Boundary', '21 characters'],
  ['password', 'password1', false, 'Invalid', 'No uppercase letter'],
  ['password', 'PASSWORD1', false, 'Invalid', 'No lowercase letter'],
  ['password', 'Password', false, 'Invalid', 'No digit'],
  ['password', '', false, 'Empty', 'Empty password'],
  // AGE
  ['age', '25', true, 'Valid', 'Normal age'],
  ['age', '1', true, 'Boundary', 'Minimum age'],
  ['age', '120', true, 'Boundary', 'Maximum age'],
  ['age', '0', false, 'Boundary', 'Age 0'],
  ['age', '121', false, 'Boundary', 'Age 121'],
  ['age', '-5', false, 'Invalid', 'Negative age'],
  ['age', '012', false, 'Invalid', 'Leading zero'],
  ['age', 'abc', false, 'Invalid', 'Letters as age'],
  ['age', '', false, 'Empty', 'Empty age'],
  // DATE
  ['date', '2030-12-01', true, 'Valid', 'Correct date format'],
  ['date', '2030-13-01', false, 'Invalid', 'Month 13'],
  ['date', '2030-12-32', false, 'Invalid', 'Day 32'],
  ['date', '01-12-2030', false, 'Invalid', 'Wrong format (DD-MM-YYYY)'],
  ['date', '', false, 'Empty', 'Empty date'],
  // COMMENT
  ['comment', 'Very helpful portal.', true, 'Valid', 'Normal comment'],
  ['comment', 'abcd', false, 'Boundary', '4 characters (min is 5)'],
  ['comment', 'abcde', true, 'Boundary', '5 characters (min)'],
  ['comment', str_repeat('a', 500), true, 'Boundary', '500 characters (max)'],
  ['comment', str_repeat('a', 501), false, 'Boundary', '501 characters'],
  ['comment', '', false, 'Empty', 'Empty comment'],
  // OUTPUT ESCAPING (XSS)
  ['escape', '<script>alert(1)</script>', true, 'Security', 'e() converts <script> to harmless text'],
  ['escape', '<img src=x onerror=alert(1)>', true, 'Security', 'e() neutralises HTML attribute payload'],
];

$results = [];
$passed = 0;
foreach ($cases as [$field, $input, $expected, $cat, $desc]) {
    $actual = ($field === 'escape') ? (strpos(e($input), '<') === false) : validate_field($field, $input);
    $ok = ($actual === $expected);
    if ($ok) $passed++;
    $results[] = compact('field', 'input', 'expected', 'actual', 'cat', 'desc', 'ok');
}
$total  = count($results);
$failed = $total - $passed;
$percent = $total ? round($passed / $total * 100, 2) : 0;

$pageTitle = 'Regex Testing';
include __DIR__ . '/../includes/header.php';
?>
<h2 style="margin-top:0">Regex Validation Tester</h2>
<div class="form-box wide" style="margin:0 0 10px">
  <form method="post">\n    <?= csrf_field() ?>\n    <input type="hidden" name="run_interactive_test" value="1">
    <div class="field"><label for="field">Field</label>
      <select id="field" name="field"><?php foreach ($rules as $k => $r): ?><option value="<?= e($k) ?>" <?= $k === $tField ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label for="value">Sample input</label><input type="text" id="value" name="value" value="<?= e($tValue) ?>"></div>
    <button class="btn" type="submit">Test Input</button>
  </form>
  <?php if ($tResult !== null): ?>
    <p><strong>Result:</strong> <span class="badge <?= $tResult ? 'PASS' : 'FAIL' ?>"><?= $tResult ? 'PASS (valid)' : 'FAIL (invalid)' ?></span>
    <?php if (!$tResult): ?><br><small><?= e(rule_message($tField)) ?></small><?php endif; ?></p>
    <small>Pattern: <code><?= e($rules[$tField]['pattern']) ?></code></small>
  <?php endif; ?>
</div>

<h2>Interactive Test History</h2>
<p>Every time you click <strong>Test Input</strong>, the result is added here. The serial number updates automatically.</p>
<div class="table-wrap"><table>
  <tr><th>#</th><th>Field</th><th>Input</th><th>Actual Result</th><th>Tested At</th></tr>
  <?php if (empty($interactiveHistory)): ?>
    <tr><td colspan="5">No interactive tests have been run yet.</td></tr>
  <?php else: ?>
    <?php foreach ($interactiveHistory as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e($r['field']) ?></td>
      <td><code><?= e(mb_strlen($r['input']) > 30 ? mb_substr($r['input'], 0, 30) . '... (' . mb_strlen($r['input']) . ' chars)' : $r['input']) ?></code></td>
      <td><span class="badge <?= $r['actual'] ? 'PASS' : 'FAIL' ?>"><?= $r['actual'] ? 'Accepted' : 'Rejected' ?></span></td>
      <td><?= e($r['tested_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  <?php endif; ?>
</table></div>

<h2>Automated Test Results</h2>
<div class="grid">
  <div class="card stat"><div class="num"><?= $total ?></div>Total Test Cases</div>
  <div class="card stat"><div class="num" style="color:var(--green)"><?= $passed ?></div>Passed</div>
  <div class="card stat"><div class="num" style="color:var(--red)"><?= $failed ?></div>Failed</div>
  <div class="card stat"><div class="num"><?= $percent ?>%</div>Pass Percentage</div>
</div>
<p>"Pass" here means the validator behaved as expected (accepted valid input and rejected invalid input).</p>
<div class="table-wrap"><table>
  <tr><th>#</th><th>Field</th><th>Input</th><th>Category</th><th>Description</th><th>Expected</th><th>Actual</th><th>Result</th></tr>
  <?php foreach ($results as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td><td><?= e($r['field']) ?></td>
    <td><code><?= e(mb_strlen($r['input']) > 30 ? mb_substr($r['input'], 0, 30) . '... (' . mb_strlen($r['input']) . ' chars)' : $r['input']) ?></code></td>
    <td><?= e($r['cat']) ?></td><td><?= e($r['desc']) ?></td>
    <td><?= $r['expected'] ? 'Accept' : 'Reject' ?></td><td><?= $r['actual'] ? 'Accepted' : 'Rejected' ?></td>
    <td><span class="badge <?= $r['ok'] ? 'PASS' : 'FAIL' ?>"><?= $r['ok'] ? 'PASS' : 'FAIL' ?></span></td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
