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

  <!-- Back to Top Button -->
  <button id="backToTop" class="back-to-top" title="На вершину">
    <i class="bi bi-arrow-up"></i>
  </button>

  <!-- Scripts -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
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

    // Parallax effect for hero
    window.addEventListener('scroll', () => {
      const scrolled = window.pageYOffset;
      const hero = document.querySelector('.hero');
      if (hero) {
        hero.style.transform = `translateY(${scrolled * 0.5}px)`;
      }
    });
  </script>
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
      const backToTopBtn = document.getElementById('backToTop');
      
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
        backToTopBtn.classList.add('show');
      } else {
        navbar.classList.remove('scrolled');
        backToTopBtn.classList.remove('show');
      }
    });

    // Back to Top button functionality
    const backToTopBtn = document.getElementById('backToTop');
    if (backToTopBtn) {
      backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    }

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
    if(document.getElementById('registerPassword')) {
      document.getElementById('registerPassword').addEventListener('input', updatePasswordStrength);
    }

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