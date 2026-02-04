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
  $category = isset($_GET['category']) ? sanitize_input($_GET['category']) : null;

  // Get all categories for filter
  $categories_stmt = $db->query('SELECT DISTINCT category FROM works WHERE category IS NOT NULL ORDER BY category');
  $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

  // Get works based on filter
  if ($category) {
    $stmt = $db->prepare('SELECT * FROM works WHERE category = :cat ORDER BY created_at DESC');
    $stmt->execute([':cat' => $category]);
  } else {
    $stmt = $db->query('SELECT * FROM works ORDER BY created_at DESC');
  }
  $works = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
  log_error('Error in gallery.php', ['message' => $e->getMessage()]);
  $works = [];
  $categories = [];
}

// Render template
echo $twig->render('gallery.html.twig', [
  'works' => $works,
  'categories' => $categories,
  'category' => $category,
  'logged_out' => isset($_GET['logged_out']) ? (int)$_GET['logged_out'] : 0,
]);
?>
