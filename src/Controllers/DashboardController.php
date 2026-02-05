<?php
namespace App\Controllers;

use App\Models\Work;

/**
 * Контролер кабінету користувача
 */
class DashboardController extends BaseController
{
  public function index()
  {
    // Вимагати авторизацію
    if (!$this->isLoggedIn()) {
      $this->redirect('/auth');
    }

    try {
      $workModel = new Work($this->db);
      $works = $workModel->getAll();
      
      $this->render('dashboard.html.twig', [
        'works' => $works,
        'success' => $this->getQuery('success') ? 'Операція успішна' : '',
        'error' => $this->getQuery('error', ''),
      ]);
    } catch (\Exception $e) {
      require_once __DIR__ . '/../../includes/helpers.php';
      log_error('Error in DashboardController::index', ['message' => $e->getMessage()]);
      $this->render('dashboard.html.twig', [
        'works' => [],
        'error' => 'Помилка завантаження даних',
      ]);
    }
  }
}
