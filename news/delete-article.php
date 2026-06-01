<?php
session_start();
if (!isset($_SESSION['news_logged_in']) || $_SESSION['news_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: news.php');
    exit;
}

$articles = json_decode(file_get_contents(__DIR__ . '/../data/articles.json'), true);
$newArticles = [];

foreach ($articles as $a) {
    if ($a['id'] !== $id) {
        $newArticles[] = $a;
    }
}

file_put_contents(__DIR__ . '/../data/articles.json', json_encode($newArticles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: news.php?deleted=1');
exit;
