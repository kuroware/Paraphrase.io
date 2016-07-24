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
/*$function_id = 4604;*/
try {
	if (is_numeric($function_id)) {
		$mysqli = Database::connection();
		$user_id = User::get_current_user_id();
		if (is_numeric($user_id)) {
			$sql = "SELECT t1.note_id, t1.note_text, t1.author_id as `user_id`, t2.username as `username`, DATE_FORMAT(t1.date_posted, '%b %e, %Y') as `date_posted`, t1.upvotes, t1.downvotes, t3.status, t2.avatar, t2.points
			FROM `function_notes` as t1 
			LEFT JOIN `users` as t2 
			ON t2.user_id = t1.author_id
			LEFT JOIN `function_note_feedback` as t3 
			ON t3.note_id = t1.note_id
			AND t3.user_id = '$user_id'
			WHERE t1.function_id = '$function_id'
			ORDER BY (t1.upvotes - t1.downvotes) DESC";
		}
		else {
			$sql = "SELECT t1.note_id, t1.note_text, t1.author_id as `user_id`, t2.username as `username`, DATE_FORMAT(t1.date_posted, '%b %e, %Y') as `date_posted`, t1.upvotes, t1.downvotes, null as `status`, t2.avatar, t2.points
			FROM `function_notes` as t1 
			LEFT JOIN `users` as t2 
			ON t2.user_id = t1.author_id
			LEFT JOIN `function_note_feedback` as t3 
			ON t3.note_id = t1.note_id
			AND t3.user_id = '$user_id'
			WHERE t1.function_id = '$function_id'
			ORDER BY (t1.upvotes - t1.downvotes) DESC";
		}
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$function_notes = array();
		$searched_function = new LanguageFunction(array(
			'function_id' => $function_id)
		);
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$row['author'] = new User(array(
				'user_id' => $row['user_id'],
				'username' => $row['username'],
				'avatar' => $row['avatar'],
				'points' => $row['points'])
			);
			$row['function'] = $searched_function;
			$note = new FunctionNote($row);
			$function_notes[] = $note;
		}
		echo json_encode($function_notes, JSON_PRETTY_PRINT);
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
?>