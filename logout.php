<?php
//Initate destruction of user variables
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
unset($_SESSION['user_id']);
$_SESSION['user_id'] = null;
session_destroy(); //Destory session

unset($_COOKIE['token']);
unset($_COOKIE['user_id']);

$_COOKIE['token'] = null;
$_COOKIE['user_id'] = null;

setcookie('user_id', null, time() - 3600, "/");
setcookie('token', null, time() - 3600, "/");

header('Location:index.php');
?>