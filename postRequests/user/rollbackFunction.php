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
$data = json_decode($request);
$rollback_id = $data->rollback_id;
$user_id = User::get_current_user_id();
try {
	if (is_numeric($rollback_id) && (is_numeric($user_id))) {
		$user = new User(array(
			'user_id' => $user_id)
		);
		$edit = new FunctionEdit(array(
			'edit_id' => $rollback_id)
		);
		$result = $user->rollback_edit($edit);
		if ($result) {
			http_response_code(200);
		}
		else {
			throw new OutOfBoundsException('OutOfBoundsException occured on request');
		}
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
catch (OutOfBoundsException $e) {
	Database::print_exception($e);
	http_response_code(400);
}