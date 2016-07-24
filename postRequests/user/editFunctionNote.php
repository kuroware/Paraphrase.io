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
$note_id = $data->note_id;
$note_text = $data->note_text;
try {
	if (is_numeric($note_id)) {
		$user = User::get_current_user();

		$note_text = Database::sanitize($note_text);
		$function_note = new FunctionNote(array(
			'note_id' => $note_id,
			'note_text' => $note_text)
		);

		$result = $user->edit_function_note($function_note);
		if ($result) {
			http_response_code(200);
		}
		else {
			throw new OutOfBoundsException('OutOfBoundsException occured on request');
		}
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
catch (OutOfRangeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}