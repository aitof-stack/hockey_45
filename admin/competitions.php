<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }

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

$compFile = __DIR__ . '/../data/competitions.json';
$clubFile = __DIR__ . '/../data/clubs.json';
$items = json_decode(file_get_contents($compFile), true);
$allClubs = json_decode(file_get_contents($clubFile), true);
$editItem = null;
$editId = 0;
if (isset($_GET['edit'])) {
  $editId = (int)$_GET['edit'];
  foreach ($items as $c) { if ($c['id'] === $editId) { $editItem = $c; break; } }
}

require_once __DIR__ . '/../inc/standings.php';
?>
<!DOCTYPE html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Соревнования — Админ-панель</title><link rel="stylesheet" href="css/admin.css">
<style>
.club-checkboxes { display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0; }
.club-checkboxes label { display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.15s; }
    .club-checkboxes label:hover { border-color: var(--primary); background: var(--block-fone); }
    .club-checkboxes input:checked + span { font-weight: 700; }
    .club-checkboxes input:checked ~ label { border-color: var(--primary); background: var(--section-even-fone); }
    .matches-table input[type="number"] { width: 50px; text-align: center; }
    .matches-table select { width: 160px; }
    .standings-table th { background: var(--primary-dark); color: #fff; padding: 8px 12px; font-size: 0.85rem; }
    .standings-table td { padding: 8px 12px; text-align: center; }
    .standings-table tr:nth-child(1) td { font-weight: 700; background: #fef3c7; }
    .standings-table tr:nth-child(2) td { background: #f0fdf4; }
     .standings-table tr:nth-child(3) td { background: var(--block-fone); }
     .protocol-btn { font-size:0.7rem; padding:3px 8px; cursor:pointer; border-radius:6px; border:1px solid #cbd5e1; background:#f8fafc; color:#475569; }
     .protocol-btn:hover { border-color:var(--primary); color:var(--primary); }
     .protocol-ok { color:#16a34a; font-size:0.7rem; font-weight:600; }
     .protocol-none { color:#94a3b8; font-size:0.7rem; }
</style>
</head><body>
<div class="admin-layout"><?php include __DIR__ . '/inc/sidebar.php'; ?>
<div class="admin-main">
<div class="admin-container" style="max-width:100%;padding:25px 30px;">
  <div class="page-header"><h2>🏆 Соревнования</h2></div>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Сохранено!</div><?php endif; ?>
  <h3 style="margin-bottom:15px;color:#0c4a6e;"><?= $editItem ? 'Редактировать турнир' : 'Добавить турнир' ?></h3>
  <div class="editor-wrapper" style="margin-bottom:30px;">
    <form action="section-save.php" method="post">
      <input type="hidden" name="type" value="competitions">
      <input type="hidden" name="id" value="<?= $editItem ? $editItem['id'] : 0 ?>">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
        <div class="form-group"><label>Название турнира</label><input type="text" name="name" value="<?= htmlspecialchars($editItem['name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Сезон</label><input type="text" name="season" value="<?= htmlspecialchars($editItem['season'] ?? '') ?>"></div>
        <div class="form-group"><label>Тип</label>
          <select name="comp_type">
            <option value="regular" <?= ($editItem['type']??'')=='regular'?'selected':'' ?>>Регулярный</option>
            <option value="playoff" <?= ($editItem['type']??'')=='playoff'?'selected':'' ?>>Плей-офф</option>
            <option value="cup" <?= ($editItem['type']??'')=='cup'?'selected':'' ?>>Кубок</option>
            <option value="friendly" <?= ($editItem['type']??'')=='friendly'?'selected':'' ?>>Товарищеский</option>
          </select>
        </div>
        <div class="form-group"><label>Статус</label>
          <select name="status">
            <option value="active" <?= ($editItem['status']??'')=='active'?'selected':'' ?>>Активен</option>
            <option value="finished" <?= ($editItem['status']??'')=='finished'?'selected':'' ?>>Завершён</option>
            <option value="upcoming" <?= ($editItem['status']??'')=='upcoming'?'selected':'' ?>>Предстоящий</option>
          </select>
        </div>
        <div class="form-group"><label>Логотип (URL или загрузить)</label>
          <div style="display:flex;gap:8px;margin-bottom:5px;">
            <input type="text" name="logo" id="comp_logo" value="<?= htmlspecialchars($editItem['logo'] ?? '') ?>" placeholder="https://..." style="flex:1;">
            <input type="file" id="logo_upload" accept="image/*" style="display:none;" onchange="uploadLogo(this)">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('logo_upload').click()">📁 Выбрать</button>
          </div>
          <div id="logo_preview"><?php if (!empty($editItem['logo'])): ?><img src="<?= htmlspecialchars($editItem['logo']) ?>" style="max-width:80px;max-height:80px;border-radius:8px;border:1px solid #e2e8f0;" onerror="this.outerHTML='<span style=\'color:#ef4444;font-size:0.8rem;\'>⚠ не загружается</span>'"><?php endif; ?></div>
        </div>
      </div>
      <div class="form-group"><label>Описание</label><textarea name="description" rows="3"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea></div>
      <div class="form-group"><label>Даты матчей (по одной на строку, формат ГГГГ-ММ-ДД)</label><textarea name="match_dates" rows="4"><?php if (isset($editItem['match_dates'])) echo htmlspecialchars(implode("\n", $editItem['match_dates'])) ?></textarea></div>

      <div class="section-title"><span>🏒 Команды-участники</span></div>
      <div class="club-checkboxes">
        <?php $selectedIds = $editItem['club_ids'] ?? []; ?>
        <?php foreach ($allClubs as $club): ?>
          <label><input type="checkbox" name="club_ids[]" value="<?= $club['id'] ?>" <?= in_array($club['id'], $selectedIds) ? 'checked' : '' ?>> <span><?= htmlspecialchars($club['name']) ?></span></label>
        <?php endforeach; ?>
      </div>

      <?php $matches = $editItem['matches'] ?? []; ?>
      <?php if ($editItem): ?>
      <div class="section-title"><span>⚔️ Матчи и результаты</span></div>
      <p style="font-size:0.85rem;color:#64748b;margin-bottom:10px;">Для каждого матча выберите дату, хозяев, гостей и счёт. Можно добавить несколько матчей на одну дату.</p>
      <div id="matchesContainer">
        <?php if (empty($matches)): ?>
        <div class="match-row" style="display:flex;gap:10px;align-items:center;margin-bottom:8px;flex-wrap:wrap;">
          <input type="date" name="match_date[]" style="width:150px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;">
          <select name="match_home[]" style="width:160px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;">
            <option value="">— хозяева —</option>
            <?php foreach ($allClubs as $club): ?>
            <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <span style="font-weight:700;">vs</span>
          <select name="match_away[]" style="width:160px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;">
            <option value="">— гости —</option>
            <?php foreach ($allClubs as $club): ?>
            <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="number" name="match_home_goals[]" placeholder="0" min="0" style="width:50px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;text-align:center;">
          <span style="font-weight:700;">:</span>
          <input type="number" name="match_away_goals[]" placeholder="0" min="0" style="width:50px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;text-align:center;">
          <button type="button" class="protocol-btn" onclick="uploadProtocol(this)" title="Загрузить протокол">📄</button>
          <span class="protocol-none" id="ps_0">нет протокола</span>
          <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()" style="padding:4px 10px;">✕</button>
        </div>
        <?php else: ?>
          <?php foreach ($matches as $i => $m): ?>
          <div class="match-row" style="display:flex;gap:10px;align-items:center;margin-bottom:8px;flex-wrap:wrap;">
            <input type="date" name="match_date[]" value="<?= htmlspecialchars($m['date'] ?? '') ?>" style="width:150px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;">
            <select name="match_home[]" style="width:160px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;">
              <option value="">— хозяева —</option>
              <?php foreach ($allClubs as $club): ?>
              <option value="<?= $club['id'] ?>" <?= ($m['home_id']??'')==$club['id']?'selected':'' ?>><?= htmlspecialchars($club['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <span style="font-weight:700;">vs</span>
            <select name="match_away[]" style="width:160px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;">
              <option value="">— гости —</option>
              <?php foreach ($allClubs as $club): ?>
              <option value="<?= $club['id'] ?>" <?= ($m['away_id']??'')==$club['id']?'selected':'' ?>><?= htmlspecialchars($club['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" name="match_home_goals[]" value="<?= $m['home_goals'] ?? '' ?>" placeholder="0" min="0" style="width:50px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;text-align:center;">
            <span style="font-weight:700;">:</span>
            <input type="number" name="match_away_goals[]" value="<?= $m['away_goals'] ?? '' ?>" placeholder="0" min="0" style="width:50px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;text-align:center;">
            <label style="font-size:0.8rem;display:flex;align-items:center;gap:3px;cursor:pointer;"><input type="hidden" name="match_shootout[<?= $i ?>]" value="0"><input type="checkbox" name="match_shootout[<?= $i ?>]" value="1" <?= !empty($m['shootout']) ? 'checked' : '' ?>> Буллиты</label>
            <button type="button" class="protocol-btn" onclick="uploadProtocol(this)" title="Загрузить протокол">📄</button>
            <span id="ps_<?= $i ?>" class="<?= !empty($m['protocol']) ? 'protocol-ok' : 'protocol-none' ?>"><?= !empty($m['protocol']) ? '✓ загружен' : 'нет протокола' ?></span>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()" style="padding:4px 10px;">✕</button>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <button type="button" class="btn btn-sm btn-secondary" onclick="addMatchRow()" style="margin-bottom:15px;">+ Добавить матч</button>

      <div class="section-title"><span>📊 Турнирная таблица</span></div>
      <?php if (!empty($matches)): ?>
        <?php $standings = computeStandings($matches, $editItem['club_ids'] ?? []); ?>
        <table class="standings-table" style="width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.04);margin-bottom:15px;">
           <thead><tr><th>#</th><th>Команда</th><th>И</th><th>В</th><th>ВБ</th><th>ПБ</th><th>П</th><th>ШЗ</th><th>ШП</th><th>±</th><th>О</th></tr></thead>
          <tbody>
            <?php $rank = 0; foreach ($standings as $cid => $s): $rank++; ?>
            <tr><td><?= $rank ?></td><td style="text-align:left;font-weight:600;"><?= clubName($cid, $allClubs) ?></td>
              <td><?= $s['w']+$s['w_so']+$s['l_so']+$s['l'] ?></td><td><?= $s['w'] ?></td><td><?= $s['w_so'] ?></td>
              <td><?= $s['l_so'] ?></td><td><?= $s['l'] ?></td><td><?= $s['gf'] ?></td><td><?= $s['ga'] ?></td>
              <td><?= ($gd = $s['gf']-$s['ga']) > 0 ? '+'.$gd : $gd ?></td>
              <td style="font-weight:800;font-size:1.1rem;"><?= $s['pts'] ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p style="color:#94a3b8;">Добавьте матчи, чтобы увидеть турнирную таблицу.</p>
      <?php endif; ?>
      <?php endif; ?>

      <div style="margin-top:20px;">
        <button type="submit" class="btn btn-primary">💾 Сохранить</button>
        <?php if ($editItem): ?><a href="competitions.php" class="btn btn-secondary">Отмена</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="editor-wrapper">
    <h3 style="color:#0c4a6e;margin-bottom:15px;">Список турниров</h3>
    <?php if (empty($items)): ?>
      <p style="text-align:center;color:#94a3b8;padding:20px;">Нет турниров.</p>
    <?php else: ?>
    <table class="table">
      <thead><tr><th>ID</th><th>Название</th><th>Сезон</th><th>Тип</th><th>Статус</th><th>Команд</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($items as $c): ?>
        <tr><td><?= $c['id'] ?></td><td><?= htmlspecialchars($c['name']) ?></td><td><?= htmlspecialchars($c['season'] ?? '') ?></td>
          <td><?= $c['type'] ?? '' ?></td><td><?= $c['status'] ?? '' ?></td>
          <td><?= count($c['club_ids'] ?? []) ?></td>
          <td class="actions">
            <a href="competitions.php?edit=<?= $c['id'] ?>" class="btn btn-primary btn-sm">✏️</a>
            <a href="section-delete.php?type=competitions&id=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить?')">🗑️</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
</div></div>

<script>
var matchRowCounter = <?= max(count($matches), 1) ?>;
function addMatchRow() {
  var idx = matchRowCounter++;
  var html = '<div class="match-row" style="display:flex;gap:10px;align-items:center;margin-bottom:8px;flex-wrap:wrap;">';
  html += '<input type="date" name="match_date[]" style="width:150px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;">';
  html += '<select name="match_home[]" style="width:160px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;">';
  html += '<option value="">— хозяева —</option>';
  <?php foreach ($allClubs as $club): ?>
  html += '<option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name'], ENT_QUOTES) ?></option>';
  <?php endforeach; ?>
  html += '</select>';
  html += '<span style="font-weight:700;">vs</span>';
  html += '<select name="match_away[]" style="width:160px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;">';
  html += '<option value="">— гости —</option>';
  <?php foreach ($allClubs as $club): ?>
  html += '<option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name'], ENT_QUOTES) ?></option>';
  <?php endforeach; ?>
  html += '</select>';
  html += '<input type="number" name="match_home_goals[]" placeholder="0" min="0" style="width:50px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;text-align:center;">';
  html += '<span style="font-weight:700;">:</span>';
  html += '<input type="number" name="match_away_goals[]" placeholder="0" min="0" style="width:50px;padding:6px;border:2px solid #e2e8f0;border-radius:8px;text-align:center;">';
  html += '<label style="font-size:0.8rem;display:flex;align-items:center;gap:3px;cursor:pointer;"><input type="hidden" name="match_shootout[' + idx + ']" value="0"><input type="checkbox" name="match_shootout[' + idx + ']" value="1"> Буллиты</label>';
  html += '<button type="button" class="protocol-btn" onclick="uploadProtocol(this)" title="Загрузить протокол">📄</button>';
  html += '<span class="protocol-none" id="ps_new' + idx + '">нет протокола</span>';
  html += '<button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()" style="padding:4px 10px;">✕</button>';
  html += '</div>';
  document.getElementById('matchesContainer').insertAdjacentHTML('beforeend', html);
}

function uploadProtocol(btn) {
  var row = btn.closest('.match-row');
  var dateInput = row.querySelector('input[name="match_date[]"]');
  var homeSelect = row.querySelector('select[name="match_home[]"]');
  var awaySelect = row.querySelector('select[name="match_away[]"]');
  if (!dateInput.value || !homeSelect.value || !awaySelect.value) {
    alert('Сначала выберите дату, хозяев и гостей.');
    return;
  }
  var input = document.createElement('input');
  input.type = 'file';
  input.accept = '.json,application/json';
  input.onchange = function() {
    var file = input.files[0];
    if (!file) return;
    var formData = new FormData();
    formData.append('protocol', file);
    formData.append('comp_id', <?= $editItem ? $editItem['id'] : 0 ?>);
    var statusSpan = row.querySelector('[id^="ps_"]');
    if (statusSpan) statusSpan.textContent = '⏳ загрузка…';
    fetch('upload-protocol.php', { method: 'POST', body: formData })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        if (res.ok) {
          if (statusSpan) {
            statusSpan.className = 'protocol-ok';
            statusSpan.textContent = '✓ загружен (' + res.matched + ' совпад.)';
          }
        } else {
          if (statusSpan) statusSpan.textContent = '✕ ' + (res.error || 'ошибка');
        }
      })
      .catch(function() {
        if (statusSpan) statusSpan.textContent = '✕ ошибка сети';
      });
  };
  input.click();
}
</script>
<script>
function uploadFile(input, formKey, fieldId, previewId) {
  var file = input.files[0];
  if (!file) return;
  var formData = new FormData();
  formData.append(formKey, file);
  formData.append('upload_' + formKey, '1');
  fetch('competitions.php', { method: 'POST', body: formData })
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
function uploadLogo(input) { uploadFile(input, 'logo_file', 'comp_logo', 'logo_preview'); }
</script>
</body></html>
