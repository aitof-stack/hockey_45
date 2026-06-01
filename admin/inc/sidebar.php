<aside class="sidebar">
  <div class="sidebar-logo">
    <a href="dashboard.php">🏒 Админ-панель</a>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">📋 Дашборд</a>

    <a href="pages.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'pages.php' ? 'active' : '' ?>">📄 Страницы</a>

    <div class="sidebar-group">
      <div class="sidebar-group-title">🔵 Команды</div>
      <a href="clubs.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'clubs.php' || basename($_SERVER['PHP_SELF']) === 'club-players.php' ? 'active' : '' ?>">🏒 Команды</a>
    </div>

    <div class="sidebar-group">
      <div class="sidebar-group-title">📜 История</div>
      <a href="edit-page.php?key=history" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'edit-page.php' && ($_GET['key'] ?? '') === 'history') ? 'active' : '' ?>">📅 От года к году</a>
      <a href="#" class="sidebar-link">👤 Личности</a>
    </div>

    <a href="competitions.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'competitions.php' ? 'active' : '' ?>">🏆 Соревнования</a>
    <a href="articles.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'articles.php' || basename($_SERVER['PHP_SELF']) === 'edit-article.php' ? 'active' : '' ?>">📰 Новости</a>

    <div class="sidebar-group">
      <div class="sidebar-group-title">📸 Медиа</div>
      <a href="media.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'media.php' ? 'active' : '' ?>">🖼 Фото</a>
      <a href="media.php" class="sidebar-link">🎬 Видео</a>
    </div>

    <div class="sidebar-group">
      <div class="sidebar-group-title">📋 Официально</div>
      <a href="edit-page.php?key=about" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'edit-page.php' && ($_GET['key'] ?? '') === 'about') ? 'active' : '' ?>">📄 Цели и задачи</a>
      <a href="edit-page.php?key=contacts" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'edit-page.php' && ($_GET['key'] ?? '') === 'contacts') ? 'active' : '' ?>">📞 Контакты</a>
      <a href="#" class="sidebar-link">⚖ Судьи</a>
      <a href="#" class="sidebar-link">📄 Регламенты и документы</a>
      <a href="#" class="sidebar-link">📋 Постановления</a>
      <a href="edit-page.php?key=rules" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'edit-page.php' && ($_GET['key'] ?? '') === 'rules') ? 'active' : '' ?>">📖 Книга правил</a>
    </div>

    <a href="#" class="sidebar-link">👤 Личный кабинет</a>
    <a href="settings.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>">⚙ Настройки</a>

    <div class="sidebar-footer">
      <a href="../" class="sidebar-link">🌐 На сайт</a>
      <a href="logout.php" class="sidebar-link" style="color:#ef4444;">🚪 Выйти</a>
    </div>
  </nav>
</aside>
