<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
/*require_once '../../includes/user.php';*/
$github = $data->github;
$stackexchange = $data->stackexchange;
/*require_once '../../includes/Database.php';*/
/*$email = 'philiptsang018@gmail.com';*/
/*$description = 'wesf3h32';*/
$user_id = User::get_current_user_id();
try {
	if (is_numeric($user_id)) {
		$mysqli = Database::connection();
		$github = Database::sanitize($github);
		$se = Database::sanitize($stackexchange);

		$sql = "UPDATE users SET github = '$github', se = '$se' WHERE user_id = '$user_id' LIMIT 1";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}