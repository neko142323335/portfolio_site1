<?php
namespace App\Models;

use PDO;

/**
 * Category Model - робота з таблицею categories
 */
class Category
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function getAll(): array
  {
    $this->ensureTable();
    $stmt = $this->db->prepare('SELECT * FROM categories ORDER BY name ASC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getById(int $id): array|false
  {
    $this->ensureTable();
    $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function create(array $data): bool
  {
    $this->ensureTable();
    $stmt = $this->db->prepare('INSERT INTO categories (name, description) VALUES (:n, :d)');
    return $stmt->execute([
      ':n' => $data['name'],
      ':d' => $data['description'],
    ]);
  }

  public function update(int $id, array $data): bool
  {
    $this->ensureTable();
    $stmt = $this->db->prepare('UPDATE categories SET name = :n, description = :d WHERE id = :id');
    return $stmt->execute([
      ':n' => $data['name'],
      ':d' => $data['description'],
      ':id' => $id,
    ]);
  }

  public function delete(int $id): bool
  {
    $this->ensureTable();
    $stmt = $this->db->prepare('DELETE FROM categories WHERE id = :id');
    return $stmt->execute([':id' => $id]);
  }

  private function ensureTable(): void
  {
    $this->db->exec('CREATE TABLE IF NOT EXISTS categories (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT UNIQUE NOT NULL,
      description TEXT NOT NULL
    );');
  }
}