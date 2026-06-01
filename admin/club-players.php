<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }

$file = __DIR__ . '/../data/clubs.json';
$clubs = json_decode(file_get_contents($file), true);
$clubId = (int)($_GET['club_id'] ?? 0);
$club = null;
foreach ($clubs as $c) { if ($c['id'] === $clubId) { $club = $c; break; } }
if (!$club) { header('Location: clubs.php'); exit; }

// Удаление игрока
if (isset($_GET['delete_player'])) {
  $delIdx = (int)$_GET['delete_player'];
  if (isset($club['players'][$delIdx])) {
    array_splice($club['players'], $delIdx, 1);
    foreach ($clubs as &$cc) { if ($cc['id'] === $clubId) { $cc['players'] = $club['players']; break; } }
    file_put_contents($file, json_encode($clubs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  }
  header('Location: club-players.php?club_id=' . $clubId . '&deleted=1');
  exit;
}
?>
<!DOCTYPE html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Игроки — <?= htmlspecialchars($club['name']) ?></title><link rel="stylesheet" href="css/admin.css">
</head><body>
<div class="admin-header">
  <h1>🏒 Админ-панель</h1>
  <div class="user-info"><a href="clubs.php">← Команды</a><a href="logout.php">Выйти</a></div>
</div>
<div class="admin-container">
  <div class="page-header">
    <h2>🏒 Игроки: <?= htmlspecialchars($club['name']) ?> (<?= htmlspecialchars($club['city']) ?>)</h2>
  </div>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Игрок сохранён!</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Игрок удалён!</div><?php endif; ?>

  <h3 style="color:#0c4a6e;margin-bottom:15px;"><?= isset($_GET['edit_player']) ? 'Редактировать игрока' : 'Добавить игрока' ?></h3>
  <div class="editor-wrapper" style="margin-bottom:30px;">
    <?php
      $editPlayer = null;
      $editKey = isset($_GET['edit_player']) ? $_GET['edit_player'] : '';
      if ($editKey !== '') foreach ($club['players'] as $idx => $p) {
        if ((string)$idx === $editKey || (string)$p['number'] === $editKey) { $editPlayer = $p; break; }
      }
    ?>
    <form action="club-player-save.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="club_id" value="<?= $clubId ?>">
      <input type="hidden" name="old_number" value="<?= $editPlayer ? htmlspecialchars($editPlayer['number'] ?? '') : '' ?>">
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:15px;">
        <div class="form-group"><label>Игровой номер (необязательно для тренеров)</label><input type="number" name="number" value="<?= $editPlayer['number'] ?? '' ?>"></div>
        <div class="form-group"><label>ФИО</label><input type="text" name="fio" value="<?= htmlspecialchars($editPlayer['fio'] ?? '') ?>" required></div>
        <div class="form-group"><label>Амплуа</label>
          <select name="position">
            <option value="Тренер" <?= ($editPlayer['position']??'')=='Тренер'?'selected':'' ?>>Тренер</option>
            <option value="Помощник тренера" <?= ($editPlayer['position']??'')=='Помощник тренера'?'selected':'' ?>>Помощник тренера</option>
            <option value="Вратарь" <?= ($editPlayer['position']??'')=='Вратарь'?'selected':'' ?>>Вратарь</option>
            <option value="Защитник" <?= ($editPlayer['position']??'')=='Защитник'?'selected':'' ?>>Защитник</option>
            <option value="Нападающий" <?= ($editPlayer['position']??'')=='Нападающий'?'selected':'' ?>>Нападающий</option>
          </select>
        </div>
        <div class="form-group"><label>Дата рождения</label><input type="date" name="birth_date" value="<?= htmlspecialchars($editPlayer['birth_date'] ?? '') ?>"></div>
        <div class="form-group"><label>Рост (см)</label><input type="number" name="height" value="<?= $editPlayer['height'] ?? '' ?>"></div>
        <div class="form-group"><label>Вес (кг)</label><input type="number" name="weight" value="<?= $editPlayer['weight'] ?? '' ?>"></div>
        <div class="form-group"><label>Хват клюшки</label>
          <select name="grip">
            <option value="Левый" <?= ($editPlayer['grip']??'')=='Левый'?'selected':'' ?>>Левый</option>
            <option value="Правый" <?= ($editPlayer['grip']??'')=='Правый'?'selected':'' ?>>Правый</option>
          </select>
        </div>
        <div class="form-group"><label>Фото (URL)</label><input type="text" name="photo" value="<?= htmlspecialchars($editPlayer['photo'] ?? '') ?>"></div>
      </div>
      <button type="submit" class="btn btn-primary">Сохранить игрока</button>
      <?php if ($editPlayer): ?><a href="club-players.php?club_id=<?= $clubId ?>" class="btn btn-secondary">Отмена</a><?php endif; ?>
    </form>
  </div>

  <table class="table">
    <thead><tr><th>#</th><th>Фото</th><th>ФИО</th><th>Амплуа</th><th>Дата рожд.</th><th>Рост</th><th>Вес</th><th>Хват</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($club['players'] as $idx => $p): ?>
      <tr>
        <td><strong><?= $p['number'] ?></strong></td>
        <td><?php if ($p['photo']): ?><img src="<?= htmlspecialchars($p['photo']) ?>" style="width:40px;height:50px;object-fit:cover;border-radius:5px;"><?php endif; ?></td>
        <td><?= htmlspecialchars($p['fio']) ?></td>
        <td><?= htmlspecialchars($p['position']) ?></td>
        <td><?= htmlspecialchars($p['birth_date'] ?? '') ?></td>
        <td><?= $p['height'] ?? '' ?></td>
        <td><?= $p['weight'] ?? '' ?></td>
        <td><?= htmlspecialchars($p['grip'] ?? '') ?></td>
        <td class="actions">
          <a href="club-players.php?club_id=<?= $clubId ?>&edit_player=<?= $idx ?>" class="btn btn-primary btn-sm">✏️</a>
          <a href="club-players.php?club_id=<?= $clubId ?>&delete_player=<?= $idx ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить игрока?')">🗑️</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($club['players'])): ?>
      <tr><td colspan="9" style="text-align:center;color:#999;">Нет игроков</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body></html>
