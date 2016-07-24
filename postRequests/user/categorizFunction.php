<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation') || ($class_name == 'TranslateFactory')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	elseif (strpos($class_name, 'Edit')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Edit.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}	
}
$request = file_get_contents('php://input');
$data = json_encode($request);
$function_id = $data->function_id;
$category_id = $data->category_id;
$user_id = User::get_current_user_id();
try {
	if (is_numeric($category_id) && is_numeric($function_id) && is_numeric($user_id)) {
		//By default, super category
		$mysqli = Database::connection();
		$sql = "INSERT INTO `categorization_project` (function_id, category_id, user_id) VALUES ('$function_id', '$category_id', '$user_id')
		ON DUPLICATE KEY UPDATE category_id = '$category_id'";
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
	http_response_code(400):
}