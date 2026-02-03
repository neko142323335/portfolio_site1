<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if (!($_SESSION['admin_logged'] ?? false)) { header('Location: login.php'); exit; }
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: dashboard.php'); exit; }
$stmt = $db->prepare('SELECT * FROM works WHERE id = :id'); $stmt->execute([':id'=>$id]); $w = $stmt->fetch(PDO::FETCH_ASSOC);
$err=''; if($_SERVER['REQUEST_METHOD']==='POST'){
  $title = $_POST['title'] ?? ''; $desc = $_POST['description'] ?? ''; $cat = $_POST['category'] ?? '';
  if (!$title) $err='Назва обов'язкова';
  if (!$err) {
    $imgpath = $w['image'];
    if (!empty($_FILES['image']['tmp_name'])) {
      $up = __DIR__ . '/../assets/img/works/';
      $fname = time().'_'.basename($_FILES['image']['name']);
      move_uploaded_file($_FILES['image']['tmp_name'], $up.$fname);
      $imgpath = 'assets/img/works/'.$fname;
    }
    $stmt = $db->prepare('UPDATE works SET title=:t,description=:d,image=:i,category=:c WHERE id=:id');
    $stmt->execute([':t'=>$title,':d'=>$desc,':i'=>$imgpath,':c'=>$cat,':id'=>$id]);
    header('Location: dashboard.php'); exit;
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Edit</title><link href="/assets/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-4"><div class="container" style="max-width:720px">
<h3>Редагувати роботу</h3>
<?php if($err): ?><div class="alert alert-danger"><?php echo $err; ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <div class="mb-3"><input class="form-control" name="title" value="<?php echo htmlspecialchars($w['title']); ?>"></div>
  <div class="mb-3"><input class="form-control" name="category" value="<?php echo htmlspecialchars($w['category']); ?>"></div>
  <div class="mb-3"><textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($w['description']); ?></textarea></div>
  <div class="mb-3"><input type="file" name="image" class="form-control"></div>
  <button class="btn btn-primary">Зберегти</button>
  <a class="btn btn-secondary" href="dashboard.php">Назад</a>
</form>
</div></body></html>
