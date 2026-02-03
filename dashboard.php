<?php
  if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
  }
?>

  <!-- Dashboard Section -->
  <section class="hero" style="min-height: 100vh; justify-content: flex-start; padding-top: 120px;">
    <div class="hero-overlay"></div>
    <div class="hero-content" style="text-align: left; max-width: 800px;">
      <h1 class="hero-title">Вітаємо, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
      <p class="hero-description">Це ваш особистий кабінет. Тут ви можете переглядати свої замовлення, налаштування та інше.</p>
      <p class="hero-description">Функціонал кабінету буде розширено в майбутньому.</p>
    </div>
  </section>

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
<? include 'includes/footer.php'; ?>