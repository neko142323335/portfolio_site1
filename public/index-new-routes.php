<?php
/**
 * ПРИКЛАД: Оновлені маршрути з динамічними параметрами
 * 
 * Це показує, як можна оптимізувати маршрути з поточним Router
 * Поточний index.php залишається незмінним для зворотної сумісності,
 * але ці маршрути можуть замінити старі.
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
use App\Controllers\Admin\WorkController as AdminWorkController;
use App\Controllers\Admin\UserController;
use App\Controllers\Dashboard\WorkController as DashboardWorkController;

$twig = require_once __DIR__ . '/../includes/twig.php';

$router = new Router($db, $twig);

// ===== ОСНОВНІ МАРШРУТИ (без змін) =====
$router
  ->add('/', HomeController::class, 'index')
  ->add('/gallery', GalleryController::class, 'index')
  ->add('/contact', ContactController::class, 'index')
  ->add('/auth', AuthController::class, 'login')
  ->add('/logout', AuthController::class, 'logout')
  ->add('/dashboard', DashboardController::class, 'index')
  ->add('/admin/login', AdminLegacyController::class, 'login');

// ===== ОДНОСТОРОННІ МАРШРУТИ =====
// Форма додавання (без параметрів)
$router
  ->add('/admin/works/add', AdminWorkController::class, 'add')
  ->add('/admin/categories/add', CategoryController::class, 'add')
  ->add('/admin/users/add', UserController::class, 'add')
  ->add('/dashboard/works/add', DashboardWorkController::class, 'add');

// ===== МАРШРУТИ З ДИНАМІЧНИМИ ПАРАМЕТРАМИ =====

// Admin Works Management
// /admin/works/123/edit → edit($id = '123')
// /admin/works/123/delete → delete($id = '123')
$router
  ->add('/admin/works/{id:\d+}/edit', AdminWorkController::class, 'edit')
  ->add('/admin/works/{id:\d+}/delete', AdminWorkController::class, 'delete')
  ->add('/admin/works/list', AdminWorkController::class, 'list');

// Admin Categories Management
// /admin/categories/5/edit → edit($id = '5')
// /admin/categories/5/delete → delete($id = '5')
$router
  ->add('/admin/categories/{id:\d+}/edit', CategoryController::class, 'edit')
  ->add('/admin/categories/{id:\d+}/delete', CategoryController::class, 'delete')
  ->add('/admin/categories', CategoryController::class, 'index');

// Admin Users Management
// /admin/users/10/edit → edit($id = '10')
// /admin/users/10/delete → delete($id = '10')
$router
  ->add('/admin/users/{id:\d+}/edit', UserController::class, 'edit')
  ->add('/admin/users/{id:\d+}/delete', UserController::class, 'delete')
  ->add('/admin/users', UserController::class, 'index');

// Admin Dashboard
$router->add('/admin/dashboard', AdminLegacyController::class, 'dashboard');

// Dashboard (User) Works Management
// /dashboard/works/7/edit → edit($id = '7')
// /dashboard/works/7/delete → delete($id = '7')
$router
  ->add('/dashboard/works/{id:\d+}/edit', DashboardWorkController::class, 'edit')
  ->add('/dashboard/works/{id:\d+}/delete', DashboardWorkController::class, 'delete');

// ===== ДОДАТКОВІ ПРИКЛАДИ =====
// Якби були такі маршрути:

// Gallery з категорієями
// /gallery/tech → show($category = 'tech')
// $router->add('/gallery/{category:[a-z0-9-]+}', GalleryController::class, 'byCategory');

// User profiles
// /user/john-doe → show($username = 'john-doe')
// $router->add('/user/{username:[a-z0-9-]+}', UserProfileController::class, 'show');

// Багато параметрів
// /blog/my-post-title/comments/5 → showComment($postSlug = 'my-post-title', $commentId = '5')
// $router->add('/blog/{slug:[a-z0-9-]+}/comments/{id:\d+}', BlogController::class, 'showComment');

// Обробити запит
$router->dispatch();
