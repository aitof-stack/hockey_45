<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }

$file = __DIR__ . '/../data/arenas.json';
$items = json_decode(file_get_contents($file), true);
$editItem = null;
if (isset($_GET['edit'])) {
  $editId = (int)$_GET['edit'];
  foreach ($items as $c) { if ($c['id'] === $editId) { $editItem = $c; break; } }
}
?>
<!DOCTYPE html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Арены — Админ-панель</title><link rel="stylesheet" href="css/admin.css">
</head><body>
<div class="admin-header">
  <h1>🏒 Админ-панель</h1>
  <div class="user-info"><a href="dashboard.php">← Дашборд</a><a href="logout.php">Выйти</a></div>
</div>
<div class="admin-container">
  <div class="page-header"><h2>🏟️ Арены</h2></div>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Сохранено!</div><?php endif; ?>
  <h3 style="margin-bottom:15px;color:#0c4a6e;"><?= $editItem ? 'Редактировать арену' : 'Добавить арену' ?></h3>
  <div class="editor-wrapper" style="margin-bottom:30px;">
    <form action="section-save.php" method="post">
      <input type="hidden" name="type" value="arenas">
      <input type="hidden" name="id" value="<?= $editItem ? $editItem['id'] : 0 ?>">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
        <div class="form-group"><label>Название</label><input type="text" name="name" value="<?= htmlspecialchars($editItem['name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Город</label><input type="text" name="city" value="<?= htmlspecialchars($editItem['city'] ?? '') ?>"></div>
        <div class="form-group"><label>Адрес</label><input type="text" name="address" value="<?= htmlspecialchars($editItem['address'] ?? '') ?>"></div>
        <div class="form-group"><label>Вместимость</label><input type="text" name="capacity" value="<?= htmlspecialchars($editItem['capacity'] ?? '') ?>"></div>
        <div class="form-group" style="grid-column:1/-1;"><label>Фото (URL)</label><input type="text" name="photo" value="<?= htmlspecialchars($editItem['photo'] ?? '') ?>"></div>
      </div>
      <div class="form-group"><label>Описание</label><textarea name="description" rows="3"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea></div>
      <button type="submit" class="btn btn-primary">Сохранить</button>
      <?php if ($editItem): ?><a href="arenas.php" class="btn btn-secondary">Отмена</a><?php endif; ?>
    </form>
  </div>
  <table class="table">
    <thead><tr><th>ID</th><th>Название</th><th>Город</th><th>Вместимость</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($items as $c): ?>
      <tr><td><?= $c['id'] ?></td><td><?= htmlspecialchars($c['name']) ?></td><td><?= htmlspecialchars($c['city']) ?></td><td><?= htmlspecialchars($c['capacity']) ?></td>
        <td class="actions">
          <a href="arenas.php?edit=<?= $c['id'] ?>" class="btn btn-primary btn-sm">✏️</a>
          <a href="section-delete.php?type=arenas&id=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить?')">🗑️</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body></html>
