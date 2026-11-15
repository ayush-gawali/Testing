<?php
// ONE-TIME SETUP: open http://localhost/CoviCare/create_admin.php once, then DELETE this file.
require_once __DIR__ . '/config/database.php';
$st = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
if ($st->fetchColumn() > 0) {
    echo 'An admin already exists. Delete create_admin.php.';
} else {
    $pdo->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, "admin")')
        ->execute(['Portal Admin', 'admin@covicare.com', '9999999999', password_hash('Admin@123', PASSWORD_DEFAULT)]);
    echo 'Admin created.<br>Email: admin@covicare.com<br>Password: Admin@123<br><strong>Now delete create_admin.php.</strong>';
}
