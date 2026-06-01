<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }

$clubId = (int)($_POST['club_id'] ?? 0);
$oldNumber = $_POST['old_number'] ?? '';
$number = $_POST['number'] ?? '';
$name = $_POST['fio'] ?? '';
if (!$clubId || !$name) { header('Location: clubs.php'); exit; }

$file = __DIR__ . '/../data/clubs.json';
$clubs = json_decode(file_get_contents($file), true);

foreach ($clubs as &$club) {
  if ($club['id'] !== $clubId) continue;

  $player = [
    'number' => $number,
    'fio' => $name,
    'position' => $_POST['position'] ?? '',
    'photo' => $_POST['photo'] ?? '',
    'birth_date' => $_POST['birth_date'] ?? '',
    'height' => (int)($_POST['height'] ?? 0),
    'weight' => (int)($_POST['weight'] ?? 0),
    'grip' => $_POST['grip'] ?? ''
  ];

  $found = false;
  foreach ($club['players'] as &$p) {
    if ($oldNumber !== '' && (string)$p['number'] === (string)$oldNumber) {
      $p = $player;
      $found = true;
      break;
    }
  }
  unset($p);

  if (!$found) {
    $club['players'][] = $player;
  }
  break;
}
unset($club);

file_put_contents($file, json_encode($clubs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: club-players.php?club_id=' . $clubId . '&saved=1');
exit;
