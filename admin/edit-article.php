<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Handle image upload (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_article_image']) && isset($_FILES['article_image'])) {
    if ($_FILES['article_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['article_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) { echo 'err:invalid'; exit; }
        $newName = 'news_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($_FILES['article_image']['tmp_name'], $uploadDir . $newName)) {
            echo '/uploads/' . $newName;
            exit;
        }
    }
    echo 'err:' . ($_FILES['article_image']['error'] ?? 'no_file');
    exit;
}

$articles = json_decode(file_get_contents(__DIR__ . '/../data/articles.json'), true);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = null;

if ($id) {
    foreach ($articles as $a) {
        if ($a['id'] === $id) {
            $article = $a;
            break;
        }
    }
}

if (!$article) {
    $maxId = 0;
    foreach ($articles as $a) {
        if ($a['id'] > $maxId) $maxId = $a['id'];
    }
    $article = [
        'id' => $maxId + 1,
        'title' => '',
        'date' => date('Y-m-d'),
        'anons' => '',
        'content' => '',
        'image' => ''
    ];
}

$isNew = !$id;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $isNew ? 'Новая' : 'Редактировать' ?> новость</title>
  <link rel="stylesheet" href="css/admin.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.25.1-lts/ckeditor.js"></script>
</head>
<body>
<div class="admin-layout"><?php include __DIR__ . '/inc/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-container">
    <div class="page-header">
      <h2><?= $isNew ? '📰 Новая новость' : '📰 Редактировать: ' . htmlspecialchars($article['title']) ?></h2>
    </div>
    <div class="editor-wrapper">
      <form action="save-article.php" method="post">
        <input type="hidden" name="id" value="<?= $article['id'] ?>">
        <div class="form-group">
          <label for="title">Заголовок</label>
          <input type="text" id="title" name="title" value="<?= htmlspecialchars($article['title']) ?>" required>
        </div>
        <div class="form-group">
          <label for="date">Дата</label>
          <input type="date" id="date" name="date" value="<?= htmlspecialchars($article['date']) ?>" required>
        </div>
        <div class="form-group">
          <label for="anons">Анонс</label>
          <textarea id="anons" name="anons" rows="2"><?= htmlspecialchars($article['anons']) ?></textarea>
        </div>
        <div class="form-group">
          <label for="content">Содержание</label>
          <textarea id="content" name="content"><?= htmlspecialchars($article['content']) ?></textarea>
        </div>
        <div class="form-group">
          <label for="image">Изображение (URL или загрузить)</label>
          <div style="display:flex;gap:8px;margin-bottom:5px;">
            <input type="text" id="image" name="image" value="<?= htmlspecialchars($article['image'] ?? '') ?>" placeholder="https://..." style="flex:1;">
            <input type="file" id="news_image_upload" accept="image/*" style="display:none;" onchange="uploadNewsImage(this)">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('news_image_upload').click()">📁 Выбрать</button>
          </div>
          <div id="news_image_preview"><?php if (!empty($article['image'])): ?><img src="<?= htmlspecialchars($article['image']) ?>" style="max-width:120px;max-height:90px;border-radius:8px;border:1px solid #e2e8f0;object-fit:cover;" onerror="this.outerHTML='<span style=\'color:#ef4444;font-size:0.8rem;\'>⚠ не загружается</span>'"><?php endif; ?></div>
        </div>
        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="articles.php" class="btn btn-secondary">Отмена</a>
      </form>
    </div>
  </div>
</div></div>
  <script>
    CKEDITOR.replace('content', {
      language: 'ru',
      height: 300,
      toolbar: [
        { name: 'basicstyles', items: ['Bold','Italic','Underline','Strike','RemoveFormat'] },
        { name: 'paragraph', items: ['NumberedList','BulletedList','Outdent','Indent','Blockquote'] },
        { name: 'links', items: ['Link','Unlink'] },
        { name: 'insert', items: ['Image','Table','HorizontalRule'] },
        { name: 'styles', items: ['Format'] },
        { name: 'tools', items: ['Maximize'] }
      ],
      format_tags: 'p;h2;h3;h4',
      removePlugins: 'exportpdf'
    });

    function uploadNewsImage(input) {
      var file = input.files[0];
      if (!file) return;
      var formData = new FormData();
      formData.append('article_image', file);
      formData.append('upload_article_image', '1');
      fetch('edit-article.php', { method: 'POST', body: formData })
        .then(function(r) { return r.text(); })
        .then(function(url) {
          if (url && url.indexOf('err:') !== 0) {
            document.getElementById('image').value = url;
            document.getElementById('news_image_preview').innerHTML = '<img src="' + url + '" style="max-width:120px;max-height:90px;border-radius:8px;object-fit:cover;">';
          } else {
            var msg = url || 'Неизвестная ошибка';
            if (msg.indexOf('err:') === 0) msg = 'Ошибка ' + msg.substring(4);
            alert('Ошибка загрузки: ' + msg);
          }
        })
        .catch(function() { alert('Ошибка загрузки'); });
      input.value = '';
    }
  </script>
</body>
</html>
