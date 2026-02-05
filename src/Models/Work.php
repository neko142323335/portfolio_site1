<?php
namespace App\Models;

use PDO;

/**
 * Work Model - робота з таблицею works
 */
class Work
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  /**
   * Отримати всі роботи
   */
  public function getAll($limit = null)
  {
    $query = 'SELECT * FROM works ORDER BY created_at DESC';
    if ($limit) {
      $query .= ' LIMIT ' . (int)$limit;
    }
    $stmt = $this->db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати роботи за категорією
   */
  public function getByCategory($category)
  {
    $stmt = $this->db->prepare('SELECT * FROM works WHERE category = :cat ORDER BY created_at DESC');
    $stmt->execute([':cat' => $category]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати роботу за ID
   */
  public function getById($id)
  {
    $stmt = $this->db->prepare('SELECT * FROM works WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати всі категорії з таблиці categories
   */
  public function getCategories()
  {
    $stmt = $this->db->query('SELECT id, name as category FROM categories ORDER BY name');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Створити нову роботу
   */
  public function create($data)
  {
    $stmt = $this->db->prepare('
      INSERT INTO works (title, description, category, image_path, created_at)
      VALUES (:title, :description, :category, :image_path, :created_at)
    ');
    
    return $stmt->execute([
      ':title' => $data['title'],
      ':description' => $data['description'] ?? null,
      ':category' => $data['category'] ?? null,
      ':image_path' => $data['image_path'],
      ':created_at' => date('Y-m-d H:i:s'),
    ]);
  }

  /**
   * Оновити роботу
   */
  public function update($id, $data)
  {
    $sql = 'UPDATE works SET title = :title, description = :description, category = :category';
    $params = [
      ':id' => $id,
      ':title' => $data['title'],
      ':description' => $data['description'] ?? null,
      ':category' => $data['category'] ?? null,
    ];

    if (isset($data['image_path'])) {
      $sql .= ', image_path = :image_path';
      $params[':image_path'] = $data['image_path'];
    }

    $sql .= ' WHERE id = :id';
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  /**
   * Видалити роботу
   */
  public function delete($id)
  {
    $stmt = $this->db->prepare('DELETE FROM works WHERE id = :id');
    return $stmt->execute([':id' => $id]);
  }
}
