<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pages = json_decode(file_get_contents(__DIR__ . '/../data/pages.json'), true);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Страницы — Админ-панель</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-layout"><?php include __DIR__ . '/inc/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-container">
    <div class="page-header">
      <h2>📄 Страницы сайта</h2>
    </div>
    <?php if (isset($_GET['saved'])): ?>
      <div class="alert alert-success">Страница сохранена!</div>
    <?php endif; ?>
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Заголовок</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pages as $key => $page): ?>
        <tr>
          <td><code><?= htmlspecialchars($key) ?></code></td>
          <td><?= htmlspecialchars($page['title']) ?></td>
          <td class="actions">
            <a href="edit-page.php?key=<?= urlencode($key) ?>" class="btn btn-primary btn-sm">Редактировать</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div></div>
</body>
</html>
