<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$allowed = ['clubs', 'competitions', 'media', 'arenas'];

if (!in_array($type, $allowed) || !$id) { header('Location: dashboard.php'); exit; }

$file = __DIR__ . '/../data/' . $type . '.json';
$items = json_decode(file_get_contents($file), true);
$newItems = [];

foreach ($items as $item) {
  if ($item['id'] !== $id) {
    $newItems[] = $item;
  }
}

file_put_contents($file, json_encode($newItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: ' . $type . '.php?deleted=1');
exit;
