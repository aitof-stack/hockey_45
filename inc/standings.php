<?php
function clubName($id, $clubs) {
  foreach ($clubs as $c) { if ($c['id'] === $id) return htmlspecialchars($c['name']); }
  return 'Неизвестная команда';
}
function clubLogo($id, $clubs) {
  foreach ($clubs as $c) { if ($c['id'] === $id) return $c['logo'] ?? ''; }
  return '';
}
function computeStandings($matches, $clubIds) {
  $st = [];
  foreach ($clubIds as $cid) {
    $st[$cid] = ['w' => 0, 'l' => 0, 'w_so' => 0, 'l_so' => 0, 'gf' => 0, 'ga' => 0, 'pts' => 0];
  }
  foreach ($matches as $m) {
    if (!isset($m['home_goals']) || $m['home_goals'] === '' || !isset($m['away_goals']) || $m['away_goals'] === '') continue;
    $hg = (int)$m['home_goals']; $ag = (int)$m['away_goals'];
    if (!isset($st[$m['home_id']])) $st[$m['home_id']] = ['w'=>0,'l'=>0,'w_so'=>0,'l_so'=>0,'gf'=>0,'ga'=>0,'pts'=>0];
    if (!isset($st[$m['away_id']])) $st[$m['away_id']] = ['w'=>0,'l'=>0,'w_so'=>0,'l_so'=>0,'gf'=>0,'ga'=>0,'pts'=>0];
    $st[$m['home_id']]['gf'] += $hg; $st[$m['home_id']]['ga'] += $ag;
    $st[$m['away_id']]['gf'] += $ag; $st[$m['away_id']]['ga'] += $hg;
    $so = !empty($m['shootout']);
    if ($hg > $ag) {
      if ($so) { $st[$m['home_id']]['w_so']++; $st[$m['home_id']]['pts'] += 2; $st[$m['away_id']]['l_so']++; $st[$m['away_id']]['pts'] += 1; }
      else { $st[$m['home_id']]['w']++; $st[$m['home_id']]['pts'] += 3; $st[$m['away_id']]['l']++; }
    } elseif ($hg < $ag) {
      if ($so) { $st[$m['away_id']]['w_so']++; $st[$m['away_id']]['pts'] += 2; $st[$m['home_id']]['l_so']++; $st[$m['home_id']]['pts'] += 1; }
      else { $st[$m['away_id']]['w']++; $st[$m['away_id']]['pts'] += 3; $st[$m['home_id']]['l']++; }
    } else {
      $st[$m['home_id']]['pts']++; $st[$m['away_id']]['pts']++;
    }
  }
  uasort($st, function($a, $b) {
    if ($a['pts'] !== $b['pts']) return $b['pts'] - $a['pts'];
    $gdA = $a['gf'] - $a['ga']; $gdB = $b['gf'] - $b['ga'];
    return $gdB - $gdA;
  });
  return $st;
}
