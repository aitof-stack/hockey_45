<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Дашборд — Админ-панель</title><link rel="stylesheet" href="css/admin.css">
</head><body>
<div class="admin-layout"><?php include __DIR__ . '/inc/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-container">
    <h2 style="margin-bottom:20px;">📋 Дашборд</h2>
    <div class="dashboard-cards">
      <a href="clubs.php" class="dashboard-card">
        <div class="icon">🏒</div>
        <h3>Команды</h3>
        <p>Добавлять и редактировать команды с игроками</p>
      </a>
      <a href="articles.php" class="dashboard-card">
        <div class="icon">📰</div>
        <h3>Новости</h3>
        <p>Управление новостями</p>
      </a>
      <a href="competitions.php" class="dashboard-card">
        <div class="icon">🏆</div>
        <h3>Соревнования</h3>
        <p>Турниры и даты матчей</p>
      </a>
      <a href="media.php" class="dashboard-card">
        <div class="icon">📸</div>
        <h3>Медиатека</h3>
        <p>Фото, видео, загрузка файлов</p>
      </a>
      <a href="settings.php" class="dashboard-card">
        <div class="icon">⚙️</div>
        <h3>Настройки</h3>
        <p>Конфигурация сайта</p>
      </a>
    </div>
  </div>
</div></div>
</body></html>
