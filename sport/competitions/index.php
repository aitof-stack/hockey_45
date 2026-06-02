<?php
$competitions = json_decode(file_get_contents(__DIR__ . '/../../data/competitions.json'), true);
$clubs = json_decode(file_get_contents(__DIR__ . '/../../data/clubs.json'), true);
$compId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selected = null;
if ($compId) {
  foreach ($competitions as $c) { if ($c['id'] === $compId) { $selected = $c; break; } }
}
$pageTitle = $selected ? $selected['name'] : 'Соревнования';
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : '';
?>
<?php require __DIR__ . '/../../inc/standings.php'; ?>
<?php require __DIR__ . '/../../inc/header.php'; ?>
<style>
    .comp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
    .comp-card { border-radius: 20px; padding: 20px; text-align: center; text-decoration: none; display: block; cursor: pointer; background: linear-gradient(180deg, var(--primary-dark), var(--primary)); transition: transform 0.2s, box-shadow 0.2s; }
    .comp-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    .comp-card h3 { color: #fff; margin: 5px 0; }
    .comp-card .meta { color: rgba(255,255,255,0.5); font-size: 0.9rem; }
    .comp-card .badge { display: inline-block; margin-top: 8px; padding: 4px 14px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; }
    .badge-active { background: #dcfce7; color: #166534; }
    .badge-finished { background: #f1f5f9; color: #475569; }
    .badge-upcoming { background: #fef3c7; color: #92400e; }
    .badge-finished { display: none; }
    .comp-detail { max-width: 960px; margin: 0 auto; }
    .comp-detail h1 { color: var(--primary-dark); }
    .comp-detail .meta { color: #64748b; margin-bottom: 20px; }
    .desc-block { background: var(--white); border-radius: 14px; padding: 20px; border: 1px solid var(--section-even-fone); margin-bottom: 20px; }
    .club-list { display: flex; flex-wrap: wrap; gap: 10px; margin: 15px 0; }
    .club-tag { background: var(--section-even-fone); color: var(--primary-dark); padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; }

    .gt-table-wrap { overflow-x: auto; margin: 15px 0; border-radius: 20px; padding: 14px; background: linear-gradient(180deg, var(--primary-dark), var(--primary)); }
    .st-table { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 2px; font-family: inherit; }
    .st-table th { background: #a8c8e0; color: #1e293b; padding: 6px 4px; font-size: 0.85rem; font-weight: 700; white-space: nowrap; text-align: center; border-radius: 6px; text-transform: uppercase; letter-spacing: 1.2px; }
    .st-table th.gt-opponent { min-width: 70px; padding: 4px; }
    .st-table th.gt-opponent img { max-width: 24px; max-height: 24px; object-fit: contain; display: block; margin: 0 auto; }
    .st-table th.gt-opponent span { font-size: 0.85rem; }
    .st-table th.sticky { position: sticky; left: 0; z-index: 2; background: #a8c8e0; border-radius: 6px 0 0 6px; min-width: 200px; }
    .st-table td { padding: 8px 6px; text-align: center; border: none; white-space: nowrap; background: #E8F4FD; color: #1e293b; font-weight: 700; font-size: 1rem; border-radius: 10px; font-family: inherit; }
    .st-table td:first-child { width: 40px; }
    .st-table td.club-cell { text-align: left; font-weight: 700; min-width: 140px; }
    .st-table td.gt-cell-team { text-align: left; position: sticky; left: 0; z-index: 1; background: #E8F4FD; border-radius: 10px 0 0 10px; width: 260px; min-width: 200px; }
    .st-table tr:nth-child(1) td { font-weight: 900; }
    .st-table tr:nth-child(1) td.gt-cell-team { background: #fef3c7; }
    .st-table tr:nth-child(2) td.gt-cell-team { background: #f0fdf4; }
    .st-table tr:nth-child(3) td.gt-cell-team { background: #fff8f0; }
    .gt-team-inner { display: flex; align-items: center; gap: 8px; }
    .gt-team-inner img { width: 28px; height: 28px; object-fit: contain; flex-shrink: 0; border-radius: 4px; }
    .gt-team-inner span { font-size: 1rem; flex-shrink: 0; }
    .gt-matches { font-size: 1rem; font-weight: 700; }
    .gt-matches.win { color: #166534; }
    .gt-matches.loss { color: #991b1b; }
    .gt-matches.draw { color: #92400e; }
    .gt-matches.same-team { font-size: 0.85rem; color: #cbd5e1; }
    .gt-so { display: inline-block; font-size: 0.7rem; color: #92400e; background: #fef3c7; padding: 0 4px; border-radius: 4px; margin-left: 2px; vertical-align: super; }
    .gt-pts { font-weight: 800; color: var(--primary-dark); }

    .matches-list { margin: 15px 0; }
    .empty-msg { text-align: center; padding: 40px; color: #94a3b8; }
    .stats-team-block { background:var(--white); border-radius:12px; border:1px solid var(--section-even-fone); margin-bottom:16px; overflow:hidden; }
    .stats-team-header { display:flex; align-items:center; gap:10px; padding:10px 14px; background:#f8fafc; border-bottom:1px solid var(--section-even-fone); font-weight:700; font-size:0.95rem; color:var(--primary-dark); }
    .stats-team-logo { width:28px; height:28px; object-fit:contain; border-radius:6px; }
    .back-link { display: inline-block; margin-bottom: 20px; color: var(--primary); text-decoration: none; font-weight: 600; }
    .back-link:hover { text-decoration: underline; }

    .block-a { display: grid; grid-template-columns: auto 1fr; gap: 16px; margin-bottom: 24px; }
    .block-a-logo { grid-row: 1 / 3; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; background: var(--white); border-radius: 16px; border: 1px solid var(--section-even-fone); padding: 10px; }
    .block-a-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .block-a-title { border: 2px solid var(--primary-dark); border-radius: 14px; padding: 14px 20px; display: flex; align-items: center; }
    .block-a-title h1 { margin: 0; font-size: 1.3rem; }
    .block-a-title .badge { margin-left: 12px; }
    .block-a-stats { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; }
    .block-a-stat { border: 1px solid var(--section-even-fone); border-radius: 12px; padding: 10px 8px; text-align: center; background: var(--white); }
    .block-a-stat .label { font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
    .block-a-stat .value { font-size: 1.2rem; font-weight: 800; color: var(--primary-dark); margin-top: 2px; }


    .block-b-tabs { display: flex; gap: 8px; margin-bottom: 6px; justify-content: center; }
    .block-b-tab { padding: 6px 18px; border: 2px solid var(--section-even-fone); border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #64748b; background: var(--white); transition: all 0.2s; }
    .block-b-tab:hover { border-color: var(--primary); color: var(--primary); }
    .block-b-tab.active { border-color: var(--primary-dark); color: var(--primary-dark); background: #f0f4f8; }
    .block-b-subtabs { display: flex; gap: 6px; margin-bottom: 12px; }
    .block-b-subtab { padding: 4px 14px; border: 1px solid var(--section-even-fone); border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-weight: 600; color: #94a3b8; background: var(--white); transition: all 0.2s; }
    .block-b-subtab:hover { border-color: var(--accent); color: var(--accent); }
    .block-b-subtab.active { border-color: var(--accent); color: var(--accent); background: #fff8f0; }
    .block-b-panel { display: none; }
    .block-b-panel.active { display: block; }
    .glossary { margin-top: 16px; padding: 14px 18px; background: var(--white); border-radius: 12px; border: 1px solid var(--section-even-fone); font-size: 0.82rem; color: #64748b; }
    .glossary b { color: var(--primary-dark); }

    .mc-item { background: var(--white); border-radius: 14px; border: 1px solid var(--section-even-fone); overflow: hidden; margin-bottom: 12px; transition: box-shadow 0.2s; }
    .mc-item:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
    .mc-item.past { border-left: 4px solid #10b981; }
    .mc-item-top { display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; background: #f8fafc; border-bottom: 1px solid var(--section-even-fone); font-size: 0.85rem; }
    .mc-item-date { color: #64748b; font-weight: 600; }
    .mc-item-status { font-size: 0.75rem; font-weight: 600; color: #10b981; }
    .mc-item-status.future { color: #94a3b8; }
    .mc-item-main { display: flex; align-items: center; padding: 14px 16px; gap: 12px; }
    .mc-team { display: flex; align-items: center; gap: 10px; flex: 1; }
    .mc-away { flex-direction: row-reverse; }
    .mc-team-img { width: 48px; height: 48px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: var(--section-even-fone); display: flex; align-items: center; justify-content: center; }
    .mc-team-img img { width: 100%; height: 100%; object-fit: contain; }
    .mc-team-img span { font-size: 1.4rem; }
    .mc-team-info { flex: 1; min-width: 0; }
    .mc-team-info-right { text-align: right; }
    .mc-team-name { font-weight: 700; font-size: 0.95rem; color: var(--primary-dark); }
    .mc-team-city { font-size: 0.75rem; color: #94a3b8; margin-top: 1px; }
    .mc-score { text-align: center; flex-shrink: 0; min-width: 80px; padding: 0 8px; }
    .mc-score a { font-size: 1.6rem; font-weight: 800; color: var(--primary-dark); text-decoration: none; }
    .mc-score a:hover { color: var(--accent); }
    .mc-so { font-size: 0.65rem; color: #92400e; background: #fef3c7; padding: 2px 8px; border-radius: 8px; margin-top: 4px; }



  .stat-cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
  .stat-card { border-radius: 20px; padding: 14px; background: linear-gradient(180deg, var(--primary-dark), var(--primary)); }
  .stat-card-header { padding: 4px 4px 12px; font-weight: 700; font-size: 1rem; color: #fff; }
  .stat-card-sub { font-weight: 400; font-size: 0.75rem; color: rgba(255,255,255,0.65); }
  .stat-card-row { display: flex; align-items: center; gap: 8px; padding: 6px 8px; background: #E8F4FD; border-radius: 10px; margin-bottom: 4px; font-size: 0.9rem; }
  .stat-card-rank { font-weight: 800; color: var(--primary); min-width: 22px; text-align: center; font-size: 0.85rem; }
  .stat-card-name { flex: 1; color: #1e293b; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .stat-card-val { font-weight: 800; color: var(--primary-dark); font-size: 1rem; min-width: 30px; text-align: center; }
  .stat-card-club { font-size: 0.72rem; color: #64748b; }
  .stat-card-empty { text-align: center; color: rgba(255,255,255,0.5); padding: 20px; font-size: 0.85rem; }
  </style>

  <main class="content">
    <section>
      <?php if ($selected): ?>
        <a href="/sport/competitions/" class="back-link">← Все соревнования</a>
        <div class="comp-detail">

          <?php
          $matches = $selected['matches'] ?? [];
          $clubIds = $selected['club_ids'] ?? [];

          // Compute stats
          $firstDate = '';
          $cities = [];
          $totalPlayers = 0;
          $totalGoals = 0;
          foreach ($matches as $m) {
            if (!empty($m['date']) && ($firstDate === '' || $m['date'] < $firstDate)) $firstDate = $m['date'];
            if (isset($m['home_goals']) && $m['home_goals'] !== '') $totalGoals += (int)$m['home_goals'];
            if (isset($m['away_goals']) && $m['away_goals'] !== '') $totalGoals += (int)$m['away_goals'];
          }
          foreach ($clubIds as $cid) {
            foreach ($clubs as $c) {
              if ($c['id'] === $cid) {
                if (!empty($c['city'])) $cities[$c['city']] = true;
                $nonCoachPlayers = array_filter($c['players'] ?? [], function($p) { return !in_array($p['position'] ?? '', ['Тренер', 'Помощник тренера']); });
                $totalPlayers += count($nonCoachPlayers);
                break;
              }
            }
          }
          $cityCount = count($cities);
          $teamCount = count($clubIds);
          $matchCount = count($matches);
          $statusMap = ['active' => 'Активен', 'finished' => 'Завершён', 'upcoming' => 'Предстоящий'];
          ?>

          <!-- Block A -->
          <div class="block-a">
            <div class="block-a-logo">
              <?php if (!empty($selected['logo'])): ?>
                <img src="<?= htmlspecialchars($selected['logo']) ?>" alt="<?= htmlspecialchars($selected['name']) ?>">
              <?php else: ?>
                <span style="font-size:2.5rem;">🏆</span>
              <?php endif; ?>
            </div>
            <div class="block-a-title">
              <h1><?= htmlspecialchars($selected['name']) ?></h1>
              <span class="badge badge-<?= $selected['status'] ?? 'upcoming' ?>"><?= $statusMap[$selected['status'] ?? 'upcoming'] ?></span>
            </div>
            <div class="block-a-stats">
              <div class="block-a-stat"><div class="label">Начало</div><div class="value"><?= htmlspecialchars($firstDate) ?: '—' ?></div></div>
              <div class="block-a-stat"><div class="label">Города</div><div class="value"><?= $cityCount ?></div></div>
              <div class="block-a-stat"><div class="label">Команды</div><div class="value"><?= $teamCount ?></div></div>
              <div class="block-a-stat"><div class="label">Игроки</div><div class="value"><?= $totalPlayers ?></div></div>
              <div class="block-a-stat"><div class="label">Матчи</div><div class="value"><?= $matchCount ?></div></div>
              <div class="block-a-stat"><div class="label">Голы</div><div class="value"><?= $totalGoals ?></div></div>
            </div>
          </div>

          <?php if (!empty($selected['description'])): ?>
            <div class="desc-block"><?= nl2br(htmlspecialchars($selected['description'])) ?></div>
          <?php endif; ?>

          <!-- Block B -->
          <?php $standings = computeStandings($matches, $clubIds); ?>

          <div class="block-b-tabs">
            <span class="block-b-tab" data-btab="classic">📊 Таблица</span>
            <span class="block-b-tab" data-btab="calendar">📅 Календарь</span>
            <span class="block-b-tab" data-btab="stats">📈 Статистика</span>
          </div>

          <div class="block-b-subtabs">
            <span class="block-b-subtab active" data-bsubtab="classic">Классика</span>
            <span class="block-b-subtab" data-bsubtab="chess">Шахматка</span>
          </div>

          <!-- Classic panel (simple standings) -->
          <div class="block-b-panel active" data-bpanel="classic">
            <?php if (!empty($standings)): ?>
            <div class="gt-table-wrap">
            <table class="st-table" style="table-layout:fixed;">
              <thead><tr><th style="width:44px;">#</th><th style="width:260px;">Команда</th><th>И</th><th>В</th><th>ВБ</th><th>ПБ</th><th>П</th><th>ШЗ</th><th>ШП</th><th>±</th><th>О</th></tr></thead>
              <tbody>
                <?php $rank = 0; foreach ($standings as $cid => $s): $rank++;
                  $rowLogo = clubLogo($cid, $clubs);
                  $rowName = clubName($cid, $clubs);
                  $rowCity = '';
                  foreach ($clubs as $c) { if ($c['id'] == $cid) { $rowCity = $c['city'] ?? ''; break; } }
                  $games = $s['w']+$s['w_so']+$s['l_so']+$s['l'];
                  $gd = $s['gf']-$s['ga'];
                  $gdStr = $gd > 0 ? '+'.$gd : $gd;
                ?>
                <tr>
                  <td><?= $rank ?></td>
                  <td class="gt-cell-team">
                    <div class="gt-team-inner">
                      <?php if ($rowLogo): ?>
                        <img src="<?= htmlspecialchars($rowLogo) ?>" alt="">
                      <?php else: ?>
                        <span>🏒</span>
                      <?php endif; ?>
                      <span><?= $rowName ?></span>
                    </div>
                  </td>
                  <td><?= $games ?></td>
                  <td><?= $s['w'] ?></td><td><?= $s['w_so'] ?></td>
                  <td><?= $s['l_so'] ?></td><td><?= $s['l'] ?></td>
                  <td><?= $s['gf'] ?></td><td><?= $s['ga'] ?></td>
                  <td><?= $gdStr ?></td>
                  <td class="gt-pts"><?= $s['pts'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>
            <?php else: ?>
              <p class="empty-msg">Нет данных для таблицы.</p>
            <?php endif; ?>
          </div>

          <!-- Chess panel (integrated table with head-to-head) -->
          <div class="block-b-panel" data-bpanel="chess">
            <?php if (!empty($standings)):
              $clubIdsSorted = array_keys($standings);
              $n = count($clubIdsSorted);
            ?>
            <div class="gt-table-wrap">
            <table class="st-table gt-table" style="width:100%;table-layout:auto;">
              <thead>
                <tr>
                  <th style="width:40px;">М</th>
                  <th class="sticky" style="width:260px;">Команда</th>
                  <?php foreach ($clubIdsSorted as $ci):
                    $logo = clubLogo($ci, $clubs);
                    $name = clubName($ci, $clubs);
                  ?>
                    <th class="gt-opponent" title="<?= $name ?>">
                      <?php if ($logo): ?>
                        <img src="<?= htmlspecialchars($logo) ?>" alt="<?= $name ?>">
                      <?php else: ?>
                        <span>🏒</span>
                      <?php endif; ?>
                    </th>
                  <?php endforeach; ?>
                  <th style="width:70px;">И</th><th style="width:70px;">±</th><th style="width:70px;">О</th>
                </tr>
              </thead>
              <tbody>
                <?php $rank = 0; foreach ($standings as $cid => $s): $rank++;
                  $rowName = clubName($cid, $clubs);
                  $rowLogo = clubLogo($cid, $clubs);
                  $rowCity = '';
                  foreach ($clubs as $c) { if ($c['id'] == $cid) { $rowCity = $c['city'] ?? ''; break; } }
                  $games = $s['w']+$s['w_so']+$s['l_so']+$s['l'];
                  $gd = $s['gf']-$s['ga'];
                  $gdStr = $gd > 0 ? '+'.$gd : $gd;
                ?>
                <tr>
                  <td><?= $rank ?></td>
                  <td class="gt-cell-team">
                    <div class="gt-team-inner">
                      <?php if ($rowLogo): ?>
                        <img src="<?= htmlspecialchars($rowLogo) ?>" alt="">
                      <?php else: ?>
                        <span>🏒</span>
                      <?php endif; ?>
                      <span><?= $rowName ?></span>
                    </div>
                  </td>
                  <?php foreach ($clubIdsSorted as $ci):
                    if ($cid === $ci):
                      $selfLogo = clubLogo($ci, $clubs);
                  ?>
                    <td class="gt-matches same-team">
                      <?php if ($selfLogo): ?>
                        <img src="<?= htmlspecialchars($selfLogo) ?>" alt="" style="max-width:20px;max-height:20px;opacity:0.2;">
                      <?php else: ?>
                        <span style="opacity:0.2;">🏒</span>
                      <?php endif; ?>
                    </td>
                  <?php else:
                    $score = '';
                    $scCls = '';
                    foreach ($matches as $m) {
                      if (($m['home_id'] == $cid && $m['away_id'] == $ci) || ($m['home_id'] == $ci && $m['away_id'] == $cid)) {
                        if ($m['home_goals'] === '' || $m['away_goals'] === '') continue;
                        $hg = (int)$m['home_goals']; $ag = (int)$m['away_goals'];
                        if ($m['home_id'] == $cid) {
                          $score = $hg . ':' . $ag;
                          if ($hg > $ag) $scCls = 'win';
                          elseif ($hg < $ag) $scCls = 'loss';
                          else $scCls = 'draw';
                        } else {
                          $score = $ag . ':' . $hg;
                          if ($ag > $hg) $scCls = 'win';
                          elseif ($ag < $hg) $scCls = 'loss';
                          else $scCls = 'draw';
                        }
                        if (!empty($m['shootout'])) $score .= '<span class="gt-so">Б</span>';
                        break;
                      }
                    }
                  ?>
                    <td class="gt-matches <?= $scCls ?>"><?= $score ?: '—' ?></td>
                  <?php endif; endforeach; ?>
                  <td><?= $games ?></td>
                  <td><?= $gdStr ?></td>
                  <td class="gt-pts"><?= $s['pts'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>
            <?php else: ?>
              <p class="empty-msg">Нет данных для шахматки.</p>
            <?php endif; ?>
          </div>

          <!-- Calendar (matches) panel -->
          <div class="block-b-panel" data-bpanel="calendar">
            <?php if (!empty($matches)): ?>
            <div class="matches-list">
              <?php $matchIdx = 0; foreach ($matches as $m):
                $homeName = clubName($m['home_id'] ?? 0, $clubs);
                $awayName = clubName($m['away_id'] ?? 0, $clubs);
                $homeLogo = clubLogo($m['home_id'] ?? 0, $clubs);
                $awayLogo = clubLogo($m['away_id'] ?? 0, $clubs);
                $homeCity = '';
                $awayCity = '';
                foreach ($clubs as $c) {
                  if ($c['id'] == ($m['home_id'] ?? 0)) $homeCity = $c['city'] ?? '';
                  if ($c['id'] == ($m['away_id'] ?? 0)) $awayCity = $c['city'] ?? '';
                }
                $hg = $m['home_goals'] !== '' ? (int)$m['home_goals'] : '?';
                $ag = $m['away_goals'] !== '' ? (int)$m['away_goals'] : '?';
                $isFinished = $m['home_goals'] !== '' && $m['away_goals'] !== '';
                $soText = !empty($m['shootout']) ? ' (б)' : '';
              ?>
                <div class="mc-item <?= $isFinished ? 'past' : '' ?>">
                  <div class="mc-item-top">
                    <span class="mc-item-date"><?= htmlspecialchars($m['date'] ?? '') ?></span>
                    <?php if ($isFinished): ?>
                      <span class="mc-item-status">Окончен</span>
                    <?php else: ?>
                      <span class="mc-item-status future">Не начался</span>
                    <?php endif; ?>
                  </div>
                  <div class="mc-item-main">
                    <div class="mc-team mc-home">
                      <div class="mc-team-img">
                        <?php if ($homeLogo): ?>
                          <img src="<?= htmlspecialchars($homeLogo) ?>" alt="<?= $homeName ?>">
                        <?php else: ?>
                          <span>🏒</span>
                        <?php endif; ?>
                      </div>
                      <div class="mc-team-info">
                        <div class="mc-team-name"><?= $homeName ?></div>
                        <div class="mc-team-city"><?= htmlspecialchars($homeCity) ?></div>
                      </div>
                    </div>
                    <div class="mc-score">
                      <a href="/sport/match/?comp=<?= $compId ?>&match=<?= $matchIdx ?>"><?= $hg ?> : <?= $ag ?></a>
                      <?php if (!empty($m['shootout'])): ?>
                        <div class="mc-so">буллиты</div>
                      <?php endif; ?>
                    </div>
                    <div class="mc-team mc-away">
                      <div class="mc-team-info mc-team-info-right">
                        <div class="mc-team-name"><?= $awayName ?></div>
                        <div class="mc-team-city"><?= htmlspecialchars($awayCity) ?></div>
                      </div>
                      <div class="mc-team-img">
                        <?php if ($awayLogo): ?>
                          <img src="<?= htmlspecialchars($awayLogo) ?>" alt="<?= $awayName ?>">
                        <?php else: ?>
                          <span>🏒</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <div style="text-align:center;margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9;">
                    <a href="/sport/match/?comp=<?= $compId ?>&match=<?= $matchIdx ?>" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;font-size:0.8rem;font-weight:600;color:var(--primary);"><img src="/img/emojis/protocol.jpg" style="width:16px;height:16px;border-radius:3px;"> Протокол</a>
                  </div>
                </div>
              <?php $matchIdx++; endforeach; ?>
            </div>
            <?php else: ?>
              <p class="empty-msg">Матчей пока нет.</p>
            <?php endif; ?>
          </div>

          <!-- Stats panel -->
          <div class="block-b-panel" data-bpanel="stats">
            <?php
            // Aggregate player stats from protocol data
            $teamStats = [];
            $hasStats = false;

            // Tournament-wide aggregates
            $allGoalScorers = []; // first goal scorers
            $playerNameCache = []; // clubId -> num -> name+pos

            foreach ($matches as $m) {
              if (empty($m['protocol'])) continue;
              $hasStats = true;
              $sides = [
                ['side' => 'home', 'clubId' => $m['home_id']],
                ['side' => 'away', 'clubId' => $m['away_id']],
              ];

              // Track first goal of this match
              $firstGoal = null;
              foreach ($sides as $s) {
                $cid = $s['clubId'];
                if (!empty($m['protocol'][$s['side']]['goals'])) {
                  foreach ($m['protocol'][$s['side']]['goals'] as $g) {
                    $key = (int)($g['period'] ?? 1) . '-' . str_pad($g['time'] ?? '99:99', 5, '0', STR_PAD_LEFT);
                    if ($firstGoal === null || $key < $firstGoal['key']) {
                      $num = $g['scorer'] ?? '';
                      $pname = $num;
                      if (!empty($m['protocol'][$s['side']]['players'])) {
                        foreach ($m['protocol'][$s['side']]['players'] as $rp) {
                          if ((string)$rp['number'] === (string)$num) { $pname = $rp['name']; break; }
                        }
                      }
                      $firstGoal = ['key' => $key, 'scorer' => $num, 'name' => $pname, 'clubId' => $cid];
                    }
                  }
                }
              }
              if ($firstGoal) {
                $ukey = $firstGoal['clubId'] . '_' . $firstGoal['scorer'];
                if (!isset($allGoalScorers[$ukey])) {
                  $allGoalScorers[$ukey] = ['name' => $firstGoal['name'], 'clubId' => $firstGoal['clubId'], 'count' => 0];
                }
                $allGoalScorers[$ukey]['count']++;
                // store club name
                foreach ($clubs as $c) { if ($c['id'] == $firstGoal['clubId']) { $allGoalScorers[$ukey]['clubName'] = $c['name']; break; } }
              }

              foreach ($sides as $s) {
                $cid = $s['clubId'];
                $pdata = $m['protocol'][$s['side']];
                if (!isset($teamStats[$cid])) {
                  $teamStats[$cid] = ['players' => [], 'goalies' => []];
                }
                // Build name cache
                if (!empty($pdata['players'])) {
                  foreach ($pdata['players'] as $rp) {
                    $playerNameCache[$cid][$rp['number']] = ['name' => $rp['name'], 'position' => $rp['position'] ?? ''];
                  }
                }
                // Player stats (skaters)
                if (!empty($pdata['playerStats'])) {
                  foreach ($pdata['playerStats'] as $num => $ps) {
                    if (!isset($teamStats[$cid]['players'][$num])) {
                      $pInfo = ['name' => $num, 'position' => '', 'goals' => 0, 'assists' => 0, 'points' => 0, 'pim' => 0, 'games' => 0];
                      if (isset($playerNameCache[$cid][$num])) {
                        $pInfo['name'] = $playerNameCache[$cid][$num]['name'];
                        $pInfo['position'] = $playerNameCache[$cid][$num]['position'];
                      }
                      $teamStats[$cid]['players'][$num] = $pInfo;
                    }
                    $teamStats[$cid]['players'][$num]['goals'] += (int)($ps['goals'] ?? 0);
                    $teamStats[$cid]['players'][$num]['assists'] += (int)($ps['assists'] ?? 0);
                    $teamStats[$cid]['players'][$num]['points'] += (int)($ps['points'] ?? 0);
                    $teamStats[$cid]['players'][$num]['pim'] += (float)($ps['pim'] ?? 0);
                    $teamStats[$cid]['players'][$num]['games']++;
                  }
                }
                // Goalie stats
                if (!empty($pdata['goalieStats'])) {
                  foreach ($pdata['goalieStats'] as $num => $gs) {
                    if (!isset($teamStats[$cid]['goalies'][$num])) {
                      $gInfo = ['name' => $num, 'saves' => 0, 'goalsAgainst' => 0, 'timeOnIce' => 0, 'games' => 0];
                      if (isset($playerNameCache[$cid][$num])) {
                        $gInfo['name'] = $playerNameCache[$cid][$num]['name'];
                      }
                      $teamStats[$cid]['goalies'][$num] = $gInfo;
                    }
                    $teamStats[$cid]['goalies'][$num]['saves'] += (int)($gs['saves'] ?? 0);
                    $teamStats[$cid]['goalies'][$num]['goalsAgainst'] += (int)($gs['goalsAgainst'] ?? 0);
                    $teamStats[$cid]['goalies'][$num]['timeOnIce'] += (int)($gs['timeOnIce'] ?? 0);
                    $teamStats[$cid]['goalies'][$num]['games']++;
                  }
                }
              }
            }

            if (!$hasStats): ?>
              <p class="empty-msg">Статистика игроков появится после загрузки протоколов матчей.</p>
            <?php else:
              // Build tournament-wide leaderboards
              $allPlayers = [];
              $allGoalies = [];
              foreach ($teamStats as $cid => $ts) {
                $clubName = '';
                foreach ($clubs as $c) { if ($c['id'] == $cid) { $clubName = $c['name']; break; } }
                foreach ($ts['players'] as $num => $p) {
                  $allPlayers[] = [
                    'name' => $p['name'],
                    'position' => $p['position'],
                    'goals' => $p['goals'],
                    'assists' => $p['assists'],
                    'points' => $p['points'],
                    'pim' => $p['pim'],
                    'games' => $p['games'],
                    'clubId' => $cid,
                    'clubName' => $clubName,
                  ];
                }
                foreach ($ts['goalies'] as $num => $g) {
                  $allGoalies[] = [
                    'name' => $g['name'],
                    'saves' => $g['saves'],
                    'goalsAgainst' => $g['goalsAgainst'],
                    'timeOnIce' => $g['timeOnIce'],
                    'games' => $g['games'],
                    'clubId' => $cid,
                    'clubName' => $clubName,
                  ];
                }
              }

              // Sort for top-5 lists
              usort($allPlayers, function($a, $b) { return $b['points'] - $a['points']; });
              $topScorers = array_slice($allPlayers, 0, 5);

              $byGoals = $allPlayers;
              usort($byGoals, function($a, $b) { return $b['goals'] - $a['goals']; });
              $topSnipers = array_slice($byGoals, 0, 5);

              $byAssists = $allPlayers;
              usort($byAssists, function($a, $b) { return $b['assists'] - $a['assists']; });
              $topAssistants = array_slice($byAssists, 0, 5);

              // Defenders only
              $defenders = array_filter($allPlayers, function($p) { return strcasecmp($p['position'], 'Защ') === 0; });
              usort($defenders, function($a, $b) { return $b['points'] - $a['points']; });
              $topDefScorers = array_slice(array_values($defenders), 0, 5);

              // First goal scorers
              $firstGoalList = array_values($allGoalScorers);
              usort($firstGoalList, function($a, $b) { return $b['count'] - $a['count']; });

              // Goalies with minimum 60 min (3600 sec) TOI
              $eligibleGoalies = array_filter($allGoalies, function($g) { return $g['timeOnIce'] >= 3600; });
              foreach ($eligibleGoalies as &$eg) {
                $eg['gaa'] = round(3600 * $eg['goalsAgainst'] / $eg['timeOnIce'], 2);
              }
              usort($eligibleGoalies, function($a, $b) { return $a['gaa'] <=> $b['gaa']; });
              $topGoalies = array_slice(array_values($eligibleGoalies), 0, 5);
            ?>

            <!-- Leaderboard cards -->
            <div class="stat-cards-grid">
              <?php $cards = [
                ['title' => 'Бомбардир', 'sub' => 'Очки по системе "Гол+Пас"', 'items' => $topScorers, 'valField' => 'points', 'valLabel' => 'О'],
                ['title' => 'Снайпер', 'sub' => 'Забитые голы — максимум', 'items' => $topSnipers, 'valField' => 'goals', 'valLabel' => 'Г'],
                ['title' => 'Ассистент', 'sub' => 'Результативные передачи', 'items' => $topAssistants, 'valField' => 'assists', 'valLabel' => 'П'],
              ]; foreach ($cards as $card): ?>
              <div class="stat-card">
                <div class="stat-card-header"><div><?= $card['title'] ?><br><span class="stat-card-sub"><?= $card['sub'] ?></span></div></div>
                <?php if (empty($card['items'])): ?>
                  <div class="stat-card-empty">Нет данных</div>
                <?php else: $rnk = 0; foreach ($card['items'] as $p): $rnk++; ?>
                  <div class="stat-card-row">
                    <span class="stat-card-rank"><?= $rnk ?></span>
                    <span class="stat-card-name"><?= htmlspecialchars($p['name'] ?? '?') ?></span>
                    <span class="stat-card-club"><?= htmlspecialchars($p['clubName'] ?? '') ?></span>
                    <span class="stat-card-val"><?= (int)$p[$card['valField']] ?></span>
                  </div>
                <?php endforeach; endif; ?>
              </div>
              <?php endforeach; ?>

              <?php
              $firstGoalItems = array_slice($firstGoalList, 0, 5);
              ?>
              <div class="stat-card">
                <div class="stat-card-header"><div>Забивает первым<br><span class="stat-card-sub">Игрок, открывающий счёт в матче</span></div></div>
                <?php if (empty($firstGoalItems)): ?>
                  <div class="stat-card-empty">Нет данных</div>
                <?php else: $rnk = 0; foreach ($firstGoalItems as $p): $rnk++; ?>
                  <div class="stat-card-row">
                    <span class="stat-card-rank"><?= $rnk ?></span>
                    <span class="stat-card-name"><?= htmlspecialchars($p['name'] ?? '?') ?></span>
                    <span class="stat-card-club"><?= htmlspecialchars($p['clubName'] ?? '') ?></span>
                    <span class="stat-card-val"><?= (int)$p['count'] ?></span>
                  </div>
                <?php endforeach; endif; ?>
              </div>

              <div class="stat-card">
                <div class="stat-card-header"><div>Бомбардир-защитник<br><span class="stat-card-sub">Очки по системе "Гол+Пас" среди защитников</span></div></div>
                <?php if (empty($topDefScorers)): ?>
                  <div class="stat-card-empty">Нет данных</div>
                <?php else: $rnk = 0; foreach ($topDefScorers as $p): $rnk++; ?>
                  <div class="stat-card-row">
                    <span class="stat-card-rank"><?= $rnk ?></span>
                    <span class="stat-card-name"><?= htmlspecialchars($p['name'] ?? '?') ?></span>
                    <span class="stat-card-club"><?= htmlspecialchars($p['clubName'] ?? '') ?></span>
                    <span class="stat-card-val"><?= (int)$p['points'] ?></span>
                  </div>
                <?php endforeach; endif; ?>
              </div>

              <div class="stat-card">
                <div class="stat-card-header"><div>Вратарь. Коэффициент надёжности<br><span class="stat-card-sub">60 мин × ПГ / ВНП, сыграл не менее 60 минут</span></div></div>
                <?php if (empty($topGoalies)): ?>
                  <div class="stat-card-empty">Нет данных</div>
                <?php else: $rnk = 0; foreach ($topGoalies as $g): $rnk++; ?>
                  <div class="stat-card-row">
                    <span class="stat-card-rank"><?= $rnk ?></span>
                    <span class="stat-card-name"><?= htmlspecialchars($g['name'] ?? '?') ?></span>
                    <span class="stat-card-club"><?= htmlspecialchars($g['clubName'] ?? '') ?></span>
                    <span class="stat-card-val"><?= number_format($g['gaa'], 2, '.', '') ?></span>
                  </div>
                <?php endforeach; endif; ?>
              </div>
            </div>

            <!-- Per-team detailed tables -->
            <?php foreach ($teamStats as $cid => $ts):
                $clubName = '';
                $clubLogo = '';
                foreach ($clubs as $c) {
                  if ($c['id'] == $cid) { $clubName = $c['name']; $clubLogo = $c['logo'] ?? ''; break; }
                }
                uasort($ts['players'], function($a, $b) { return $b['points'] - $a['points']; });
                uasort($ts['goalies'], function($a, $b) { return $b['timeOnIce'] - $a['timeOnIce']; });
            ?>
              <div class="stats-team-block">
                <div class="stats-team-header">
                  <?php if ($clubLogo): ?><img src="<?= htmlspecialchars($clubLogo) ?>" alt="" class="stats-team-logo"><?php endif; ?>
                  <span><?= htmlspecialchars($clubName) ?></span>
                </div>
                <?php if (!empty($ts['players'])): ?>
                <div class="gt-table-wrap"><table class="st-table" style="table-layout:auto;">
                  <thead><tr><th>#</th><th>Игрок</th><th>Амплуа</th><th>И</th><th>Г</th><th>П</th><th>О</th><th>Штр</th></tr></thead>
                  <tbody>
                    <?php $rnk = 0; foreach ($ts['players'] as $num => $p): $rnk++; ?>
                    <tr>
                      <td><?= htmlspecialchars($num) ?></td>
                      <td style="text-align:left;"><?= htmlspecialchars($p['name']) ?></td>
                      <td><?= htmlspecialchars($p['position']) ?></td>
                      <td><?= $p['games'] ?></td>
                      <td><?= $p['goals'] ?></td>
                      <td><?= $p['assists'] ?></td>
                      <td style="font-weight:700;"><?= $p['points'] ?></td>
                      <td><?= $p['pim'] ? number_format($p['pim'], 1, '.', '') : '0' ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table></div>
                <?php endif; ?>
                <?php if (!empty($ts['goalies'])): ?>
                <div class="gt-table-wrap"><table class="st-table" style="table-layout:auto;">
                  <thead><tr><th>#</th><th>Вратарь</th><th>И</th><th>КН</th><th>ПШ</th><th>Время</th></tr></thead>
                  <tbody>
                    <?php foreach ($ts['goalies'] as $num => $g):
                      $gaa = $g['timeOnIce'] > 0
                        ? round(3600 * $g['goalsAgainst'] / $g['timeOnIce'], 2)
                        : 0;
                      $toiMin = floor($g['timeOnIce'] / 60);
                      $toiSec = $g['timeOnIce'] % 60;
                    ?>
                    <tr>
                      <td><?= htmlspecialchars($num) ?></td>
                      <td style="text-align:left;"><?= htmlspecialchars($g['name']) ?></td>
                      <td><?= $g['games'] ?></td>
                      <td><?= number_format($gaa, 2, '.', '') ?></td>
                      <td><?= $g['goalsAgainst'] ?></td>
                      <td><?= $toiMin ?>:<?= str_pad($toiSec, 2, '0', STR_PAD_LEFT) ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table></div>
                <?php endif; ?>
              </div>
            <?php endforeach; endif; ?>
          </div>

          <!-- Glossary -->
          <div class="glossary">
            <b>Глоссарий:</b> <b>В</b> — победы в основное время (3 очка), <b>ВБ</b> — победы по буллитам (2 очка), <b>ПБ</b> — поражения по буллитам (1 очко), <b>П</b> — поражения в основное время (0 очков), <b>ШЗ</b> — забитые шайбы, <b>ШП</b> — пропущенные шайбы, <b>±</b> — разница шайб, <b>О</b> — очки.
          </div>

        </div>
      <?php else: ?>
         <h1 style="color:var(--primary-dark);">Соревнования</h1>
        <?php if (empty($competitions)): ?>
          <p class="empty-msg">Нет доступных соревнований.</p>
        <?php else: ?>
          <div class="comp-grid">
            <?php foreach ($competitions as $c): ?>
              <a href="?id=<?= $c['id'] ?>" class="comp-card">
                <h3><?= htmlspecialchars($c['name']) ?></h3>
                <div class="meta"><?= htmlspecialchars($c['season'] ?? '') ?> <?= ($c['type'] ?? '') && $c['type'] !== 'regular' ? '· '.htmlspecialchars($c['type']) : '' ?></div>
                <span class="badge badge-<?= $c['status'] ?? 'upcoming' ?>">
                  <?= ['active'=>'Активен','finished'=>'Завершён','upcoming'=>'Предстоящий'][$c['status'] ?? 'upcoming'] ?>
                </span>
                <?php if (!empty($c['matches'])): ?>
                  <div style="margin-top:8px;font-size:0.85rem;color:rgba(255,255,255,0.5);">
                    🏒 <?= count($c['matches']) ?> матчей · 👥 <?= count($c['club_ids'] ?? []) ?> команд
                  </div>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>

<script>
function showPanel(panelType) {
  document.querySelectorAll('.block-b-tab').forEach(function(t) { t.classList.remove('active'); });
  document.querySelectorAll('.block-b-subtab').forEach(function(t) { t.classList.remove('active'); });
  document.querySelectorAll('.block-b-panel').forEach(function(p) { p.classList.remove('active'); });
  var panel = document.querySelector('.block-b-panel[data-bpanel="' + panelType + '"]');
  if (panel) panel.classList.add('active');
  var tab = document.querySelector('.block-b-tab[data-btab="' + panelType + '"]');
  if (tab) tab.classList.add('active');
  var sub = document.querySelector('.block-b-subtab[data-bsubtab="' + panelType + '"]');
  if (sub) sub.classList.add('active');
  var subs = document.querySelector('.block-b-subtabs');
  if (subs) subs.style.display = (panelType === 'classic' || panelType === 'chess') ? 'flex' : 'none';
}

document.querySelectorAll('.block-b-tab').forEach(function(tab) {
  tab.addEventListener('click', function() {
    showPanel(this.getAttribute('data-btab'));
  });
});

document.querySelectorAll('.block-b-subtab').forEach(function(tab) {
  tab.addEventListener('click', function() {
    showPanel(this.getAttribute('data-bsubtab'));
  });
});

var initialTab = '<?= $activeTab ?>';
if (initialTab) {
  var tabBtn = document.querySelector('.block-b-tab[data-btab="' + initialTab + '"]');
  if (tabBtn) tabBtn.click();
} else {
  var subs = document.querySelector('.block-b-subtabs');
  if (subs) subs.style.display = 'flex';
}
</script>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
