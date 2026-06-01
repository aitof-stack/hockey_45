<?php
session_start();
if (!isset($_SESSION['news_logged_in']) || $_SESSION['news_logged_in'] !== true) { header('Location: login.php'); exit; }

$pageTitle = 'Медиа';
$file = __DIR__ . '/../data/albums.json';
$albums = json_decode(file_get_contents($file), true);
$type = isset($_GET['type']) && $_GET['type'] === 'video' ? 'video' : 'photo';

// Create album with multiple file upload
$created = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_album'])) {
  $title = trim($_POST['album_title'] ?? '');
  if ($title !== '') {
    $albumType = $_POST['album_type'] ?? 'photo';
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $items = [];
    if (!empty($_FILES['album_files']['name'][0])) {
      $allowed = ['jpg','jpeg','png','gif','webp','mp4','mov','avi','mkv'];
      foreach ($_FILES['album_files']['name'] as $i => $name) {
        if ($_FILES['album_files']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;
        $newName = 'album_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        move_uploaded_file($_FILES['album_files']['tmp_name'][$i], $uploadDir . $newName);
        $items[] = ['file' => '/uploads/' . $newName];
      }
    }
    $maxId = 0;
    foreach ($albums as $a) { if ($a['id'] > $maxId) $maxId = $a['id']; }
    $albums[] = [
      'id' => $maxId + 1,
      'title' => $title,
      'type' => $albumType,
      'date' => date('Y-m-d'),
      'items' => $items
    ];
    file_put_contents($file, json_encode($albums, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $created = true;
  }
}

// Rename album
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_album'])) {
  $rid = (int)($_POST['album_id'] ?? 0);
  $newTitle = trim($_POST['album_title'] ?? '');
  if ($rid && $newTitle !== '') {
    foreach ($albums as &$a) { if ($a['id'] === $rid) { $a['title'] = $newTitle; break; } }
    unset($a);
    file_put_contents($file, json_encode($albums, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: albums.php?type=' . $type . '&renamed=1'); exit;
  }
}

// Delete album
if (isset($_GET['delete'])) {
  $delId = (int)$_GET['delete'];
  $newAlbums = [];
  foreach ($albums as $a) { if ($a['id'] !== $delId) $newAlbums[] = $a; }
  $albums = $newAlbums;
  file_put_contents($file, json_encode($albums, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  header('Location: albums.php?type=' . $type . '&deleted=1'); exit;
}

$filtered = array_filter($albums, function($a) use ($type) { return ($a['type'] ?? 'photo') === $type; });
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Медиа — Медиацентр</title>
  <link rel="stylesheet" href="/css/style.css">
  <style>
    .mc-layout { max-width: 960px; margin: 120px auto 40px; padding: 0 20px; }
    .mc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 10px; }
    .mc-header h1 { font-size: 1.4rem; color: var(--primary-dark); margin: 0; }
    .mc-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 12px; font-size: 0.9rem; font-weight: 700; text-decoration: none; border: 2px solid var(--section-even-fone); background: var(--white); color: var(--primary-dark); transition: all 0.2s; cursor: pointer; }
    .mc-btn:hover { border-color: var(--primary); background: #f0f4f8; }
    .mc-btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
    .mc-btn.primary:hover { background: var(--primary-dark); }
    .mc-btn.danger { border-color: #fca5a5; color: #dc2626; }
    .mc-btn.danger:hover { background: #fef2f2; }

    /* Tabs */
    .mc-tabs { display: flex; gap: 0; margin-bottom: 24px; background: var(--white); border-radius: 14px; overflow: hidden; border: 1px solid var(--section-even-fone); }
    .mc-tabs a { flex: 1; text-align: center; padding: 12px 20px; font-weight: 700; font-size: 0.95rem; text-decoration: none; color: #94a3b8; transition: all 0.2s; }
    .mc-tabs a.active { background: var(--primary); color: #fff; }
    .mc-tabs a:not(.active):hover { background: #f1f5f9; color: var(--primary-dark); }

    .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.9rem; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

    /* Create form */
    .album-form-wrap { background: var(--white); border-radius: 14px; border: 1px solid var(--section-even-fone); padding: 20px; margin-bottom: 24px; display: none; }
    .album-form-wrap.open { display: block; }
    .album-form-wrap h3 { color: var(--primary-dark); margin: 0 0 15px; font-size: 1.1rem; }
    .form-group { margin-bottom: 12px; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 4px; }
    .form-group input, .form-group select { width: 100%; padding: 8px 12px; border: 2px solid var(--section-even-fone); border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; }
    .form-group input:focus, .form-group select:focus { border-color: var(--primary); outline: none; }
    .form-row { display: flex; gap: 15px; }
    .form-row .form-group { flex: 1; }
    .file-info { font-size: 0.8rem; color: #94a3b8; margin-top: 4px; }

    /* Album grid */
    .album-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px; }
    .album-card { background: var(--white); border-radius: 14px; border: 1px solid var(--section-even-fone); overflow: hidden; transition: all 0.2s; }
    .album-card:hover { box-shadow: 0 6px 20px rgba(28,78,122,0.1); transform: translateY(-2px); }
    .album-cover { width: 100%; height: 160px; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.3); font-size: 3rem; position: relative; overflow: hidden; }
    .album-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .album-cover .vid-icon { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3); color: #fff; font-size: 2.5rem; }
    .album-info { padding: 14px 16px; }
    .album-info .album-title { font-weight: 700; color: var(--primary-dark); font-size: 0.95rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .album-info .album-meta { font-size: 0.8rem; color: #94a3b8; margin-top: 3px; }
    .album-actions { margin-top: 8px; display: flex; gap: 6px; }
    .album-actions a { font-size: 0.78rem; padding: 4px 10px; border-radius: 6px; text-decoration: none; border: 1px solid var(--section-even-fone); color: var(--primary); }
    .album-actions a.danger { color: #dc2626; }

    .mc-empty { padding: 40px; text-align: center; color: #94a3b8; background: var(--white); border-radius: 14px; border: 1px solid var(--section-even-fone); }
    @media (max-width: 600px) { .album-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <?php require __DIR__ . '/../inc/header.php'; ?>
  <div class="mc-layout">
    <div class="mc-header">
      <h1>📸 Медиа</h1>
      <div style="display:flex;gap:10px;">
        <button class="mc-btn primary" onclick="toggleForm()">➕ Создать альбом</button>
        <a href="index.php" class="mc-btn">← Назад</a>
      </div>
    </div>

    <!-- Tabs -->
    <div class="mc-tabs">
      <a href="albums.php?type=photo" class="<?= $type === 'photo' ? 'active' : '' ?>">📷 Фото</a>
      <a href="albums.php?type=video" class="<?= $type === 'video' ? 'active' : '' ?>">🎬 Видео</a>
    </div>

    <?php if ($created): ?><div class="alert alert-success">Альбом создан!</div><?php endif; ?>
    <?php if (isset($_GET['renamed'])): ?><div class="alert alert-success">Альбом переименован!</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Альбом удалён!</div><?php endif; ?>

    <!-- Create album form -->
    <div class="album-form-wrap" id="albumForm">
      <h3>Создать <?= $type === 'photo' ? 'фотоальбом' : 'видеоальбом' ?></h3>
      <form method="post" enctype="multipart/form-data" action="albums.php?type=<?= $type ?>">
        <input type="hidden" name="create_album" value="1">
        <input type="hidden" name="album_type" value="<?= $type ?>">
        <div class="form-row">
          <div class="form-group">
            <label>Название альбома</label>
            <input type="text" name="album_title" required placeholder="Введите название">
          </div>
          <div class="form-group">
            <label><?= $type === 'photo' ? 'Фотографии' : 'Видео' ?></label>
            <input type="file" name="album_files[]" multiple accept="<?= $type === 'photo' ? 'image/*' : 'video/*' ?>" required>
            <div class="file-info">Можно выбрать несколько файлов одновременно</div>
          </div>
        </div>
        <button type="submit" class="mc-btn primary">Сохранить альбом</button>
      </form>
    </div>

    <!-- Albums grid -->
    <?php if (empty($filtered)): ?>
      <div class="mc-empty">Нет <?= $type === 'photo' ? 'фотоальбомов' : 'видеоальбомов' ?>. Создайте первый!</div>
    <?php else: ?>
      <div class="album-grid">
        <?php foreach ($filtered as $a):
          $editing = isset($_GET['edit']) && (int)$_GET['edit'] === $a['id'];
        ?>
        <div class="album-card" style="text-decoration:none;color:inherit;display:block;">
          <a href="album-view.php?id=<?= $a['id'] ?>" style="text-decoration:none;color:inherit;display:block;">
          <div class="album-cover">
            <?php if (!empty($a['items'][0]['file'])): ?>
              <?php if ($type === 'photo'): ?>
                <img src="<?= htmlspecialchars($a['items'][0]['file']) ?>" alt="" onerror="this.style.display='none'">
              <?php else: ?>
                <div class="vid-icon">▶</div>
              <?php endif; ?>
            <?php else: ?>
              <span><?= $type === 'photo' ? '🖼' : '🎬' ?></span>
            <?php endif; ?>
          </div>
          </a>
          <div class="album-info">
            <?php if ($editing): ?>
              <form method="post" action="albums.php?type=<?= $type ?>" style="display:flex;gap:6px;">
                <input type="hidden" name="rename_album" value="1">
                <input type="hidden" name="album_id" value="<?= $a['id'] ?>">
                <input type="text" name="album_title" value="<?= htmlspecialchars($a['title']) ?>" required style="flex:1;padding:6px 10px;border:2px solid var(--primary);border-radius:8px;font-size:0.85rem;">
                <button type="submit" class="mc-btn" style="padding:6px 12px;font-size:0.8rem;">💾</button>
                <a href="albums.php?type=<?= $type ?>" class="mc-btn" style="padding:6px 12px;font-size:0.8rem;">✕</a>
              </form>
            <?php else: ?>
              <a href="album-view.php?id=<?= $a['id'] ?>" style="text-decoration:none;color:inherit;display:block;">
              <div class="album-title"><?= htmlspecialchars($a['title']) ?></div>
              <div class="album-meta"><?= count($a['items']) ?> файлов · <?= htmlspecialchars($a['date'] ?? '') ?></div>
              </a>
            <?php endif; ?>
            <div class="album-actions" onclick="event.stopPropagation()">
              <a href="albums.php?type=<?= $type ?>&edit=<?= $a['id'] ?>">✏️</a>
              <a href="albums.php?type=<?= $type ?>&delete=<?= $a['id'] ?>" class="danger" onclick="return confirm('Удалить альбом?')">🗑️</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <script>
    function toggleForm() { document.getElementById('albumForm').classList.toggle('open'); }
    <?php if ($created): ?>document.getElementById('albumForm').classList.remove('open');<?php endif; ?>
  </script>
  <?php require __DIR__ . '/../inc/footer.php'; ?>
</body>
</html>
