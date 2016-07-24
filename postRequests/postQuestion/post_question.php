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
$title = $data->title;
$body = $data->body;
$source_language = $data->src;
$destination_language = $data->des;
$tagged_language = $data->tagged_language;
/*$title = 'sadsdsad';
$body = 'asdsddsadsadsad';
$source_language = 1;
$destination_language = 3;*/
$user_id = User::get_current_user_id();	

try {
	if (is_numeric($user_id) && is_numeric($source_language) && (is_numeric($destination_language) && ($source_language > 0) && ($source_language <= 10) && ($destination_language > 0) && ($destination_language <= 10)) XOR (is_numeric($tagged_language))) {
		$mysqli = Database::connection();
		list($title, $body) = Database::sanitize(array($title, $body)); //Sanitize inputs

		if ($tagged_language) {
			$sql = "INSERT INTO questions (title, body, date_posted, author_id, tagged_language) VALUES ('$title', '$body', NOW(), '$user_id', '$tagged_language')";
		}
		else {
			$sql = "INSERT INTO questions (title, body, date_posted, author_id, src_language, des_language) VALUES ('$title', '$body', NOW(), '$user_id', '$source_language', '$destination_language')";
		}
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$id = $mysqli->insert_id;
		$question = new Question(array(
			'question_id' => $id)
		);
		echo json_encode($question, JSON_PRETTY_PRINT);
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
?>