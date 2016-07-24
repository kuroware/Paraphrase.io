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
$language_id = $data->language_id;
//$language_id = 11;
try {
	if (is_numeric($language_id)) {
		$mysqli = Database::connection();
		if ($language_id == 11) {
			$sql = "SELECT t1.note_text, t1.author_id as `user_id`, t2.function_id, t2.function_name, DATE_FORMAT(t1.date_posted, '') as `date`, t3.username, t3.avatar, t3.points, t4.language_id as `language_id`, t4.language_name as `language_name`, t5.counter
			FROM function_notes as t1
			INNER JOIN functions as t2 
			ON t2.function_id = t1.function_id
			INNER JOIN (
				SELECT t1.function_id, COUNT(t1.note_id) as `counter`
				FROM function_notes as t1
				GROUP BY t1.function_id
			) as t5
			ON t5.function_id = t2.function_id
			LEFT JOIN users as t3 
			ON t3.user_id = t1.author_id
			LEFT JOIN languages as t4 
			ON t4.language_id = t2.language
			ORDER BY t1.date_posted DESC";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			$notes = array();
			while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$row['function_language'] = new Language($row);
				$row['function'] = new LanguageFunction($row);
				$row['author'] = new User($row);
				$function_note = new FunctionNote($row);
				$function_note->counter = $row['counter'];
				$notes[] = $function_note;
			}
			http_response_code(200);
			echo json_encode($notes, JSON_PRETTY_PRINT);
		}
		else {
			$sql = "SELECT t1.note_text, t1.author_id as `user_id`, t2.function_id, t2.function_name, DATE_FORMAT(t1.date_posted, '') as `date`, t3.username, t3.avatar, t3.points, t5.counter
			FROM function_notes as t1
			INNER JOIN functions as t2 
			ON t2.function_id = t1.function_id
			INNER JOIN (
				SELECT t1.function_id, COUNT(t1.note_id) as `counter`
				FROM function_notes as t1
				GROUP BY t1.function_id
			) as t5
			ON t5.function_id = t2.function_id
			LEFT JOIN users as t3 
			ON t3.user_id = t1.author_id
			WHERE t2.language = '$language_id'
			ORDER BY t1.date_posted DESC";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			$notes = array();
			while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$row['function'] = new LanguageFunction($row);
				$row['author'] = new User($row);
				$function_note = new FunctionNote($row);
				$function_note->counter = $row['counter'];
				$notes[] = $function_note;
			}
			http_response_code(200);
			echo json_encode($notes, JSON_PRETTY_PRINT);

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