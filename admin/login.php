<?php
session_start();
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$config = json_decode(file_get_contents(__DIR__ . '/../data/config.json'), true);

if ($username === 'admin' && password_verify($password, $config['password_hash'])) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user'] = $username;
    header('Location: dashboard.php');
    exit;
}

header('Location: index.php?error=1');
exit;
