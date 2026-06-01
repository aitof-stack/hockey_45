<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$config = json_decode(file_get_contents(__DIR__ . '/../data/config.json'), true);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config['site_name'] = $_POST['site_name'] ?? $config['site_name'];
    
    if (!empty($_POST['new_password'])) {
        $config['password_hash'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $message = 'Пароль изменён!';
    }
    
    file_put_contents(__DIR__ . '/../data/config.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $message = $message ?: 'Настройки сохранены!';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Настройки — Админ-панель</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-layout"><?php include __DIR__ . '/inc/sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-container">
    <div class="page-header">
      <h2>⚙️ Настройки сайта</h2>
    </div>
    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <div class="editor-wrapper">
      <form method="post">
        <div class="form-group">
          <label for="site_name">Название сайта</label>
          <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($config['site_name']) ?>">
        </div>
        <div class="form-group">
          <label for="new_password">Новый пароль (оставьте пустым, чтобы не менять)</label>
          <input type="password" id="new_password" name="new_password">
        </div>
        <button type="submit" class="btn btn-primary">Сохранить настройки</button>
      </form>
    </div>
  </div>
</div></div>
</body>
</html>
