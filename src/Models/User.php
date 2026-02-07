<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * User Model - робота з таблицею users
 */
class User
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  /**
   * Знайти користувача за email
   */
  public function findByEmail(string $email): array|false
  {
    $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Знайти користувача за ID
   */
  public function findById(int $id): array|false
  {
    $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function get_current_user(): array|null
  {
    if (isset($_SESSION['user_id'])) {
      return $this->findById($_SESSION['user_id']);
    }
    return null;
  }

  /**
   * Створити нового користувача
   */
  public function create(array $data): bool
  {
    try {
      $stmt = $this->db->prepare('
        INSERT INTO users (name, email, password, is_admin, created_at)
        VALUES (:name, :email, :password, :is_admin, :created_at)
      ');
      
      return $stmt->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ':is_admin' => $data['is_admin'] ?? 0,
        ':created_at' => date('Y-m-d H:i:s'),
      ]);
    } catch (PDOException $e) {
      if ($e->getCode() == 23000) { // Duplicate email
        return false;
      }
      throw $e;
    }
  }

  /**
   * Перевірити пароль
   */
  public function verifyPassword(array $user, string $password): bool
  {
    return password_verify($password, $user['password']);
  }

  /**
   * Перевірити чи користувач адміністратор
   */
  public function isAdmin(array $user): bool
  {
    return isset($user['is_admin']) && (bool)$user['is_admin'];
  }

  /**
   * Отримати всіх користувачів
   */
  public function getAll(): array
  {
    $stmt = $this->db->prepare('SELECT id, name, email, is_admin, created_at FROM users ORDER BY created_at DESC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Отримати користувача за ID (публічна інформація)
   */
  public function getById(int $id): array|false
  {
    $stmt = $this->db->prepare('SELECT id, name, email, is_admin, created_at FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Перевірити, чи існує email
   */
  public function emailExists(string $email, ?int $excludeId = null): bool
  {
    $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
    $params = [':email' => $email];

    if ($excludeId) {
      $sql .= ' AND id != :id';
      $params[':id'] = $excludeId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
  }

  /**
   * Оновити користувача
   */
  public function update(int $id, array $data): bool
  {
    $sql = 'UPDATE users SET name = :name, email = :email, is_admin = :is_admin';
    $params = [
      ':id' => $id,
      ':name' => $data['name'],
      ':email' => $data['email'],
      ':is_admin' => $data['is_admin'] ?? 0,
    ];

    // Якщо передано новий пароль
    if (!empty($data['password'])) {
      $sql .= ', password = :password';
      $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $sql .= ' WHERE id = :id';
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  /**
   * Видалити користувача
   */
  public function delete(int $id): bool
  {
    $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
    return $stmt->execute([':id' => $id]);
  }

  /**
   * Отримати кількість адміністраторів
   */
  public function getAdminCount(): int
  {
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE is_admin = 1');
    $stmt->execute();
    return $stmt->fetchColumn();
  }
}
