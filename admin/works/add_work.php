<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';

$is_admin_page = true;

require_once __DIR__ . '/../../includes/twig.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');

    if (!$title) {
      $error = "Назва обов'язкова";
    } elseif (!$category) {
      $error = 'Категорія обов\'язкова';
    } elseif (empty($_FILES['image']['tmp_name'])) {
      $error = 'Виберіть зображення';
    } else {
      $file_upload = save_uploaded_file($_FILES['image']);
      if (!$file_upload['success']) {
        $error = $file_upload['error'];
      } else {
        $stmt = $db->prepare('INSERT INTO works (title, description, image, category, created_at) VALUES (:t, :d, :i, :c, :ca)');
        $stmt->execute([
          ':t' => $title,
          ':d' => $description,
          ':i' => $file_upload['path'],
          ':c' => $category,
          ':ca' => date('Y-m-d H:i:s')
        ]);
        header('Location: dashboard.php?success=1');
        exit;
      }
    }
  } catch (PDOException $e) {
    log_error('Database error in add_work.php', ['message' => $e->getMessage()]);
    $error = 'Помилка бази даних. Спробуйте пізніше.';
  } catch (Exception $e) {
    log_error('Unexpected error in add_work.php', ['message' => $e->getMessage()]);
    $error = 'Неочікувана помилка. Спробуйте пізніше.';
  }
}

echo $twig->render('admin/works/add_work.html.twig', [
  'error' => $error,
  'success' => $success,
  'logged_out' => isset($_GET['logged_out']) ? (int)$_GET['logged_out'] : 0,
]);
