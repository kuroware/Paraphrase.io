<?php
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
$comment_id = $data->comment_id;
$user_id = User::get_current_user_id();
try {
	if (is_numeric($user_id) && is_numeric($comment_id)) {
		$mysqli = Database::connection();
		$sql = "SELECT author_id FROM translation_comments WHERE comment_id = '$comment_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		if ($result->num_rows == 1) {
			$author_id = mysqli_fetch_row($result)[0];
			if ($author_id == $user_id) {
				$sql = "DELETE FROM `translation_comments` WHERE comment_id = '$comment_id'";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
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
	http_response_code(400);
	Database::print_exception($e);
}
catch (OutOfRangeException $e) {
	http_response_code(400);
	Database::print_exception($e);
}
catch (OutOfBoundsException $e) {
	http_response_code(400);
	Database::print_exception($e);
}