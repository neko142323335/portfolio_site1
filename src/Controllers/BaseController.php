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
  protected function render($template, $data = [])
  {
    echo $this->twig->render($template, $data);
  }

  /**
   * Редірект
   */
  protected function redirect($url)
  {
    header('Location: ' . $url);
    exit;
  }

  /**
   * Перевірка чи користувач залогінений
   */
  protected function isLoggedIn()
  {
    return isset($_SESSION['user_id']);
  }

  /**
   * Перевірка чи користувач адміністратор
   */
  protected function isAdmin()
  {
    return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
  }

  /**
   * Вимагати авторизацію
   */
  protected function requireAuth()
  {
    if (!$this->isLoggedIn()) {
      $this->redirect('auth.php');
    }
  }

  /**
   * Вимагати права адміністратора
   */
  protected function requireAdmin()
  {
    if (!$this->isAdmin()) {
      $this->redirect('admin/login.php');
    }
  }

  /**
   * Отримати POST дані
   */
  protected function getPost($key, $default = null)
  {
    return $_POST[$key] ?? $default;
  }

  /**
   * Отримати GET дані
   */
  protected function getQuery($key, $default = null)
  {
    return $_GET[$key] ?? $default;
  }

  /**
   * Перевірка POST запиту
   */
  protected function isPost()
  {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
  }
}
