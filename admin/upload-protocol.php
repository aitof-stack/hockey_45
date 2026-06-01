<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Content-Type: application/json'); http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Не авторизован']); exit; }

$compId = (int)($_POST['comp_id'] ?? 0);
if (!$compId) { echo json_encode(['ok'=>false,'error'=>'Нет comp_id']); exit; }

if (!isset($_FILES['protocol']) || $_FILES['protocol']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['ok'=>false,'error'=>'Ошибка загрузки файла']); exit; }

$data = json_decode(file_get_contents($_FILES['protocol']['tmp_name']), true);
if (!$data || !is_array($data)) { echo json_encode(['ok'=>false,'error'=>'Невалидный JSON']); exit; }

$compFile = __DIR__ . '/../data/competitions.json';
$clubFile = __DIR__ . '/../data/clubs.json';
$comps = json_decode(file_get_contents($compFile), true);
$clubs = json_decode(file_get_contents($clubFile), true);

$compIdx = -1; $comp = null;
foreach ($comps as $i => $c) { if ($c['id'] === $compId) { $compIdx = $i; $comp = &$comps[$i]; break; } }
if ($compIdx < 0) { echo json_encode(['ok'=>false,'error'=>'Турнир не найден']); exit; }

// Build club name lookup (normalized -> id)
function normalizeTeamName($name) {
  $n = trim(preg_replace('/["\xc2\xab\xc2\xbb]/u', '', $name));
  $n = trim(str_ireplace(['хк ', 'хк'], '', $n));
  return $n;
}

$clubNameMap = [];
foreach ($clubs as $club) {
  $clubNameMap[normalizeTeamName($club['name'])] = $club['id'];
}

$matched = 0;
$skipped = 0;

foreach ($data as $pm) {
  if (empty($pm['teamA']) || empty($pm['teamB'])) continue;

  $teamANorm = normalizeTeamName($pm['teamA']);
  $teamBNorm = normalizeTeamName($pm['teamB']);

  $homeId = $clubNameMap[$teamANorm] ?? 0;
  $awayId = $clubNameMap[$teamBNorm] ?? 0;
  if (!$homeId || !$awayId) { $skipped++; continue; }

  // Find matching comp match
  $matchIdx = -1;
  $compMatches = $comp['matches'] ?? [];
  $protocolHomeId = 0; $protocolAwayId = 0;
  foreach ($compMatches as $mi => $mm) {
    if ($mm['home_id'] == $homeId && $mm['away_id'] == $awayId) {
      $matchIdx = $mi;
      $protocolHomeId = $homeId; $protocolAwayId = $awayId;
      break;
    }
    if ($mm['home_id'] == $awayId && $mm['away_id'] == $homeId) {
      $matchIdx = $mi;
      $protocolHomeId = $awayId; $protocolAwayId = $homeId;
      break;
    }
  }
  if ($matchIdx < 0) { $skipped++; continue; }

  // Build protocol data (team A/B from protocol)
  $protocolA = [
    'players' => [],
    'playerStats' => $pm['playerStats']['A'] ?? [],
    'goalieStats' => $pm['goalieStats']['A'] ?? [],
    'periodStats' => $pm['periodStats'] ?? [],
    'goals' => $pm['goals']['A'] ?? [],
    'penalties' => $pm['penalties']['A'] ?? [],
  ];
  $protocolB = [
    'players' => [],
    'playerStats' => $pm['playerStats']['B'] ?? [],
    'goalieStats' => $pm['goalieStats']['B'] ?? [],
    'periodStats' => $pm['periodStats'] ?? [],
    'goals' => $pm['goals']['B'] ?? [],
    'penalties' => $pm['penalties']['B'] ?? [],
  ];

  if (!empty($pm['teamAData'])) {
    foreach ($pm['teamAData'] as $p) {
      $protocolA['players'][] = ['number' => $p['number'], 'name' => $p['name'], 'position' => $p['position']];
    }
  }
  if (!empty($pm['teamBData'])) {
    foreach ($pm['teamBData'] as $p) {
      $protocolB['players'][] = ['number' => $p['number'], 'name' => $p['name'], 'position' => $p['position']];
    }
  }

  // Map protocol sides to home/away based on which club ID matches
  if ($clubNameMap[$teamANorm] == $protocolHomeId) {
    $comp['matches'][$matchIdx]['protocol'] = ['home' => $protocolA, 'away' => $protocolB];
  } else {
    $comp['matches'][$matchIdx]['protocol'] = ['home' => $protocolB, 'away' => $protocolA];
  }

  $matched++;
}

file_put_contents($compFile, json_encode($comps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok'=>true, 'matched'=>$matched, 'skipped'=>$skipped]);
exit;
