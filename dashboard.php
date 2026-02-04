<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/twig.php';

require_user();

echo $twig->render('dashboard.html.twig', [
  'user_name' => htmlspecialchars($_SESSION['user_name'] ?? 'User'),
  'user_email' => htmlspecialchars($_SESSION['user_email'] ?? ''),
  'logged_out' => isset($_GET['logged_out']) ? (int)$_GET['logged_out'] : 0,
]);