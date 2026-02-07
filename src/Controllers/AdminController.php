<?php
namespace App\Controllers;

/**
 * Admin root redirect controller
 * Перенаправляє /admin на логін або дашборд залежно від статусу користувача
 */
class AdminController extends BaseController
{
  public function index(): void
  {
    // Якщо адміністратор залогінений, перенаправити на дашборд
    if ($this->isAdmin()) {
      $this->redirect('/admin/dashboard');
    }
    
    // Інакше, перенаправити на сторінку логіну
    $this->redirect('/admin/login');
  }
}
