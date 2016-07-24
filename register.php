<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
$user_id = User::get_current_user_id();
if (is_numeric($user_id)) {
	///The user is already logged in
	header('Location: http://paraphrase.io');
}
?>
<html ng-app="app">
	<head>
		<link>
	</head>
</html>