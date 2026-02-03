<?php
session_start();
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
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Вхід / Реєстрація - Noosfera</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>

  <!-- Navigation -->
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-logo">Noosfera</div>
      <ul class="nav-menu">
        <li><a href="index.php">Головна</a></li>
        <li><a href="index.php#gallery">Проекти</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
          <li><a href="dashboard.php">Кабінет</a></li>
          <li><a href="logout.php">Вихід</a></li>
        <?php else: ?>
          <li><a href="auth.php" class="active">Вхід</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <!-- Auth Section -->
  <section class="auth-section">
    <div class="auth-container">
      <h1 class="auth-title">Вхід або Реєстрація</h1>
      <p class="auth-subtitle">Приєднуйтесь до спільноти дослідників української міфології</p>
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
                <label for="loginEmail" class="form-label">
                  <i class="bi bi-envelope-fill me-2"></i>Email
                </label>
                <div class="input-group">
                  <i class="bi bi-envelope input-icon"></i>
                  <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Введіть ваш email" required aria-describedby="loginEmailHelp loginEmailError">
                  <div id="loginEmailError" class="validation-message error" role="alert" aria-live="polite"></div>
                </div>
                <div class="form-help" id="loginEmailHelp">Введіть дійсну email адресу</div>
              </div>
              <div class="mb-3">
                <label for="loginPassword" class="form-label">
                  <i class="bi bi-lock-fill me-2"></i>Пароль
                </label>
                <div class="input-group">
                  <i class="bi bi-lock input-icon"></i>
                  <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Введіть ваш пароль" required aria-describedby="loginPasswordHelp loginPasswordError">
                  <button type="button" class="password-toggle" data-target="loginPassword" aria-label="Показати/приховати пароль">
                    <i class="bi bi-eye"></i>
                  </button>
                  <div id="loginPasswordError" class="validation-message error" role="alert" aria-live="polite"></div>
                </div>
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
                <label for="registerName" class="form-label">
                  <i class="bi bi-person-fill me-2"></i>Ім'я
                </label>
                <div class="input-group">
                  <i class="bi bi-person input-icon"></i>
                  <input type="text" class="form-control" id="registerName" name="name" placeholder="Введіть ваше ім'я" required aria-describedby="registerNameHelp registerNameError">
                  <div id="registerNameError" class="validation-message error" role="alert" aria-live="polite"></div>
                </div>
                <div class="form-help" id="registerNameHelp">Введіть ваше повне ім'я</div>
              </div>
              <div class="mb-3">
                <label for="registerEmail" class="form-label">
                  <i class="bi bi-envelope-fill me-2"></i>Email
                </label>
                <div class="input-group">
                  <i class="bi bi-envelope input-icon"></i>
                  <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Введіть ваш email" required aria-describedby="registerEmailHelp registerEmailError">
                  <div id="registerEmailError" class="validation-message error" role="alert" aria-live="polite"></div>
                </div>
                <div class="form-help" id="registerEmailHelp">Введіть дійсну email адресу</div>
              </div>
              <div class="mb-3">
                <label for="registerPassword" class="form-label">
                  <i class="bi bi-lock-fill me-2"></i>Пароль
                </label>
                <div class="input-group">
                  <i class="bi bi-lock input-icon"></i>
                  <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Створіть надійний пароль" required aria-describedby="registerPasswordHelp registerPasswordError">
                  <button type="button" class="password-toggle" data-target="registerPassword" aria-label="Показати/приховати пароль">
                    <i class="bi bi-eye"></i>
                  </button>
                  <div id="registerPasswordError" class="validation-message error" role="alert" aria-live="polite"></div>
                </div>
                <div class="password-strength">
                  <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <div class="form-help" id="registerPasswordHelp">Мінімум 8 символів, включаючи цифри та букви</div>
              </div>
              <div class="mb-3">
                <label for="registerConfirm" class="form-label">
                  <i class="bi bi-lock-fill me-2"></i>Підтвердити пароль
                </label>
                <div class="input-group">
                  <i class="bi bi-lock input-icon"></i>
                  <input type="password" class="form-control" id="registerConfirm" name="confirm" placeholder="Повторіть пароль" required aria-describedby="registerConfirmHelp registerConfirmError">
                  <button type="button" class="password-toggle" data-target="registerConfirm" aria-label="Показати/приховати пароль">
                    <i class="bi bi-eye"></i>
                  </button>
                  <div id="registerConfirmError" class="validation-message error" role="alert" aria-live="polite"></div>
                </div>
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
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>Noosfera</h3>
          <p>Дослідження української міфології через сучасне мистецтво.
             Кожна робота розповідає історію давніх легенд та переказів.</p>
        </div>
        <div class="footer-section">
          <h3>Контакти</h3>
          <p>Email: contact@noosfera.art</p>
          <p>Соціальні мережі:
            <a href="#" target="_blank">Instagram</a>,
            <a href="#" target="_blank">Facebook</a>
          </p>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2024 Noosfera. Всі права захищені.</p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Initialize AOS
    AOS.init({
      duration: 1000,
      easing: 'ease-in-out',
      once: true,
      offset: 100
    });

    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
      const navbar = document.querySelector('.navbar');
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });

    // Auth enhancements
    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(button => {
      button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');

        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('bi-eye');
          icon.classList.add('bi-eye-slash');
          this.setAttribute('aria-label', 'Приховати пароль');
        } else {
          input.type = 'password';
          icon.classList.remove('bi-eye-slash');
          icon.classList.add('bi-eye');
          this.setAttribute('aria-label', 'Показати пароль');
        }
      });
    });

    // Password strength indicator
    function checkPasswordStrength(password) {
      let strength = 0;
      if (password.length >= 8) strength++;
      if (/[a-z]/.test(password)) strength++;
      if (/[A-Z]/.test(password)) strength++;
      if (/[0-9]/.test(password)) strength++;
      if (/[^A-Za-z0-9]/.test(password)) strength++;
      return strength;
    }

    function updatePasswordStrength() {
      const password = document.getElementById('registerPassword').value;
      const strength = checkPasswordStrength(password);
      const bar = document.getElementById('passwordStrengthBar');
      bar.className = 'password-strength-bar';

      if (strength === 0) {
        bar.style.width = '0%';
      } else if (strength <= 2) {
        bar.style.width = '33%';
        bar.classList.add('password-strength-weak');
      } else if (strength <= 4) {
        bar.style.width = '66%';
        bar.classList.add('password-strength-medium');
      } else {
        bar.style.width = '100%';
        bar.classList.add('password-strength-strong');
      }
    }

    document.getElementById('registerPassword').addEventListener('input', updatePasswordStrength);

    // Validation functions
    function showValidationMessage(elementId, message, isError = true) {
      const errorDiv = document.getElementById(elementId + 'Error');
      errorDiv.textContent = message;
      errorDiv.className = `validation-message ${isError ? 'error' : 'success'} show`;
    }

    function hideValidationMessage(elementId) {
      const errorDiv = document.getElementById(elementId + 'Error');
      errorDiv.className = 'validation-message';
    }

    function validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    }

    function validateField(fieldId, value) {
      switch (fieldId) {
        case 'loginEmail':
        case 'registerEmail':
          if (!value) {
            showValidationMessage(fieldId, 'Email обов\'язковий');
            return false;
          } else if (!validateEmail(value)) {
            showValidationMessage(fieldId, 'Невірний формат email');
            return false;
          } else {
            hideValidationMessage(fieldId);
            return true;
          }
        case 'loginPassword':
          if (!value) {
            showValidationMessage(fieldId, 'Пароль обов\'язковий');
            return false;
          } else if (value.length < 6) {
            showValidationMessage(fieldId, 'Пароль повинен містити мінімум 6 символів');
            return false;
          } else {
            hideValidationMessage(fieldId);
            return true;
          }
        case 'registerName':
          if (!value) {
            showValidationMessage(fieldId, 'Ім\'я обов\'язкове');
            return false;
          } else if (value.length < 2) {
            showValidationMessage(fieldId, 'Ім\'я занадто коротке');
            return false;
          } else {
            hideValidationMessage(fieldId);
            return true;
          }
        case 'registerPassword':
          if (!value) {
            showValidationMessage(fieldId, 'Пароль обов\'язковий');
            return false;
          } else if (value.length < 8) {
            showValidationMessage(fieldId, 'Пароль повинен містити мінімум 8 символів');
            return false;
          } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
            showValidationMessage(fieldId, 'Пароль повинен містити великі та малі букви, цифри');
            return false;
          } else {
            hideValidationMessage(fieldId);
            return true;
          }
        case 'registerConfirm':
          const password = document.getElementById('registerPassword').value;
          if (!value) {
            showValidationMessage(fieldId, 'Підтвердження обов\'язкове');
            return false;
          } else if (value !== password) {
            showValidationMessage(fieldId, 'Паролі не співпадають');
            return false;
          } else {
            hideValidationMessage(fieldId);
            return true;
          }
        default:
          return true;
      }
    }

    // Add real-time validation
    const fields = ['loginEmail', 'loginPassword', 'registerName', 'registerEmail', 'registerPassword', 'registerConfirm'];
    fields.forEach(fieldId => {
      const element = document.getElementById(fieldId);
      if (element) {
        element.addEventListener('input', function() {
          validateField(fieldId, this.value);
        });
        element.addEventListener('blur', function() {
          validateField(fieldId, this.value);
        });
      }
    });

    // Form submission validation
    document.querySelectorAll('.auth-form').forEach(form => {
      form.addEventListener('submit', function(e) {
        let isValid = true;
        const formFields = this.querySelectorAll('input[required]');
        formFields.forEach(input => {
          if (!validateField(input.id, input.value)) {
            isValid = false;
            input.classList.add('input-error');
            setTimeout(() => input.classList.remove('input-error'), 500);
          }
        });
        if (!isValid) {
          e.preventDefault();
        }
      });
    });
  </script>
</body>
</html>
