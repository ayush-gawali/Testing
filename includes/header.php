<?php
// includes/header.php - navbar + flash messages. Set $pageTitle before including.
$role  = $_SESSION['role'] ?? null;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'CoviCare') ?> | CoviCare</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>
<header class="navbar"><div class="container nav-inner">
  <a class="brand" href="<?= BASE_URL ?>index.php">Covi<span>Care</span></a>
  <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">&#9776;</button>
  <nav id="navLinks">
    <a href="<?= BASE_URL ?>index.php">Home</a>
    <?php if ($role === 'user'): ?>
      <a href="<?= BASE_URL ?>user/dashboard.php">Dashboard</a>
      <a href="<?= BASE_URL ?>user/test_registration.php">Book Test</a>
      <a href="<?= BASE_URL ?>user/comments.php">Feedback</a>
    <?php elseif ($role === 'admin'): ?>
      <a href="<?= BASE_URL ?>admin/dashboard.php">Admin Dashboard</a>
      <a href="<?= BASE_URL ?>admin/users.php">Users</a>
      <a href="<?= BASE_URL ?>admin/registrations.php">Tests</a>
      <a href="<?= BASE_URL ?>admin/comments.php">Comments</a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>testing/regex_testing.php">Testing</a>
    <?php if ($role): ?>
      <a class="btn-nav" href="<?= BASE_URL ?>logout.php">Logout (<?= e($_SESSION['name']) ?>)</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>admin/login.php">Admin</a>
      <a href="<?= BASE_URL ?>login.php">Login</a>
      <a class="btn-nav" href="<?= BASE_URL ?>register.php">Register</a>
    <?php endif; ?>
  </nav>
</div></header>
<main class="container">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash[0]) ?>"><?= e($flash[1]) ?></div>
<?php endif; ?>
