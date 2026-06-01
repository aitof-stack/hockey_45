<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }

$type = $_POST['type'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$allowed = ['clubs', 'competitions', 'media', 'arenas'];

if (!in_array($type, $allowed)) { header('Location: dashboard.php'); exit; }

$file = __DIR__ . '/../data/' . $type . '.json';
$items = json_decode(file_get_contents($file), true);

$fields = $_POST;
unset($fields['type'], $fields['id']);

if (isset($fields['comp_type'])) {
  $fields['type'] = $fields['comp_type'];
  unset($fields['comp_type']);
}

if (isset($fields['match_dates'])) {
  $lines = array_map('trim', explode("\n", $fields['match_dates']));
  $dates = [];
  foreach ($lines as $line) {
    if (empty($line)) continue;
    // Normalize DD.MM.YYYY → YYYY-MM-DD
    if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $line, $m)) {
      $dates[] = $m[3] . '-' . $m[2] . '-' . $m[1];
    } else {
      $dates[] = $line;
    }
  }
  $fields['match_dates'] = $dates;
}

// Handle club_ids array
if (isset($fields['club_ids']) && is_array($fields['club_ids'])) {
  $fields['club_ids'] = array_map('intval', $fields['club_ids']);
} else {
  $fields['club_ids'] = [];
}

// Preserve protocol data from existing matches before form overwrites them
$oldProtocols = [];
if ($id && $type === 'competitions') {
  foreach ($items as $ex) {
    if ($ex['id'] === $id && !empty($ex['matches'])) {
      foreach ($ex['matches'] as $em) {
        if (!empty($em['protocol'])) {
          $key = ($em['home_id'] ?? 0) . '_' . ($em['away_id'] ?? 0);
          $oldProtocols[$key] = $em['protocol'];
        }
      }
    }
  }
}

// Handle matches arrays — combine into structured array
if (isset($fields['match_date']) && is_array($fields['match_date'])) {
  $matches = [];
  foreach ($fields['match_date'] as $i => $date) {
    $homeId = (int)($fields['match_home'][$i] ?? 0);
    $awayId = (int)($fields['match_away'][$i] ?? 0);
    if (!$homeId || !$awayId || $homeId === $awayId) continue;
    $matches[] = [
      'date' => $date,
      'home_id' => $homeId,
      'away_id' => $awayId,
      'home_goals' => isset($fields['match_home_goals'][$i]) && $fields['match_home_goals'][$i] !== '' ? (int)$fields['match_home_goals'][$i] : '',
      'away_goals' => isset($fields['match_away_goals'][$i]) && $fields['match_away_goals'][$i] !== '' ? (int)$fields['match_away_goals'][$i] : '',
      'shootout' => !empty($fields['match_shootout'][$i])
    ];
  }
  // Restore preserved protocol data
  foreach ($matches as $mi => &$nm) {
    $key = ($nm['home_id'] ?? 0) . '_' . ($nm['away_id'] ?? 0);
    if (isset($oldProtocols[$key])) {
      $nm['protocol'] = $oldProtocols[$key];
    }
  }
  unset($nm);
  $fields['matches'] = $matches;
}
// Remove raw match arrays
foreach (['match_date', 'match_home', 'match_away', 'match_home_goals', 'match_away_goals', 'match_shootout'] as $k) {
  unset($fields[$k]);
}

$found = false;
foreach ($items as &$item) {
  if ($item['id'] === $id) {
    foreach ($fields as $key => $val) {
      $item[$key] = $val;
    }
    $found = true;
    break;
  }
}
unset($item);

if (!$found) {
  $maxId = 0;
  foreach ($items as $item) {
    if ($item['id'] > $maxId) $maxId = $item['id'];
  }
  $newItem = ['id' => $maxId + 1];
  foreach ($fields as $key => $val) {
    $newItem[$key] = $val;
  }
  $items[] = $newItem;
  $id = $maxId + 1;
}

file_put_contents($file, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: ' . $type . '.php?edit=' . $id . '&saved=1');
exit;
