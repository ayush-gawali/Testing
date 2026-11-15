<?php
require_once __DIR__ . '/config/database.php';
$stats = $pdo->query('SELECT * FROM covid_stats ORDER BY id')->fetchAll();
$tot = ['total_cases' => 0, 'recovered' => 0, 'active_cases' => 0, 'deaths' => 0];
foreach ($stats as $s) foreach ($tot as $k => $v) $tot[$k] += $s[$k];
$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <h1>CoviCare - COVID-19 Information &amp; Awareness Portal</h1>
  <p>Learn the facts, know the symptoms, follow prevention guidelines and book a COVID test online.</p>
  <?php if (empty($_SESSION['user_id'])): ?>
    <a class="btn btn-light" href="register.php">Get Started</a> <a class="btn btn-light" href="login.php">Login</a>
  <?php endif; ?>
</section>

<h2>About COVID-19</h2>
<div class="card"><p>COVID-19 is an infectious disease caused by the SARS-CoV-2 virus. It spreads mainly through respiratory
droplets when an infected person coughs, sneezes or talks. Most people have mild to moderate illness, but older people
and those with other health conditions can become seriously ill.</p></div>

<h2>COVID-19 Statistics</h2>
<p class="sample-note"><strong>Illustrative sample data</strong> - these figures are stored in our MySQL table for demonstration only and are <strong>not live statistics</strong>.</p>
<br>
<div class="grid">
  <div class="card stat"><div class="num"><?= number_format($tot['total_cases']) ?></div>Total Cases</div>
  <div class="card stat"><div class="num"><?= number_format($tot['recovered']) ?></div>Recovered</div>
  <div class="card stat"><div class="num"><?= number_format($tot['active_cases']) ?></div>Active Cases</div>
  <div class="card stat"><div class="num"><?= number_format($tot['deaths']) ?></div>Deaths</div>
</div>
<br>
<div class="table-wrap"><table>
  <tr><th>Location</th><th>Total</th><th>Recovered</th><th>Active</th><th>Deaths</th><th>Last Updated</th></tr>
  <?php foreach ($stats as $s): ?>
  <tr><td><?= e($s['location']) ?></td><td><?= number_format($s['total_cases']) ?></td><td><?= number_format($s['recovered']) ?></td>
      <td><?= number_format($s['active_cases']) ?></td><td><?= number_format($s['deaths']) ?></td><td><?= e($s['updated_at']) ?></td></tr>
  <?php endforeach; ?>
</table></div>

<h2>Common Symptoms</h2>
<div class="grid">
  <div class="card"><strong>Most common</strong><ul><li>Fever or chills</li><li>Dry cough</li><li>Tiredness</li></ul></div>
  <div class="card"><strong>Less common</strong><ul><li>Sore throat</li><li>Headache, body aches</li><li>Loss of taste or smell</li></ul></div>
  <div class="card"><strong>Seek urgent care if</strong><ul><li>Difficulty breathing</li><li>Persistent chest pain</li><li>Confusion or bluish lips</li></ul></div>
</div>

<h2>Prevention Guidelines</h2>
<div class="grid">
  <div class="card"><strong>Wash hands</strong><br>Use soap and water for 20 seconds or an alcohol-based sanitizer.</div>
  <div class="card"><strong>Wear a mask</strong><br>Especially in crowded or poorly ventilated places.</div>
  <div class="card"><strong>Keep distance</strong><br>Avoid close contact with people who are unwell.</div>
  <div class="card"><strong>Get vaccinated</strong><br>Stay up to date with vaccines recommended by health authorities.</div>
</div>

<h2>Frequently Asked Questions</h2>
<details><summary>How does COVID-19 spread?</summary><p>Mainly through droplets and aerosols released when an infected person breathes, coughs or talks.</p></details>
<details><summary>When should I get tested?</summary><p>If you have symptoms or have been in close contact with a confirmed case. You can book a test from your dashboard.</p></details>
<details><summary>How long should I isolate?</summary><p>Follow the guidance of your doctor and local health authority, as rules can change.</p></details>
<details><summary>Is this portal a replacement for a doctor?</summary><p>No. CoviCare provides awareness information only. Consult a qualified doctor for medical advice.</p></details>
<?php include __DIR__ . '/includes/footer.php'; ?>
