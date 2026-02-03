<?php 
  session_start();
?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $_SESSION["title"] ?? "Noosfera"; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-logo">Noosfera</div>
      <ul class="nav-menu">
        <li><a href="index.php">Головна</a></li>
        <li><a href="gallery.php">Проекти</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
          <li><a href="dashboard.php">Кабінет</a></li>
          <li><a href="logout.php">Вихід</a></li>
        <?php else: ?>
          <li><a href="auth.php" class="active">Вхід/Реєстрація</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
