<?php
session_start();

unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['redirect_after_login']);

$_SESSION['message'] = "You have been logged out.";
header("Location: index.php");
exit();
?>