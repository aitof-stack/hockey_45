<?php
$competitions = json_decode(file_get_contents(__DIR__ . '/../../data/competitions.json'), true);
$clubs = json_decode(file_get_contents(__DIR__ . '/../../data/clubs.json'), true);
$compId = isset($_GET['comp']) ? (int)$_GET['comp'] : 0;
$matchIdx = isset($_GET['match']) ? (int)$_GET['match'] : -1;

$selected = null;
$match = null;
foreach ($competitions as $c) {
  if ($c['id'] === $compId) {
    $selected = $c;
    if ($matchIdx >= 0 && isset($c['matches'][$matchIdx])) {
      $match = $c['matches'][$matchIdx];
    }
    break;
  }
}
if (!$selected || !$match) {
  header('Location: /sport/competitions/?id=' . $compId);
  exit;
}

$homeClub = null;
$awayClub = null;
foreach ($clubs as $cl) {
  if ($cl['id'] == $match['home_id']) $homeClub = $cl;
  if ($cl['id'] == $match['away_id']) $awayClub = $cl;
}

$isFinished = $match['home_goals'] !== '' && $match['away_goals'] !== '';
$pageTitle = ($homeClub['name'] ?? '?') . ' — ' . ($awayClub['name'] ?? '?');
$protocol = $match['protocol'] ?? [];
$hasEvents = !empty($protocol['home']['goals']) || !empty($protocol['away']['goals']) || !empty($protocol['home']['penalties']) || !empty($protocol['away']['penalties']);
?>
<?php require __DIR__ . '/../../inc/standings.php'; ?>
<?php require __DIR__ . '/../../inc/header.php'; ?>
<style>
  .competition-info { border-radius: 20px; padding: 14px 20px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: linear-gradient(180deg, var(--primary-dark), var(--primary)); }
  .competition-info .ci-name { font-weight: 700; color: #fff; font-size: 0.95rem; }
  .competition-info .ci-links { display: flex; gap: 8px; }
  .competition-info .ci-links a { padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 600; text-decoration: none; background: #E8F4FD; color: #1e293b; transition: background 0.2s; }
  .competition-info .ci-links a:hover { background: #d4e8f5; }

  .match-item-teamsresult { border-radius: 20px; padding: 20px 24px; text-align: center; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 16px; background: linear-gradient(180deg, var(--primary-dark), var(--primary)); }
  .match-item-teamsresult .mit-team { display: flex; flex-direction: column; align-items: center; gap: 6px; flex: 1; }
  .match-item-teamsresult .mit-team .mit-logo { width: 64px; height: 64px; border-radius: 12px; overflow: hidden; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; }
  .match-item-teamsresult .mit-team .mit-logo img { width: 100%; height: 100%; object-fit: contain; }
  .match-item-teamsresult .mit-team .mit-name { font-weight: 700; font-size: 1.1rem; color: #fff; }
  .match-item-teamsresult .mit-team .mit-city { font-size: 0.85rem; color: rgba(255,255,255,0.5); }
  .match-item-teamsresult .mit-result { display: flex; flex-direction: column; align-items: center; gap: 6px; flex-shrink: 0; min-width: 100px; }
  .match-item-teamsresult .mit-result .mit-score { font-size: 2.8rem; font-weight: 800; color: #fff; line-height: 1; }
  .match-item-teamsresult .mit-result .mit-score .so-badge { font-size: 1rem; color: #fbbf24; font-weight: 600; margin-left: 4px; }
  .match-item-teamsresult .mit-result .mit-periods { font-size: 0.8rem; color: rgba(255,255,255,0.5); display: flex; gap: 8px; }
  .match-item-teamsresult .mit-result .mit-periods span { background: rgba(255,255,255,0.1); padding: 2px 8px; border-radius: 6px; color: #fff; }
  .match-item-teamsresult .mit-result .mit-status { font-size: 0.85rem; font-weight: 600; padding: 3px 12px; border-radius: 6px; }
  .match-item-teamsresult .mit-result .mit-status.over { background: #dcfce7; color: #166534; }
  .match-item-teamsresult .mit-result .mit-status.pending { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.6); }

  .match-tabs-switcher { display: flex; gap: 4px; margin-bottom: 16px; border-radius: 12px; padding: 4px; background: linear-gradient(180deg, var(--primary-dark), var(--primary)); }
  .match-tabs-switcher button { flex: 1; padding: 10px 16px; text-align: center; border-radius: 10px; font-size: 0.88rem; font-weight: 700; color: rgba(255,255,255,0.6); cursor: pointer; transition: all 0.2s; border: none; background: none; font-family: inherit; }
  .match-tabs-switcher button.active { background: #E8F4FD; color: #1e293b; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
  .match-tab-panel { display: none; }
  .match-tab-panel.active { display: block; }

  .rosters-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .roster-block { border-radius: 20px; padding: 14px; background: linear-gradient(180deg, var(--primary-dark), var(--primary)); }
  .roster-block .rb-header { display: flex; align-items: center; gap: 10px; padding: 10px 4px 14px; }
  .roster-block .rb-header img { width: 28px; height: 28px; border-radius: 6px; object-fit: contain; }
  .roster-block .rb-header span { font-weight: 700; font-size: 1.1rem; color: #fff; }
  .roster-block .rb-group-title { font-weight: 700; font-size: 0.78rem; color: rgba(255,255,255,0.6); padding: 10px 4px 4px; text-transform: uppercase; letter-spacing: 0.5px; }
  .roster-block .rb-player { display: flex; align-items: center; gap: 8px; padding: 6px 8px; font-size: 0.9rem; background: #E8F4FD; border-radius: 10px; margin-bottom: 4px; }
  .roster-block .rb-player .rb-num { font-weight: 800; color: var(--primary); min-width: 28px; font-size: 0.85rem; text-align: center; }
  .roster-block .rb-player .rb-name { color: #1e293b; font-weight: 600; }

  .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .stats-block { overflow-x: auto; border-radius: 20px; padding: 14px; background: linear-gradient(180deg, var(--primary-dark), var(--primary)); }
  .stats-block .sb-header { display: flex; align-items: center; gap: 10px; padding: 10px 4px 14px; }
  .stats-block .sb-header img { width: 28px; height: 28px; border-radius: 6px; object-fit: contain; }
  .stats-block .sb-header span { font-weight: 700; font-size: 1.1rem; color: #fff; }
  .stats-block td.left { text-align: left; }
  .stats-block .sb-subtitle { font-weight: 700; font-size: 0.8rem; color: rgba(255,255,255,0.6); padding: 10px 4px 4px; text-transform: uppercase; letter-spacing: 0.5px; }

  .st-table { width: 100%; border-collapse: separate; border-spacing: 2px; font-family: inherit; }
  .st-table th { background: #a8c8e0; color: #1e293b; padding: 6px 4px; font-size: 0.85rem; font-weight: 700; white-space: nowrap; text-align: center; border-radius: 6px; text-transform: uppercase; letter-spacing: 1.2px; }
  .st-table td { padding: 8px 6px; text-align: center; border: none; white-space: nowrap; background: #E8F4FD; color: #1e293b; font-weight: 700; font-size: 1rem; border-radius: 10px; font-family: inherit; }

  .events-wrapper { border-radius: 20px; padding: 14px; background: linear-gradient(180deg, var(--primary-dark), var(--primary)); }
  .events-wrapper .ev-header { display: flex; align-items: center; gap: 12px; padding: 10px 4px 14px; font-weight: 700; font-size: 1.1rem; color: #fff; }
  .ev-row { display: grid; grid-template-columns: 1fr 70px 1fr; gap: 4px; padding: 4px 0; }
  .ev-home, .ev-away, .ev-time-col { background: #E8F4FD; border-radius: 10px; padding: 8px 12px; display: flex; align-items: center; min-height: 44px; }
  .ev-home:empty::before, .ev-away:empty::before { content: '\00A0'; }
  .ev-home { justify-content: flex-end; }
  .ev-away { justify-content: flex-start; }
  .ev-time-col { justify-content: center; flex-direction: column; }
  .ev-time-col .ev-time-val { font-size: 0.82rem; font-weight: 700; color: #1e293b; }
  .ev-time-col .ev-score-val { font-size: 0.72rem; color: #94a3b8; margin-top: 1px; }
  .ev-period-divider { text-align: center; font-weight: 700; font-size: 0.85rem; color: rgba(255,255,255,0.6); padding: 12px 4px 8px; text-transform: uppercase; letter-spacing: 0.5px; }

  .no-protocol { text-align: center; color: #94a3b8; padding: 60px 20px; font-size: 1rem; background: var(--white); border-radius: 16px; border: 1px solid var(--section-even-fone); }
  .back-link-top { display: inline-block; margin-bottom: 16px; color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.9rem; }
</style>

<main class="content">
  <section class="section">
    <a href="/sport/competitions/?id=<?= $compId ?>&tab=calendar" class="back-link-top">← Назад к турниру</a>

    <div class="competition-info">
      <div class="ci-name"><?= htmlspecialchars($selected['name']) ?></div>
      <div class="ci-links">
        <a href="/sport/competitions/?id=<?= $compId ?>">📊 Таблица</a>
        <a href="/sport/competitions/?id=<?= $compId ?>&tab=calendar">📅 Календарь</a>
        <a href="/sport/competitions/?id=<?= $compId ?>&tab=stats">📈 Статистика</a>
      </div>
    </div>

    <?php
    $periodsText = '';
    $periodScores = $protocol['home']['periodStats'] ?? [];
    if (!empty($periodScores)) {
      $parts = [];
      foreach (['1','2','3'] as $p) {
        if (isset($periodScores[$p])) {
          $parts[] = $periodScores[$p]['scoreA'] . ':' . $periodScores[$p]['scoreB'];
        }
      }
      if (!empty($parts)) $periodsText = implode(', ', $parts);
    }
    ?>

    <div class="match-item-teamsresult">
      <div class="mit-team">
        <div class="mit-logo"><?php if ($homeClub && !empty($homeClub['logo'])): ?><img src="<?= htmlspecialchars($homeClub['logo']) ?>"><?php else: ?>👤<?php endif; ?></div>
        <div class="mit-name"><?= htmlspecialchars($homeClub['name'] ?? '—') ?></div>
        <div class="mit-city"><?= htmlspecialchars($homeClub['city'] ?? '') ?></div>
      </div>
      <div class="mit-result">
        <div class="mit-score">
          <?= htmlspecialchars($match['home_goals']) ?>:<?= htmlspecialchars($match['away_goals']) ?>
          <?php if (!empty($match['shootout'])): ?><span class="so-badge">Б</span><?php endif; ?>
        </div>
        <?php if ($periodsText): ?>
        <div class="mit-periods"><span><?= $periodsText ?></span></div>
        <?php endif; ?>
        <div class="mit-status <?= $isFinished ? 'over' : 'pending' ?>"><?= $isFinished ? 'Окончен' : 'Не начался' ?></div>
        <div style="font-size:0.82rem;color:#94a3b8;margin-top:4px;"><?= htmlspecialchars($match['date']) ?> <?= htmlspecialchars($match['time'] ?? '') ?></div>
      </div>
      <div class="mit-team">
        <div class="mit-logo"><?php if ($awayClub && !empty($awayClub['logo'])): ?><img src="<?= htmlspecialchars($awayClub['logo']) ?>"><?php else: ?>👤<?php endif; ?></div>
        <div class="mit-name"><?= htmlspecialchars($awayClub['name'] ?? '—') ?></div>
        <div class="mit-city"><?= htmlspecialchars($awayClub['city'] ?? '') ?></div>
      </div>
    </div>

    <?php if (empty($protocol)): ?>
      <div class="no-protocol">Протокол матча ещё не загружен.</div>
    <?php else:
      $sides = [
        ['key' => 'home', 'club' => $homeClub, 'label' => 'home'],
        ['key' => 'away', 'club' => $awayClub, 'label' => 'away'],
      ];
    ?>
    <div class="match-tabs-switcher">
      <button class="tab-btn active" data-tab="rosters">📋 Составы</button>
      <?php if ($hasEvents): ?><button class="tab-btn" data-tab="events">⚡ События</button><?php endif; ?>
      <button class="tab-btn" data-tab="stats">📊 Статистика</button>
    </div>

    <!-- Rosters -->
    <div class="match-tab-panel active" id="tab-rosters">
      <div class="rosters-grid">
      <?php foreach ($sides as $side):
        $pdata = $protocol[$side['key']] ?? [];
        $club = $side['club'];
        $goalies = []; $defenders = []; $forwards = [];
        if (!empty($pdata['players'])) {
          foreach ($pdata['players'] as $rp) {
            $pos = $rp['position'] ?? '';
            if (strcasecmp($pos, 'Вр') === 0) $goalies[] = $rp;
            elseif (strcasecmp($pos, 'Защ') === 0) $defenders[] = $rp;
            else $forwards[] = $rp;
          }
        }
      ?>
        <div class="roster-block">
          <div class="rb-header">
            <?php if ($club && !empty($club['logo'])): ?><img src="<?= htmlspecialchars($club['logo']) ?>"><?php endif; ?>
            <span><?= htmlspecialchars($club['name'] ?? '') ?></span>
          </div>
          <?php if (!empty($goalies)): ?>
          <div class="rb-group-title">Вратари</div>
          <?php foreach ($goalies as $rp): ?>
          <div class="rb-player"><span class="rb-num"><?= htmlspecialchars($rp['number']) ?></span><span class="rb-name"><?= htmlspecialchars($rp['name']) ?></span></div>
          <?php endforeach; endif; ?>
          <?php if (!empty($defenders)): ?>
          <div class="rb-group-title">Защитники</div>
          <?php foreach ($defenders as $rp): ?>
          <div class="rb-player"><span class="rb-num"><?= htmlspecialchars($rp['number']) ?></span><span class="rb-name"><?= htmlspecialchars($rp['name']) ?></span></div>
          <?php endforeach; endif; ?>
          <?php if (!empty($forwards)): ?>
          <div class="rb-group-title">Нападающие</div>
          <?php foreach ($forwards as $rp): ?>
          <div class="rb-player"><span class="rb-num"><?= htmlspecialchars($rp['number']) ?></span><span class="rb-name"><?= htmlspecialchars($rp['name']) ?></span></div>
          <?php endforeach; endif; ?>
          <?php if (empty($pdata['players'])): ?>
          <div style="text-align:center;color:#94a3b8;padding:20px;">Состав не указан</div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      </div>
    </div>

    <!-- Events -->
    <?php if ($hasEvents): ?>
    <div class="match-tab-panel" id="tab-events">
      <div class="events-wrapper">
        <div class="ev-header">⚡ События матча</div>
        <?php
        $allEvents = [];
        $playerNameCache = [];
        foreach (['home', 'away'] as $side) {
          $pdata = $protocol[$side] ?? [];
          if (!empty($pdata['players'])) {
            foreach ($pdata['players'] as $rp) {
              $playerNameCache[$side][$rp['number']] = $rp['name'];
            }
          }
          foreach (($pdata['goals'] ?? []) as $g) {
            $g['type'] = 'goal'; $g['side'] = $side;
            $allEvents[] = $g;
          }
          foreach (($pdata['penalties'] ?? []) as $g) {
            $g['type'] = 'penalty'; $g['side'] = $side;
            $allEvents[] = $g;
          }
        }
        usort($allEvents, function($a, $b) {
          $pa = (int)($a['period'] ?? 1); $pb = (int)($b['period'] ?? 1);
          if ($pa !== $pb) return $pa - $pb;
          return strcmp($a['time'] ?? '', $b['time'] ?? '');
        });

        $currentPeriod = 0; $homeScore = 0; $awayScore = 0;
        foreach ($allEvents as $ev):
          $per = (int)($ev['period'] ?? 1);
          if ($per !== $currentPeriod): $currentPeriod = $per;
        ?>
          <div class="ev-period-divider"><?= $per ?>-й период</div>
        <?php endif;
          if ($ev['type'] === 'goal'):
            $scorerNum = $ev['scorer'] ?? '';
            $side = $ev['side'];
            $scorerName = $playerNameCache[$side][$scorerNum] ?? '#' . $scorerNum;
            if ($side === 'home') $homeScore++; else $awayScore++;
        ?>
          <div class="ev-row">
            <div class="ev-home">
              <?php if ($side === 'home'): ?>
                <strong><?= htmlspecialchars($scorerName) ?></strong> #<?= htmlspecialchars($scorerNum) ?>
                <?php if (!empty($ev['assist1'])): $a1 = $playerNameCache[$side][$ev['assist1']] ?? '#' . $ev['assist1']; ?><br><span style="font-size:0.78rem;color:#64748b;">ас. <?= htmlspecialchars($a1) ?></span><?php endif; ?>
                <?php if (!empty($ev['assist2'])): $a2 = $playerNameCache[$side][$ev['assist2']] ?? '#' . $ev['assist2']; ?><br><span style="font-size:0.78rem;color:#64748b;">ас. <?= htmlspecialchars($a2) ?></span><?php endif; ?>
              <?php endif; ?>
            </div>
            <div class="ev-time-col">
              <div class="ev-time-val"><?= htmlspecialchars($ev['time'] ?? '') ?></div>
              <div class="ev-score-val"><?= $homeScore ?>:<?= $awayScore ?></div>
            </div>
            <div class="ev-away">
              <?php if ($side === 'away'): ?>
                <strong><?= htmlspecialchars($scorerName) ?></strong> #<?= htmlspecialchars($scorerNum) ?>
                <?php if (!empty($ev['assist1'])): $a1 = $playerNameCache[$side][$ev['assist1']] ?? '#' . $ev['assist1']; ?><br><span style="font-size:0.78rem;color:#64748b;">ас. <?= htmlspecialchars($a1) ?></span><?php endif; ?>
                <?php if (!empty($ev['assist2'])): $a2 = $playerNameCache[$side][$ev['assist2']] ?? '#' . $ev['assist2']; ?><br><span style="font-size:0.78rem;color:#64748b;">ас. <?= htmlspecialchars($a2) ?></span><?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php elseif ($ev['type'] === 'penalty'):
            $pNum = $ev['player'] ?? '';
            $side = $ev['side'];
            $pName = $playerNameCache[$side][$pNum] ?? '#' . $pNum;
        ?>
          <div class="ev-row">
            <div class="ev-home">
              <?php if ($side === 'home'): ?>
                <span style="color:#dc2626;font-weight:600;">Штраф <?= htmlspecialchars($ev['minutes'] ?? '2') ?>'</span>
                <span style="font-size:0.8rem;"><?= htmlspecialchars($pName) ?></span>
                <span style="font-size:0.72rem;color:#94a3b8;"><?= htmlspecialchars($ev['reason'] ?? '') ?></span>
              <?php endif; ?>
            </div>
            <div class="ev-time-col">
              <div class="ev-time-val"><?= htmlspecialchars($ev['time'] ?? '') ?></div>
            </div>
            <div class="ev-away">
              <?php if ($side === 'away'): ?>
                <span style="color:#dc2626;font-weight:600;">Штраф <?= htmlspecialchars($ev['minutes'] ?? '2') ?>'</span>
                <span style="font-size:0.8rem;"><?= htmlspecialchars($pName) ?></span>
                <span style="font-size:0.72rem;color:#94a3b8;"><?= htmlspecialchars($ev['reason'] ?? '') ?></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="match-tab-panel" id="tab-stats">
      <div class="stats-grid">
      <?php foreach ($sides as $side):
        $pdata = $protocol[$side['key']] ?? [];
        $club = $side['club'];
        $skaters = [];
        foreach ($pdata['playerStats'] ?? [] as $num => $ps) {
          $name = $num; $pos = '';
          if (!empty($pdata['players'])) {
            foreach ($pdata['players'] as $rp) {
              if ((string)$rp['number'] === (string)$num) { $name = $rp['name']; $pos = $rp['position']; break; }
            }
          }
          if (strcasecmp($pos, 'Вр') === 0) continue;
          $skaters[$num] = ['name' => $name, 'pos' => $pos, 'g' => (int)($ps['goals'] ?? 0), 'a' => (int)($ps['assists'] ?? 0), 'p' => (int)($ps['points'] ?? 0), 'pim' => (float)($ps['pim'] ?? 0)];
        }
        uasort($skaters, function($a, $b) { return $b['p'] - $a['p']; });
        $goalies = [];
        foreach ($pdata['goalieStats'] ?? [] as $num => $gs) {
          $name = $num;
          if (!empty($pdata['players'])) {
            foreach ($pdata['players'] as $rp) {
              if ((string)$rp['number'] === (string)$num) { $name = $rp['name']; break; }
            }
          }
          $ga = (int)($gs['goalsAgainst'] ?? 0); $toi = (int)($gs['timeOnIce'] ?? 0);
          $gaa = $toi > 0 ? round($ga / ($toi / 60), 1) : 0;
          $goalies[$num] = ['name' => $name, 'ga' => $ga, 'gaa' => $gaa, 'toi' => $toi];
        }
      ?>
        <div class="stats-block">
          <div class="sb-header">
            <?php if ($club && !empty($club['logo'])): ?><img src="<?= htmlspecialchars($club['logo']) ?>"><?php endif; ?>
            <span><?= htmlspecialchars($club['name'] ?? '') ?></span>
          </div>
          <?php if (!empty($skaters)): ?>
          <div class="sb-subtitle">Полевые игроки</div>
          <table class="st-table" style="table-layout:auto;">
            <thead><tr><th>#</th><th style="text-align:left;">Игрок</th><th>Г</th><th>П</th><th>О</th><th>Штр</th></tr></thead>
            <tbody>
              <?php foreach ($skaters as $num => $p): ?>
              <tr><td><?= htmlspecialchars($num) ?></td><td class="left"><?= htmlspecialchars($p['name']) ?></td><td><?= $p['g'] ?></td><td><?= $p['a'] ?></td><td style="font-weight:700;"><?= $p['p'] ?></td><td><?= $p['pim'] ? number_format($p['pim'], 1, '.', '') : '0' ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
          <?php if (!empty($goalies)): ?>
          <div class="sb-subtitle" style="margin-top:8px;">Вратари</div>
          <table class="st-table" style="table-layout:auto;">
            <thead><tr><th>#</th><th style="text-align:left;">Вратарь</th><th>КН</th><th>ПШ</th><th>Время</th></tr></thead>
            <tbody>
              <?php foreach ($goalies as $num => $g):
                $toiMin = floor($g['toi'] / 60); $toiSec = $g['toi'] % 60;
              ?>
              <tr><td><?= htmlspecialchars($num) ?></td><td class="left"><?= htmlspecialchars($g['name']) ?></td><td><?= number_format($g['gaa'], 1, '.', '') ?></td><td><?= $g['ga'] ?></td><td><?= $toiMin ?>:<?= str_pad($toiSec, 2, '0', STR_PAD_LEFT) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
          <?php if (empty($skaters) && empty($goalies)): ?>
          <div style="text-align:center;color:#94a3b8;padding:20px;">Нет данных</div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      </div>
    </div>

    <?php endif; ?>
  </section>
</main>
<script>
document.querySelectorAll('.tab-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.match-tab-panel').forEach(function(p) { p.classList.remove('active'); });
    btn.classList.add('active');
    var panel = document.getElementById('tab-' + btn.getAttribute('data-tab'));
    if (panel) panel.classList.add('active');
  });
});
</script>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
