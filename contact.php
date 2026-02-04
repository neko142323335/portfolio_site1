<?php
require_once 'includes/db.php';
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/twig.php';

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    if (!$name || !$email || !$message) {
      $error = ERROR_MESSAGES['required_fields'];
    } elseif (!validate_email($email)) {
      $error = ERROR_MESSAGES['invalid_email'];
    } elseif (strlen($message) < 10) {
      $error = 'Повідомлення повинно містити мінімум 10 символів';
    } else {
      $log_dir = __DIR__ . '/logs';
      if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
      }

      $log_file = $log_dir . '/contacts.log';
      $timestamp = date('Y-m-d H:i:s');
      $log_entry = "[$timestamp] $name <$email>\n$message\n" . str_repeat('-', 60) . "\n\n";

      if (@file_put_contents($log_file, $log_entry, FILE_APPEND)) {
        $sent = true;
      } else {
        $error = 'Помилка при збереженні повідомлення. Спробуйте пізніше.';
        log_error('Failed to save contact message', ['email' => $email]);
      }
    }
  } catch (Exception $e) {
    log_error('Error in contact form', ['message' => $e->getMessage()]);
    $error = 'Неочікувана помилка. Спробуйте пізніше.';
  }
}

echo $twig->render('contact.html.twig', [
  'sent' => $sent,
  'error' => $error,
  'form_name' => htmlspecialchars($_POST['name'] ?? ''),
  'form_email' => htmlspecialchars($_POST['email'] ?? ''),
  'form_message' => htmlspecialchars($_POST['message'] ?? ''),
  'logged_out' => isset($_GET['logged_out']) ? (int)$_GET['logged_out'] : 0,
]);
