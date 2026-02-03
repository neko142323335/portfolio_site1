<?php
// Simple PDO SQLite connection. Creates DB if not exists.
$dir = __DIR__ . '/../database';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$dbfile = $dir . '/portfolio.db';
$init = !file_exists($dbfile);
$db = new PDO('sqlite:' . $dbfile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($init) {
  $db->exec('
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      email TEXT UNIQUE NOT NULL,
      is_admin INTEGER DEFAULT 1,
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
      username TEXT UNIQUE NOT NULL,
      password TEXT NOT NULL
    );
  ');

  $pw = password_hash("admin123", PASSWORD_DEFAULT);
  $db->prepare('INSERT INTO admin (username,password) VALUES (:u,:p)')
     ->execute([':u' => 'admin', ':p' => $pw]);

  $db->prepare('INSERT INTO works (title,description,image,category,created_at) VALUES (?,?,?,?,?)')
     ->execute(["Приклад роботи","Короткий опис","assets/img/works/default.png","Ілюстрація",date('Y-m-d H:i:s')]);
}
