<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }

$file = __DIR__ . '/../data/clubs.json';
$clubs = json_decode(file_get_contents($file), true);
$editClub = null;
$currentPlayers = [];
$isNew = false;
$uploadMsg = '';

  // Handle photo upload for players (AJAX)
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_player_photo']) && isset($_FILES['player_photo'])) {
    if ($_FILES['player_photo']['error'] === UPLOAD_ERR_OK) {
      $uploadDir = __DIR__ . '/../uploads/';
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
      $ext = strtolower(pathinfo($_FILES['player_photo']['name'], PATHINFO_EXTENSION));
      $newName = 'player_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      if (move_uploaded_file($_FILES['player_photo']['tmp_name'], $uploadDir . $newName)) {
        echo '/uploads/' . $newName;
        exit;
      }
    }
    echo 'err:' . ($_FILES['player_photo']['error'] ?? 'no_file');
    exit;
  }

  // Handle logo upload (AJAX)
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_logo_file']) && isset($_FILES['logo_file'])) {
    if ($_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
      $uploadDir = __DIR__ . '/../uploads/';
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
      $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','gif','webp','svg'];
      if (!in_array($ext, $allowed)) { echo 'err:invalid'; exit; }
      $newName = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $uploadDir . $newName)) {
        echo '/uploads/' . $newName;
        exit;
      }
    }
    echo 'err:' . $_FILES['logo_file']['error'];
    exit;
  }

