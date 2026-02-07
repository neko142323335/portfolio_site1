<?php
/**
 * Точка входу для MVC додатку
 * Всі запити проходять через цей файл
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

use App\Router;
use App\Controllers\HomeController;
use App\Controllers\GalleryController;
use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\DashboardController;
use App\Controllers\AdminController;
use App\Controllers\AdminLegacyController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\WorkController as AdminWorkController;
use App\Controllers\Admin\UserController;
use App\Controllers\Dashboard\WorkController as DashboardWorkController;

// Ініціалізація Twig
$twig = require_once __DIR__ . '/../includes/twig.php';

// Створити роутер
$router = new Router($db, $twig);

// Визначити маршрути
$router
  // Основні маршрути
  ->add('/', HomeController::class, 'index')
  ->add('/gallery', GalleryController::class, 'index')
  ->add('/contact', ContactController::class, 'index')
  ->add('/auth', AuthController::class, 'login')
  ->add('/logout', AuthController::class, 'logout')
  ->add('/dashboard', DashboardController::class, 'index')
  
  // Маршрути адміністратора
  ->add('/admin', AdminController::class, 'index')
  ->group('/admin', function($router) {
    $router
      ->add('/login', AdminLegacyController::class, 'login')
      ->add('/dashboard', AdminLegacyController::class, 'dashboard')
      // Works
      ->group('/works', function($router) {
        $router
          ->add('/add', AdminWorkController::class, 'add')
          ->add('/edit', AdminWorkController::class, 'edit')
          ->add('/delete', AdminWorkController::class, 'delete');
      })
      // Categories
      ->group('/categories', function($router) {
        $router
          ->add('', CategoryController::class, 'index')
          ->add('/add', CategoryController::class, 'add')
          ->add('/edit', CategoryController::class, 'edit')
          ->add('/delete', CategoryController::class, 'delete');
      })
      // Users
      ->group('/users', function($router) {
        $router
          ->add('', UserController::class, 'index')
          ->add('/add', UserController::class, 'add')
          ->add('/edit', UserController::class, 'edit')
          ->add('/delete', UserController::class, 'delete');
      });
  })
  
  // Маршрути користувача
  ->group('/dashboard', function($router) {
    $router->group('/works', function($router) {
      $router
        ->add('/add', DashboardWorkController::class, 'add')
        ->add('/edit', DashboardWorkController::class, 'edit')
        ->add('/delete', DashboardWorkController::class, 'delete');
    });
  });

// Обробити запит
$router->dispatch();
