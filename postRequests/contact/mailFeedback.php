<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation') || ($class_name == 'TranslateFactory')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}	
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$comment = $data->comment;
$reason = $data->reason;
$developer = $data->developer;
$moderator = $data->moderator;
$user_id = (is_numeric(User::get_current_user_id())) ? User::get_current_user_id() : 3;
/*
$developer = true;
$moderator = false;
$reason = 'Bug';
$comment = 'hey thereeeeeee';
*/
try {
	if (is_numeric($user_id)) {
		$to = 'philiptsang018@gmail.com';
		$subject = 'Paraphrase';
		$message = "
		From user_id: $user_id,
		<br/>
		Reason: $reason
		<br/>
		Additional: Developer is $developer, moderator is $moderator
		<br/>
		Comment: <br/>
		$comment";
		$headers = "Content-Type: text/html; charset=ISO-8859-1\r\n";
		$database = new Database();
		$rows = $database->log_feedback();
		if ($rows == 1) {
		//	echo 'hi';
			//Rate limt approved, send
			http_response_code(200);
			mail($to, $subject, $message, $headers);
		}
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	http_response_code(400);
	Database::print_exception($e);
}