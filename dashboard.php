<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth.php');
  exit;
}
?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Кабінет - Noosfera</title>
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
        <li><a href="index.php">Головна</a></li>
        <li><a href="index.php#gallery">Проекти</a></li>
        <li><a href="dashboard.php" class="active">Кабінет</a></li>
        <li><a href="logout.php">Вихід</a></li>
      </ul>
    </div>
  </nav>

  <!-- Dashboard Section -->
  <section class="hero" style="min-height: 100vh; justify-content: flex-start; padding-top: 120px;">
    <div class="hero-overlay"></div>
    <div class="hero-content" style="text-align: left; max-width: 800px;">
      <h1 class="hero-title">Вітаємо, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
      <p class="hero-description">Це ваш особистий кабінет. Тут ви можете переглядати свої замовлення, налаштування та інше.</p>
      <p class="hero-description">Функціонал кабінету буде розширено в майбутньому.</p>
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
    AOS.init({
      duration: 1000,
      easing: 'ease-in-out',
      once: true,
      offset: 100
    });
  </script>
</body>
</html>
