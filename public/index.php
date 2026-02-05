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
use App\Controllers\AdminLegacyController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\WorkController;
use App\Controllers\Admin\UserController;

// Ініціалізація Twig
$twig = require_once __DIR__ . '/../includes/twig.php';

// Створити роутер
$router = new Router($db, $twig);

// Визначити маршрути
$router
  ->add('/', HomeController::class, 'index')
  ->add('/gallery', GalleryController::class, 'index')
  ->add('/contact', ContactController::class, 'index')
  ->add('/auth', AuthController::class, 'login')
  ->add('/logout', AuthController::class, 'logout')
  ->add('/dashboard', DashboardController::class, 'index')
  ->add('/admin/login', AdminLegacyController::class, 'login')
  ->add('/admin/dashboard', AdminLegacyController::class, 'dashboard')
  ->add('/admin/works/add', WorkController::class, 'add')
  ->add('/admin/works/edit', WorkController::class, 'edit')
  ->add('/admin/works/delete', WorkController::class, 'delete')
  ->add('/admin/categories', CategoryController::class, 'index')
  ->add('/admin/categories/add', CategoryController::class, 'add')
  ->add('/admin/categories/edit', CategoryController::class, 'edit')
  ->add('/admin/categories/delete', CategoryController::class, 'delete')
  ->add('/admin/users', UserController::class, 'index')
  ->add('/admin/users/add', UserController::class, 'add')
  ->add('/admin/users/edit', UserController::class, 'edit')
  ->add('/admin/users/delete', UserController::class, 'delete');

// Обробити запит
$router->dispatch();