if (isset($_GET['edit'])) {
  $editId = (int)$_GET['edit'];
  if ($editId === 0) { $isNew = true; $editClub = ['id'=>0,'name'=>'','city'=>'','region'=>'','website'=>'','logo'=>'','description'=>'','players'=>[]]; }
  else { foreach ($clubs as $c) { if ($c['id'] === $editId) { $editClub = $c; $currentPlayers = $c['players'] ?? []; break; } } }
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_club'])) {
  $id = (int)($_POST['id'] ?? 0);
  $name = $_POST['name'] ?? '';
  if (!$name) { header('Location: clubs.php'); exit; }

  // Upload logo
  $logoPath = $_POST['logo'] ?? '';

  $clubData = [
    'id' => $id > 0 ? $id : 0,
    'name' => $name,
    'city' => $_POST['city'] ?? '',
    'region' => $_POST['region'] ?? '',
    'website' => $_POST['website'] ?? '',
    'logo' => $logoPath,
    'description' => $_POST['description'] ?? '',
    'players' => []
  ];

  // Parse players from JSON string
  if (!empty($_POST['players_json'])) {
    $parsed = json_decode($_POST['players_json'], true);
    if (is_array($parsed)) $clubData['players'] = $parsed;
  }

  if ($id > 0) {
    foreach ($clubs as &$c) {
      if ($c['id'] === $id) {
        $c = $clubData;
        break;
      }
    }
    unset($c);
  } else {
    $maxId = 0;
    foreach ($clubs as $c) { if ($c['id'] > $maxId) $maxId = $c['id']; }
    $clubData['id'] = $maxId + 1;
    $clubs[] = $clubData;
  }

  file_put_contents($file, json_encode($clubs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  $savedId = $clubData['id'];
  header('Location: clubs.php?edit=' . $savedId . '&saved=1');
  exit;
}

// Handle delete player from club
if (isset($_GET['delete_player']) && (isset($_GET['club_id']) || isset($_GET['edit']))) {
  $clubId = isset($_GET['club_id']) ? (int)$_GET['club_id'] : (int)$_GET['edit'];
  $playerIdx = (int)$_GET['delete_player'];
  foreach ($clubs as &$c) {
    if ($c['id'] === $clubId) {
      if (isset($c['players'][$playerIdx])) {
        array_splice($c['players'], $playerIdx, 1);
      }
      break;
    }
  }
  unset($c);
  file_put_contents($file, json_encode($clubs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  header('Location: clubs.php?edit=' . $clubId . '&deleted=1');
  exit;
}
?>
<!DOCTYPE html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Команды — Админ-панель</title><link rel="stylesheet" href="css/admin.css">
<style>
.player-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin: 15px 0; }
.player-card { background: #fff; border-radius: 14px; padding: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); border: 1px solid #e0f2fe; text-align: center; position: relative; transition: all 0.2s; }
.player-card:hover { box-shadow: 0 6px 20px rgba(2,132,199,0.1); transform: translateY(-2px); }
.player-card .photo { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin: 0 auto 8px; background: #e0f2fe; display: block; }
.player-card .no-photo { width: 70px; height: 70px; border-radius: 50%; margin: 0 auto 8px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #94a3b8; }
.player-card .number { position: absolute; top: 6px; left: 6px; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; border-radius: 8px; padding: 2px 8px; font-size: 0.75rem; font-weight: 800; }
.player-card .fio { font-weight: 700; font-size: 0.9rem; color: #0c4a6e; margin-top: 2px; }
.player-card .position { font-size: 0.8rem; color: #0284c7; font-weight: 600; }
.player-card .bd { font-size: 0.8rem; color: #64748b; }
.player-card .edit-btn { position: absolute; top: 6px; right: 32px; background: #e0f2fe; border: none; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; color: #0284c7; font-size: 0.85rem; line-height: 26px; text-align: center; transition: all 0.15s; text-decoration: none; }
.player-card .edit-btn:hover { background: #0284c7; color: #fff; }
.player-card .del-btn { position: absolute; top: 6px; right: 6px; background: #fef2f2; border: none; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; color: #ef4444; font-size: 1rem; line-height: 26px; text-align: center; transition: all 0.15s; text-decoration: none; }
.player-card .del-btn:hover { background: #ef4444; color: #fff; }
.player-form { background: #f8fafc; border-radius: 14px; padding: 20px; border: 2px dashed #cbd5e1; margin-top: 15px; }
.player-form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px; }
.player-form-row input { width: 100%; padding: 8px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; font-family: inherit; }
.player-form-row input:focus { outline: none; border-color: #0284c7; }
.player-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.section-title { font-size: 1.1rem; color: #0c4a6e; margin: 20px 0 10px; padding-bottom: 8px; border-bottom: 2px solid #e0f2fe; display: flex; justify-content: space-between; align-items: center; }
.club-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
</style>
</head><body>
<div class="admin-layout"><?php include __DIR__ . '/inc/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-container" style="max-width:100%;padding:25px 30px;">
    <div class="page-header"><h2>🏒 Команды</h2></div>
    <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Сохранено!</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Удалено!</div><?php endif; ?>

    <?php if ($editClub): ?>
    <form method="post" class="editor-wrapper" style="margin-bottom:30px;">
      <input type="hidden" name="id" value="<?= $editClub['id'] ?>">
      <input type="hidden" name="players_json" id="players_json" value='<?= htmlspecialchars(json_encode($currentPlayers, JSON_UNESCAPED_UNICODE)) ?>'>

      <h3 style="color:#0c4a6e;margin-bottom:15px;"><?= $isNew ? 'Создать новую команду' : 'Редактировать: ' . htmlspecialchars($editClub['name']) ?></h3>
      <div class="club-form-grid">
        <div class="form-group"><label>Название команды</label><input type="text" name="name" value="<?= htmlspecialchars($editClub['name']) ?>" required></div>
        <div class="form-group"><label>Город</label><input type="text" name="city" value="<?= htmlspecialchars($editClub['city']) ?>"></div>
        <div class="form-group"><label>Область/Регион</label><input type="text" name="region" value="<?= htmlspecialchars($editClub['region']) ?>"></div>
        <div class="form-group"><label>Сайт</label><input type="text" name="website" value="<?= htmlspecialchars($editClub['website']) ?>"></div>
        <div class="form-group"><label>Логотип (URL или загрузить)</label>
          <div style="display:flex;gap:8px;margin-bottom:5px;">
            <input type="text" name="logo" id="club_logo" value="<?= htmlspecialchars($editClub['logo']) ?>" placeholder="https://..." style="flex:1;">
            <input type="file" id="logo_upload" accept="image/*" style="display:none;" onchange="uploadLogo(this)">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('logo_upload').click()">📁 Выбрать</button>
          </div>
          <div id="logo_preview"><?php if ($editClub['logo']): ?><img src="<?= htmlspecialchars($editClub['logo']) ?>" style="max-width:80px;max-height:80px;border-radius:8px;border:1px solid #e2e8f0;" onerror="this.outerHTML='<span style=\'color:#ef4444;font-size:0.8rem;\'>⚠ не загружается</span>'"><?php endif; ?></div>
        </div>
      </div>
      <div class="form-group"><label>Описание</label><textarea name="description" rows="3"><?= htmlspecialchars($editClub['description']) ?></textarea></div>

      <div class="section-title">
        <span>👥 Игроки (карточки)</span>
        <span>
          <button type="button" class="btn btn-secondary btn-sm" onclick="showAddForm()">+ Добавить игрока</button>
        </span>
      </div>

      <div id="playerCards" class="player-grid"></div>

      <div id="addPlayerForm" class="player-form" style="display:none;">
        <h4 style="margin-bottom:10px;color:#0c4a6e;"><span id="addFormTitle">Новый игрок</span></h4>
        <div style="margin-bottom:10px;padding:10px;background:#fff;border-radius:10px;border:1px solid #e0f2fe;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <span style="font-size:0.85rem;color:#475569;">📸 Загрузить фото игрока:</span>
          <input type="file" id="photo_upload" accept="image/*" style="flex:1;min-width:150px;padding:6px;border:1px solid #e2e8f0;border-radius:6px;">
          <button type="button" class="btn btn-sm btn-secondary" onclick="uploadPhoto()">Загрузить</button>
        </div>
        <div class="player-form-row" style="grid-template-columns:60px 1fr 1fr;">
          <div><label style="display:block;font-size:0.8rem;margin-bottom:3px;color:#475569;">Номер</label>
            <input type="number" id="p_number" min="1" max="99" value="" placeholder="10"></div>
          <div><label style="display:block;font-size:0.8rem;margin-bottom:3px;color:#475569;">Фамилия Имя Отчество</label>
            <input type="text" id="p_fio" placeholder="Иванов Иван Иванович"></div>
          <div><label style="display:block;font-size:0.8rem;margin-bottom:3px;color:#475569;">Дата рождения</label>
            <input type="date" id="p_bd"></div>
        </div>
        <div class="player-form-row" style="grid-template-columns:1fr 1fr 1fr;">
          <div><label style="display:block;font-size:0.8rem;margin-bottom:3px;color:#475569;">Амплуа</label>
            <select id="p_position" style="width:100%;padding:8px 12px;border:2px solid #e2e8f0;border-radius:8px;font-family:inherit;">
              <option value="">— выберите —</option>
              <option value="Тренер">Тренер</option>
              <option value="Помощник тренера">Помощник тренера</option>
              <option value="Вратарь">Вратарь</option>
              <option value="Защитник">Защитник</option>
              <option value="Нападающий">Нападающий</option>
            </select></div>
          <div><label style="display:block;font-size:0.8rem;margin-bottom:3px;color:#475569;">Фото (URL)</label>
            <input type="text" id="p_photo" placeholder="https://..."></div>
          <div style="display:flex;align-items:flex-end;gap:8px;">
            <button type="button" class="btn btn-primary btn-sm" onclick="addPlayer()" style="flex:1;"><span id="addBtnText">+ Добавить</span></button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="hideAddForm()">Отмена</button>
          </div>
        </div>
      </div>

      <div style="margin-top:20px;display:flex;gap:10px;">
        <button type="submit" name="save_club" class="btn btn-primary">💾 Сохранить команду и всех игроков</button>
        <a href="clubs.php" class="btn btn-secondary">Отмена</a>
      </div>
    </form>
    <?php endif; ?>

    <div class="editor-wrapper">
      <h3 style="color:#0c4a6e;margin-bottom:15px;">Список команд</h3>
      <?php if (empty($clubs)): ?>
        <p style="color:#94a3b8;text-align:center;padding:20px;">Нет команд. <a href="clubs.php?edit=0" style="color:#0284c7;">Создать первую команду</a></p>
      <?php else: ?>
      <table class="table">
        <thead><tr><th>ID</th><th>Лого</th><th>Название</th><th>Город</th><th>Игроков</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($clubs as $c): ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td><?php if ($c['logo']): ?><img src="<?= htmlspecialchars($c['logo']) ?>" style="width:36px;height:36px;object-fit:contain;border-radius:8px;"><?php endif; ?></td>
            <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
            <td><?= htmlspecialchars($c['city']) ?></td>
            <td><?= count(array_filter($c['players'] ?? [], function($p) { return !in_array($p['position'] ?? '', ['Тренер', 'Помощник тренера']); })) ?></td>
            <td class="actions">
              <a href="clubs.php?edit=<?= $c['id'] ?>" class="btn btn-primary btn-sm">✏️</a>
              <a href="section-delete.php?type=clubs&id=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить команду? Все игроки будут потеряны.')">🗑️</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
      <div style="margin-top:15px;"><a href="clubs.php?edit=0" class="btn btn-primary">+ Создать новую команду</a></div>
    </div>
  </div>
</div></div>

<script>
var players = <?= json_encode($currentPlayers, JSON_UNESCAPED_UNICODE) ?>;
var editingIndex = -1;

function renderCards() {
  var container = document.getElementById('playerCards');
  if (!container) return;
  if (players.length === 0) {
    container.innerHTML = '<p style="color:#94a3b8;grid-column:1/-1;text-align:center;padding:20px;">Игроков пока нет. Нажмите «+ Добавить игрока»</p>';
    return;
  }
  var html = '';
  for (var i = 0; i < players.length; i++) {
    var p = players[i];
    var photoHtml = p.photo
      ? '<img class="photo" src="' + p.photo + '" alt="' + p.fio + '">'
      : '<div class="no-photo">👤</div>';
    html += '<div class="player-card">';
    html += '<a href="?edit=<?= $editClub ? $editClub['id'] : 0 ?>&delete_player=' + i + '" class="del-btn" onclick="return confirm(\'Удалить игрока?\')">×</a>';
    html += '<a href="javascript:editPlayer(' + i + ')" class="edit-btn">✎</a>';
    if (p.number) html += '<div class="number">#' + p.number + '</div>';
    html += photoHtml;
    html += '<div class="fio">' + (p.fio || '') + '</div>';
    if (p.position) html += '<div class="position">' + p.position + '</div>';
    html += '<div class="bd">' + (p.birth_date || '') + '</div>';
    html += '</div>';
  }
  container.innerHTML = html;
  document.getElementById('players_json').value = JSON.stringify(players);
}

function editPlayer(i) {
  var p = players[i];
  document.getElementById('p_number').value = p.number || '';
  document.getElementById('p_fio').value = p.fio || '';
  document.getElementById('p_bd').value = p.birth_date || '';
  document.getElementById('p_position').value = p.position || '';
  document.getElementById('p_photo').value = p.photo || '';
  editingIndex = i;
  document.getElementById('addBtnText').textContent = '💾 Сохранить';
  document.getElementById('addFormTitle').textContent = 'Редактировать игрока';
  showAddForm();
}

function addPlayer() {
  var number = document.getElementById('p_number').value.trim();
  var fio = document.getElementById('p_fio').value.trim();
  var bd = document.getElementById('p_bd').value;
  var position = document.getElementById('p_position').value;
  var photo = document.getElementById('p_photo').value.trim();
  if (!fio) { alert('Введите ФИО игрока'); return; }
  var player = { number: number, fio: fio, birth_date: bd, position: position, photo: photo };
  if (editingIndex >= 0) {
    players[editingIndex] = player;
    editingIndex = -1;
  } else {
    players.push(player);
  }
  document.getElementById('p_number').value = '';
  document.getElementById('p_fio').value = '';
  document.getElementById('p_bd').value = '';
  document.getElementById('p_position').selectedIndex = 0;
  document.getElementById('p_photo').value = '';
  document.getElementById('addBtnText').textContent = '+ Добавить';
  document.getElementById('players_json').value = JSON.stringify(players);
  renderCards();
  hideAddForm();
}

function showAddForm() { document.getElementById('addPlayerForm').style.display = 'block'; }
function hideAddForm() { editingIndex = -1; document.getElementById('addBtnText').textContent = '+ Добавить'; document.getElementById('addFormTitle').textContent = 'Новый игрок'; document.getElementById('addPlayerForm').style.display = 'none'; }

function uploadFile(input, formKey, fieldId, previewId) {
  var file = input.files[0];
  if (!file) return;
  var formData = new FormData();
  formData.append(formKey, file);
  formData.append('upload_' + formKey, '1');
  fetch('clubs.php', { method: 'POST', body: formData })
    .then(function(r) { return r.text(); })
    .then(function(url) {
      if (url && url.indexOf('err:') !== 0) {
        document.getElementById(fieldId).value = url;
        if (previewId) {
          document.getElementById(previewId).innerHTML = '<img src="' + url + '" style="max-width:80px;max-height:80px;border-radius:8px;">';
        }
      } else {
        var msg = url || 'Неизвестная ошибка';
        if (msg.indexOf('err:') === 0) msg = 'Ошибка ' + msg.substring(4);
        alert('Ошибка загрузки: ' + msg);
      }
    })
    .catch(function() { alert('Ошибка загрузки'); });
  input.value = '';
}

function uploadPhoto() { uploadFile(document.getElementById('photo_upload'), 'player_photo', 'p_photo', null); }
function uploadLogo(input) { uploadFile(input, 'logo_file', 'club_logo', 'logo_preview'); }

renderCards();

// Ensure players_json is sent on form submit
var clubForm = document.querySelector('form');
if (clubForm) {
  clubForm.addEventListener('submit', function() {
    document.getElementById('players_json').value = JSON.stringify(players);
  });
}
</script>
</body></html>
