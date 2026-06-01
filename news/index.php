<?php
session_start();
if (!isset($_SESSION['news_logged_in']) || $_SESSION['news_logged_in'] !== true) {
  header('Location: login.php');
  exit;
}
$pageTitle = 'Медиацентр';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Медиацентр — Панель</title>
  <link rel="stylesheet" href="/css/style.css">
  <style>
    .mc-layout { max-width: 800px; margin: 120px auto 40px; padding: 0 20px; }
    .mc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
    .mc-header h1 { font-size: 1.5rem; color: var(--primary-dark); margin: 0; }
    .mc-header .user { font-size: 0.85rem; color: #64748b; margin-top: 4px; }
    .mc-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 12px; font-size: 0.9rem; font-weight: 700; text-decoration: none; border: 2px solid var(--section-even-fone); background: var(--white); color: var(--primary-dark); transition: all 0.2s; cursor: pointer; }
    .mc-btn:hover { border-color: var(--primary); background: #f0f4f8; }
    .mc-btn.danger { border-color: #fca5a5; color: #dc2626; }
    .mc-btn.danger:hover { background: #fef2f2; }
    .dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .dash-card { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; padding: 48px 24px; border-radius: 20px; text-decoration: none; border: 2px solid var(--section-even-fone); background: var(--white); color: var(--primary-dark); transition: all 0.25s; }
    .dash-card:hover { border-color: var(--primary); box-shadow: 0 8px 30px rgba(28,78,122,0.1); transform: translateY(-3px); }
    .dash-card .icon { font-size: 3rem; }
    .dash-card .label { font-size: 1.2rem; font-weight: 700; }
    .dash-card .desc { font-size: 0.85rem; color: #94a3b8; text-align: center; }
    @media (max-width: 600px) { .dash-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <?php require __DIR__ . '/../inc/header.php'; ?>
  <div class="mc-layout">
    <div class="mc-header">
      <div>
        <h1>Медиацентр</h1>
        <div class="user">Вы вошли как: <?= htmlspecialchars($_SESSION['news_user'] ?? '') ?></div>
      </div>
      <a href="logout.php" class="mc-btn danger">Выйти</a>
    </div>
    <div class="dash-grid">
      <a href="news.php" class="dash-card">
        <div class="icon">📰</div>
        <div class="label">Новости</div>
        <div class="desc">Создавайте и редактируйте новости турнира</div>
      </a>
      <a href="albums.php" class="dash-card">
        <div class="icon">📸</div>
        <div class="label">Медиа</div>
        <div class="desc">Фотоальбомы и видеозаписи турнира</div>
      </a>
    </div>
  </div>
  <?php require __DIR__ . '/../inc/footer.php'; ?>
</body>
</html>
