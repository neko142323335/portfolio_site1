<?php
namespace App;

use PDO;
use Twig\Environment;

/**
 * Роутер з підтримкою динамічних параметрів URL та regex патернів
 */
class Router
{
  private array $routes = [];
  private PDO $db;
  private Environment $twig;
  private ?string $currentPrefix = null;

  public function __construct(PDO $db, Environment $twig)
  {
    $this->db = $db;
    $this->twig = $twig;
  }

  /**
   * Додати маршрут з підтримкою:
   * - Точних шляхів: '/about'
   * - Динамічних параметрів: '/user/{id}', '/work/{id}/edit'
   * - Regex патернів: '/post/{id:\d+}' або '/slug/{slug:[a-z0-9-]+}'
   * - Групування: використовувати group() для префіксу
   */
  public function add(string $path, string $controller, string $action): self
  {
    // Додати префікс якщо ми всередині group()
    $fullPath = ($this->currentPrefix ?? '') . $path;
    
    $this->routes[$fullPath] = [
      'controller' => $controller,
      'action' => $action,
      'pattern' => $this->convertToRegex($fullPath)
    ];
    return $this;
  }

  /**
   * Групування маршрутів з спільним префіксом
   * 
   * Приклад:
   * $router->group('/admin', function($router) {
   *   $router->add('/works/add', WorkController::class, 'add');
   *   $router->add('/works/edit', WorkController::class, 'edit');
   * });
   */
  public function group(string $prefix, callable $callback): self
  {
    // Зберегти поточний префікс
    $previousPrefix = $this->currentPrefix ?? '';
    $this->currentPrefix = $previousPrefix . $prefix;
    
    // Викликати callback з цим роутером
    $callback($this);
    
    // Восстановити попередній префікс
    $this->currentPrefix = $previousPrefix;
    
    return $this;
  }

  /**
   * Конвертувати шлях до regex патерну
   * /user/{id} -> /user/(\d+)
   * /user/{id:\d+} -> /user/(\d+)
   * /slug/{name:[a-z0-9-]+} -> /slug/([a-z0-9-]+)
   */
  private function convertToRegex(string $path): ?string
  {
    // Якщо немає параметрів, повернути null (точний матч)
    if (!preg_match('/{.*?}/', $path)) {
      return null;
    }

    // Екранувати спеціальні символи, але не { }
    $pattern = preg_quote($path, '#');
    $pattern = str_replace('\\{', '{', $pattern);
    $pattern = str_replace('\\}', '}', $pattern);

    // Замінити {param} або {param:regex} на capture groups
    $pattern = preg_replace_callback(
      '/{([a-zA-Z_][a-zA-Z0-9_]*)?:?([^}]*)}/',
      function ($matches) {
        $paramName = $matches[1] ?? 'param';
        $regex = $matches[2] ?: '\d+'; // За замовчуванням - цифри
        return "(?P<$paramName>$regex)";
      },
      $pattern
    );

    return "#^$pattern$#";
  }

  /**
   * Обробити запит
   */
  public function dispatch(): void
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

    // Спочатку спробувати точний матч (більша пріоритет)
    if (isset($this->routes[$path])) {
      $route = $this->routes[$path];
      $this->executeRoute($route);
      return;
    }

    // Потім спробувати динамічні маршрути з regex
    foreach ($this->routes as $routePath => $route) {
      if ($route['pattern'] === null) {
        continue; // Пропустити маршрути без параметрів
      }

      if (preg_match($route['pattern'], $path, $matches)) {
        // Отримати лише именовані параметри (без числових індексів)
        $params = array_filter(
          $matches,
          function ($key) {
            return !is_numeric($key);
          },
          ARRAY_FILTER_USE_KEY
        );

        // Передати параметри до контролера
        $this->executeRoute($route, $params);
        return;
      }
    }

    // 404 - маршрут не знайдено
    http_response_code(404);
    echo "404 - Сторінку не знайдено";
  }

  /**
   * Виконати маршрут з передачею параметрів
   */
  private function executeRoute(array $route, array $params = []): void
  {
    $controllerName = $route['controller'];
    $action = $route['action'];

    // Створити екземпляр контролера
    $controller = new $controllerName($this->db, $this->twig);
    
    // Викликати метод з параметрами
    if (!empty($params)) {
      // Передати параметри як аргументи методу по порядку
      call_user_func_array([$controller, $action], array_values($params));
    } else {
      // Викликати без аргументів для зворотної сумісності
      $controller->$action();
    }
  }
}
