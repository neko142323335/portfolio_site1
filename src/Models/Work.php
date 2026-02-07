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
   * 
   * @param int|null $limit Максимальна кількість рядків (безпечно обробляється)
   * @return array
   */
  public function getAll(?int $limit = null): array
  {
    $query = 'SELECT * FROM works ORDER BY created_at DESC';
    $params = [];
    
    if ($limit) {
      $query .= ' LIMIT :limit';
      $params[':limit'] = (int)$limit;
    }
    
    $stmt = $this->db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getByUserId(int $user_id): array
  {
    $stmt = $this->db->prepare('SELECT * FROM works WHERE user_id = :user_id ORDER BY created_at DESC');
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати роботи користувача з пагінацією
   */
  public function getByUserIdPaginated(int $user_id, int $page = 1, int $perPage = 12): array
  {
    $page = max(1, (int)$page);
    $perPage = max(1, (int)$perPage);
    $offset = ($page - 1) * $perPage;

    $stmt = $this->db->prepare('
      SELECT * FROM works 
      WHERE user_id = :user_id 
      ORDER BY created_at DESC 
      LIMIT :limit OFFSET :offset
    ');
    $stmt->execute([
      ':user_id' => (int)$user_id,
      ':limit' => $perPage,
      ':offset' => $offset,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати кількість робіт користувача
   */
  public function getUserWorkCount(int $user_id): int
  {
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM works WHERE user_id = :user_id');
    $stmt->execute([':user_id' => (int)$user_id]);
    return (int)$stmt->fetchColumn();
  }

  /**
   * Отримати кількість сторінок для робіт користувача
   */
  public function getUserWorkPages(int $user_id, int $perPage = 12): int
  {
    $total = $this->getUserWorkCount($user_id);
    return (int)ceil($total / max(1, (int)$perPage));
  }

  public function getByUserIdAndCategory(int $user_id, string $category): array
  {
    $stmt = $this->db->prepare('SELECT * FROM works WHERE user_id = :user_id AND category = :category ORDER BY created_at DESC');
    $stmt->execute([':user_id' => $user_id, ':category' => $category]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати роботи за категорією
   */
  public function getByCategory(string $category): array
  {
    $stmt = $this->db->prepare('SELECT * FROM works WHERE category = :cat ORDER BY created_at DESC');
    $stmt->execute([':cat' => $category]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати роботу за ID
   */
  public function getById(int $id): array|false
  {
    $stmt = $this->db->prepare('SELECT * FROM works WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати всі категорії з таблиці categories
   * @return array
   */
  public function getCategories(): array
  {
    $stmt = $this->db->prepare('SELECT id, name as category FROM categories ORDER BY name');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Створити нову роботу
   */
  public function create(array $data): bool
  {
    $stmt = $this->db->prepare('
      INSERT INTO works (title, user_id, description, category, image, created_at)
      VALUES (:title, :user_id, :description, :category, :image, :created_at)
    ');
    
    return $stmt->execute([
      ':title' => $data['title'],
      ':user_id' => $data['user_id'],
      ':description' => $data['description'] ?? null,
      ':category' => $data['category'] ?? null,
      ':image' => $data['image'],
      ':created_at' => date('Y-m-d H:i:s'),
    ]);
  }

  /**
   * Оновити роботу
   */
  public function update(int $id, array $data): bool
  {
    $sql = 'UPDATE works SET title = :title, user_id = :user_id, description = :description, category = :category';
    $params = [
      ':id' => $id,
      ':title' => $data['title'],
      ':user_id' => $data['user_id'],
      ':description' => $data['description'] ?? null,
      ':category' => $data['category'] ?? null,
    ];

    if (isset($data['image'])) {
      $sql .= ', image = :image';
      $params[':image'] = $data['image'];
    }

    $sql .= ' WHERE id = :id';
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  /**
   * Видалити роботу
   */
  public function delete(int $id): bool
  {
    $stmt = $this->db->prepare('DELETE FROM works WHERE id = :id');
    return $stmt->execute([':id' => $id]);
  }

  /**
   * Отримати загальну кількість робіт (з фільтром за категорією)
   */
  public function getTotal(?string $category = null): int
  {
    $query = 'SELECT COUNT(*) FROM works';
    $params = [];

    if ($category) {
      $query .= ' WHERE category = :category';
      $params[':category'] = $category;
    }

    $stmt = $this->db->prepare($query);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
  }

  /**
   * Отримати роботи з пагінацією
   * 
   * @param int $page Номер сторінки (починаючи з 1)
   * @param int $perPage Кількість робіт на сторінку
   * @param string|null $category Фільтр за категорією (необов'язково)
   * @return array Масив робіт
   */
  public function getPaginated(int $page = 1, int $perPage = 12, ?string $category = null): array
  {
    $page = max(1, (int)$page); // Переконатися що page >= 1
    $perPage = max(1, (int)$perPage); // Переконатися що perPage >= 1
    $offset = ($page - 1) * $perPage;

    $query = 'SELECT * FROM works';
    $params = [];

    if ($category) {
      $query .= ' WHERE category = :category';
      $params[':category'] = $category;
    }

    $query .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
    $params[':limit'] = $perPage;
    $params[':offset'] = $offset;

    $stmt = $this->db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати кількість сторінок
   * 
   * @param int $perPage Кількість робіт на сторінку
   * @param string|null $category Фільтр за категорією
   * @return int Кількість сторінок
   */
  public function getTotalPages(int $perPage = 12, ?string $category = null): int
  {
    $total = $this->getTotal($category);
    return (int)ceil($total / max(1, (int)$perPage));
  }
}
