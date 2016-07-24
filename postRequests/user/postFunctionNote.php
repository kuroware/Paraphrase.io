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
$function_id = $data->function_id;
$note_text = $data->note_text;
/*$function_id = 86;
$note_text = "$comment->date_posted = date('M j, y');";*/
try {
	if (is_numeric($function_id) && ($note_text)) {
		//Sanitize user input
		$mysqli = Database::connection();
		$note_text = Database::sanitize($note_text);

		//Create new user
		$user_id = User::get_current_user_id();
		if ($user_id == 'None') {
			//Post under anonymous
			$user_id = 3;
		}

		$sql = "SELECT user_id, username, avatar, points FROM users WHERE user_id = '$user_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);

		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$user = new User($row);

		//Create the new function note to be posted
		$function = new LanguageFunction(array(
			'function_id' => $function_id)
		);
		$function_note = new FunctionNote(array(
			'note_text' => $note_text,
			'function' => $function)
		);

		//Post the note on the function
		$result = $user->post_function_note($function_note);
		if ($result) {
			$result->note_text = $data->note_text;
			echo json_encode($result, JSON_PRETTY_PRINT);
			http_response_code(200);
		}
		else {
			throw new RuntimeException('RuntimeException occured on request');
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
catch (RuntimeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}