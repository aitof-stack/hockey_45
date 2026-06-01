<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }

$file = __DIR__ . '/../data/media.json';
$items = json_decode(file_get_contents($file), true);
$editItem = null;
if (isset($_GET['edit'])) {
  $editId = (int)$_GET['edit'];
  foreach ($items as $c) { if ($c['id'] === $editId) { $editItem = $c; break; } }
}

// Загрузка файла
$uploadMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
  $uploadDir = __DIR__ . '/../uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
  $ext = strtolower(pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION));
  $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  move_uploaded_file($_FILES['media_file']['tmp_name'], $uploadDir . $newName);
  $url = '/uploads/' . $newName;
  // Auto-add entry to media.json
  $maxId = 0;
  foreach ($items as $item) { if ($item['id'] > $maxId) $maxId = $item['id']; }
  $items[] = [
    'id' => $maxId + 1,
    'title' => $_FILES['media_file']['name'],
    'type' => in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'photo' : 'video',
    'file' => $url,
    'date' => date('Y-m-d')
  ];
  file_put_contents($file, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  $uploadMsg = $url;
}
?>
<!DOCTYPE html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Медиа — Админ-панель</title><link rel="stylesheet" href="css/admin.css">
</head><body>
<div class="admin-layout"><?php include __DIR__ . '/inc/sidebar.php'; ?>
<div class="admin-main">
<div class="admin-container">
  <div class="page-header"><h2>📸 Медиатека</h2></div>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Сохранено!</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Удалено!</div><?php endif; ?>

  <div class="editor-wrapper" style="margin-bottom:20px;">
    <h3 style="color:#0c4a6e;margin-bottom:15px;">📤 Загрузить файл</h3>
    <form method="post" enctype="multipart/form-data" action="media.php">
      <div class="form-group">
        <input type="file" name="media_file" required>
      </div>
      <button type="submit" class="btn btn-primary">Загрузить</button>
    </form>
    <?php if ($uploadMsg): ?>
      <div class="alert alert-success" style="margin-top:10px;">Файл загружен: <code><?= $uploadMsg ?></code> <button class="btn btn-sm btn-secondary" onclick="navigator.clipboard.writeText('<?= $uploadMsg ?>')">Копировать</button></div>
    <?php endif; ?>
  </div>

  <h3 style="margin-bottom:15px;color:#0c4a6e;"><?= $editItem ? 'Редактировать' : 'Добавить запись' ?></h3>
  <div class="editor-wrapper" style="margin-bottom:30px;">
    <form action="section-save.php" method="post">
      <input type="hidden" name="type" value="media">
      <input type="hidden" name="id" value="<?= $editItem ? $editItem['id'] : 0 ?>">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
        <div class="form-group"><label>Название</label><input type="text" name="title" value="<?= htmlspecialchars($editItem['title'] ?? '') ?>" required></div>
        <div class="form-group"><label>Тип</label>
          <select name="type">
            <option value="photo" <?= ($editItem['type']??'')=='photo'?'selected':'' ?>>Фото</option>
            <option value="video" <?= ($editItem['type']??'')=='video'?'selected':'' ?>>Видео</option>
          </select>
        </div>
        <div class="form-group"><label>Дата</label><input type="date" name="date" value="<?= htmlspecialchars($editItem['date'] ?? date('Y-m-d')) ?>"></div>
        <div class="form-group"><label>Файл (URL)</label><input type="text" name="file" value="<?= htmlspecialchars($editItem['file'] ?? '') ?>"></div>
      </div>
      <div class="form-group"><label>Описание</label><textarea name="description" rows="2"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea></div>
      <button type="submit" class="btn btn-primary">Сохранить</button>
      <?php if ($editItem): ?><a href="media.php" class="btn btn-secondary">Отмена</a><?php endif; ?>
    </form>
  </div>

  <table class="table">
    <thead><tr><th>ID</th><th>Файл</th><th>Название</th><th>Тип</th><th>Дата</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($items as $c): ?>
      <tr>
        <td><?= $c['id'] ?></td>
        <td><?php if ($c['type']==='photo' && $c['file']): ?><img src="<?= htmlspecialchars($c['file']) ?>" style="width:60px;height:40px;object-fit:cover;border-radius:5px;"><?php else: ?><span style="color:#999;">—</span><?php endif; ?></td>
        <td><?= htmlspecialchars($c['title']) ?></td>
        <td><?= $c['type']==='video'?'🎬':'📷' ?></td>
        <td><?= htmlspecialchars($c['date']) ?></td>
        <td class="actions">
          <a href="media.php?edit=<?= $c['id'] ?>" class="btn btn-primary btn-sm">✏️</a>
          <a href="section-delete.php?type=media&id=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить?')">🗑️</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div></div>
</body></html>
