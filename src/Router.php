<?php
namespace App;

use PDO;
use Twig\Environment;

/**
 * Простий роутер для обробки запитів
 */
class Router
{
  private array $routes = [];
  private PDO $db;
  private Environment $twig;

  public function __construct(PDO $db, Environment $twig)
  {
    $this->db = $db;
    $this->twig = $twig;
  }

  /**
   * Додати маршрут
   */
  public function add($path, $controller, $action)
  {
    $this->routes[$path] = ['controller' => $controller, 'action' => $action];
    return $this;
  }

  /**
   * Обробити запит
   */
  public function dispatch()
  {
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    
    // Видалити базовий шлях якщо є
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    if ($scriptName !== '/') {
      $path = str_replace($scriptName, '', $path);
    }
    
    // Видалити початковий слеш
    $path = '/' . trim($path, '/');
    
    // Головна сторінка
    if ($path === '/' || $path === '/index.php') {
      $path = '/';
    }

    // Знайти маршрут
    if (isset($this->routes[$path])) {
      $route = $this->routes[$path];
      $controllerName = $route['controller'];
      $action = $route['action'];

      // Створити екземпляр контролера
      $controller = new $controllerName($this->db, $this->twig);
      
      // Викликати метод
      $controller->$action();
      return;
    }

    // 404 - маршрут не знайдено
    http_response_code(404);
    echo "404 - Сторінку не знайдено";
  }
}
