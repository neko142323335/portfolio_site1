<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

$login_err = '';
$register_err = '';
$register_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
      $stmt = $db->prepare('SELECT * FROM users WHERE email = :e');
      $stmt->execute([':e' => $email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['admin_logged'] = $user['is_admin'];
        header('Location: index.php');
        exit;
      } else {
        $login_err = 'Невірний email або пароль';
      }
    } else {
      $login_err = 'Заповніть всі поля';
    }
  } elseif (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if ($name && $email && $password && $confirm) {
      if ($password === $confirm) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
          $stmt = $db->prepare('INSERT INTO users (name, email, password, created_at) VALUES (:n, :e, :p, :c)');
          $stmt->execute([':n' => $name, ':e' => $email, ':p' => $hashed, ':c' => date('Y-m-d H:i:s')]);
          $register_success = 'Реєстрація успішна! Тепер увійдіть.';
        } catch (PDOException $e) {
          if ($e->getCode() == 23000) {
            $register_err = 'Email вже використовується';
          } else {
            $register_err = 'Помилка реєстрації';
          }
        }
      } else {
        $register_err = 'Паролі не співпадають';
      }
    } else {
      $register_err = 'Заповніть всі поля';
    }
  }
}
?>

  <!-- Auth Section -->
  <section class="auth-section">
    <div class="auth-container">
      <h1 class="auth-title">Вхід або Реєстрація</h1>
      <p class="auth-subtitle text-secondary">Приєднуйтесь до спільноти дослідників української міфології</p>
      <div class="auth-tabs">
        <ul class="nav nav-tabs" id="authTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">Вхід</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">Реєстрація</button>
          </li>
        </ul>
        <div class="tab-content" id="authTabContent">
          <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
            <form method="post" class="auth-form" novalidate>
              <?php if($login_err): ?><div class="alert alert-danger"><?php echo $login_err; ?></div><?php endif; ?>
              <div class="mb-3">
                <label for="loginEmail" class="form-label">Email</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-envelope-fill"></i>
                  </span>
                  <input type="email" class="form-control text-white" id="loginEmail" name="email"
                    placeholder="Введіть ваш email" required aria-describedby="loginEmailHelp loginEmailError">
                </div>
                <div id="loginEmailError" class="validation-message error" role="alert" aria-live="polite"></div>
                <div class="form-help" id="loginEmailHelp">Введіть дійсну email адресу</div>
              </div>
              <div class="mb-3">
                <label for="loginPassword" class="form-label">Пароль
                </label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-lock-fill"></i>
                  </span>
                  <input type="password" class="form-control text-white" id="loginPassword" name="password" placeholder="Введіть ваш пароль" required aria-describedby="loginPasswordHelp loginPasswordError">
                  <button type="button" class="password-toggle" data-target="loginPassword" aria-label="Показати/приховати пароль">
                    <i class="bi bi-eye"></i>
                  </button>
                  </div>
                  <div id="loginPasswordError" class="validation-message error" role="alert" aria-live="polite"></div>
                <div class="form-help" id="loginPasswordHelp">Мінімум 6 символів</div>
              </div>
              <button type="submit" name="login" class="btn btn-primary">Увійти</button>
            </form>
          </div>
          <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
            <form method="post" class="auth-form" novalidate>
              <?php if($register_err): ?><div class="alert alert-danger"><?php echo $register_err; ?></div><?php endif; ?>
              <?php if($register_success): ?><div class="alert alert-success"><?php echo $register_success; ?></div><?php endif; ?>
              <div class="mb-3">
                <label for="registerName" class="form-label">Ім'я
                </label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-person-fill"></i>
                  </span>                  
                  <input type="text" class="form-control text-white" id="registerName" name="name" placeholder="Введіть ваше ім'я" required aria-describedby="registerNameHelp registerNameError">
                </div>
                <div id="registerNameError" class="validation-message error" role="alert" aria-live="polite"></div>
                <div class="form-help" id="registerNameHelp">Введіть ваше повне ім'я</div>
              </div>
              <div class="mb-3">
                <label for="registerEmail" class="form-label">Email
                </label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-envelope-fill"></i>
                  </span>
                  <input type="email" class="form-control text-white" id="registerEmail" name="email" placeholder="Введіть ваш email" required aria-describedby="registerEmailHelp registerEmailError">
                </div>
                <div id="registerEmailError" class="validation-message error" role="alert" aria-live="polite"></div>
                <div class="form-help" id="registerEmailHelp">Введіть дійсну email адресу</div>
              </div>
              <div class="mb-3">
                <label for="registerPassword" class="form-label">Пароль
                </label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-lock-fill"></i>
                  </span>
                  <input type="password" class="form-control text-white" id="registerPassword" name="password" placeholder="Створіть надійний пароль" required aria-describedby="registerPasswordHelp registerPasswordError">
                  <button type="button" class="password-toggle" data-target="registerPassword" aria-label="Показати/приховати пароль">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div id="registerPasswordError" class="validation-message error" role="alert" aria-live="polite"></div>
                <div class="password-strength">
                  <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <div class="form-help" id="registerPasswordHelp">Мінімум 8 символів, включаючи цифри та букви</div>
              </div>
              <div class="mb-3">
                <label for="registerConfirm" class="form-label">
                  Підтвердити пароль
                </label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-lock-fill"></i>
                  </span>
                  <input type="password" class="form-control text-white" id="registerConfirm" name="confirm" placeholder="Повторіть пароль" required aria-describedby="registerConfirmHelp registerConfirmError">
                  <button type="button" class="password-toggle" data-target="registerConfirm" aria-label="Показати/приховати пароль">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div id="registerConfirmError" class="validation-message error" role="alert" aria-live="polite"></div>
                <div class="form-help" id="registerConfirmHelp">Паролі повинні співпадати</div>
              </div>
              <button type="submit" name="register" class="btn btn-primary">Зареєструватися</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>
