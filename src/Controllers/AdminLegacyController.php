<?php
namespace App\Controllers;

/**
 * Тимчасовий контролер для legacy admin сторінок
 */
class AdminLegacyController extends BaseController
{
  private function includeLegacy(string $file): void
  {
    require __DIR__ . '/../../admin/' . $file;
  }

  public function login(): void
  {
    $this->includeLegacy('login.php');
  }

  public function dashboard(): void
  {
    $this->includeLegacy('dashboard.php');
  }
}