<?php
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
$edit_text = $data->edit_text;
$comment_id = $data->comment_id;
/*$comment_id = 5;
$edit_text = 'lolololoolollololollolololo';*/
$user_id = User::get_current_user_id();
//echo $user_id;
try {
	 if(is_numeric($comment_id) && $edit_text && is_numeric($user_id)) {
	 	$mysqli = Database::connection();
	 	$edit_text = Database::sanitize($edit_text);
	 	$sql = "SELECT author_id FROM profile_comments WHERE comment_id = '$comment_id'";
	 	$result = $mysqli->query($sql)
	 	or die ($mysqli->error);
	 	$author_id = mysqli_fetch_row($result)[0];
	 	if ($author_id == $user_id) {
	 		//Correct owner
	 		$sql = "UPDATE profile_comments SET comment_text = '$edit_text' WHERE comment_id = '$comment_id' LIMIT 1";
	 		$result = $mysqli->query($sql)
	 		or die ($mysqli->error);
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