<?php
// config/database.php - DB connection, session start and shared helper functions.
if (session_status() === PHP_SESSION_NONE) session_start();

define('BASE_URL', '/CoviCare/');   // folder name inside htdocs

try {
    $pdo = new PDO('mysql:host=localhost;dbname=covicare_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
    ]);
} catch (PDOException $ex) {
    die('Database connection failed. Start MySQL in XAMPP and import database/covicare.sql.');
}

require_once __DIR__ . '/../testing/regex_patterns.php';

// Escape output (prevents XSS)
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// CSRF protection
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_field() { return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">'; }
function csrf_ok() {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function redirect($path) { header('Location: ' . BASE_URL . $path); exit; }
function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }

// Access control
function require_user()  { if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user')  redirect('login.php'); }
function require_admin() { if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') redirect('admin/login.php'); }

// Shared login check (used by user login and admin login)
function attempt_login(PDO $pdo, $email, $password, $role) {
    $st = $pdo->prepare('SELECT user_id, name, password FROM users WHERE email = ? AND role = ?');
    $st->execute([strtolower($email), $role]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password'])) {
        session_regenerate_id(true);           // stops session fixation
        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['name']    = $u['name'];
        $_SESSION['role']    = $role;
        return true;
    }
    return false;
}
