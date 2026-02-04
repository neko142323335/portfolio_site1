<?php 
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// Determine base path - check if we're in admin section
$is_admin_page = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$base = $is_admin_page ? '../' : '';
?>
<!doctype html>
<html lang="<?php echo SITE_LANG; ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($_SESSION["title"] ?? SITE_NAME); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-logo"><?php echo SITE_NAME; ?></div>
      <ul class="nav-menu">
        <li><a href="<?php echo $base; ?>index.php" <?php echo is_current_page('index') ? 'class="active"' : ''; ?>>Головна</a></li>
        <li><a href="<?php echo $base; ?>gallery.php" <?php echo is_current_page('gallery') ? 'class="active"' : ''; ?>>Проекти</a></li>
        <li><a href="<?php echo $base; ?>contact.php" <?php echo is_current_page('contact') ? 'class="active"' : ''; ?>>Контакти</a></li>
        <?php if (is_admin()): ?>
          <li><a href="<?php echo $base; ?>admin/dashboard.php" class="btn btn-sm btn-primary ms-2"><i class="bi bi-gear"></i> Адмін</a></li>
          <li><a href="<?php echo $base; ?>logout.php" class="btn btn-sm btn-outline-danger ms-2">Вихід</a></li>
        <?php elseif (isset($_SESSION['user_id'])): ?>
          <li><a href="<?php echo $base; ?>dashboard.php" <?php echo is_current_page('dashboard') ? 'class="active"' : ''; ?>>Кабінет</a></li>
          <li><a href="<?php echo $base; ?>logout.php" class="btn btn-sm btn-outline-danger ms-2">Вихід</a></li>
        <?php else: ?>
          <li><a href="<?php echo $base; ?>auth.php" <?php echo is_current_page('auth') ? 'class="active"' : ''; ?>>Вхід/Реєстрація</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <?php if (isset($_GET['logged_out']) && $_GET['logged_out'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 0;">
      <i class="bi bi-check-circle"></i> Ви успішно вийшли з облікового запису.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
