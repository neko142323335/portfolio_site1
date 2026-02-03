<?php
// Simple PDO SQLite connection. Creates DB if not exists.
$dir = __DIR__ . '/../database';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$dbfile = $dir . '/portfolio.db';
$init = !file_exists($dbfile);
$db = new PDO('sqlite:' . $dbfile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($init) {
  $db->exec('CREATE TABLE works (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, description TEXT, image TEXT, category TEXT, created_at DATETIME)');
  $db->exec('CREATE TABLE admin (id INTEGER PRIMARY KEY, username TEXT UNIQUE, password TEXT)');
  $db->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT UNIQUE, password TEXT, created_at DATETIME)');
  $pw = password_hash("admin123", PASSWORD_DEFAULT);
  $db->prepare('INSERT INTO admin (id,username,password) VALUES (NULL, :u, :p)')->execute([':u'=>'admin',':p'=>$pw]);
  // sample work
  $db->prepare('INSERT INTO works (title,description,image,category,created_at) VALUES (?,?,?,?,?)')
    ->execute(["Приклад роботи","Короткий опис","assets/img/works/default.png","Ілюстрація",date('Y-m-d H:i:s')]);
}
