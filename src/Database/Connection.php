<?php
namespace App\Database;

use PDO;
use PDOException;
use Exception;

/**
 * Centralized DB connection for MVC
 */
class Connection
{
  private static ?PDO $db = null;

  public static function get(): PDO
  {
    if (self::$db instanceof PDO) {
      return self::$db;
    }

    try {
      if (!is_dir(DB_DIR)) {
        if (!@mkdir(DB_DIR, 0755, true)) {
          throw new Exception('Cannot create database directory: ' . DB_DIR);
        }
      }

      self::$db = new PDO(DB_DSN);
      self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      if (!file_exists(DB_FILE) || filesize(DB_FILE) === 0) {
        self::initializeDatabase(self::$db);
      }

      return self::$db;
    } catch (PDOException $e) {
      log_error('Database connection failed', ['error' => $e->getMessage()]);
      if (DEBUG_MODE) {
        die('Database Error: ' . htmlspecialchars($e->getMessage()));
      }
      die('Database connection error. Please try again later.');
    } catch (Exception $e) {
      log_error('Unexpected error during database setup', ['error' => $e->getMessage()]);
      die('An unexpected error occurred. Please try again later.');
    }
  }

  private static function initializeDatabase(PDO $db): void
  {
    try {
      $db->exec('
        CREATE TABLE IF NOT EXISTS users (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          name TEXT NOT NULL,
          email TEXT UNIQUE NOT NULL,
          is_admin INTEGER DEFAULT 0,
          password TEXT NOT NULL,
          created_at DATETIME NOT NULL
        );
      ');

      $db->exec('
        CREATE TABLE IF NOT EXISTS works (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          title TEXT NOT NULL,
          description TEXT,
          image TEXT,
          category TEXT,
          created_at DATETIME NOT NULL
        );
      ');

      $db->exec('
        CREATE TABLE IF NOT EXISTS admin (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          email TEXT UNIQUE NOT NULL,
          password TEXT NOT NULL
        );
      ');

      $db->exec('
        CREATE TABLE IF NOT EXISTS categories (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          name TEXT UNIQUE NOT NULL,
          description TEXT NOT NULL
        );
      ');

      $admin_email = DEFAULT_ADMIN_EMAIL;
      $admin_pass = DEFAULT_ADMIN_PASSWORD;
      $admin_password = password_hash($admin_pass, PASSWORD_DEFAULT);
      $admin_stmt = $db->prepare('INSERT INTO admin (email, password) VALUES (:e, :p)');
      $admin_stmt->execute([':e' => $admin_email, ':p' => $admin_password]);

      $categories = [
        [
          'name' => 'Ілюстрація',
          'description' => 'Мистецтво створення візуальних образів для книг, журналів та інших медіа.'
        ],
        [
          'name' => 'Живопис',
          'description' => 'Техніка створення мистецьких творів за допомогою фарб на різних поверхнях.'
        ],
        [
          'name' => 'Графіка',
          'description' => 'Мистецтво створення зображень за допомогою ліній, форм і текстур.'
        ],
        [
          'name' => 'Фотографія',
          'description' => 'Мистецтво та техніка створення зображень за допомогою світла та камери.'
        ]
      ];

      $categories_stmt = $db->prepare('INSERT INTO categories (name, description) VALUES (:n, :d)');
      foreach ($categories as $category) {
        $categories_stmt->execute([
          ':n' => $category['name'],
          ':d' => $category['description'],
        ]);
      }

      $works = [
        [
          'title' => 'Дух лісу',
          'description' => 'Таємнича духовна сутність, що мешкає в глибині давніх лісів. Легендарна істота, обережна, але милостива до тих, хто поважає природу.',
          'image' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=500&h=500&fit=crop',
          'category' => 'Ілюстрація'
        ],
        [
          'title' => 'Русалка Дніпра',
          'description' => 'Українська русалка, водяна дева річки Дніпро. Її чарівний спів привертає мандрівників, а краса вважається безсмертною в українських легендах.',
          'image' => 'https://images.unsplash.com/photo-1507531173827-e71b99932e29?w=500&h=500&fit=crop',
          'category' => 'Живопис'
        ],
        [
          'title' => 'Баба Яга',
          'description' => 'Легендарна славянська ведунка, що живе в лісовій хижинці на курячих ніжках. Мудра і грізна, вона охороняє границю між світом живих і мертвих.',
          'image' => 'https://images.unsplash.com/photo-1503599810694-e3ea5ecd189f?w=500&h=500&fit=crop',
          'category' => 'Графіка'
        ],
        [
          'title' => 'Жар-птиця',
          'description' => 'Чарівний птах із горячого вогню, символ багатства й удачі в слов\'янській міфології. Її полювання стає завданням героїв в безлічі легенд.',
          'image' => 'https://images.unsplash.com/photo-1516541497487-3f3f602e9430?w=500&h=500&fit=crop',
          'category' => 'Ілюстрація'
        ],
        [
          'title' => 'Ілля Муромець',
          'description' => 'Найбільший богатир Київської Русі. Його підвиги легендарні: побідив Святогора, боровся зі змієм та захищав землю від ворогів.',
          'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=500&h=500&fit=crop',
          'category' => 'Живопис'
        ],
        [
          'title' => 'Мавка',
          'description' => 'Українська лісна дева, молода і прекрасна мешканиця чащі. По ночах співає чарівні пісні й водить хороводи з іншими міфологічними створіннями.',
          'image' => 'https://images.unsplash.com/photo-1517457373614-b7152f800fd1?w=500&h=500&fit=crop',
          'category' => 'Графіка'
        ],
        [
          'title' => 'Велес - Бог худе',
          'description' => 'Священний бог худоби, музики й магії в слов\'янській міфології. Охоронець знань, мудрості та потойбічного світу.',
          'image' => 'https://images.unsplash.com/photo-1551623901-7dfa435ad00d?w=500&h=500&fit=crop',
          'category' => 'Фотографія'
        ],
        [
          'title' => 'Перун - Бог грому',
          'description' => 'Могутній бог війни, грому й блискавки. Головний у слов\'янській пантеоні, захисник від сил зла й символ мужності.',
          'image' => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?w=500&h=500&fit=crop',
          'category' => 'Ілюстрація'
        ],
      ];

      $work_stmt = $db->prepare('INSERT INTO works (title, description, image, category, created_at) VALUES (:t, :d, :i, :c, :ca)');
      foreach ($works as $index => $work) {
        $work_stmt->execute([
          ':t' => $work['title'],
          ':d' => $work['description'],
          ':i' => $work['image'],
          ':c' => $work['category'],
          ':ca' => date('Y-m-d H:i:s', strtotime('-' . (count($works) - $index) . ' hours'))
        ]);
      }
    } catch (PDOException $e) {
      log_error('Database initialization failed', ['error' => $e->getMessage()]);
      throw new Exception('Failed to initialize database: ' . $e->getMessage());
    }
  }
}