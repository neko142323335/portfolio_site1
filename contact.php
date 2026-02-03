<?php
require_once 'includes/db.php';
$sent = false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = $_POST['name'] ?? '';
  $email = $_POST['email'] ?? '';
  $message = $_POST['message'] ?? '';
  // Simple validation
  if ($name && $email && $message) {
    // Here you could send mail or save to DB. We'll save to file for demo.
    $log = sprintf("[%s] %s <%s>\n%s\n\n", date('Y-m-d H:i:s'), $name, $email, $message);
    file_put_contents('contacts.log', $log, FILE_APPEND);
    $sent = true;
  }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container py-5">
  <h2>Контакти</h2>
  <?php if($sent): ?>
    <div class="alert alert-success">Повідомлення надіслано. Дякую!</div>
  <?php endif; ?>
  <form method="post" action="contact.php">
    <div class="mb-3">
      <label class="form-label">Ім'я</label>
      <input class="form-control" name="name" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input class="form-control" name="email" type="email" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Повідомлення</label>
      <textarea class="form-control" name="message" rows="5" required></textarea>
    </div>
    <button class="btn btn-primary">Надіслати</button>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
