<?php
/**
 * Application Configuration
 * Central location for all app constants and settings
 */

// Database configuration
if (!defined('DB_DIR')) define('DB_DIR', __DIR__ . '/../database');
if (!defined('DB_FILE')) define('DB_FILE', DB_DIR . '/portfolio.db');
if (!defined('DB_DSN')) define('DB_DSN', 'sqlite:' . DB_FILE);

// App paths
if (!defined('UPLOADS_DIR')) define('UPLOADS_DIR', __DIR__ . '/../assets/img/works');
if (!defined('UPLOADS_URL')) define('UPLOADS_URL', 'assets/img/works');

// App settings
if (!defined('SITE_NAME')) define('SITE_NAME', 'Noosfera');
if (!defined('SITE_LANG')) define('SITE_LANG', 'uk');
if (!defined('APP_ENV')) define('APP_ENV', getenv('APP_ENV') ?: 'production');
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', getenv('DEBUG_MODE') === '1');

// Security
if (!defined('PASSWORD_ALGORITHM')) define('PASSWORD_ALGORITHM', PASSWORD_DEFAULT);
if (!defined('PASSWORD_OPTIONS')) define('PASSWORD_OPTIONS', []);

// Admin credentials from environment
if (!defined('DEFAULT_ADMIN_EMAIL')) define('DEFAULT_ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@noosfera.ua');
if (!defined('DEFAULT_ADMIN_PASSWORD')) define('DEFAULT_ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'admin123');

// Session configuration
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600); // 1 hour

// File upload settings
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
if (!defined('ALLOWED_MIME_TYPES')) define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
if (!defined('ALLOWED_EXTENSIONS')) define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// Error messages
if (!defined('ERROR_MESSAGES')) define('ERROR_MESSAGES', [
  'invalid_email' => 'Невірна адреса email',
  'invalid_password' => 'Пароль повинен містити мінімум 6 символів',
  'password_mismatch' => 'Паролі не співпадають',
  'duplicate_email' => 'Email вже використовується',
  'invalid_file' => 'Невірний формат файлу. Дозволені: JPG, PNG, WebP, GIF',
  'file_too_large' => 'Файл занадто великий (макс. 5MB)',
  'upload_failed' => 'Помилка при завантаженні файлу',
  'auth_failed' => 'Невірні облікові дані',
  'unauthorized' => 'Доступ заборонений',
  'required_fields' => 'Заповніть всі обов\'язкові поля',
  'record_not_found' => 'Запис не знайдено',
]);

?>
