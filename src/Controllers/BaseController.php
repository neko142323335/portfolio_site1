<?php
namespace App\Controllers;

use PDO;
use Twig\Environment;

/**
 * Базовий контролер з загальною функціональністю
 */
abstract class BaseController
{
  protected PDO $db;
  protected Environment $twig;

  public function __construct(PDO $db, Environment $twig)
  {
    $this->db = $db;
    $this->twig = $twig;
    
    // Стартуємо сесію якщо ще не стартована
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  /**
   * Рендер шаблону
   */
  protected function render(string $template, array $data = []): void
  {
    echo $this->twig->render($template, $data);
  }

  /**
   * Редірект
   */
  protected function redirect(string $url): void
  {
    header('Location: ' . $url);
    exit;
  }

  /**
   * Перевірка чи користувач залогінений
   */
  protected function isLoggedIn(): bool
  {
    return isset($_SESSION['user_id']);
  }

  /**
   * Перевірка чи користувач адміністратор
   */
  protected function isAdmin(): bool
  {
    return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
  }

  /**
   * Вимагати авторизацію
   */
  protected function requireAuth(): void
  {
    if (!$this->isLoggedIn()) {
      $this->redirect('/auth');
    }
  }

  /**
   * Вимагати авторизацію користувача (аліас)
   */
  protected function requireUser(): void
  {
    $this->requireAuth();
  }

  /**
   * Вимагати права адміністратора
   */
  protected function requireAdmin(): void
  {
    if (!$this->isAdmin()) {
      $this->redirect('/admin/login');
    }
  }

  /**
   * Отримати POST дані
   */
  protected function getPost(string $key, mixed $default = null): mixed
  {
    return $_POST[$key] ?? $default;
  }

  /**
   * Отримати GET дані
   */
  protected function getQuery(string $key, mixed $default = null): mixed
  {
    return $_GET[$key] ?? $default;
  }

  /**
   * Перевірка POST запиту
   */
  protected function isPost(): bool
  {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
  }
}
