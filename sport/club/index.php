<?php
$clubs = json_decode(file_get_contents(__DIR__ . '/../../data/clubs.json'), true);
$clubId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selectedClub = null;
if ($clubId) {
  foreach ($clubs as $c) { if ($c['id'] === $clubId) { $selectedClub = $c; break; } }
  if ($selectedClub && !empty($selectedClub['players'])) {
    $positionOrder = ['Тренер' => 1, 'Помощник тренера' => 2, 'Вратарь' => 3, 'Защитник' => 4, 'Нападающий' => 5];
    usort($selectedClub['players'], function($a, $b) use ($positionOrder) {
      $pa = $positionOrder[$a['position']] ?? 9;
      $pb = $positionOrder[$b['position']] ?? 9;
      if ($pa !== $pb) return $pa <=> $pb;
      $na = isset($a['number']) && $a['number'] !== '' ? (int)$a['number'] : 999;
      $nb = isset($b['number']) && $b['number'] !== '' ? (int)$b['number'] : 999;
      return $na <=> $nb;
    });
  }
}
$pageTitle = $selectedClub ? $selectedClub['name'] : 'Команды';
?><?php require __DIR__ . '/../../inc/header.php'; ?>
<style>
    .clubs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; margin-top: 20px; }
    .club-card { background: var(--white); border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--section-even-fone); text-align: center; transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; color: inherit; display: block; cursor: pointer; }
    .club-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,56,96,0.12); }
    .club-card .logo-wrap { width: 200px; height: 200px; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; }
    .club-card img { max-width: 200px; max-height: 200px; object-fit: contain; }
    .club-card .no-logo { width: 80px; height: 80px; border-radius: 50%; background: var(--section-even-fone); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #94a3b8; }
    .club-card h3 { color: var(--primary-dark); margin: 5px 0; }
    .club-card .city { color: #64748b; font-size: 0.9rem; }
    .club-card .players-count { display: inline-block; margin-top: 10px; padding: 5px 15px; background: var(--section-even-fone); border-radius: 15px; color: var(--primary); font-size: 0.85rem; }

    .club-detail { max-width: 1100px; margin: 0 auto; }
    .club-detail-header { display: flex; gap: 30px; align-items: center; margin-bottom: 30px; padding: 25px; background: var(--white); border-radius: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid var(--section-even-fone); }
    .club-detail-header .logo-wrap { width: 240px; height: 240px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .club-detail-header img { max-width: 240px; max-height: 240px; object-fit: contain; }
    .club-detail-header .no-logo { width: 200px; height: 200px; border-radius: 50%; background: var(--section-even-fone); display: flex; align-items: center; justify-content: center; font-size: 5rem; color: #94a3b8; }
    .club-detail-header h1 { color: var(--primary-dark); margin: 0 0 5px; }
    .club-detail-header .info { color: #64748b; }
    .club-detail-header a { color: var(--primary); }
    .players-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 15px; max-width: 100%; overflow: hidden; }
    .player-card { background: #0B0D14; border-radius: 12px; overflow: hidden; box-shadow: 0 6px 24px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.07); transition: transform 0.2s, box-shadow 0.2s; perspective: 800px; cursor: pointer; min-width: 0; position: relative; }
    .player-card:hover { transform: translateY(-3px); box-shadow: 0 10px 35px rgba(0,0,0,0.5); }
    .player-card .card-inner { position: relative; width: 100%; transition: transform 0.6s; transform-style: preserve-3d; }
    .player-card.flipped .card-inner { transform: rotateY(180deg); }
    .player-card .card-front, .player-card .card-back { backface-visibility: hidden; position: relative; width: 100%; }
    .player-card .card-front { padding: 0; }
    .player-card .card-back { padding: 20px; position: absolute; top: 0; left: 0; transform: rotateY(180deg); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100%; box-sizing: border-box; background: #0B0D14; border-radius: 12px; }
    .player-card .card-back .bd-label { font-size: 0.85rem; color: rgba(255,255,255,0.35); margin-bottom: 4px; }
    .player-card .card-back .bd-value { font-size: 1.3rem; font-weight: 700; color: #B0C8DD; }
    .player-card .photo-wrap { position: relative; display: block; width: 100%; margin: 0; background: #0d1f3a; overflow: hidden; }
    .player-card .photo-wrap .photo { width: 100%; height: auto; aspect-ratio: 1/1; object-fit: contain; display: block; }
    .player-card .photo-wrap .no-photo { width: 100%; aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; font-size: 4rem; color: rgba(255,255,255,0.1); background: #0d1f3a; }
    .player-card .photo-number { position: absolute; bottom: 12px; right: 14px; font-size: 3.5rem; font-weight: 900; color: #ffffff; line-height: 1; text-shadow: 0 2px 10px rgba(0,0,0,0.7); }
    .player-card .card-panel { background: #0B0D14; padding: 12px 14px 8px; text-align: center; position: relative; }
    .player-card .panel-crest { display: flex; align-items: center; justify-content: center; gap: 14px; margin-bottom: 8px; }
    .player-card .panel-crest .crest-line { flex: 1; max-width: 50px; height: 1px; background: rgba(255,255,255,0.12); }
    .player-card .panel-crest .crest-icon { width: 28px; height: 28px; border: 1.5px solid rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: rgba(255,255,255,0.3); flex-shrink: 0; }
    .player-card .panel-crest .crest-icon img { width: 18px; height: auto; opacity: 0.4; }
    .player-card .panel-name { margin-bottom: 2px; }
    .player-card .surname { font-size: 1.875rem; font-weight: 900; color: #ffffff; line-height: 1.1; overflow-wrap: break-word; word-wrap: break-word; letter-spacing: 0.03em; }
    .player-card .firstname { font-size: 1.2rem; font-weight: 400; color: #ffffff; line-height: 1.2; overflow-wrap: break-word; word-wrap: break-word; opacity: 0.6; }
    .player-card .card-divider { height: 1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), rgba(200,215,235,0.5), rgba(255,255,255,0.2), transparent); margin: 0 20px; }
    .player-card .card-footer { padding: 8px 14px 12px; background: #0B0D14; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .player-card .position { font-size: 0.65rem; color: rgba(255,255,255,0.2); font-weight: 500; text-transform: uppercase; letter-spacing: 2.5px; }
    .player-card .footer-mark { width: 18px; height: 18px; border: 1px solid rgba(255,255,255,0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .player-card .footer-mark::after { content: ''; width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.15); }
    .section-header { color: var(--primary-dark); margin: 30px 0 12px; font-size: 1.3rem; font-weight: 800; padding-bottom: 6px; border-bottom: 3px solid rgba(221,119,0,0.3); }
    @media (max-width:1200px) { .players-grid { grid-template-columns: repeat(3, 1fr); } .player-card .surname { font-size: 1.389rem; } .player-card .firstname { font-size: 0.972rem; } .player-card .photo-number { font-size: 2.4rem; } }
    @media (max-width:900px) { .players-grid { grid-template-columns: repeat(2, 1fr); } .player-card .surname { font-size: 1.25rem; } .player-card .firstname { font-size: 0.833rem; } .player-card .photo-number { font-size: 2rem; } }
    @media (max-width:600px) { .players-grid { grid-template-columns: 1fr; } .player-card .surname { font-size: 1.111rem; } .player-card .firstname { font-size: 0.764rem; } .player-card .photo-number { font-size: 1.8rem; } }
    .back-link { display: inline-block; margin-bottom: 20px; color: var(--primary); text-decoration: none; font-weight: 600; }
    .back-link:hover { text-decoration: underline; }
    .empty-players { text-align: center; padding: 40px; color: #94a3b8; }
  </style>

    <main class="content">
      <section>
        <?php if ($selectedClub): ?>
          <a href="/sport/club/" class="back-link">← Все команды</a>
          <div class="club-detail">
            <div class="club-detail-header">
              <div class="logo-wrap">
                <?php if ($selectedClub['logo']): ?>
                  <img src="<?= htmlspecialchars($selectedClub['logo']) ?>" alt="<?= htmlspecialchars($selectedClub['name']) ?>" onerror="this.outerHTML='<div class=\'no-logo\'>🏒</div>'">
                <?php else: ?>
                  <div class="no-logo">🏒</div>
                <?php endif; ?>
              </div>
              <div>
                <h1><?= htmlspecialchars($selectedClub['name']) ?></h1>
                <div class="info"><?= htmlspecialchars($selectedClub['city']) ?></div>
                <?php if ($selectedClub['region']): ?>
                  <div class="info"><?= htmlspecialchars($selectedClub['region']) ?></div>
                <?php endif; ?>
                <?php if ($selectedClub['website']): ?>
                  <div class="info"><a href="<?= htmlspecialchars($selectedClub['website']) ?>" target="_blank"><?= htmlspecialchars($selectedClub['website']) ?></a></div>
                <?php endif; ?>
                <?php
                  $nonCoachPlayers = array_filter($selectedClub['players'] ?? [], function($p) {
                    return !in_array($p['position'] ?? '', ['Тренер', 'Помощник тренера']);
                  });
                ?>
                <?php if (!empty($nonCoachPlayers)): ?>
                  <div class="info" style="margin-top:8px;">👥 <?= count($nonCoachPlayers) ?> игроков</div>
                <?php endif; ?>
              </div>
            </div>

            <h2>Состав команды</h2>
            <?php if (!empty($selectedClub['players'])):
              $groups = ['Тренерский состав' => ['Тренер', 'Помощник тренера'], 'Вратари' => ['Вратарь'], 'Защитники' => ['Защитник'], 'Нападающие' => ['Нападающий']];
              $grouped = [];
              foreach ($selectedClub['players'] as $p) {
                $pos = $p['position'] ?? '';
                foreach ($groups as $label => $positions) {
                  if (in_array($pos, $positions)) { $grouped[$label][] = $p; break; }
                }
              }
              foreach ($groups as $label => $positions):
                if (empty($grouped[$label])) continue;
            ?>
              <h3 class="section-header"><?= $label ?></h3>
              <div class="players-grid">
                <?php foreach ($grouped[$label] as $p):
                  $fullName = $p['fio'] ?? $p['name'] ?? '';
                  $parts = explode(' ', trim($fullName));
                  $surname = $parts[0] ?? '';
                  $firstname = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
                ?>
                   <div class="player-card">
                    <div class="card-inner">
                      <div class="card-front">
                        <div class="photo-wrap">
                          <?php if ($p['photo']): ?>
                            <img class="photo" src="<?= htmlspecialchars($p['photo']) ?>" alt="<?= htmlspecialchars($fullName) ?>" onerror="this.outerHTML='<div class=\'no-photo\'>👤</div>'">
                          <?php else: ?>
                            <div class="no-photo">👤</div>
                          <?php endif; ?>
                          <?php if (!empty($p['number'])): ?><div class="photo-number"><?= htmlspecialchars($p['number']) ?></div><?php endif; ?>
                        </div>
                        <div class="card-panel">
                          <div class="panel-crest">
                            <span class="crest-line"></span>
                            <span class="crest-icon">★</span>
                            <span class="crest-line"></span>
                          </div>
                          <div class="panel-name">
                            <div class="surname"><?= htmlspecialchars($surname) ?></div>
                            <div class="firstname"><?= htmlspecialchars($firstname) ?></div>
                          </div>
                        </div>
                        <div class="card-divider"></div>
                        <div class="card-footer">
                          <?php if (!empty($p['position'])): ?><div class="position"><?= htmlspecialchars($p['position']) ?></div><?php endif; ?>
                          <div class="footer-mark"></div>
                        </div>
                      </div>
                      <div class="card-back">
                        <?php if (!empty($p['birth_date'])): ?>
                        <div class="bd-label">Дата рождения</div>
                        <div class="bd-value"><?= htmlspecialchars($p['birth_date']) ?></div>
                        <?php else: ?>
                        <div class="bd-label"><?= htmlspecialchars($p['position'] ?? '') ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endforeach; else: ?>
              <p class="empty-players">Состав команды пока не заполнен</p>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <?php if (empty($clubs)): ?>
            <p style="text-align:center;color:#64748b;">Список команд пока пуст.</p>
          <?php else: ?>
          <div class="clubs-grid">
            <?php foreach ($clubs as $club): ?>
            <a href="?id=<?= $club['id'] ?>" class="club-card">
              <div class="logo-wrap">
                <?php if ($club['logo']): ?>
                  <img src="<?= htmlspecialchars($club['logo']) ?>" alt="<?= htmlspecialchars($club['name']) ?>" onerror="this.outerHTML='<div class=\'no-logo\'>🏒</div>'">
                <?php else: ?>
                  <div class="no-logo">🏒</div>
                <?php endif; ?>
              </div>
              <h3><?= htmlspecialchars($club['name']) ?></h3>
              <div class="city"><?= htmlspecialchars($club['city']) ?></div>
              <?php if ($club['region']): ?>
                <div class="city region"><?= htmlspecialchars($club['region']) ?></div>
              <?php endif; ?>
              <?php
                $nonCoachPlayers = array_filter($club['players'] ?? [], function($p) {
                  return !in_array($p['position'] ?? '', ['Тренер', 'Помощник тренера']);
                });
              ?>
              <?php if (!empty($nonCoachPlayers)): ?>
                <div class="players-count">👥 <?= count($nonCoachPlayers) ?> игроков</div>
              <?php endif; ?>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </section>
    </main>

<script>
  document.querySelectorAll('.player-card').forEach(function(card) {
    card.addEventListener('click', function() { this.classList.toggle('flipped'); });
  });
</script>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
