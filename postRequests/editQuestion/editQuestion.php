<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$question_id = $data->question_id;
$title = $data->title;
$body = $data->body;
$user_id = User::get_current_user_id();
try {
	if (is_numeric($question_id) && is_numeric($user_id)) {
		$question = new Question(array(
			'question_id' => $question_id)
		);
		$mysqli = Database::connection();
		$sql = "SELECT author_id FROM questions WHERE question_id = '$question->question_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$author_id = mysqli_fetch_row($result)[0];
		if ($author_id == $user_id) {
			//Allow the edit
			list($title, $body) = Database::sanitzie(array($title, $body));
			$sql = "UPDATE questions SET title = '$title' AND body = '$body' WHERE question_id = '$question->question_id' LIMIT 1";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			//Log the edit
			$user = new User(array(
				'user_id' => $user_id)
			);
			$result = $question->log_edit($user);
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