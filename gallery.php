<?php
require_once 'includes/db.php';
$category = $_GET['category'] ?? null;
if ($category) {
  $stmt = $db->prepare('SELECT * FROM works WHERE category = :cat ORDER BY created_at DESC');
  $stmt->execute([':cat'=>$category]);
} else {
  $stmt = $db->query('SELECT * FROM works ORDER BY created_at DESC');
}
$works = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<div class="container py-5">
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
</div>
<?php include 'includes/footer.php'; ?>
