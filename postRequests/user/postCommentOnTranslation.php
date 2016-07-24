<?php
session_start();
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	elseif (strpos($class_name, 'Comment')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Comment.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$comment_text = $data->comment_text;
$result_id = $data->result_id;
/*$comment_text = 'asdsadsad';
$result_id = 1;*/
$user_id = User::get_current_user_id();
try {
	if (is_numeric($user_id) && (is_numeric($result_id))) {
		//Sanitize the input first
		list($comment_text, $result_id) = Database::sanitize(array($comment_text, $result_id));
		$comment = new TranslationComment(array(
			'comment_text' => $comment_text)
		);
		$user = new User(array(
			'user_id' => $user_id)
		);
		$translation = new OutgoingTranslation(array(
			'result_id' => $result_id)
		);
		$result = $user->post_comment_on_translation($translation, $comment);
		if ($result) {
			//Construct the newly posted comment
			$user = new User(array(
				'user_id' => $user_id,
				'username' => $username)
			);
			$user->get_fields();
			$comment = new TranslationComment(array(
				'comment_id' => $result,
				'comment_text' => $data->comment_text,
				'author' => $user,
				'date_posted' => date('M j, Y'))
			);
			http_response_code(200);
			echo json_encode($comment, JSON_PRETTY_PRINT);
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
	http_response_code(400);
	Database::print_exception($e);
}
catch (OutOfBoundsException $e) {
	http_response_code(400);
	Database::print_exception($e);
}