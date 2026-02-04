<?php
session_start();

// Perform logout
$_SESSION = [];
session_destroy();

// Redirect to home with success message
header('Location: index.php?logged_out=1');
exit;
?>
