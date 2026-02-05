<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Category;

/**
 * Admin controller for categories
 */
class CategoryController extends BaseController
{
  public function __construct($db, $twig)
  {
    parent::__construct($db, $twig);
    $this->requireAdmin();
  }

  public function index()
  {
    $categoryModel = new Category($this->db);
    $categories = $categoryModel->getAll();

    $this->render('admin/categories/index.html.twig', [
      'categories' => $categories,
      'success' => $this->getQuery('success', 0),
      'error' => $this->getQuery('error', ''),
      'logged_out' => 0,
    ]);
  }

  public function add()
  {
    $error = '';

    if ($this->isPost()) {
      $error = $this->handleAdd();
      if (!$error) {
        $this->redirect('/admin/categories?success=1');
      }
    }

    $this->render('admin/categories/add.html.twig', [
      'error' => $error,
      'logged_out' => 0,
    ]);
  }

  public function edit()
  {
    $id = (int) $this->getQuery('id', 0);
    $categoryModel = new Category($this->db);
    $category = $categoryModel->getById($id);

    if (!$category) {
      $this->redirect('/admin/categories?error=Категорія%20не%20знайдена');
    }

    $error = '';
    if ($this->isPost()) {
      $error = $this->handleEdit($id);
      if (!$error) {
        $this->redirect('/admin/categories?success=1');
      }
    }

    $this->render('admin/categories/edit.html.twig', [
      'category' => $category,
      'error' => $error,
      'logged_out' => 0,
    ]);
  }

  public function delete()
  {
    $id = (int) $this->getQuery('id', 0);
    $categoryModel = new Category($this->db);

    try {
      $categoryModel->delete($id);
      $this->redirect('/admin/categories?success=1');
    } catch (\Exception $e) {
      $this->redirect('/admin/categories?error=Помилка%20видалення');
    }
  }

  private function handleAdd()
  {
    require_once __DIR__ . '/../../../includes/helpers.php';

    $name = sanitize_input($this->getPost('name', ''));
    $description = sanitize_input($this->getPost('description', ''));

    if (!$name || !$description) {
      return ERROR_MESSAGES['required_fields'];
    }

    $categoryModel = new Category($this->db);
    $categoryModel->create([
      'name' => $name,
      'description' => $description,
    ]);

    return '';
  }

  private function handleEdit($id)
  {
    require_once __DIR__ . '/../../../includes/helpers.php';

    $name = sanitize_input($this->getPost('name', ''));
    $description = sanitize_input($this->getPost('description', ''));

    if (!$name || !$description) {
      return ERROR_MESSAGES['required_fields'];
    }

    $categoryModel = new Category($this->db);
    $categoryModel->update($id, [
      'name' => $name,
      'description' => $description,
    ]);

    return '';
  }
}