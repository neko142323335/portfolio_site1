<?php
 include 'includes/header.php';
require_once 'includes/db.php';
$_SESSION['title'] = 'Галерея - Noosfera';
$category = $_GET['category'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
  $user_stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
  $user_stmt->execute([':id' => $user_id]);
  $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
} else {
  $user = null;
}
if ($category) {
  $stmt = $db->prepare('SELECT * FROM works WHERE category = :cat ORDER BY created_at DESC');
  $stmt->execute([':cat'=>$category]);
} else {
  $stmt = $db->query('SELECT * FROM works ORDER BY created_at DESC');
}
$works = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
  // if(($user && $user["is_admin"]) || ($_SESSION['admin_logged'] ?? false)) {
    include 'admin/dashboard.php';
  // } 
?>
  <!-- Gallery Section -->
<section id="gallery" class="gallery-section">
    <!-- Artwork 1 - Alley -->
    <?php 
      foreach($works as $w) {
    ?>

    <div class="artwork-container" data-aos="fade-up">
      <div class="artwork-image">
        <img src="<?php echo htmlspecialchars($w['image']); ?>" alt="<?php echo htmlspecialchars($w['title']); ?>" loading="lazy">
      </div>
      <div class="artwork-caption">
        <h2 class="artwork-title"><?php echo htmlspecialchars($w['title']); ?></h2>
        <p class="artwork-description">
          <?php echo nl2br(htmlspecialchars($w['description'])); ?>
        </p>
      </div>
    </div>
    <?php
      }
    ?>
  </section>
<!-- <div class="container py-5">
  <h2>Портфоліо</h2>
  <div class="mb-3">
    <a href="gallery.php" class="btn btn-sm btn-light">Усі</a>
    <?php
    $cats = $db->query('SELECT DISTINCT category FROM works')->fetchAll(PDO::FETCH_COLUMN);
    foreach($cats as $c) {
      echo '<a href="gallery.php?category='.urlencode($c).'" class="btn btn-sm btn-outline-primary mx-1">'.htmlspecialchars($c).'</a>';
    }
    ?>
  </div>
  <div class="row">
    <?php foreach($works as $w): ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <img src="<?php echo htmlspecialchars($w['image']); ?>" class="card-img-top" alt="">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($w['title']); ?></h5>
            <p class="card-text"><?php echo nl2br(htmlspecialchars($w['description'])); ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if(count($works)===0): ?>
      <p>Робіт не знайдено.</p>
    <?php endif; ?>
  </div>
</div> -->
<?php include 'includes/footer.php'; ?>
