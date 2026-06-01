<?php
$pages = json_decode(file_get_contents(__DIR__ . '/../../data/pages.json'), true);
$page = $pages[$pageKey] ?? ['title' => '', 'content' => ''];
$pageTitle = $page['title'];
?>
<?php require __DIR__ . '/../../inc/header.php'; ?>
    <main class="content">
      <section>
        <h2><?= htmlspecialchars($page['title']) ?></h2>
        <?= $page['content'] ?>
      </section>
    </main>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
