<?php
namespace App\Controllers;

use App\Models\Work;
use App\Models\User;

/**
 * Контролер кабінету користувача
 */
class DashboardController extends BaseController
{
  private const PER_PAGE = 12;

  public function index(): void
  {
    // Вимагати авторизацію
    if (!$this->isLoggedIn()) {
      $this->redirect('/auth');
    }

    try {
      $workModel = new Work($this->db);
      $userModel = new User($this->db);
      $current_user = $userModel->get_current_user();
      $page = max(1, (int)($this->getQuery('page', 1)));

      $works = $workModel->getByUserIdPaginated($current_user['id'], $page, self::PER_PAGE);
      $totalPages = $workModel->getUserWorkPages($current_user['id'], self::PER_PAGE);
      
      $this->render('dashboard.html.twig', [
        'works' => $works,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages,
        'success' => $this->getQuery('success') ? 'Операція успішна' : '',
        'error' => $this->getQuery('error', ''),
      ]);
    } catch (\Exception $e) {
      require_once __DIR__ . '/../../includes/helpers.php';
      log_error('Error in DashboardController::index', ['message' => $e->getMessage()]);
      $this->render('dashboard.html.twig', [
        'works' => [],
        'current_page' => 1,
        'total_pages' => 0,
        'has_prev' => false,
        'has_next' => false,
        'error' => 'Помилка завантаження даних',
      ]);
    }
  }
}
