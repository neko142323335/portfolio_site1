<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/twig.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

$login_error = '';
$register_error = '';
$register_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (isset($_POST['login'])) {
      $email = sanitize_input($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      if (!$email || !$password) {
        $login_error = ERROR_MESSAGES['required_fields'];
      } elseif (!validate_email($email)) {
        $login_error = ERROR_MESSAGES['invalid_email'];
      } else {
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :e');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['user_name'] = $user['name'];
          $_SESSION['admin_logged'] = (bool) $user['is_admin'];
          header('Location: index.php');
          exit;
        } else {
          $login_error = ERROR_MESSAGES['auth_failed'];
          log_error('Failed login attempt', ['email' => $email]);
        }
      }

    } elseif (isset($_POST['register'])) {
      $name = sanitize_input($_POST['name'] ?? '');
      $email = sanitize_input($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';
      $confirm = $_POST['confirm'] ?? '';

      if (!$name || !$email || !$password || !$confirm) {
        $register_error = ERROR_MESSAGES['required_fields'];
      } elseif (!validate_email($email)) {
        $register_error = ERROR_MESSAGES['invalid_email'];
      } elseif (!validate_password($password)) {
        $register_error = ERROR_MESSAGES['invalid_password'];
      } elseif ($password !== $confirm) {
        $register_error = ERROR_MESSAGES['password_mismatch'];
      } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
          $stmt = $db->prepare('INSERT INTO users (name, email, password, created_at) VALUES (:n, :e, :p, :c)');
          $stmt->execute([
            ':n' => $name,
            ':e' => $email,
            ':p' => $hashed,
            ':c' => date('Y-m-d H:i:s')
          ]);
          $register_success = 'Реєстрація успішна! Тепер увійдіть.';
        } catch (PDOException $e) {
          if ($e->getCode() == 23000) {
            $register_error = ERROR_MESSAGES['duplicate_email'];
            log_error('Duplicate email registration attempt', ['email' => $email]);
          } else {
            $register_error = 'Помилка реєстрації. Спробуйте пізніше.';
            log_error('Database error during registration', ['message' => $e->getMessage()]);
          }
        }
      }
    }
  } catch (Exception $e) {
    log_error('Unexpected error in auth.php', ['message' => $e->getMessage()]);
    $login_error = 'Неочікувана помилка. Спробуйте пізніше.';
  }
}

// Render template
echo $twig->render('auth.html.twig', [
  'login_error' => $login_error,
  'register_error' => $register_error,
  'register_success' => $register_success,
  'logged_out' => isset($_GET['logged_out']) ? (int)$_GET['logged_out'] : 0,
]);
