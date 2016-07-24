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
//$language_id = 4;
try {
	if (is_numeric($language_id)) {
		$mysqli = Database::connection();
		if ($language_id == 11) {
			$sql = "SELECT t1.function_id, t1.function_name, t1.language as `language_id`, t2.recent_posted, t3.language_name, t4.notes
			FROM functions as t1
			RIGHT JOIN (
				SELECT t1.function_id, MAX(t1.date_posted) as `recent_posted`
				FROM function_notes as t1
				GROUP BY t1.function_id
			) as t2
			ON t2.function_id = t1.function_id
			INNER JOIN (
				SELECT t1.function_id, COUNT(*) as `notes`
				FROM function_notes as t1
				GROUP BY t1.function_id
			) as t4
			ON t4.function_id = t1.function_id
			INNER JOIN languages as t3 
			ON t3.language_id = t1.language
			ORDER BY t2.recent_posted DESC
			LIMIT 10";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			$notes = array();
			while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$row['function_language'] = new Language($row);
				$function = new LanguageFunction($row);
				$function->notes = $row['notes'];
				$notes[] = $function;

			}
			http_response_code(200);
			echo json_encode($notes, JSON_PRETTY_PRINT);
		}
		else {
			$sql = "SELECT t1.function_id, t1.function_name, t1.language as `language_id`, t2.recent_posted, t3.language_name, t4.notes
			FROM functions as t1
			RIGHT JOIN (
				SELECT t1.function_id, MAX(t1.date_posted) as `recent_posted`
				FROM function_notes as t1
				GROUP BY t1.function_id
			) as t2
			ON t2.function_id = t1.function_id
			INNER JOIN (
				SELECT t1.function_id, COUNT(*) as `notes`
				FROM function_notes as t1
				GROUP BY t1.function_id
			) as t4
			ON t4.function_id = t1.function_id
			INNER JOIN languages as t3 
			ON t3.language_id = t1.language
			WHERE t1.language = '$language_id'
			ORDER BY t2.recent_posted DESC
			LIMIT 10";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			$notes = array();
			while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$row['function_language'] = new Language($row);
				$function = new LanguageFunction($row);
				$function->notes = $row['notes'];
				$notes[] = $function;
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