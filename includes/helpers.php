<?php
/**
 * Helper functions for validation and common operations
 */

require_once 'config.php';

/**
 * Validate email format
 */
function validate_email($email) {
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength (min 6 chars)
 */
function validate_password($password) {
  return strlen($password) >= 6;
}

/**
 * Validate file upload (DEPRECATED - use FileValidator::validate() instead)
 * 
 * @deprecated Використовуйте \App\Validators\FileValidator::validate()
 * @see \App\Validators\FileValidator::validate()
 */
function validate_file_upload($file) {
  if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
    return ['valid' => false, 'error' => 'Файл не завантажено'];
  }

  if ($file['size'] > MAX_FILE_SIZE) {
    return ['valid' => false, 'error' => ERROR_MESSAGES['file_too_large']];
  }

  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ALLOWED_EXTENSIONS)) {
    return ['valid' => false, 'error' => ERROR_MESSAGES['invalid_file']];
  }

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);

  if (!in_array($mime, ALLOWED_MIME_TYPES)) {
    return ['valid' => false, 'error' => ERROR_MESSAGES['invalid_file']];
  }

  return ['valid' => true];
}

/**
 * Move uploaded file to correct directory with timestamp
 * 
 * ВИМОГИ: Завжди передавайте resultado валідацію FileValidator перед цією функцією!
 * 
 * @param array $file $_FILES['fieldname'] - ПОВИНЕН бути валідований через FileValidator::validate()
 * @return array ['success' => bool, 'error' => string, 'path' => string, 'filename' => string]
 * 
 * ПРИКЛАД:
 * use App\Validators\FileValidator;
 * $validation = FileValidator::validate($_FILES['image'] ?? []);
 * if (!$validation['valid']) {
 *   $error = $validation['error'];
 * } else {
 *   $result = save_uploaded_file($_FILES['image']);
 * }
 */
function save_uploaded_file($file) {
  // Остаточна перевірка - файл має вже бути валідований через FileValidator
  if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    return ['success' => false, 'error' => 'Невалідний файл для завантаження'];
  }

  if (!is_dir(UPLOADS_DIR)) {
    if (!@mkdir(UPLOADS_DIR, 0755, true)) {
      return ['success' => false, 'error' => ERROR_MESSAGES['upload_failed']];
    }
  }

  $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
  $filepath = UPLOADS_DIR . '/' . $filename;
  $url_path = UPLOADS_URL . '/' . $filename;

  if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    return ['success' => false, 'error' => ERROR_MESSAGES['upload_failed']];
  }

  return ['success' => true, 'path' => $url_path, 'filename' => $filename];
}

/**
 * Sanitize user input
 */
function sanitize_input($input) {
  return trim(htmlspecialchars(stripslashes($input ?? ''), ENT_QUOTES, 'UTF-8'));
}

/**
 * Check if user is admin
 */
function is_admin() {
  return !empty($_SESSION['admin_logged']);
}

/**
 * Require admin access (with redirect)
 */
function require_admin() {
  if (!is_admin()) {
    header('Location: /admin/login');
    exit;
  }
}

/**
 * Require logged in user
 */
function require_user() {
  if (!isset($_SESSION['user_id'])) {
    header('Location: /auth');
    exit;
  }
}

/**
 * Check if current page matches navigation link
 */
function is_current_page($page) {
  $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
  $path = trim($path, '/');

  if ($path === '') {
    $path = 'index';
  }

  if (str_ends_with($path, '.php')) {
    $path = basename($path, '.php');
  } else {
    $path = basename($path);
  }

  return $path === $page;
}

/**
 * Log error to file for debugging
 */
function log_error($message, $context = []) {
  if (!DEBUG_MODE) return;

  $log_dir = __DIR__ . '/../logs';
  if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
  }

  $log_file = $log_dir . '/error.log';
  $timestamp = date('Y-m-d H:i:s');
  $context_str = !empty($context) ? ' | ' . json_encode($context) : '';
  $log_line = "[$timestamp] $message$context_str\n";

  @file_put_contents($log_file, $log_line, FILE_APPEND);
}

?>
