<?php
namespace App\Controllers;

use App\Models\Work;

/**
 * Контролер галереї
 */
class GalleryController extends BaseController
{
  public function index()
  {
    try {
      $workModel = new Work($this->db);
      $category = $this->getQuery('category');

      // Отримати всі категорії для фільтру
      $categories = $workModel->getCategories();

      // Отримати роботи на основі фільтру
      if ($category) {
        $works = $workModel->getByCategory(sanitize_input($category));
      } else {
        $works = $workModel->getAll();
      }

    } catch (\Exception $e) {
      require_once __DIR__ . '/../../includes/helpers.php';
      log_error('Error in GalleryController::index', ['message' => $e->getMessage()]);
      $works = [];
      $categories = [];
    }

    $this->render('gallery.html.twig', [
      'works' => $works,
      'categories' => $categories,
      'category' => $category,
      'logged_out' => $this->getQuery('logged_out', 0),
    ]);
  }
}
