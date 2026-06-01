<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
$error = isset($_GET['error']) ? 'Неверный логин или пароль' : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Вход — Админ-панель</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <img src="/images/logo.png" alt="Кубок Юных Чемпионов">
    </div>
    <h1>Вход в панель управления</h1>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    <form action="login.php" method="post">
      <div class="form-group">
        <label for="username">Логин</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary">Войти</button>
    </form>
  </div>
</body>
</html>
