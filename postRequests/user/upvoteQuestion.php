<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
$question_id = $data->question_id;
/*$question_id = 10;*/
$user_id = User::get_current_user_id();
try {
	if (is_numeric($question_id) && is_numeric($user_id)) {
		$mysqli = Database::connection();
		$user = new User(array(
			'user_id' => $user_id)
		);
		$question = new Question(array(
			'question_id' => $question_id)
		);
		$result = $user->unupvote_upvote_question($question);
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