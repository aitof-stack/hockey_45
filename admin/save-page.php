<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$key = $_POST['key'] ?? '';
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';

if (!$key || !$title) {
    header('Location: pages.php');
    exit;
}

$pages = json_decode(file_get_contents(__DIR__ . '/../data/pages.json'), true);

if (isset($pages[$key])) {
    $pages[$key]['title'] = $title;
    $pages[$key]['content'] = $content;
    if (isset($_POST['badge'])) {
        $pages[$key]['badge'] = $_POST['badge'];
    }
    file_put_contents(__DIR__ . '/../data/pages.json', json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

header('Location: pages.php?saved=1');
exit;
