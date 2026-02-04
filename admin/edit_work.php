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

$error = '';
$work = null;

try {
  $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

  if (!$id) {
    throw new Exception(ERROR_MESSAGES['record_not_found']);
  }

  $stmt = $db->prepare('SELECT * FROM works WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $work = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$work) {
    throw new Exception(ERROR_MESSAGES['record_not_found']);
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');

    if (!$title) {
      $error = "Назва обов'язкова";
    } elseif (!$category) {
      $error = 'Категорія обов\'язкова';
    } else {
      $image_path = $work['image'];

      if (!empty($_FILES['image']['tmp_name'])) {
        $file_upload = save_uploaded_file($_FILES['image']);
        if (!$file_upload['success']) {
          $error = $file_upload['error'];
        } else {
          if (!empty($work['image'])) {
            $old_image_path = __DIR__ . '/../' . $work['image'];
            if (file_exists($old_image_path)) {
              @unlink($old_image_path);
            }
          }
          $image_path = $file_upload['path'];
        }
      }

      if (!$error) {
        $update_stmt = $db->prepare('UPDATE works SET title = :t, description = :d, image = :i, category = :c WHERE id = :id');
        $update_stmt->execute([
          ':t' => $title,
          ':d' => $description,
          ':i' => $image_path,
          ':c' => $category,
          ':id' => $id
        ]);
        header('Location: dashboard.php?success=1');
        exit;
      }
    }
  }

} catch (PDOException $e) {
  log_error('Database error in edit_work.php', ['id' => $_GET['id'] ?? null, 'message' => $e->getMessage()]);
  $error = 'Помилка бази даних. Спробуйте пізніше.';
} catch (Exception $e) {
  log_error('Error in edit_work.php', ['message' => $e->getMessage()]);
  $error = $e->getMessage();
}

if ($work) {
  echo $twig->render('admin/edit_work.html.twig', [
    'work' => $work,
    'error' => $error,
    'logged_out' => isset($_GET['logged_out']) ? (int)$_GET['logged_out'] : 0,
  ]);
} else {
  echo "Роботу не знайдено";
}
