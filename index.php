<?php
session_start();
?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Noosfera - Портфоліо</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>

  <!-- Navigation -->
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-logo">Noosfera</div>
      <ul class="nav-menu">
        <li><a href="#hero">Головна</a></li>
        <li><a href="#gallery">Проекти</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
          <li><a href="dashboard.php">Кабінет</a></li>
          <li><a href="logout.php">Вихід</a></li>
        <?php else: ?>
          <li><a href="auth.php">Вхід</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <!-- Hero Section -->
  <section id="hero" class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1 class="hero-title">Noosfera</h1>
      <p class="hero-subtitle">Мистецтво Української Міфології</p>
      <p class="hero-description">Дослідження давніх легенд через сучасне мистецтво</p>
    </div>
    <div class="scroll-down">
      <a href="#gallery" class="scroll-btn">
        <span>Переглянути проекти</span>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M12 5V19M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
    </div>
  </section>

  <!-- Gallery Section -->
  <section id="gallery" class="gallery-section">

    <!-- Artwork 1 - Alley -->
    <div class="artwork-container" data-aos="fade-up">
      <div class="artwork-image">
        <img src="assets/img/alley.jpg" alt="Alley" loading="lazy">
      </div>
      <div class="artwork-caption">
        <h2 class="artwork-title">Alley</h2>
        <p class="artwork-description">
          A mysterious alleyway shrouded in shadows, inviting exploration and wonder.
        </p>
      </div>
    </div>

    <!-- Artwork 2 - Flowers -->
    <div class="artwork-container" data-aos="fade-up">
      <div class="artwork-image">
        <img src="assets/img/flowers.jpg" alt="Flowers" loading="lazy">
      </div>
      <div class="artwork-caption">
        <h2 class="artwork-title">Flowers</h2>
        <p class="artwork-description">
          Vibrant blooms capturing the essence of nature's beauty and renewal.
        </p>
      </div>
    </div>

    <!-- Artwork 3 - Pink Sky -->
    <div class="artwork-container" data-aos="fade-up">
      <div class="artwork-image">
        <img src="assets/img/pink sky.png" alt="Pink Sky" loading="lazy">
      </div>
      <div class="artwork-caption">
        <h2 class="artwork-title">Pink Sky</h2>
        <p class="artwork-description">
          A serene pink sky evoking feelings of peace and tranquility at dusk.
        </p>
      </div>
    </div>

    <!-- Artwork 4 - Sunset -->
    <div class="artwork-container" data-aos="fade-up">
      <div class="artwork-image">
        <img src="assets/img/sunset.jpg" alt="Sunset" loading="lazy">
      </div>
      <div class="artwork-caption">
        <h2 class="artwork-title">Sunset</h2>
        <p class="artwork-description">
          A breathtaking sunset painting the horizon with warm, golden hues.
        </p>
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
  <script src="assets/js/script.js"></script>
</body>
</html>
