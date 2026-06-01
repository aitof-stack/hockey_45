<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$key = $_GET['key'] ?? '';
$pages = json_decode(file_get_contents(__DIR__ . '/../data/pages.json'), true);

if (!$key || !isset($pages[$key])) {
    header('Location: pages.php');
    exit;
}

$page = $pages[$key];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Редактировать — <?= htmlspecialchars($page['title']) ?></title>
  <link rel="stylesheet" href="css/admin.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.25.1-lts/ckeditor.js"></script>
</head>
<body>
<div class="admin-layout"><?php include __DIR__ . '/inc/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-container">
    <div class="page-header">
      <h2>📄 Редактировать: <?= htmlspecialchars($page['title']) ?></h2>
    </div>
    <div class="editor-wrapper">
      <form action="save-page.php" method="post">
        <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
        <div class="form-group">
          <label for="title">Заголовок страницы</label>
          <input type="text" id="title" name="title" value="<?= htmlspecialchars($page['title']) ?>" required>
        </div>
        <div class="form-group">
          <label for="content">Содержание</label>
          <textarea id="content" name="content"><?= htmlspecialchars($page['content']) ?></textarea>
        </div>
        <?php if (isset($page['badge'])): ?>
        <div class="form-group">
          <label for="badge">Бейдж</label>
          <input type="text" id="badge" name="badge" value="<?= htmlspecialchars($page['badge']) ?>">
        </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="pages.php" class="btn btn-secondary">Отмена</a>
      </form>
    </div>
  </div>
</div></div>
  <script>
    CKEDITOR.replace('content', {
      language: 'ru',
      height: 400,
      toolbar: [
        { name: 'basicstyles', items: ['Bold','Italic','Underline','Strike','RemoveFormat'] },
        { name: 'paragraph', items: ['NumberedList','BulletedList','Outdent','Indent','Blockquote'] },
        { name: 'links', items: ['Link','Unlink'] },
        { name: 'insert', items: ['Image','Table','HorizontalRule'] },
        { name: 'styles', items: ['Format'] },
        { name: 'tools', items: ['Maximize'] }
      ],
      format_tags: 'p;h2;h3;h4',
      removePlugins: 'exportpdf',
      contentsCss: '/css/style.css',
      bodyClass: 'content'
    });
  </script>
</body>
</html>
