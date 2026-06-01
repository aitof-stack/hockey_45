<?php
$clubs = json_decode(file_get_contents(__DIR__ . '/../data/clubs.json'), true);
$pageTitle = $pageTitle ?? 'Кубок Юных Чемпионов';
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> — Кубок Юных Чемпионов</title>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="/sport/css/crossover.css">
  <link rel="stylesheet" href="/sport/css/style.css">
</head>
<body>
<div class="wrapper">
  <header class="header" id="page_head">
    <div class="head-shadow"></div>
    <div class="header-line"></div>
    <div class="header-half left">
      <div class="logo">
        <a href="/"><img src="/images/logo.png" alt="Кубок Юных Чемпионов"></a>
      </div>
      <div class="header-half-part nav-wrapper">
        <input type="checkbox" id="menu-checker" class="switch-tabs">
        <div class="menu-button">
          <label for="menu-checker" class="icon btn-menu"></label>
          <label for="menu-checker" class="icon btn-close"></label>
        </div>
        <nav class="header-menu">
          <div class="expandable">
            <label><a href="/sport/club/"><span>Команды</span></a></label>
            <input type="checkbox" class="submenu-expander switch-tabs" id="about-expander">
            <ul>
              <?php foreach ($clubs as $club): ?>
              <li><a href="/sport/club/?id=<?= $club['id'] ?>"><?= htmlspecialchars($club['name']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="expandable">
            <label><span>История</span></label>
            <input type="checkbox" class="submenu-expander switch-tabs" id="history-expander">
            <ul>
              <li><a href="#">От года к году</a></li>
              <li><a href="#">Личности</a></li>
            </ul>
          </div>
          <div><a href="/sport/competitions/">Соревнования</a></div>
          <div><a href="/sport/list/1/">Новости</a></div>
          <div class="expandable">
            <label><span>Медиа</span></label>
            <input type="checkbox" class="submenu-expander switch-tabs" id="media-expander">
            <ul>
              <li><a href="/sport/albums/">Фото</a></li>
              <li><a href="/sport/videos/">Видео</a></li>
            </ul>
          </div>
          <div class="expandable">
            <label><span>Официально</span></label>
            <input type="checkbox" class="submenu-expander switch-tabs" id="official-expander">
            <ul>
              <li><a href="/sport/article/9/">Цели и задачи</a></li>
              <li><a href="/sport/article/8/">Контакты</a></li>
              <li><a href="#">Судьи</a></li>
              <li><a href="#">Регламенты и документы</a></li>
              <li><a href="#">Постановления</a></li>
              <li><a href="/sport/article/7/">Книга правил</a></li>
            </ul>
          </div>
        </nav>
      </div>
    </div>
    <div class="header-half right">
      <div class="partner-logos">
        <a href="https://kurganobl.ru/" target="_blank" title="Правительство Курганской области"><img src="/uploads/partner_kurgan_oblast.png" alt="Курганская область"></a>
        <a href="https://shadrinsk.gosuslugi.ru/" target="_blank" title="Администрация г. Шадринска"><img src="/uploads/partner_shadrinsk.png" alt="Шадринск"></a>
        <a href="https://zauralhockey.ru/" target="_blank" title="Федерация хоккея Зауралья"><img src="/uploads/partner_zauralhockey.png" alt="Зауральский хоккей"></a>
        <a href="https://shaaz.biz/" target="_blank" title="ОАО «ШААЗ»"><img src="/uploads/partner_shaaz.png" alt="ШААЗ"></a>
        <a href="https://vk.com/hockey_45" target="_blank" title="Мы ВКонтакте"><img src="/uploads/partner_vk.svg" alt="VK"></a>
        <a href="/news/login.php" title="Вход для медиацентра"><img src="/uploads/partner_news.png" alt="Медиацентр"></a>
      </div>
    </div>
  </header>
