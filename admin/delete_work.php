<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

$is_admin_page = true;

// Start session first
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Then check admin
require_admin();

require_once __DIR__ . '/../includes/db.php';

$err = '';
$success = '';

try {
  $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

  if (!$id) {
    throw new Exception(ERROR_MESSAGES['record_not_found']);
  }

  // Get the work record to find image path
  $stmt = $db->prepare('SELECT image FROM works WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $work = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$work) {
    throw new Exception(ERROR_MESSAGES['record_not_found']);
  }

  // Delete from database
  $delete_stmt = $db->prepare('DELETE FROM works WHERE id = :id');
  $delete_stmt->execute([':id' => $id]);

  // Attempt to delete image file if it exists
  if (!empty($work['image'])) {
    $image_path = __DIR__ . '/../' . $work['image'];
    if (file_exists($image_path)) {
      @unlink($image_path);
    }
  }

  $success = 'Роботу успішно видалено';
  header('Location: dashboard.php?success=1');
  exit;

} catch (Exception $e) {
  log_error('Error deleting work', ['id' => $_GET['id'] ?? null, 'message' => $e->getMessage()]);
  $err = $e->getMessage();
  header('Location: dashboard.php?error=' . urlencode($err));
  exit;
}

?>
