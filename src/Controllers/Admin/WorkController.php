<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Work;
use App\Models\Category;

/**
 * Контролер адмін панелі для управління роботами
 */
class WorkController extends BaseController
{
  public function __construct($db, $twig)
  {
    parent::__construct($db, $twig);
    $this->requireAdmin();
  }

  /**
   * Форма додавання роботи
   */
  public function add()
  {
    require_once __DIR__ . '/../../../includes/helpers.php';
    
    $error = '';
    $categoryModel = new Category($this->db);
    $categories = $categoryModel->getAll();
    
    if ($this->isPost()) {
      try {
        $title = sanitize_input($this->getPost('title'));
        $description = sanitize_input($this->getPost('description'));
        $category = sanitize_input($this->getPost('category'));

        if (!$title) {
          $error = "Назва обов'язкова";
        } elseif (!$category) {
          $error = "Категорія обов'язкова";
        } elseif (empty($_FILES['image']['tmp_name'])) {
          $error = 'Виберіть зображення';
        } else {
          $file_upload = save_uploaded_file($_FILES['image']);
          if (!$file_upload['success']) {
            $error = $file_upload['error'];
          } else {
            $workModel = new Work($this->db);
            $workModel->create([
              'title' => $title,
              'description' => $description,
              'category' => $category,
              'image_path' => $file_upload['path']
            ]);
            $this->redirect('/admin/dashboard?success=1');
          }
        }
      } catch (\PDOException $e) {
        log_error('Database error in WorkController::add', ['message' => $e->getMessage()]);
        $error = 'Помилка бази даних. Спробуйте пізніше.';
      } catch (\Exception $e) {
        log_error('Unexpected error in WorkController::add', ['message' => $e->getMessage()]);
        $error = 'Неочікувана помилка. Спробуйте пізніше.';
      }
    }

    $this->render('admin/works/add_work.html.twig', [
      'error' => $error,
      'categories' => $categories,
    ]);
  }


  /**
   * Форма редагування роботи
   */
  public function edit()
  {
    require_once __DIR__ . '/../../../includes/helpers.php';
    
    $error = '';
    $id = (int) $this->getQuery('id');
    
    if (!$id) {
      $this->redirect('/admin/dashboard?error=' . urlencode('Роботу не знайдено'));
    }

    $categoryModel = new Category($this->db);
    $categories = $categoryModel->getAll();

    try {
      $workModel = new Work($this->db);
      $work = $workModel->getById($id);

      if (!$work) {
        $this->redirect('/admin/dashboard?error=' . urlencode('Роботу не знайдено'));
      }

      if ($this->isPost()) {
        $title = sanitize_input($this->getPost('title'));
        $description = sanitize_input($this->getPost('description'));
        $category = sanitize_input($this->getPost('category'));

        if (!$title) {
          $error = "Назва обов'язкова";
        } elseif (!$category) {
          $error = "Категорія обов'язкова";
        } else {
          $image_path = $work['image'];

          // Якщо завантажено нове зображення
          if (!empty($_FILES['image']['tmp_name'])) {
            $file_upload = save_uploaded_file($_FILES['image']);
            if (!$file_upload['success']) {
              $error = $file_upload['error'];
            } else {
              // Видалити старе зображення
              if (!empty($work['image'])) {
                $old_image_path = __DIR__ . '/../../../' . $work['image'];
                if (file_exists($old_image_path)) {
                  @unlink($old_image_path);
                }
              }
              $image_path = $file_upload['path'];
            }
          }

          if (!$error) {
            $workModel->update($id, [
              'title' => $title,
              'description' => $description,
              'category' => $category,
              'image_path' => $image_path
            ]);
            $this->redirect('/admin/dashboard?success=1');
          }
        }
      }

      $this->render('admin/works/edit_work.html.twig', [
        'work' => $work,
        'error' => $error,
        'categories' => $categories,
      ]);

    } catch (\PDOException $e) {
      log_error('Database error in WorkController::edit', ['id' => $id, 'message' => $e->getMessage()]);
      $this->redirect('/admin/dashboard?error=' . urlencode('Помилка бази даних'));
    } catch (\Exception $e) {
      log_error('Error in WorkController::edit', ['message' => $e->getMessage()]);
      $this->redirect('/admin/dashboard?error=' . urlencode($e->getMessage()));
    }
  }

  /**
   * Видалення роботи
   */
  public function delete()
  {
    require_once __DIR__ . '/../../../includes/helpers.php';
    
    $id = (int) $this->getQuery('id');

    if (!$id) {
      $this->redirect('/admin/dashboard?error=' . urlencode('Роботу не знайдено'));
    }

    try {
      $workModel = new Work($this->db);
      $work = $workModel->getById($id);

      if (!$work) {
        $this->redirect('/admin/dashboard?error=' . urlencode('Роботу не знайдено'));
      }

      // Видалити з бази даних
      $workModel->delete($id);

      // Видалити файл зображення
      if (!empty($work['image'])) {
        $image_path = __DIR__ . '/../../../' . $work['image'];
        if (file_exists($image_path)) {
          @unlink($image_path);
        }
      }

      $this->redirect('/admin/dashboard?success=1');

    } catch (\Exception $e) {
      log_error('Error deleting work', ['id' => $id, 'message' => $e->getMessage()]);
      $this->redirect('/admin/dashboard?error=' . urlencode('Помилка при видаленні'));
    }
  }
}
