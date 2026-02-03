<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if (!($_SESSION['admin_logged'] ?? false)) { header('Location: login.php'); exit; }
$id = $_GET['id'] ?? null;
if ($id) {
  $stmt = $db->prepare('DELETE FROM works WHERE id = :id');
  $stmt->execute([':id'=>$id]);
}
header('Location: dashboard.php'); exit;
