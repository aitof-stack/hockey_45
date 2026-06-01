<?php
$pages = json_decode(file_get_contents(__DIR__ . '/data/pages.json'), true);
$home = $pages['home'] ?? ['title' => 'Главная', 'content' => '', 'badge' => ''];
$pageTitle = $home['title'];
$articles = json_decode(file_get_contents(__DIR__ . '/data/articles.json'), true);
$clubs = json_decode(file_get_contents(__DIR__ . '/data/clubs.json'), true);
$competitions = json_decode(file_get_contents(__DIR__ . '/data/competitions.json'), true);
$latestArticles = array_reverse($articles);

// Collect birthdays: keyed by MM-DD
$birthdays = [];
foreach ($clubs as $club) {
  foreach ($club['players'] ?? [] as $p) {
    if (!empty($p['birth_date'])) {
      $bd = substr($p['birth_date'], 5);
      $birthdays[$bd][] = ['player' => ($p['fio'] ?? $p['name'] ?? ''), 'club' => $club['name'], 'photo' => $p['photo'] ?? '', 'birth_date' => $p['birth_date'] ?? ''];
    }
  }
}
// Collect match dates and full competition data for calendar
$matchDates = [];
$competitionData = [];
foreach ($competitions as $comp) {
  foreach ($comp['match_dates'] ?? [] as $d) {
    $matchDates[$d][] = $comp['name'];
  }
  if (!empty($comp['matches'])) {
    $mdata = [];
    foreach ($comp['matches'] as $mi => $m) {
      $m['_matchIdx'] = $mi;
      $mdata[] = $m;
    }
    $competitionData[] = [
      'id' => $comp['id'],
      'name' => $comp['name'],
      'club_ids' => $comp['club_ids'] ?? [],
      'matches' => $mdata
    ];
  }
}
?>
<?php require __DIR__ . '/inc/header.php'; ?>

    <main class="content">
      <div class="home-columns">
        <div class="home-col home-col-news">
          <?php if (!empty($latestArticles)): ?>
          <div class="news-slider">
            <div class="news-slider-inner">
              <div class="news-item news-item-welcome">
                <div class="news-item-welcome-inner">
                  <?= $home['content'] ?>
                  <?php if (!empty($home['badge'])): ?>
                    <span class="champion-badge"><?= htmlspecialchars($home['badge']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <?php foreach ($latestArticles as $article): ?>
              <div class="news-item news-slide-photo"<?php if (!empty($article['image'])): ?> style="background-image: url('<?= htmlspecialchars($article['image']) ?>')"<?php endif; ?>>
                <div class="news-slide-overlay"></div>
                <div class="news-slide-bottom">
                  <span class="news-item-date"><?= htmlspecialchars($article['date']) ?></span>
                  <h3><?= htmlspecialchars($article['title']) ?></h3>
                  <p><?= htmlspecialchars($article['anons']) ?></p>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="news-slider-arrows">
              <button class="news-arrow news-arrow-prev" aria-label="Назад">‹</button>
              <button class="news-arrow news-arrow-next" aria-label="Вперёд">›</button>
            </div>
            <div class="news-slider-dots"></div>
          </div>
          <?php else: ?>
          <p class="empty-message">Новости пока отсутствуют</p>
          <?php endif; ?>
        </div>

        <div class="home-col home-col-calendar">
          <div id="calendar"></div>
        </div>
      </div>
    </main>

    <style>
    .home-columns { display: flex; gap: 30px; margin-top: 30px; align-items: stretch; }
    .home-col { flex: 1; display: flex; flex-direction: column; }
    .home-col-news { flex: 1; }
    .home-col-calendar { flex: 1; min-width: 320px; }

    .news-slider { position: relative; background: var(--white); border-radius: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid var(--section-even-fone); overflow: hidden; height: 440px; display: flex; flex-direction: column; }
    .news-slider-inner { display: flex; transition: transform 0.5s ease; flex: 1; width: 100%; }
    .news-item { width: 100%; flex-shrink: 0; box-sizing: border-box; position: relative !important; display: flex !important; flex-direction: column !important; height: auto; z-index: auto; transition: none; }
    .news-slide-photo { background-size: cover; background-position: center; justify-content: flex-end; }
    .news-slide-photo:not([style*="background-image"]) { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
    .news-slide-overlay { position: absolute; bottom: 0; left: 0; right: 0; height: 65%; background: linear-gradient(transparent, rgba(0,0,0,0.7)); pointer-events: none; }
    .news-slide-bottom { position: relative; z-index: 2; padding: 20px; color: #fff; }
    .news-slide-bottom .news-item-date { display: block; font-size: 0.8rem; color: rgba(255,255,255,0.7); margin-bottom: 4px; }
    .news-slide-bottom h3 { margin: 3px 0; font-size: 1.1rem; color: #fff; line-height: 1.3; }
    .news-slide-bottom p { margin: 3px 0 0; font-size: 0.9rem; color: rgba(255,255,255,0.85); line-height: 1.4; }
    .news-item-welcome { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; display: flex; align-items: center; justify-content: center; text-align: center; padding: 40px 30px; position: relative; overflow: hidden; }
    .news-item-welcome::before { content: '\f091'; font-family: 'icons'; position: absolute; top: -20px; right: -20px; font-size: 160px; opacity: 0.08; color: var(--accent); }
    .news-item-welcome-inner { position: relative; z-index: 1; }
    .news-item-welcome h2 { font-size: 1.5rem; margin: 0 0 12px; color: white; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
    .news-item-welcome p { font-size: 1rem; opacity: 0.9; line-height: 1.5; margin: 0 0 5px; }
    .news-item-welcome .champion-badge { display: inline-block; background: var(--accent); color: var(--primary-dark); padding: 6px 20px; border-radius: 25px; font-weight: bold; font-family: 'Exo_2'; margin-top: 15px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
    .news-slider-arrows { display: flex; justify-content: space-between; position: absolute; top: 50%; left: 0; right: 0; transform: translateY(-50%); pointer-events: none; padding: 0 6px; }
    .news-arrow { pointer-events: auto; width: 34px; height: 34px; border-radius: 50%; border: 1px solid var(--section-even-fone); background: var(--white); color: var(--primary); font-size: 1.4rem; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.2s; opacity: 0; line-height: 1; }
    .news-slider:hover .news-arrow { opacity: 1; }
    .news-arrow:hover { background: var(--primary); color: var(--white); border-color: var(--primary); }
    .news-slider-dots { display: flex; justify-content: center; gap: 6px; padding: 0 20px 14px; position: absolute; bottom: 0; left: 0; right: 0; }
    .news-slider-dots .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--section-even-fone); border: none; cursor: pointer; padding: 0; transition: background 0.3s; }
    .news-slider-dots .dot.active { background: var(--primary); width: 24px; border-radius: 4px; }
    .empty-message { color: #94a3b8; text-align: center; padding: 30px; }

    #calendar { background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary) 100%); border-radius: 20px; padding: 22px 18px 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.08); position: relative; overflow: hidden; height: 440px; box-sizing: border-box; display: flex; flex-direction: column; }
    #calendar .cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding: 0 4px; position: relative; z-index: 1; flex-shrink: 0; }
    #calendar .cal-header .month-year { font-weight: 800; color: #ffffff; font-size: 1.2rem; letter-spacing: 0.5px; }
    #calendar .cal-header button { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; cursor: pointer; font-size: 1.1rem; color: rgba(255,255,255,0.7); transition: all 0.2s; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
    #calendar .cal-header button:hover { background: rgba(255,255,255,0.18); color: #ffffff; }
    #calendar table { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 2px; position: relative; z-index: 1; flex: 1; }
    #calendar th { color: #1e293b; font-size: 0.85rem; font-weight: 700; padding: 3px 2px; text-align: center; text-transform: uppercase; letter-spacing: 1.2px; width: 14.28%; background: #a8c8e0; border-radius: 6px; }
    #calendar td { text-align: center; padding: 4px 2px; font-size: 1rem; border-radius: 10px; cursor: default; position: relative; vertical-align: middle; line-height: 1.2; transition: all 0.2s ease; font-weight: 700; color: #1e293b; height: 44px; background: #E8F4FD; box-sizing: border-box; }
    #calendar td:not(.other-month):hover { background: #d4e8f5; }
    #calendar td.clickable { cursor: pointer; }
    #calendar td.today { background: var(--primary); color: #ffffff; font-weight: 800; box-shadow: 0 2px 10px rgba(28,78,122,0.3); }
    #calendar td.event-match { background: #E8F4FD; color: #1e293b; font-weight: 700; border: 1px solid rgba(221,119,0,0.15); }
    #calendar td.event-match:hover { background: #d4e8f5; }
    #calendar td.event-birthday { background: #E8F4FD; color: #1e293b; font-weight: 700; border: 1px solid rgba(221,119,0,0.1); }
    #calendar td.event-birthday:hover { background: #d4e8f5; }
    #calendar td.event-both { background: #E8F4FD; color: #1e293b; font-weight: 700; border: 1px solid rgba(221,119,0,0.15); }
    #calendar td.other-month { background: #d4e8f5; color: #94a3b8; }
    #calendar .emoji-wrap { position: absolute; right: 2px; top: 50%; transform: translateY(-50%); display: flex; gap: 2px; align-items: center; pointer-events: none; font-size: 18px; filter: contrast(1.3) brightness(1.1); }
    #calendar td.event-both .emoji-wrap { flex-direction: column; gap: 0; top: auto; bottom: 2px; transform: none; font-size: 16px; right: 0; }
    #calendar td.event-both .emoji-wrap .puck { width: 16px; height: 12px; }
    #calendar .puck { display: inline-block; width: 18px; height: 14px; background: url('/uploads/puck.png') no-repeat center/contain; vertical-align: middle; }
    #calendar .cal-legend { display: flex; gap: 18px; justify-content: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.78rem; color: rgba(255,255,255,0.5); position: relative; z-index: 1; flex-shrink: 0; }
    #calendar .cal-legend span { display: flex; align-items: center; gap: 6px; }
    @media (max-width: 768px) { .home-columns { flex-direction: column; } }
    </style>

    <script>
    (function() {
      var today = new Date();
      var curYear = today.getFullYear();
      var curMonth = today.getMonth();

      var birthdays = <?= json_encode($birthdays) ?>;
      var matchDates = <?= json_encode($matchDates) ?>;
      var competitions = <?= json_encode($competitionData) ?>;
      var dateInfo = {};

      function pad(n) { return n < 10 ? '0' + n : '' + n; }

      var clubMap = <?= json_encode(array_combine(array_column($clubs, 'id'), $clubs), JSON_UNESCAPED_UNICODE) ?>;
      function clubName(id) {
        var c = clubMap[id];
        return c ? c.name : 'Неизвестная команда';
      }
      function clubData(id) {
        var c = clubMap[id];
        return c ? { name: c.name, city: c.city || '', logo: c.logo || '' } : { name: 'Неизвестная команда', city: '', logo: '' };
      }

      function computeStandings(matches, clubIds) {
        var st = {};
        (clubIds || []).forEach(function(cid) { st[cid] = { w:0, w_so:0, l_so:0, l:0, gf:0, ga:0, pts:0 }; });
        matches.forEach(function(m) {
          if (m.home_goals === '' || m.away_goals === '') return;
          var hg = parseInt(m.home_goals, 10), ag = parseInt(m.away_goals, 10);
          if (!st[m.home_id]) st[m.home_id] = { w:0, w_so:0, l_so:0, l:0, gf:0, ga:0, pts:0 };
          if (!st[m.away_id]) st[m.away_id] = { w:0, w_so:0, l_so:0, l:0, gf:0, ga:0, pts:0 };
          st[m.home_id].gf += hg; st[m.home_id].ga += ag;
          st[m.away_id].gf += ag; st[m.away_id].ga += hg;
          var so = m.shootout ? true : false;
          if (hg > ag) {
            if (so) { st[m.home_id].w_so++; st[m.home_id].pts += 2; st[m.away_id].l_so++; st[m.away_id].pts += 1; }
            else { st[m.home_id].w++; st[m.home_id].pts += 3; st[m.away_id].l++; }
          } else if (ag > hg) {
            if (so) { st[m.away_id].w_so++; st[m.away_id].pts += 2; st[m.home_id].l_so++; st[m.home_id].pts += 1; }
            else { st[m.away_id].w++; st[m.away_id].pts += 3; st[m.home_id].l++; }
          }
        });
        var sorted = Object.keys(st).map(function(k) { return { id: parseInt(k), data: st[k] }; });
        sorted.sort(function(a, b) {
          if (a.data.pts !== b.data.pts) return b.data.pts - a.data.pts;
          var gdA = a.data.gf - a.data.ga, gdB = b.data.gf - b.data.ga;
          return gdB - gdA;
        });
        return sorted;
      }

      function showPopup(dateStr, md) {
        var existing = document.getElementById('calPopup');
        if (existing) existing.remove();
        var calendar = document.getElementById('calendar');

        // Find matches for this date from competitions
        var dateMatches = [];
        competitions.forEach(function(comp) {
          (comp.matches || []).forEach(function(m) {
            if (m.date === dateStr) {
              dateMatches.push({ comp: comp, match: m });
            }
          });
        });

        var hasMatches = dateMatches.length > 0;
        var hasBirthdays = birthdays[md] !== undefined;
        if (!hasMatches && !hasBirthdays) return;

        var popup = document.createElement('div');
        popup.id = 'calPopup';
        popup.style.cssText = 'position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(180deg,#0C1E3A,#1C4E7A);border-radius:20px;padding:20px;z-index:10;overflow-y:auto;color:#fff;display:flex;flex-direction:column;';

        // Date centered + close button
        var html = '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-shrink:0;">';
        html += '<div style="flex:1;text-align:center;font-weight:700;font-size:1rem;">' + dateStr + '</div>';
        html += '<button onclick="this.closest(\'#calPopup\').remove()" style="background:rgba(255,255,255,0.1);border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;color:#fff;font-size:1.1rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">×</button>';
        html += '</div>';

        // Tabs
        var activeTab = hasMatches ? 'matches' : 'birthdays';
        html += '<div style="display:flex;gap:8px;margin-bottom:12px;flex-shrink:0;">';
        if (hasMatches) {
          html += '<button class="cal-tab" data-tab="matches" style="flex:1;padding:8px;border:none;border-radius:8px;cursor:pointer;font-size:0.9rem;font-weight:700;background:#c0d8e8;color:#1e293b;">🏒 Матчи</button>';
        }
        if (hasBirthdays) {
          html += '<button class="cal-tab" data-tab="birthdays" style="flex:1;padding:8px;border:none;border-radius:8px;cursor:pointer;font-size:0.9rem;font-weight:700;background:#c0d8e8;color:#1e293b;">🎂 Дни рождения</button>';
        }
        html += '</div>';

        // Content containers
        html += '<div id="calTabContent" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:10px;">';

        // Matches content
        if (hasMatches) {
          html += '<div class="cal-tab-panel" data-panel="matches"' + (activeTab === 'matches' ? '' : ' style="display:none;"') + '>';

          // Group by competition
          var byComp = {};
          dateMatches.forEach(function(dm) {
            if (!byComp[dm.comp.name]) byComp[dm.comp.name] = { club_ids: dm.comp.club_ids, matches: [], comp: dm.comp };
            byComp[dm.comp.name].matches.push(dm.match);
          });
          var compNames = Object.keys(byComp);
          compNames.forEach(function(compName) {
            var data = byComp[compName];
            var comp = data.comp;

            // Tournament name
            html += '<div style="font-weight:700;font-size:1.7rem;color:#fff;padding:6px 0;text-align:center;">' + compName + '</div>';

            // Sub-tabs: Table, Calendar, Stats
            html += '<div style="display:flex;gap:6px;margin-bottom:8px;">';
            html += '<a href="/sport/competitions/?id=' + comp.id + '" style="flex:1;padding:7px;border:none;border-radius:6px;font-size:0.85rem;font-weight:700;background:#c0d8e8;color:#1e293b;text-decoration:none;display:flex;align-items:center;justify-content:center;transition:background 0.2s;">📊 Таблица</a>';
            html += '<a href="/sport/competitions/?id=' + comp.id + '&tab=calendar" style="flex:1;padding:7px;border:none;border-radius:6px;font-size:0.85rem;font-weight:700;background:#c0d8e8;color:#1e293b;text-decoration:none;display:flex;align-items:center;justify-content:center;transition:background 0.2s;">📅 Календарь</a>';
            html += '<a href="/sport/competitions/?id=' + comp.id + '&tab=stats" style="flex:1;padding:7px;border:none;border-radius:6px;font-size:0.85rem;font-weight:700;background:#c0d8e8;color:#1e293b;text-decoration:none;display:flex;align-items:center;justify-content:center;transition:background 0.2s;">📈 Статистика</a>';
            html += '</div>';

            // Match cards
            html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">';
            data.matches.forEach(function(m) {
              var home = clubData(m.home_id);
              var away = clubData(m.away_id);
              var hg = m.home_goals !== '' ? m.home_goals : '?';
              var ag = m.away_goals !== '' ? m.away_goals : '?';
              var soText = m.shootout ? ' <span style="font-size:0.7rem;color:#ffcc80;">Б</span>' : '';
              var isFinished = m.home_goals !== '' && m.away_goals !== '';
              var statusText = isFinished ? 'Окончен' : 'Не начался';

              html += '<div style="background:#E8F4FD;border-radius:12px;padding:10px 12px;border:1px solid #d4e8f5;">';

              // Row 1: Time + Status
              html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:0.72rem;">';
              html += '<span style="color:#64748b;">🕐 ' + (m.time || '—') + '</span>';
              html += '<span style="color:' + (isFinished ? '#16a34a' : '#94a3b8') + ';">' + statusText + '</span>';
              html += '</div>';

              // Row 2: Home team
              html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">';
              html += '<div style="width:28px;height:28px;border-radius:6px;overflow:hidden;flex-shrink:0;background:rgba(0,0,0,0.05);display:flex;align-items:center;justify-content:center;">';
              if (home.logo) {
                html += '<img src="' + home.logo + '" style="width:100%;height:100%;object-fit:contain;" onerror="this.outerHTML=\'👤\'">';
              } else {
                html += '👤';
              }
              html += '</div>';
              html += '<div style="flex:1;min-width:0;"><div style="font-weight:700;font-size:0.85rem;color:#1e293b;">' + home.name + '</div><div style="font-size:0.7rem;color:#64748b;">' + (home.city || '') + '</div></div>';
              html += '<a href="/sport/match/?comp=' + comp.id + '&match=' + m._matchIdx + '" style="font-weight:800;font-size:1.5rem;color:#1e293b;flex-shrink:0;text-decoration:none;">' + hg + '</a>';
              html += '</div>';

              // Row 3: Away team
              html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">';
              html += '<div style="width:28px;height:28px;border-radius:6px;overflow:hidden;flex-shrink:0;background:rgba(0,0,0,0.05);display:flex;align-items:center;justify-content:center;">';
              if (away.logo) {
                html += '<img src="' + away.logo + '" style="width:100%;height:100%;object-fit:contain;" onerror="this.outerHTML=\'👤\'">';
              } else {
                html += '👤';
              }
              html += '</div>';
              html += '<div style="flex:1;min-width:0;"><div style="font-weight:700;font-size:0.85rem;color:#1e293b;">' + away.name + '</div><div style="font-size:0.7rem;color:#64748b;">' + (away.city || '') + '</div></div>';
              html += '<a href="/sport/match/?comp=' + comp.id + '&match=' + m._matchIdx + '" style="font-weight:800;font-size:1.5rem;color:#1e293b;flex-shrink:0;text-decoration:none;">' + ag + soText + '</a>';
              html += '</div>';

              // Row 4: Protocol link
              html += '<div style="display:flex;justify-content:center;padding-top:6px;border-top:1px solid #d4e8f5;">';
              html += '<a href="/sport/match/?comp=' + comp.id + '&match=' + m._matchIdx + '" style="display:flex;align-items:center;gap:6px;text-decoration:none;font-size:0.82rem;font-weight:600;color:#1C4E7A;transition:color 0.2s;"><img src="/img/emojis/protocol.jpg" style="width:18px;height:18px;border-radius:3px;"> Протокол</a>';
              html += '</div>';

              html += '</div>'; // end match card
            });
            html += '</div>'; // end grid
          });
          html += '</div>'; // end matches panel
        }

        // Birthdays content
        if (hasBirthdays) {
          html += '<div class="cal-tab-panel" data-panel="birthdays"' + (activeTab !== 'birthdays' ? ' style="display:none;"' : '') + '>';
          var bdayCards = [];
          birthdays[md].forEach(function(b) {
            var parts = (b.player || '').split(' ');
            var lastName = parts[0] || '';
            var firstName = parts.slice(1).join(' ') || '';
            var photoHtml = b.photo
              ? '<div style="width:200px;height:270px;border-radius:8px;overflow:hidden;flex-shrink:0;border:2px solid rgba(255,255,255,0.15);display:flex;"><img src="' + b.photo + '" style="width:100%;height:100%;object-fit:contain;display:block;" onerror="this.outerHTML=\'<div style=\\\'width:200px;height:270px;border-radius:8px;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;font-size:3rem;color:rgba(255,255,255,0.4);\\\'>👤</div>\'"></div>'
              : '<div style="width:200px;height:270px;border-radius:8px;border:2px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;font-size:3rem;color:rgba(255,255,255,0.4);flex-shrink:0;">👤</div>';
            bdayCards.push('<div style="display:flex;gap:14px;background:rgba(221,119,0,0.1);border-radius:12px;height:270px;box-sizing:border-box;padding:0 16px;">' + photoHtml + '<div style="flex:1;min-width:0;display:flex;flex-direction:column;justify-content:center;"><div style="font-weight:700;font-size:1.1rem;color:#DD7700;">' + lastName + '</div><div style="font-size:0.95rem;color:rgba(255,255,255,0.65);margin-top:4px;">' + firstName + '</div><div style="font-size:0.85rem;color:rgba(255,255,255,0.5);margin-top:4px;">' + b.club + '</div></div></div>');
          });
          html += '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">' + bdayCards.join('') + '</div>';
          html += '</div>';
        }

        html += '</div>'; // end tab content
        popup.innerHTML = html;
        calendar.appendChild(popup);

        // Tab switching
        popup.querySelectorAll('.cal-tab').forEach(function(btn) {
          btn.addEventListener('click', function() {
            var tab = this.getAttribute('data-tab');
            popup.querySelectorAll('.cal-tab').forEach(function(b) {
              b.style.background = 'rgba(192,216,232,0.3)';
              b.style.color = 'rgba(255,255,255,0.6)';
            });
            this.style.background = '#c0d8e8';
            this.style.color = '#1e293b';
            popup.querySelectorAll('.cal-tab-panel').forEach(function(p) {
              p.style.display = p.getAttribute('data-panel') === tab ? '' : 'none';
            });
          });
        });

      }

      function formatDate(d) {
        var months = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
        var p = d.split('-');
        return parseInt(p[2], 10) + ' ' + months[parseInt(p[1], 10) - 1] + ' ' + p[0];
      }

      function render(year, month) {
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var firstDay = new Date(year, month, 1).getDay();
        firstDay = firstDay === 0 ? 6 : firstDay - 1;
        var months = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];

        var html = '<div class="cal-header">';
        html += '<button onclick="window.calPrev()">‹</button>';
        html += '<span class="month-year">' + months[month] + ' ' + year + '</span>';
        html += '<button onclick="window.calNext()">›</button>';
        html += '</div>';
        html += '<table><tr>';
        html += '<th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th>Сб</th><th>Вс</th>';
        html += '</tr><tr>';

        var prevMonthDays = new Date(year, month, 0).getDate();
        for (var i = firstDay - 1; i >= 0; i--) {
          html += '<td class="other-month">' + (prevMonthDays - i) + '</td>';
        }

        for (var d = 1; d <= daysInMonth; d++) {
          if ((firstDay + d - 1) % 7 === 0 && d > 1) html += '</tr><tr>';
          var dateStr = year + '-' + pad(month + 1) + '-' + pad(d);
          var md = pad(month + 1) + '-' + pad(d);
          var cls = 'day';

          if (year === today.getFullYear() && month === today.getMonth() && d === today.getDate()) cls += ' today';

          var hasMatch = matchDates[dateStr] !== undefined;
          var hasBirth = birthdays[md] !== undefined;

          if (hasMatch && hasBirth) cls += ' event-both';
          else if (hasMatch) cls += ' event-match';
          else if (hasBirth) cls += ' event-birthday';

          if (hasMatch || hasBirth) cls += ' clickable';

          var emojis = '';
          if (hasMatch || hasBirth) {
            emojis = '<span class="emoji-wrap">';
            if (hasMatch) emojis += '<span class="puck"></span>';
            if (hasBirth) emojis += '🎂';
            emojis += '</span>';
          }

          var onclick = '';
          if (hasMatch || hasBirth) {
            onclick = ' onclick="window.calPopup(\'' + dateStr + '\',\'' + md + '\')"';
          }

          html += '<td class="' + cls + '"' + onclick + '>' + d + emojis + '</td>';
        }

        var totalCells = firstDay + daysInMonth;
        var remainder = totalCells % 7;
        if (remainder > 0) {
          for (var i = 1; i <= 7 - remainder; i++) {
            html += '<td class="other-month">' + i + '</td>';
          }
        }
        html += '</tr></table>';

        html += '<div class="cal-legend">';
        html += '<span><span class="puck"></span> Матчи</span>';
        html += '<span>🎂 Дни рождения</span>';
        html += '</div>';

        document.getElementById('calendar').innerHTML = html;
      }

      window.calPopup = function(dateStr, md) { showPopup(dateStr, md); };
      window.calPrev = function() { curMonth--; if (curMonth < 0) { curMonth = 11; curYear--; } render(curYear, curMonth); };
      window.calNext = function() { curMonth++; if (curMonth > 11) { curMonth = 0; curYear++; } render(curYear, curMonth); };

      render(curYear, curMonth);
    })();
    </script>

<script>
(function() {
  var slider = document.querySelector('.news-slider-inner');
  if (!slider) return;
  var items = slider.children;
  if (items.length < 2) return;
  var current = 0, timer = null, total = items.length;
  var dots = document.querySelector('.news-slider-dots');
  function slideWidth() { return items[0].offsetWidth; }
  function update() {
    slider.style.transform = 'translateX(-' + (current * slideWidth()) + 'px)';
    var d = dots.firstChild;
    for (var i = 0; i < total; i++) {
      d.className = 'dot' + (i === current ? ' active' : '');
      d = d.nextSibling;
    }
  }
  for (var i = 0; i < total; i++) {
    var d = document.createElement('button');
    d.className = 'dot' + (i === 0 ? ' active' : '');
    d.setAttribute('aria-label', 'Новость ' + (i + 1));
    (function(idx) { d.addEventListener('click', function() { current = idx; update(); resetTimer(); }); })(i);
    dots.appendChild(d);
  }
  function next() { current = (current + 1) % total; update(); }
  function prev() { current = (current - 1 + total) % total; update(); }
  var nextBtn = document.querySelector('.news-arrow-next');
  var prevBtn = document.querySelector('.news-arrow-prev');
  if (nextBtn) nextBtn.addEventListener('click', function() { next(); resetTimer(); });
  if (prevBtn) prevBtn.addEventListener('click', function() { prev(); resetTimer(); });
  function resetTimer() { clearInterval(timer); timer = setInterval(next, 10000); }
  timer = setInterval(next, 10000);
  var container = document.querySelector('.news-slider');
  if (container) {
    container.addEventListener('mouseenter', function() { clearInterval(timer); });
    container.addEventListener('mouseleave', function() { timer = setInterval(next, 5000); });
  }
})();
</script>
<?php require __DIR__ . '/inc/footer.php'; ?>
