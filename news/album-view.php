<?php
session_start();
if (!isset($_SESSION['news_logged_in']) || $_SESSION['news_logged_in'] !== true) { header('Location: login.php'); exit; }

$pageTitle = 'Альбом';
$file = __DIR__ . '/../data/albums.json';
$albums = json_decode(file_get_contents($file), true);
$id = (int)($_GET['id'] ?? 0);
$album = null;
foreach ($albums as $a) { if ($a['id'] === $id) { $album = $a; break; } }

if (!$album) { header('Location: albums.php'); exit; }

// Add more files to album
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_files'])) {
  $uploadDir = __DIR__ . '/../uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
  $allowed = ['jpg','jpeg','png','gif','webp','mp4','mov','avi','mkv'];
  if (!empty($_FILES['more_files']['name'][0])) {
    $changed = false;
    foreach ($_FILES['more_files']['name'] as $i => $name) {
      if ($_FILES['more_files']['error'][$i] !== UPLOAD_ERR_OK) continue;
      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed)) continue;
      $newName = 'album_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      move_uploaded_file($_FILES['more_files']['tmp_name'][$i], $uploadDir . $newName);
      foreach ($albums as &$aa) { if ($aa['id'] === $id) { $aa['items'][] = ['file' => '/uploads/' . $newName]; $changed = true; break; } }
      unset($aa);
    }
    if ($changed) {
      file_put_contents($file, json_encode($albums, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
      $album = null; foreach ($albums as $a) { if ($a['id'] === $id) { $album = $a; break; } }
    }
  }
  header('Location: album-view.php?id=' . $id . '&uploaded=1'); exit;
}

// Delete item from album
if (isset($_GET['delitem'])) {
  $idx = (int)$_GET['delitem'];
  foreach ($albums as &$aa) {
    if ($aa['id'] === $id && isset($aa['items'][$idx])) {
      array_splice($aa['items'], $idx, 1);
      file_put_contents($file, json_encode($albums, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
      break;
    }
  }
  unset($aa);
  header('Location: album-view.php?id=' . $id . '&deleted=1'); exit;
}

$type = $album['type'] ?? 'photo';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($album['title']) ?> — Медиацентр</title>
  <link rel="stylesheet" href="/css/style.css">
  <style>
    .mc-layout { max-width: 960px; margin: 120px auto 40px; padding: 0 20px; }
    .mc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 10px; }
    .mc-header h1 { font-size: 1.4rem; color: var(--primary-dark); margin: 0; }
    .mc-header .sub { font-size: 0.85rem; color: #94a3b8; margin-top: 3px; }
    .mc-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 12px; font-size: 0.9rem; font-weight: 700; text-decoration: none; border: 2px solid var(--section-even-fone); background: var(--white); color: var(--primary-dark); transition: all 0.2s; cursor: pointer; }
    .mc-btn:hover { border-color: var(--primary); background: #f0f4f8; }
    .mc-btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
    .mc-btn.primary:hover { background: var(--primary-dark); }
    .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.9rem; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

    .add-form { background: var(--white); border-radius: 14px; border: 1px solid var(--section-even-fone); padding: 20px; margin-bottom: 24px; }
    .add-form h3 { color: var(--primary-dark); margin: 0 0 15px; font-size: 1.1rem; }
    .form-group { margin-bottom: 12px; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 4px; }
    .form-group input { width: 100%; padding: 8px 12px; border: 2px solid var(--section-even-fone); border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; }
    .form-group input:focus { border-color: var(--primary); outline: none; }
    .file-info { font-size: 0.8rem; color: #94a3b8; margin-top: 4px; }

    .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
    .media-item { position: relative; background: var(--white); border-radius: 12px; border: 1px solid var(--section-even-fone); overflow: hidden; }
    .media-item img { width: 100%; height: 160px; object-fit: cover; display: block; }
    .media-item video { width: 100%; height: 160px; object-fit: cover; display: block; background: #000; }
    .media-item .del-overlay { position: absolute; top: 6px; right: 6px; opacity: 0; transition: opacity 0.2s; }
    .media-item:hover .del-overlay { opacity: 1; }
    .media-item .del-overlay a { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: rgba(220,38,38,0.9); color: #fff; text-decoration: none; font-size: 0.8rem; }
    .media-item .del-overlay a:hover { background: #dc2626; }

    .mc-empty { padding: 40px; text-align: center; color: #94a3b8; background: var(--white); border-radius: 14px; border: 1px solid var(--section-even-fone); }
    @media (max-width: 600px) { .media-grid { grid-template-columns: repeat(2, 1fr); } }
  </style>
</head>
<body>
  <?php require __DIR__ . '/../inc/header.php'; ?>
  <div class="mc-layout">
    <div class="mc-header">
      <div>
        <h1><?= htmlspecialchars($album['title']) ?></h1>
        <div class="sub"><?= count($album['items']) ?> файлов · <?= htmlspecialchars($album['date'] ?? '') ?></div>
      </div>
      <a href="albums.php?type=<?= $type ?>" class="mc-btn">← К альбомам</a>
    </div>

    <?php if (isset($_GET['uploaded'])): ?><div class="alert alert-success">Файлы добавлены!</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Файл удалён!</div><?php endif; ?>

    <!-- Add more files -->
    <div class="add-form">
      <h3>Добавить файлы</h3>
      <form method="post" enctype="multipart/form-data" action="album-view.php?id=<?= $id ?>">
        <input type="hidden" name="add_files" value="1">
        <div class="form-group">
          <input type="file" name="more_files[]" multiple accept="<?= $type === 'photo' ? 'image/*' : 'video/*' ?>" required>
          <div class="file-info">Можно выбрать несколько файлов одновременно</div>
        </div>
        <button type="submit" class="mc-btn primary">Загрузить</button>
      </form>
    </div>

    <!-- Media grid -->
    <?php if (empty($album['items'])): ?>
      <div class="mc-empty">В альбоме пока нет файлов. Загрузите первые!</div>
    <?php else: ?>
      <div class="media-grid">
        <?php foreach ($album['items'] as $idx => $item): ?>
        <div class="media-item">
          <?php if ($type === 'photo'): ?>
            <img src="<?= htmlspecialchars($item['file']) ?>" alt="" onerror="this.outerHTML='<div style=\'height:160px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;\'>ошибка</div>'">
          <?php else: ?>
            <video src="<?= htmlspecialchars($item['file']) ?>" controls></video>
          <?php endif; ?>
          <div class="del-overlay">
            <a href="album-view.php?id=<?= $id ?>&delitem=<?= $idx ?>" onclick="return confirm('Удалить этот файл?')" title="Удалить">✕</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php require __DIR__ . '/../inc/footer.php'; ?>
</body>
</html>
