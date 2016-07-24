<?php
session_start();
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
$translation_id = $data->translation_id;
/*$translation_id = 4;*/
/*require_once '../../includes/Database.php';*/
try {
	if (is_numeric($translation_id)) {
/*		require_once '../../includes/Translate.php';
		require_once '../../includes/User.php';
*/
		$user_id = User::get_current_user_id();
		if (is_numeric($user_id)) {
			$user = new User(array(
				'user_id' => $user_id)
			);
			$translation = new OutgoingTranslation(array(
				'translation_id' => $translation_id)
			);
			$result = $user->downvote_translation($translation);
			if ($result) {
				http_response_code(200);
			}
			else {
				throw new OutOfBoundsException('OutOfBoundsException occured on request');
			}
		}
		else {
			throw new OutOfRangeException('OutOfRangeException occured on request');
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
catch (OutOfRangeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}