<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$articles = json_decode(file_get_contents(__DIR__ . '/../data/articles.json'), true);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Новости — Админ-панель</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-layout"><?php include __DIR__ . '/inc/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-container">
    <div class="page-header">
      <h2>📰 Новости</h2>
      <a href="edit-article.php" class="btn btn-primary">+ Новая новость</a>
    </div>
    <?php if (isset($_GET['saved'])): ?>
      <div class="alert alert-success">Новость сохранена!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success">Новость удалена!</div>
    <?php endif; ?>
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Заголовок</th>
          <th>Дата</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($articles as $article): ?>
        <tr>
          <td><?= $article['id'] ?></td>
          <td><?= htmlspecialchars($article['title']) ?></td>
          <td><?= htmlspecialchars($article['date']) ?></td>
          <td class="actions">
            <a href="edit-article.php?id=<?= $article['id'] ?>" class="btn btn-primary btn-sm">Редактировать</a>
            <a href="delete-article.php?id=<?= $article['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить новость?')">Удалить</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div></div>
</body>
</html>
