<?php
namespace App\Controllers;

use App\Models\User;

/**
 * Контролер авторизації
 */
class AuthController extends BaseController
{
  public function login(): void
  {
    // Якщо вже залогінений - редірект
    if ($this->isLoggedIn()) {
      $this->redirect('/');
    }

    $login_error = '';
    $register_error = '';
    $register_success = '';

    if ($this->isPost()) {
      require_once __DIR__ . '/../../includes/helpers.php';
      $userModel = new User($this->db);

      try {
        if ($this->getPost('login')) {
          $this->handleLogin($userModel, $login_error);
        } elseif ($this->getPost('register')) {
          $this->handleRegister($userModel, $register_error, $register_success);
        }
      } catch (\Exception $e) {
        log_error('Error in AuthController', ['message' => $e->getMessage()]);
        $login_error = ERROR_MESSAGES['general_error'];
      }
    }

    $this->render('auth.html.twig', [
      'login_error' => $login_error,
      'register_error' => $register_error,
      'register_success' => $register_success,
      'logged_out' => 0,
    ]);
  }

  private function handleLogin(User $userModel, string &$login_error): void
  {
    require_once __DIR__ . '/../../includes/helpers.php';
    
    $email = sanitize_input($this->getPost('email', ''));
    $password = $this->getPost('password', '');

    if (!$email || !$password) {
      $login_error = ERROR_MESSAGES['required_fields'];
      return;
    }

    if (!validate_email($email)) {
      $login_error = ERROR_MESSAGES['invalid_email'];
      return;
    }

    $user = $userModel->findByEmail($email);

    if ($user && $userModel->verifyPassword($user, $password)) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['name'];
      $_SESSION['admin_logged'] = !empty($user['is_admin']) ? true : false;
      $this->redirect('/');
    } else {
      $login_error = ERROR_MESSAGES['auth_failed'];
      log_error('Failed login attempt', ['email' => $email]);
    }
  }

  private function handleRegister(User $userModel, string &$register_error, string &$register_success): void
  {
    require_once __DIR__ . '/../../includes/helpers.php';
    
    $name = sanitize_input($this->getPost('name', ''));
    $email = sanitize_input($this->getPost('email', ''));
    $password = $this->getPost('password', '');
    $confirm = $this->getPost('confirm', '');

    if (!$name || !$email || !$password || !$confirm) {
      $register_error = ERROR_MESSAGES['required_fields'];
      return;
    }

    if (!validate_email($email)) {
      $register_error = ERROR_MESSAGES['invalid_email'];
      return;
    }

    if (!validate_password($password)) {
      $register_error = ERROR_MESSAGES['weak_password'];
      return;
    }

    if ($password !== $confirm) {
      $register_error = ERROR_MESSAGES['password_mismatch'];
      return;
    }

    $result = $userModel->create([
      'name' => $name,
      'email' => $email,
      'password' => $password,
    ]);

    if ($result) {
      $register_success = 'Реєстрація успішна! Тепер ви можете увійти.';
    } else {
      $register_error = ERROR_MESSAGES['email_exists'];
    }
  }

  public function logout(): void
  {
    session_destroy();
    $this->redirect('/?logged_out=1');
  }
}
