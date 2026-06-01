<?php
/**
 * CLI-скрипт импорта протокола матчей
 * 
 * Использование:
 *   php admin/import-protocol.php <путь_к_json>
 * 
 * Пример:
 *   php admin/import-protocol.php "D:\Хоккей\ИИ\hockey_protocol_6.json"
 */

// --- Setup paths ---
$projectRoot = __DIR__ . '/..';
$compFile = $projectRoot . '/data/competitions.json';
$clubFile = $projectRoot . '/data/clubs.json';

// --- Parse CLI args ---
if ($argc < 2) {
  echo "Ошибка: Укажите путь к JSON-файлу протокола.\n";
  echo "Использование: php admin/import-protocol.php <путь_к_json>\n";
  exit(1);
}

$jsonPath = $argv[1];
if (!file_exists($jsonPath)) {
  echo "Ошибка: Файл не найден: $jsonPath\n";
  exit(1);
}

// --- Load data ---
$data = json_decode(file_get_contents($jsonPath), true);
if (!$data || !is_array($data)) {
  echo "Ошибка: Невалидный JSON в файле.\n";
  exit(1);
}

$comps = json_decode(file_get_contents($compFile), true);
$clubs = json_decode(file_get_contents($clubFile), true);

// --- Normalize team name ---
function normalizeTeamName($name) {
  $n = trim(preg_replace('/["\xc2\xab\xc2\xbb]/u', '', $name));
  $n = trim(str_ireplace(['хк ', 'хк'], '', $n));
  return $n;
}

// --- Build club name map ---
$clubNameMap = [];
foreach ($clubs as $club) {
  $clubNameMap[normalizeTeamName($club['name'])] = ['id' => $club['id'], 'name' => $club['name']];
}

// --- Find competition by name from protocol ---
$compName = trim(preg_replace('/\s+/u', ' ', $data[0]['competition'] ?? ''));
if (!$compName) {
  echo "Ошибка: В протоколе не указан турнир (competition).\n";
  exit(1);
}

$compIdx = -1;
foreach ($comps as $i => $c) {
  // Try exact match, then partial
  if (strpos($c['name'], $compName) !== false || strpos($compName, $c['name']) !== false) {
    $compIdx = $i;
    break;
  }
}

if ($compIdx < 0) {
  echo "Ошибка: Турнир «{$compName}» не найден в competitions.json.\n";
  echo "Доступные турниры:\n";
  foreach ($comps as $c) {
    echo "  - {$c['name']}\n";
  }
  exit(1);
}

$comp = &$comps[$compIdx];
echo "Турнир: {$comp['name']} (ID: {$comp['id']})\n";
echo str_repeat('-', 50) . "\n";

// --- Process matches ---
$matched = 0;
$skipped = 0;
$skippedDetails = [];

foreach ($data as $pm) {
  if (empty($pm['teamA']) || empty($pm['teamB'])) continue;

  $teamANorm = normalizeTeamName($pm['teamA']);
  $teamBNorm = normalizeTeamName($pm['teamB']);

  $homeInfo = $clubNameMap[$teamANorm] ?? null;
  $awayInfo = $clubNameMap[$teamBNorm] ?? null;

  if (!$homeInfo || !$awayInfo) {
    $skipped++;
    $reason = !$homeInfo ? "команда «{$pm['teamA']}» не найдена" : "команда «{$pm['teamB']}» не найдена";
    $skippedDetails[] = "  Матч #{$pm['matchNumber']}: {$pm['teamA']} vs {$pm['teamB']} — {$reason}";
    continue;
  }

  $homeId = $homeInfo['id'];
  $awayId = $awayInfo['id'];

  // Find matching comp match
  $matchIdx = -1;
  $protocolHomeId = 0; $protocolAwayId = 0;
  foreach ($comp['matches'] as $mi => $mm) {
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

  if ($matchIdx < 0) {
    $skipped++;
    $skippedDetails[] = "  Матч #{$pm['matchNumber']}: {$pm['teamA']} vs {$pm['teamB']} — не найден в расписании турнира";
    continue;
  }

  // Build protocol data
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

  // Map protocol sides to home/away
  if ($clubNameMap[$teamANorm]['id'] == $protocolHomeId) {
    $comp['matches'][$matchIdx]['protocol'] = ['home' => $protocolA, 'away' => $protocolB];
  } else {
    $comp['matches'][$matchIdx]['protocol'] = ['home' => $protocolB, 'away' => $protocolA];
  }

  $matched++;

  // Print summary for this match
  $scoreStr = $pm['scoreA'] . ':' . $pm['scoreB'];
  $homePlayers = count($protocolA['players']);
  $awayPlayers = count($protocolB['players']);
  $homeScorers = count($protocolA['playerStats']);
  $awayScorers = count($protocolB['playerStats']);
  echo "  Матч #{$pm['matchNumber']}: {$pm['teamA']} {$scoreStr} {$pm['teamB']}\n";
  echo "    Состав: {$homeInfo['name']} ({$homePlayers} игр.) vs {$awayInfo['name']} ({$awayPlayers} игр.)\n";
  echo "    Статистика: {$homeScorers} полевых + " . count($protocolA['goalieStats']) . " вр. / {$awayScorers} полевых + " . count($protocolB['goalieStats']) . " вр.\n";
}

echo str_repeat('-', 50) . "\n";

// --- Save ---
file_put_contents($compFile, json_encode($comps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// --- Summary ---
echo "Итого: сопоставлено {$matched} матчей";
if ($skipped > 0) {
  echo ", пропущено {$skipped}\n";
  foreach ($skippedDetails as $d) {
    echo $d . "\n";
  }
} else {
  echo "\n";
}
echo "Готово! Данные сохранены в competitions.json\n";
