<?php
session_start();
unset($_SESSION['news_logged_in']);
unset($_SESSION['news_user']);
header('Location: /');
exit;
