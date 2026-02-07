<?php
namespace App\Controllers;

use App\Models\Work;

/**
 * Контролер галереї
 */
class GalleryController extends BaseController
{
  private const PER_PAGE = 12; // Кількість робіт на сторінку

  public function index(): void
  {
    try {
      $workModel = new Work($this->db);
      $category = $this->getQuery('category');
      $page = max(1, (int)($this->getQuery('page', 1)));

      // Отримати всі категорії для фільтру
      $categories = $workModel->getCategories();

      // Отримати роботи з пагінацією
      $works = $workModel->getPaginated($page, self::PER_PAGE, $category ? sanitize_input($category) : null);
      $totalPages = $workModel->getTotalPages(self::PER_PAGE, $category ? sanitize_input($category) : null);

    } catch (\Exception $e) {
      require_once __DIR__ . '/../../includes/helpers.php';
      log_error('Error in GalleryController::index', ['message' => $e->getMessage()]);
      $works = [];
      $categories = [];
      $page = 1;
      $totalPages = 0;
    }

    $this->render('gallery.html.twig', [
      'works' => $works,
      'categories' => $categories,
      'category' => $category,
      'current_page' => $page,
      'total_pages' => $totalPages,
      'has_prev' => $page > 1,
      'has_next' => $page < $totalPages,
      'logged_out' => $this->getQuery('logged_out', 0),
    ]);
  }
}

