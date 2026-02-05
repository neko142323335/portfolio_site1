<?php
namespace App\Controllers;

use App\Models\Work;

/**
 * Контролер головної сторінки
 */
class HomeController extends BaseController
{
  public function index()
  {
    try {
      $workModel = new Work($this->db);
      $works = $workModel->getAll(5); // Останні 5 робіт
    } catch (\Exception $e) {
      require_once __DIR__ . '/../../includes/helpers.php';
      log_error('Error in HomeController::index', ['message' => $e->getMessage()]);
      $works = [];
    }

    $this->render('index.html.twig', [
      'works' => $works,
      'logged_out' => $this->getQuery('logged_out', 0),
    ]);
  }
}
