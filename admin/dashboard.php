<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

$is_admin_page = true;

require_once __DIR__ . '/../includes/twig.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_admin();

try {
  $stmt = $db->query('SELECT * FROM works ORDER BY created_at DESC');
  $works = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  log_error('Error fetching works', ['message' => $e->getMessage()]);
  $works = [];
}

$success = isset($_GET['success']) ? 'Операція успішна!' : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

echo $twig->render('admin/dashboard.html.twig', [
  'works' => $works,
  'success' => $success,
  'error' => $error,
  'logged_out' => isset($_GET['logged_out']) ? (int)$_GET['logged_out'] : 0,
]);