<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $u = $_POST['username'] ?? '';
  $p = $_POST['password'] ?? '';
  $stmt = $db->prepare('SELECT * FROM admin WHERE username = :u');
  $stmt->execute([':u'=>$u]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row && password_verify($p, $row['password'])) {
    $_SESSION['admin_logged'] = true;
    header('Location: dashboard.php'); exit;
  } else {
    $err = 'Невірні дані';
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
<div class="container" style="max-width:420px">
  <h3>Вхід в адмінпанель</h3>
  <?php if($err): ?><div class="alert alert-danger"><?php echo $err; ?></div><?php endif; ?>
  <form method="post">
    <div class="mb-3"><input class="form-control" name="username" placeholder="Логін"></div>
    <div class="mb-3"><input class="form-control" name="password" type="password" placeholder="Пароль"></div>
    <button class="btn btn-primary">Увійти</button>
  </form>
</div>
</body>
</html>
