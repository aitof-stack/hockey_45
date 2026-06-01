<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$title = $_POST['title'] ?? '';
$date = $_POST['date'] ?? '';
$anons = $_POST['anons'] ?? '';
$content = $_POST['content'] ?? '';
$image = $_POST['image'] ?? '';

if (!$id || !$title) {
    header('Location: articles.php');
    exit;
}

$articles = json_decode(file_get_contents(__DIR__ . '/../data/articles.json'), true);
$found = false;

foreach ($articles as &$a) {
    if ($a['id'] === $id) {
        $a['title'] = $title;
        $a['date'] = $date;
        $a['anons'] = $anons;
        $a['content'] = $content;
        $a['image'] = $image;
        $found = true;
        break;
    }
}
unset($a);

if (!$found) {
    $articles[] = [
        'id' => $id,
        'title' => $title,
        'date' => $date,
        'anons' => $anons,
        'content' => $content,
        'image' => $image
    ];
}

file_put_contents(__DIR__ . '/../data/articles.json', json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: articles.php?saved=1');
exit;
