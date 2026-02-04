<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/db.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Initialize Twig
$twig = require_once 'includes/twig.php';

try {
  // Get last 5 works ordered by creation date
  $stmt = $db->query('SELECT * FROM works ORDER BY created_at DESC LIMIT 5');
  $works = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  log_error('Error in index.php', ['message' => $e->getMessage()]);
  $works = [];
}

// Render template
echo $twig->render('index.html.twig', [
  'works' => $works,
  'logged_out' => isset($_GET['logged_out']) ? (int)$_GET['logged_out'] : 0,
]);
?>
