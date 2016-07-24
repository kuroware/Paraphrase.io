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
$profile_id = $data->profile_id;
$comment_text = $data->comment_text;
/*$comment_text = '12321hsihsa';
$profile_id = 2;*/
$user_id = User::get_current_user_id();
/*$user_id = 2;*/
/*echo $user_id;
echo $comment_text;
echo $profile_id;*/
try {
	if (is_numeric($user_id) && is_numeric($profile_id) && ($comment_text)) {
		$comment_text = Database::sanitize($comment_text);
		$comment = new ProfileComment(array(
			'comment_text' => $comment_text)
		);
		$profile = new User(array(
			'user_id' => $profile_id)
		);
/*		$user = new User(array(
			'user_id' => $user_id)
		);*/

		//Get the user name and avatar
		$mysqli = Database::connection();
		$sql = "SELECT user_id, username, avatar FROM users WHERE user_id = '$user_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$user = new User($row);

		$result = $user->post_comment_on_user_profile($profile, $comment);
		if ($result) {
			$result->comment_text = $data->comment_text;
			echo json_encode($result, JSON_PRETTY_PRINT);
			http_response_code(200);
		}
		else {
			throw new OutOfBoundsException('OutOfBoundsException occured');
		}
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured');
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