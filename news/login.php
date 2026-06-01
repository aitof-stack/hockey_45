<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  if ($username === 'news' && $password === 'news') {
    $_SESSION['news_logged_in'] = true;
    $_SESSION['news_user'] = 'news';
    header('Location: index.php');
    exit;
  }
  header('Location: login.php?error=1');
  exit;
}
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Медиацентр — Вход</title>
  <link rel="stylesheet" href="/css/style.css">
  <style>
    .login-wrap { max-width: 360px; margin: 100px auto; padding: 30px; background: var(--white); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid var(--section-even-fone); text-align: center; }
    .login-wrap h1 { font-size: 1.3rem; color: var(--primary-dark); margin-bottom: 20px; }
    .login-wrap input { width: 100%; padding: 10px 14px; margin-bottom: 12px; border: 2px solid var(--section-even-fone); border-radius: 10px; font-size: 0.95rem; box-sizing: border-box; }
    .login-wrap input:focus { border-color: var(--primary); outline: none; }
    .login-wrap button { width: 100%; padding: 10px; border: none; border-radius: 10px; background: var(--primary); color: #fff; font-size: 1rem; font-weight: 700; cursor: pointer; }
    .login-wrap button:hover { background: var(--primary-dark); }
    .login-wrap .error { color: #dc2626; font-size: 0.85rem; margin-bottom: 10px; }
    .login-wrap .back-link { display: block; margin-top: 15px; color: var(--primary); font-size: 0.85rem; }
  </style>
</head>
<body>
  <div class="login-wrap">
    <h1>🔐 Медиацентр</h1>
    <?php if (isset($_GET['error'])): ?><div class="error">Неверный логин или пароль</div><?php endif; ?>
    <form method="post">
      <input type="text" name="username" placeholder="Логин" required>
      <input type="password" name="password" placeholder="Пароль" required>
      <button type="submit">Войти</button>
    </form>
    <a href="/" class="back-link">← На главную</a>
  </div>
</body>
</html>
