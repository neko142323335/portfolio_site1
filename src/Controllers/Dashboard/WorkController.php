<?php
namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\Work;
use App\Models\Category;
use App\Models\User;
use App\Validators\FileValidator;

/**
 * Контролер користувацької панелі для управління роботами
 */
class WorkController extends BaseController
{
  public function __construct($db, $twig)
  {
    parent::__construct($db, $twig);
    $this->requireAuth();
  }

  /**
   * Форма додавання роботи
   */
  public function add(): void
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
        } else {
          // Валідація файлу зображення
          $fileValidation = FileValidator::validate($_FILES['image'] ?? []);
          
          if (!$fileValidation['valid']) {
            $error = $fileValidation['error'];
          } else {
            $file_upload = save_uploaded_file($_FILES['image']);
            if (!$file_upload['success']) {
              $error = $file_upload['error'];
            } else {
              $workModel = new Work($this->db);
              $userModel = new User($this->db);
              $currentUser = $userModel->get_current_user();
              $workModel->create([
                'title' => $title,
                'user_id' => $currentUser['id'],
                'description' => $description,
                'category' => $category,
                'image' => $file_upload['path']
              ]);
              $this->redirect('/dashboard?success=1');
            }
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

    $this->render('/dashboard/works/add.html.twig', [
      'error' => $error,
      'categories' => $categories,
    ]);
  }


  /**
   * Форма редагування роботи
   */
  public function edit(): void
  {
    require_once __DIR__ . '/../../../includes/helpers.php';
    
    $error = '';
    $id = (int) $this->getQuery('id');
    
    if (!$id) {
      $this->redirect('/dashboard?error=' . urlencode('Роботу не знайдено'));
    }

    $categoryModel = new Category($this->db);
    $categories = $categoryModel->getAll();

    try {
      $workModel = new Work($this->db);
      $work = $workModel->getById($id);

      if (!$work) {
        $this->redirect('/dashboard?error=' . urlencode('Роботу не знайдено'));
      }

      if ($this->isPost()) {
        $title = sanitize_input($this->getPost('title'));
        $description = sanitize_input($this->getPost('description'));
        $category = sanitize_input($this->getPost('category'));
        $userModel = new User($this->db);
        $currentUser = $userModel->get_current_user();
        if (!$title) {
          $error = "Назва обов'язкова";
        } elseif (!$category) {
          $error = "Категорія обов'язкова";
        } else {
          $image_path = $work['image'];

          // Якщо завантажено нове зображення
          if (!empty($_FILES['image']['tmp_name'])) {
            // Валідація файлу
            $fileValidation = FileValidator::validate($_FILES['image']);
            
            if (!$fileValidation['valid']) {
              $error = $fileValidation['error'];
            } else {
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
          }

          if (!$error) {
            $workModel->update($id, [
              'title' => $title,
              'description' => $description,
              'category' => $category,
              'image' => $image_path
            ]);
            $this->redirect('/dashboard?success=1');
          }
        }
      }

      $this->render('/dashboard/works/edit.html.twig', [
        'work' => $work,
        'error' => $error,
        'categories' => $categories,
      ]);

    } catch (\PDOException $e) {
      log_error('Database error in WorkController::edit', ['id' => $id, 'message' => $e->getMessage()]);
      $this->redirect('/dashboard?error=' . urlencode('Помилка бази даних'));
    } catch (\Exception $e) {
      log_error('Error in WorkController::edit', ['message' => $e->getMessage()]);
      $this->redirect('/dashboard?error=' . urlencode($e->getMessage()));
    }
  }

  /**
   * Видалення роботи
   */
  public function delete(): void
  {
    require_once __DIR__ . '/../../../includes/helpers.php';
    
    $id = (int) $this->getQuery('id');

    if (!$id) {
      $this->redirect('/dashboard?error=' . urlencode('Роботу не знайдено'));
    }

    try {
      $workModel = new Work($this->db);
      $work = $workModel->getById($id);

      if (!$work) {
        $this->redirect('/dashboard?error=' . urlencode('Роботу не знайдено'));
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

      $this->redirect('/dashboard?success=1');

    } catch (\Exception $e) {
      log_error('Error deleting work', ['id' => $id, 'message' => $e->getMessage()]);
      $this->redirect('/dashboard?error=' . urlencode('Помилка при видаленні'));
    }
  }
}
