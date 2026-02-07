<?php
namespace App\Validators;

/**
 * Файловий валідатор - централізована валідація завантажених файлів
 * Забезпечує послідовну валідацію в усіх контролерах
 */
class FileValidator
{
  /**
   * Максимальний розмір файлу (5MB)
   */
  private const MAX_FILE_SIZE = 5 * 1024 * 1024;

  /**
   * Допустимі MIME типи
   */
  private const ALLOWED_MIME_TYPES = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif'
  ];

  /**
   * Допустимі розширення файлів
   */
  private const ALLOWED_EXTENSIONS = [
    'jpg', 'jpeg', 'png', 'webp', 'gif'
  ];

  /**
   * Повний валідація файлу з детальними перевірками
   * 
   * @param array $file $_FILES['fieldname']
   * @return array ['valid' => bool, 'error' => string|null, 'details' => array]
   */
  public static function validate(array $file): array
  {
    // 1. Перевірка наявності файлу у формі
    if (!isset($file) || !is_array($file)) {
      return [
        'valid' => false,
        'error' => 'Поле файлу відсутнє у формі',
        'code' => 'MISSING_FIELD'
      ];
    }

    // 2. Перевірка помилок завантаження PHP
    if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
      return [
        'valid' => false,
        'error' => self::getUploadErrorMessage($file['error']),
        'code' => 'UPLOAD_ERROR_' . $file['error']
      ];
    }

    // 3. Перевірка наявності тимчасового файлу
    if (empty($file['tmp_name'])) {
      return [
        'valid' => false,
        'error' => 'Файл не було завантажено',
        'code' => 'EMPTY_FILE'
      ];
    }

    // 4. Перевірка розміру файлу
    if (isset($file['size']) && $file['size'] > self::MAX_FILE_SIZE) {
      return [
        'valid' => false,
        'error' => sprintf(
          'Файл занадто великий (макс. %s MB)',
          self::MAX_FILE_SIZE / (1024 * 1024)
        ),
        'code' => 'FILE_TOO_LARGE',
        'maxSize' => self::MAX_FILE_SIZE,
        'fileSize' => $file['size'] ?? 0
      ];
    }

    // 5. Перевірка розширення файлу
    $extension = self::getFileExtension($file['name'] ?? '');
    if (!$extension || !in_array(strtolower($extension), self::ALLOWED_EXTENSIONS, true)) {
      return [
        'valid' => false,
        'error' => sprintf(
          'Невірний формат файлу. Дозволені: %s',
          implode(', ', array_map('strtoupper', self::ALLOWED_EXTENSIONS))
        ),
        'code' => 'INVALID_EXTENSION',
        'extension' => $extension,
        'allowed' => self::ALLOWED_EXTENSIONS
      ];
    }

    // 6. Перевірка MIME типу файлу
    $mimeType = self::getMimeType($file['tmp_name']);
    if (!$mimeType || !in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
      return [
        'valid' => false,
        'error' => 'Тип файлу не підтримується. Завантажте зображення (JPG, PNG, WebP, GIF)',
        'code' => 'INVALID_MIME_TYPE',
        'mimeType' => $mimeType,
        'allowed' => self::ALLOWED_MIME_TYPES
      ];
    }

    // 7. Перевірка що файл є дійсним зображенням
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
      return [
        'valid' => false,
        'error' => 'Файл не є дійсним зображенням',
        'code' => 'INVALID_IMAGE'
      ];
    }

    // Усі перевірки пройдені
    return [
      'valid' => true,
      'error' => null,
      'code' => 'VALID',
      'details' => [
        'filename' => $file['name'] ?? '',
        'size' => $file['size'] ?? 0,
        'extension' => $extension,
        'mimeType' => $mimeType,
        'dimensions' => [
          'width' => $imageInfo[0] ?? 0,
          'height' => $imageInfo[1] ?? 0
        ]
      ]
    ];
  }

  /**
   * Швидка перевірка - лише базові перевірки
   * 
   * @param array $file $_FILES['fieldname']
   * @return bool
   */
  public static function isValid(array $file): bool
  {
    $result = self::validate($file);
    return $result['valid'] === true;
  }

  /**
   * Отримати розширення файлу
   */
  private static function getFileExtension(string $filename): ?string
  {
    if (empty($filename)) {
      return null;
    }

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return !empty($extension) ? $extension : null;
  }

  /**
   * Отримати MIME тип файлу
   */
  private static function getMimeType(string $filepath): ?string
  {
    if (!file_exists($filepath)) {
      return null;
    }

    // Спробувати з finfo_file (найнадійніше)
    if (function_exists('finfo_file')) {
      $finfo = @finfo_open(FILEINFO_MIME_TYPE);
      if ($finfo !== false) {
        $mimeType = @finfo_file($finfo, $filepath);
        @finfo_close($finfo);
        return $mimeType !== false ? $mimeType : null;
      }
    }

    // Резервний варіант - mime_content_type (deprecated але кращий за нічого)
    if (function_exists('mime_content_type')) {
      return @mime_content_type($filepath);
    }

    return null;
  }

  /**
   * Отримати людиночитаєму помилку завантаження PHP
   */
  private static function getUploadErrorMessage(int $errorCode): string
  {
    $errors = [
      UPLOAD_ERR_INI_SIZE => 'Файл перевищує максимальний розмір, встановлений в php.ini',
      UPLOAD_ERR_FORM_SIZE => 'Файл перевищує максимальний розмір встановлений у формі',
      UPLOAD_ERR_PARTIAL => 'Файл завантажено частково',
      UPLOAD_ERR_NO_FILE => 'Файл не було завантажено',
      UPLOAD_ERR_NO_TMP_DIR => 'Відсутня тимчасова папка сервера',
      UPLOAD_ERR_CANT_WRITE => 'Помилка при записі файлу на диск',
      UPLOAD_ERR_EXTENSION => 'Завантаження файлу зупинено розширенням PHP',
    ];

    return $errors[$errorCode] ?? 'Невідома помилка завантаження файлу';
  }

  /**
   * Отримати допустимі MIME типи
   */
  public static function getAllowedMimeTypes(): array
  {
    return self::ALLOWED_MIME_TYPES;
  }

  /**
   * Отримати допустимі розширення
   */
  public static function getAllowedExtensions(): array
  {
    return self::ALLOWED_EXTENSIONS;
  }

  /**
   * Отримати максимальний розмір файлу в байтах
   */
  public static function getMaxFileSize(): int
  {
    return self::MAX_FILE_SIZE;
  }

  /**
   * Отримати максимальний розмір файлу в MB
   */
  public static function getMaxFileSizeMB(): float
  {
    return self::MAX_FILE_SIZE / (1024 * 1024);
  }
}
