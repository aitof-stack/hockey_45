<?php
$pageTitle = 'Видеоролики';
$albums = json_decode(file_get_contents(__DIR__ . '/../../data/albums.json'), true);
$videoAlbums = array_filter($albums, function($a) { return ($a['type'] ?? 'photo') === 'video'; });
require __DIR__ . '/../../inc/header.php';
?>
<main class="content">
<h1>Видеоролики</h1>
<?php if (empty($videoAlbums)): ?>
  <p style="text-align:center;color:#94a3b8;padding:40px 20px;">Видеоальбомов пока нет.</p>
<?php else: ?>
  <div class="album-grid">
    <?php foreach ($videoAlbums as $a): ?>
    <a href="/sport/albums/album.php?id=<?= $a['id'] ?>" class="album-card">
      <div class="album-cover">
        <?php if (!empty($a['items'][0]['file'])): ?>
          <video src="<?= htmlspecialchars($a['items'][0]['file']) ?>" muted preload="metadata" style="width:100%;height:100%;object-fit:cover;" onloadedmetadata="this.pause()"></video>
        <?php endif; ?>
        <div class="play-overlay">▶</div>
      </div>
      <div class="album-info">
        <div class="album-title"><?= htmlspecialchars($a['title']) ?></div>
        <div class="album-meta"><?= count($a['items']) ?> видео</div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
