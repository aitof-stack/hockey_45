<?php
session_start();
if (!isset($_SESSION['news_logged_in']) || $_SESSION['news_logged_in'] !== true) {
  header('Location: login.php');
  exit;
}

$articles = json_decode(file_get_contents(__DIR__ . '/../data/articles.json'), true);
$pageTitle = 'Новости';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Новости — Медиацентр</title>
  <link rel="stylesheet" href="/css/style.css">
  <style>
    .mc-layout { max-width: 960px; margin: 120px auto 40px; padding: 0 20px; }
    .mc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 10px; }
    .mc-header h1 { font-size: 1.4rem; color: var(--primary-dark); margin: 0; }
    .mc-actions { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
    .mc-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 12px; font-size: 0.9rem; font-weight: 700; text-decoration: none; border: 2px solid var(--section-even-fone); background: var(--white); color: var(--primary-dark); transition: all 0.2s; cursor: pointer; }
    .mc-btn:hover { border-color: var(--primary); background: #f0f4f8; }
    .mc-btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
    .mc-btn.primary:hover { background: var(--primary-dark); }
    .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.9rem; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .mc-list { background: var(--white); border-radius: 14px; border: 1px solid var(--section-even-fone); overflow: hidden; }
    .mc-list-item { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border-bottom: 1px solid var(--section-even-fone); }
    .mc-list-item:last-child { border-bottom: none; }
    .mc-list-item .title { font-weight: 600; color: var(--primary-dark); }
    .mc-list-item .date { font-size: 0.8rem; color: #94a3b8; margin-top: 2px; }
    .mc-list-item .actions { display: flex; gap: 6px; }
    .mc-list-item .actions a { font-size: 0.8rem; padding: 4px 10px; border-radius: 6px; text-decoration: none; border: 1px solid var(--section-even-fone); color: var(--primary); }
    .mc-list-item .actions a:hover { background: var(--block-fone); }
    .mc-list-item .actions a.danger { color: #dc2626; }
    .mc-empty { padding: 40px; text-align: center; color: #94a3b8; }
  </style>
</head>
<body>
  <?php require __DIR__ . '/../inc/header.php'; ?>
  <div class="mc-layout">
    <div class="mc-header">
      <h1>📰 Новости</h1>
      <div style="display:flex;gap:10px;">
        <a href="edit-article.php" class="mc-btn primary">➕ Создать новость</a>
        <a href="index.php" class="mc-btn">← Назад</a>
      </div>
    </div>

    <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Новость сохранена!</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Новость удалена!</div><?php endif; ?>

    <div class="mc-list">
      <?php if (empty($articles)): ?>
        <div class="mc-empty">Новостей пока нет. Нажмите «Создать новость».</div>
      <?php else:
        $items = array_reverse($articles);
        foreach ($items as $a): ?>
        <div class="mc-list-item">
          <div>
            <div class="title"><?= htmlspecialchars($a['title'] ?? 'Без названия') ?></div>
            <div class="date"><?= htmlspecialchars($a['date'] ?? '') ?></div>
          </div>
          <div class="actions">
            <a href="edit-article.php?id=<?= $a['id'] ?>">✏️ Редактировать</a>
            <a href="delete-article.php?id=<?= $a['id'] ?>" class="danger" onclick="return confirm('Удалить новость?')">🗑️</a>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
  <?php require __DIR__ . '/../inc/footer.php'; ?>
</body>
</html>
