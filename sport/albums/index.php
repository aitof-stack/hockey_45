<?php
$pageTitle = 'Фотоальбомы';
$albums = json_decode(file_get_contents(__DIR__ . '/../../data/albums.json'), true);
$photoAlbums = array_filter($albums, function($a) { return ($a['type'] ?? 'photo') === 'photo'; });
$albumIds = []; $albumPhotos = [];
foreach ($photoAlbums as $a) {
  $albumIds[] = $a['id'];
  $photos = array_column($a['items'] ?? [], 'file');
  $albumPhotos[$a['id']] = $photos;
}
require __DIR__ . '/../../inc/header.php';
?>
<main class="content">
<h1>Фотоальбомы</h1>
<?php if (empty($photoAlbums)): ?>
  <p style="text-align:center;color:#94a3b8;padding:40px 20px;">Фотоальбомов пока нет.</p>
<?php else: ?>
  <div class="album-grid" id="albumGrid">
    <?php foreach ($photoAlbums as $a):
      $photos = array_column($a['items'] ?? [], 'file');
    ?>
    <a href="album.php?id=<?= $a['id'] ?>" class="album-card" data-photos='<?= json_encode($photos) ?>'>
      <div class="album-cover">
        <?php if (!empty($photos[0])): ?>
          <img class="album-cover-img" src="<?= htmlspecialchars($photos[0]) ?>" alt="" loading="lazy">
        <?php else: ?>
          <span>🖼</span>
        <?php endif; ?>
      </div>
      <div class="album-info">
        <div class="album-title"><?= htmlspecialchars($a['title']) ?></div>
        <div class="album-meta"><?= count($photos) ?> фото</div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</main>
<script>
(function() {
  var cards = document.querySelectorAll('#albumGrid .album-card');
  cards.forEach(function(card) {
    var photos = [];
    try { photos = JSON.parse(card.getAttribute('data-photos') || '[]'); } catch(e) {}
    if (photos.length < 2) return;
    var img = card.querySelector('.album-cover-img');
    if (!img) return;
    var idx = 0;
    setInterval(function() {
      idx = (idx + 1) % photos.length;
      img.src = photos[idx];
    }, 5000);
  });
})();
</script>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
