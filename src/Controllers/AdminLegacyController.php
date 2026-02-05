<?php
namespace App\Controllers;

/**
 * Тимчасовий контролер для legacy admin сторінок
 */
class AdminLegacyController extends BaseController
{
  private function includeLegacy($file)
  {
    require __DIR__ . '/../../admin/' . $file;
  }

  public function login()
  {
    $this->includeLegacy('login.php');
  }

  public function dashboard()
  {
    $this->includeLegacy('dashboard.php');
  }
}