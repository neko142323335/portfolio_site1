<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

$is_admin_page = true;

require_once __DIR__ . '/../includes/twig.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
  header('Location: dashboard.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
      $error = ERROR_MESSAGES['required_fields'];
    } else {
      $stmt = $db->prepare('SELECT * FROM admin WHERE email = :e');
      $stmt->execute([':e' => $email]);
      $admin = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_email'] = $admin['email'];
        header('Location: dashboard.php');
        exit;
      } else {
        $error = ERROR_MESSAGES['auth_failed'];
        log_error('Failed admin login attempt', ['email' => $email]);
      }
    }
  } catch (Exception $e) {
    log_error('Error during admin login', ['message' => $e->getMessage()]);
    $error = 'Помилка при вході. Спробуйте пізніше.';
  }
}

echo $twig->render('admin/login.html.twig', [
  'error' => $error,
  'logged_out' => isset($_GET['logged_out']) ? (int)$_GET['logged_out'] : 0,
]);
