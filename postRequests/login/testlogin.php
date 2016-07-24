<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

session_start();
error_reporting(0);
header("Content-Type: application/json"); //Set header for outputing the JSON information
/*require_once '../../includes/database.php';
require_once '../../includes/user.php';*/
$return_val = array();
$return_val['session'] = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : null;
$return_val['token'] = (isset($_COOKIE['token'])) ? $_COOKIE['token'] : null;
$return_val['cookie'] = (isset($_COOKIE['user_id'])) ? $_COOKIE['user_id'] : null;
echo json_encode($return_val, JSON_PRETTY_PRINT);
?>