<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\User;

/**
 * Контролер для управління користувачами
 */
class UserController extends BaseController
{
  private User $userModel;

  public function __construct($db, $twig)
  {
    parent::__construct($db, $twig);
    $this->userModel = new User($db);
    $this->requireAdmin();
  }

  /**
   * Список користувачів
   */
  public function index()
  {
    try {
      $users = $this->userModel->getAll();
      $success = $this->getQuery('success') ? 'Операція успішна' : '';
      $error = $this->getQuery('error', '');

      $this->render('admin/users/index.html.twig', [
        'users' => $users,
        'success' => $success,
        'error' => $error,
      ]);
    } catch (\Exception $e) {
      require_once __DIR__ . '/../../../includes/helpers.php';
      log_error('Error in UserController::index', ['message' => $e->getMessage()]);
      $this->render('admin/users/index.html.twig', [
        'users' => [],
        'error' => 'Помилка завантаження користувачів',
      ]);
    }
  }

  /**
   * Форма додавання користувача
   */
  public function add()
  {
    require_once __DIR__ . '/../../../includes/helpers.php';
    
    $error = '';

    if ($this->isPost()) {
      try {
        $name = sanitize_input($this->getPost('name'));
        $email = sanitize_input($this->getPost('email'));
        $password = $this->getPost('password');
        $password_confirm = $this->getPost('password_confirm');
        $is_admin = (int) $this->getPost('is_admin', 0);

        if (!$name || !$email || !$password) {
          $error = "Всі поля обов'язкові";
        } elseif (!validate_email($email)) {
          $error = 'Невірний формат email';
        } elseif ($this->userModel->emailExists($email)) {
          $error = 'Користувач з таким email вже існує';
        } elseif (!validate_password($password)) {
          $error = 'Пароль має бути мінімум 6 символів';
        } elseif ($password !== $password_confirm) {
          $error = 'Паролі не співпадають';
        } else {
          $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_admin' => $is_admin,
          ]);
          $this->redirect('/admin/users?success=1');
        }
      } catch (\PDOException $e) {
        log_error('Database error in UserController::add', ['message' => $e->getMessage()]);
        $error = 'Помилка бази даних. Спробуйте пізніше.';
      } catch (\Exception $e) {
        log_error('Unexpected error in UserController::add', ['message' => $e->getMessage()]);
        $error = 'Неочікувана помилка. Спробуйте пізніше.';
      }
    }

    $this->render('admin/users/add.html.twig', [
      'error' => $error,
    ]);
  }

  /**
   * Форма редагування користувача
   */
  public function edit()
  {
    require_once __DIR__ . '/../../../includes/helpers.php';
    
    $error = '';
    $id = (int) $this->getQuery('id');

    if (!$id) {
      $this->redirect('/admin/users?error=' . urlencode('Користувача не знайдено'));
    }

    try {
      $user = $this->userModel->getById($id);

      if (!$user) {
        $this->redirect('/admin/users?error=' . urlencode('Користувача не знайдено'));
      }

      if ($this->isPost()) {
        $name = sanitize_input($this->getPost('name'));
        $email = sanitize_input($this->getPost('email'));
        $password = $this->getPost('password');
        $password_confirm = $this->getPost('password_confirm');
        $is_admin = (int) $this->getPost('is_admin', 0);

        if (!$name || !$email) {
          $error = "Ім'я та email обов'язкові";
        } elseif (!validate_email($email)) {
          $error = 'Невірний формат email';
        } elseif ($this->userModel->emailExists($email, $id)) {
          $error = 'Користувач з таким email вже існує';
        } elseif ($password && !validate_password($password)) {
          $error = 'Пароль має бути мінімум 6 символів';
        } elseif ($password && $password !== $password_confirm) {
          $error = 'Паролі не співпадають';
        } else {
          // Перевірка: якщо це останній адмін і намагаємося зняти права
          if ($user['is_admin'] == 1 && $is_admin == 0) {
            $adminCount = $this->userModel->getAdminCount();
            if ($adminCount <= 1) {
              $error = 'Не можна зняти права адміна з останнього адміністратора';
            }
          }

          if (!$error) {
            $updateData = [
              'name' => $name,
              'email' => $email,
              'is_admin' => $is_admin,
            ];

            if ($password) {
              $updateData['password'] = $password;
            }

            $this->userModel->update($id, $updateData);
            $this->redirect('/admin/users?success=1');
          }
        }
      }

      $this->render('admin/users/edit.html.twig', [
        'user' => $user,
        'error' => $error,
      ]);

    } catch (\PDOException $e) {
      log_error('Database error in UserController::edit', ['id' => $id, 'message' => $e->getMessage()]);
      $this->redirect('/admin/users?error=' . urlencode('Помилка бази даних'));
    } catch (\Exception $e) {
      log_error('Error in UserController::edit', ['message' => $e->getMessage()]);
      $this->redirect('/admin/users?error=' . urlencode($e->getMessage()));
    }
  }

  /**
   * Видалення користувача
   */
  public function delete()
  {
    require_once __DIR__ . '/../../../includes/helpers.php';
    
    $id = (int) $this->getQuery('id');

    if (!$id) {
      $this->redirect('/admin/users?error=' . urlencode('Користувача не знайдено'));
    }

    try {
      $user = $this->userModel->getById($id);

      if (!$user) {
        $this->redirect('/admin/users?error=' . urlencode('Користувача не знайдено'));
      }

      // Перевірка: не можна видалити останнього адміна
      if ($user['is_admin'] == 1) {
        $adminCount = $this->userModel->getAdminCount();
        if ($adminCount <= 1) {
          $this->redirect('/admin/users?error=' . urlencode('Не можна видалити останнього адміністратора'));
        }
      }

      // Перевірка: не можна видалити себе
      if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
        $this->redirect('/admin/users?error=' . urlencode('Не можна видалити свій власний акаунт'));
      }

      $this->userModel->delete($id);
      $this->redirect('/admin/users?success=1');

    } catch (\Exception $e) {
      log_error('Error deleting user', ['id' => $id, 'message' => $e->getMessage()]);
      $this->redirect('/admin/users?error=' . urlencode('Помилка при видаленні'));
    }
  }
}
