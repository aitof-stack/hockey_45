<?php
$clubs = json_decode(file_get_contents(__DIR__ . '/../../../data/clubs.json'), true);
$articles = json_decode(file_get_contents(__DIR__ . '/../../../data/articles.json'), true);
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selectedArticle = null;
if ($articleId) {
  foreach ($articles as $a) { if ($a['id'] === $articleId) { $selectedArticle = $a; break; } }
}
$pageTitle = 'Новости';
?><?php require __DIR__ . '/../../../inc/header.php'; ?>
<style>
    .news-list { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 1100px; margin: 0 auto; }
    .news-card-item { background: var(--white); border-radius: 14px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid var(--section-even-fone); transition: transform 0.2s; display: flex; flex-direction: column; }
    .news-card-item:hover { transform: translateY(-2px); }
    .news-card-item .card-img { width: 100%; height: 180px; background-size: cover; background-position: center; flex-shrink: 0; }
    .news-card-item .card-body { padding: 15px; flex: 1; display: flex; flex-direction: column; }
    .news-card-item h2 { margin: 0 0 5px; font-size: 1.1rem; }
    .news-card-item h2 a { color: var(--primary-dark); text-decoration: none; }
    .news-card-item h2 a:hover { color: var(--primary); }
    .news-card-item .date { font-size: 0.8rem; color: #94a3b8; margin-bottom: 8px; }
    .news-card-item p { color: #475569; line-height: 1.4; margin: 0; font-size: 0.9rem; flex: 1; }
    .news-card-item .read-more { display: inline-block; margin-top: auto; padding-top: 10px; color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.9rem; }
    .news-card-item .read-more:hover { text-decoration: underline; }
    @media (max-width: 768px) { .news-list { grid-template-columns: 1fr; } }
    @media (min-width: 769px) and (max-width: 1024px) { .news-list { grid-template-columns: repeat(2, 1fr); } }
    .empty-msg { text-align: center; padding: 60px 20px; color: #94a3b8; }
    .empty-msg h2 { color: #64748b; }
    .article-detail { max-width: 800px; margin: 0 auto; }
    .article-detail .back-link { display: inline-block; margin-bottom: 20px; color: var(--primary); text-decoration: none; font-weight: 600; }
    .article-detail .back-link:hover { text-decoration: underline; }
    .article-detail h1 { color: var(--primary-dark); margin-bottom: 5px; }
    .article-detail .date { color: #94a3b8; font-size: 0.9rem; margin-bottom: 20px; }
    .article-detail .content { background: var(--white); border-radius: 14px; padding: 25px; border: 1px solid var(--section-even-fone); line-height: 1.7; }
  </style>

  <main class="content">
    <section>
      <?php if ($selectedArticle): ?>
        <div class="article-detail">
          <a href="/sport/list/1/" class="back-link">← Все новости</a>
          <h1><?= htmlspecialchars($selectedArticle['title']) ?></h1>
          <div class="date"><?= htmlspecialchars($selectedArticle['date'] ?? '') ?></div>
          <?php if (!empty($selectedArticle['image'])): ?>
            <img src="<?= htmlspecialchars($selectedArticle['image']) ?>" style="width:100%;max-height:300px;object-fit:cover;border-radius:14px;margin-bottom:20px;">
          <?php endif; ?>
          <div class="content"><?= $selectedArticle['content'] ?? '' ?></div>
        </div>
      <?php else: ?>
      <h1>Новости</h1>
      <?php if (empty($articles)): ?>
        <div class="empty-msg"><h2>Новостей пока нет</h2><p>Следите за обновлениями!</p></div>
      <?php else: ?>
        <div class="news-list">
          <?php foreach (array_reverse($articles) as $a): ?>
            <article class="news-card-item">
              <?php if (!empty($a['image'])): ?>
                <div class="card-img" style="background-image: url('<?= htmlspecialchars($a['image']) ?>')"></div>
              <?php else: ?>
                <div class="card-img" style="background: linear-gradient(135deg, var(--primary-dark), var(--primary));"></div>
              <?php endif; ?>
              <div class="card-body">
                <h2><a href="/sport/list/1/?id=<?= $a['id'] ?>"><?= htmlspecialchars($a['title']) ?></a></h2>
                <div class="date"><?= htmlspecialchars($a['date'] ?? '') ?></div>
                <p><?= nl2br(htmlspecialchars($a['anons'] ?? '')) ?></p>
                <a href="/sport/list/1/?id=<?= $a['id'] ?>" class="read-more">Читать далее →</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>

<?php require __DIR__ . '/../../../inc/footer.php'; ?>
