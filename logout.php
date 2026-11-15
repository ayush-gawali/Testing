<?php
require_once __DIR__ . '/config/database.php';
$_SESSION = [];
session_destroy();
session_start();                       // new session just to show the message
flash('success', 'You have been logged out.');
redirect('index.php');
