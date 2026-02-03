<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if (!($_SESSION['admin_logged'] ?? false)) {
  header('Location: login.php'); exit;
}
$works = $db->query('SELECT * FROM works ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h3>Адмінпанель</h3>
  <a class="btn btn-success mb-3" href="add_work.php">Додати роботу</a>
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Назва</th><th>Категорія</th><th>Дії</th></tr></thead>
    <tbody>
    <?php foreach($works as $w): ?>
      <tr>
        <td><?php echo $w['id']; ?></td>
        <td><?php echo htmlspecialchars($w['title']); ?></td>
        <td><?php echo htmlspecialchars($w['category']); ?></td>
        <td>
          <a class="btn btn-sm btn-primary" href="edit_work.php?id=<?php echo $w['id']; ?>">Редагувати</a>
          <a class="btn btn-sm btn-danger" href="delete_work.php?id=<?php echo $w['id']; ?>" onclick="return confirm('Видалити?')">Видалити</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
