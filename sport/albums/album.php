<?php
$albums = json_decode(file_get_contents(__DIR__ . '/../../data/albums.json'), true);
$id = (int)($_GET['id'] ?? 0);
$album = null;
foreach ($albums as $a) { if ($a['id'] === $id) { $album = $a; break; } }

if (!$album) {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>Альбом не найден</h1>';
    exit;
}

$pageTitle = $album['title'];
$isPhoto = ($album['type'] ?? 'photo') === 'photo';
require __DIR__ . '/../../inc/header.php';
?>
<main class="content">
<a href="<?= $isPhoto ? '/sport/albums/' : '/sport/videos/' ?>" class="back-link">← К <?= $isPhoto ? 'фотоальбомам' : 'видео' ?></a>
<h1><?= htmlspecialchars($album['title']) ?></h1>
<p style="color:#94a3b8;margin:-10px 0 10px 20px;font-size:0.9rem;"><?= count($album['items']) ?> файлов · <?= htmlspecialchars($album['date'] ?? '') ?></p>

<?php if (empty($album['items'])): ?>
  <p style="text-align:center;color:#94a3b8;padding:40px 20px;">В альбоме пока нет файлов.</p>
<?php elseif ($isPhoto): ?>
  <div class="photo-grid">
    <?php foreach ($album['items'] as $item): ?>
    <a href="<?= htmlspecialchars($item['file']) ?>" target="_blank">
      <img src="<?= htmlspecialchars($item['file']) ?>" alt="" loading="lazy">
    </a>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div style="max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:16px;padding:0 20px;">
    <?php foreach ($album['items'] as $item): ?>
    <video src="<?= htmlspecialchars($item['file']) ?>" controls preload="metadata" style="width:100%;border-radius:12px;background:#000;"></video>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
